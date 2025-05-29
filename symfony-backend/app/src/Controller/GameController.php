<?php

namespace App\Controller;

use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/games')]
class GameController extends AbstractController
{
    #[Route('/by-league', name: 'game_by_league', methods: ['GET'])]
    public function getGamesByLeagueAndSeason(Request $request, GameRepository $gameRepository): JsonResponse
    {
        $league = $request->query->get('league');   // ej: "Premier League"
        $season = $request->query->get('season');   // ej: "2023"

        if (!$league || !$season) {
            return $this->json(['error' => 'Missing league or season parameter'], 400);
        }

        // Aquí asumimos que tienes un campo 'competition' para liga
        // y que 'gameDate' contiene la fecha para filtrar por temporada

        $startDate = new \DateTime("{$season}-01-01 00:00:00");
        $endDate = new \DateTime("{$season}-12-31 23:59:59");

        $games = $gameRepository->createQueryBuilder('g')
            ->andWhere('g.competition = :league')
            ->andWhere('g.gameDate BETWEEN :start AND :end')
            ->setParameters([
                'league' => $league,
                'start' => $startDate,
                'end' => $endDate,
            ])
            ->orderBy('g.gameDate', 'ASC')
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($games as $game) {
            $data[] = [
                'id' => $game->getId(),
                'gameDate' => $game->getGameDate()->format('Y-m-d H:i:s'),
                'competition' => $game->getCompetition(),
                'localTeam' => [
                    'id' => $game->getLocalTeam()?->getId(),
                    'name' => $game->getLocalTeam()?->getTeamName(),
                ],
                'awayTeam' => [
                    'id' => $game->getAwayTeam()?->getId(),
                    'name' => $game->getAwayTeam()?->getTeamName(),
                ],
            ];
        }

        return $this->json($data);
    }
}
