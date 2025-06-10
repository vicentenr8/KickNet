<?php

namespace App\Controller;

use App\Entity\Forecast;
use App\Entity\Game;
use App\Entity\Team;
use App\Repository\ForecastRepository;
use App\Repository\GameRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/forecast', name: 'api_forecast_')]
class ForecastController extends AbstractController
{
    private $em;
    private $forecastRepository;
    private $gameRepository;
    private $teamRepository;

    public function __construct(EntityManagerInterface $em, ForecastRepository $forecastRepository, GameRepository $gameRepository, TeamRepository $teamRepository)
    {
        $this->em = $em;
        $this->forecastRepository = $forecastRepository;
        $this->gameRepository = $gameRepository;
        $this->teamRepository = $teamRepository;
    }
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Comprueba campos obligatorios mínimos
        if (!isset($data['result'], $data['game_id'], $data['competition'], $data['localTeamName'], $data['awayTeamName'])) {
            return $this->json(['error' => 'Campos requeridos faltantes'], 400);
        }

        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Usuario no autenticado'], 401);
        }

        // Extraemos datos del request
        $gameId = $data['game_id'];
        $competition = $data['competition'];
        $localTeamName = $data['localTeamName'];
        $awayTeamName = $data['awayTeamName'];

        // Buscamos partido existente
        $game = $this->gameRepository->findOneBy(['externalGameId' => $gameId]);

        if (!$game) {
            // Buscar o crear equipos
            $localteam = $this->teamRepository->findOneBy(['teamName' => $localTeamName]);
            if (!$localteam) {
                $localteam = new Team();
                $localteam->setTeamName($localTeamName);
                $localteam->setCountry('Desconocido'); // Pon algo por defecto o lo que tengas
                $localteam->setLeague('Desconocida');
                $this->em->persist($localteam);
                $this->em->flush(); // flush para asegurar ID
            }

            $awayteam = $this->teamRepository->findOneBy(['teamName' => $awayTeamName]);
            if (!$awayteam) {
                $awayteam = new Team();
                $awayteam->setTeamName($awayTeamName);
                $awayteam->setCountry('Desconocido');
                $awayteam->setLeague('Desconocida');
                $this->em->persist($awayteam);
                $this->em->flush();
            }

            // Crear partido nuevo
            $game = new Game();
            $game->setExternalGameId($gameId);
            $game->setGameDate(new \DateTime());
            $game->setCompetition($competition);
            $game->setLocalTeam($localteam);
            $game->setAwayTeam($awayteam);

            $this->em->persist($game);
            $this->em->flush();
        }

        // Verificar si usuario ya votó este partido
        $existingForecast = $this->forecastRepository->findOneBy([
            'users' => $user,
            'game' => $game
        ]);

        if ($existingForecast) {
            return $this->json(['error' => 'Ya has votado en este partido'], 409);
        }

        // Crear pronóstico
        $forecast = new Forecast();
        $forecast->setResult($data['result']);
        $forecast->setGame($game);
        $forecast->setUsers($user);
        $forecast->setForecastDate(new \DateTime());

        $this->em->persist($forecast);
        $this->em->flush();

        return $this->json(['status' => 'Pronóstico guardado correctamente'], 201);
    }


    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $forecasts = $this->forecastRepository->findAll();
        return $this->json($forecasts);
    }

    #[Route('/votes/{gameId}', name: 'votes', methods: ['GET'])]
    public function getVotes(int $gameId): JsonResponse
    {
        $game = $this->gameRepository->findOneBy(['externalGameId' => $gameId]);
        if (!$game) {
            return $this->json(['error' => 'Partido no encontrado'], 404);
        }

        $forecasts = $this->forecastRepository->findBy(['game' => $game]);

        $counts = ['1' => 0, 'X' => 0, '2' => 0];
        $total = count($forecasts);

        foreach ($forecasts as $forecast) {
            $result = $forecast->getResult();
            if (isset($counts[$result])) {
                $counts[$result]++;
            }
        }

        $percentages = [];
        foreach ($counts as $key => $count) {
            $percentages[$key] = $total > 0 ? round(($count / $total) * 100) : 0;
        }

        return $this->json([
            'game_id' => $gameId,
            'votes' => $counts,
            'percentages' => $percentages,
            'totalVotes' => $total
        ]);
    }

    #[Route('/user-votes', name: 'user_votes', methods: ['GET'])]
    public function getUserVotes(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Usuario no autenticado'], 401);
        }

        $forecasts = $this->forecastRepository->findBy(['users' => $user]);
        $gameIds = array_map(function ($forecast) {
            return $forecast->getGame()->getExternalGameId();
        }, $forecasts);

        return $this->json($gameIds);
    }
}
