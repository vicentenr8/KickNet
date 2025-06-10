<?php

namespace App\Controller;

use App\Entity\Team;
use App\Repository\GameRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/games')]
class GameController extends AbstractController
{
    private $em;
    private $gameRepository;
    private $teamRepository;

    public function __construct(EntityManagerInterface $em, GameRepository $gameRepository, TeamRepository $teamRepository)
    {
        $this->em = $em;
        $this->gameRepository = $gameRepository;
        $this->teamRepository = $teamRepository;
    }

    #[Route('/get-or-create/{externalGameId}', name: 'game_get_or_create', methods: ['GET'])]
    public function getOrCreateGame(int $externalGameId): JsonResponse
    {
        $game = $this->gameRepository->findOneBy(['externalGameId' => $data['game_id']]);

        if (!$game) {
            $competition = $data['competition'] ?? 'Competición X';
            $localTeamName = $data['localTeamName'] ?? 'Equipo Local';
            $awayTeamName = $data['awayTeamName'] ?? 'Equipo Visitante';
        
            $localteam = $this->teamRepository->findOneBy(['teamName' => $localTeamName]);
            if (!$localteam) {
                $localteam = new Team();
                $localteam->setTeamName($localTeamName);
                $localteam->setCountry('Desconocido');
                $localteam->setLeague('Desconocida');
                $this->em->persist($localteam);
                $this->em->flush();  // flush aquí
            }
        
            $awayteam = $this->teamRepository->findOneBy(['teamName' => $awayTeamName]);
            if (!$awayteam) {
                $awayteam = new Team();
                $awayteam->setTeamName($awayTeamName);
                $awayteam->setCountry('Desconocido');
                $awayteam->setLeague('Desconocida');
                $this->em->persist($awayteam);
                $this->em->flush();  // flush aquí
            }
        
            $game = new Game();
            $game->setExternalGameId($data['game_id']);
            $game->setGameDate(new \DateTime());
            $game->setCompetition($competition);
            $game->setLocalTeam($localteam);
            $game->setAwayTeam($awayteam);
        
            $this->em->persist($game);
            $this->em->flush();
        }
        
        return $this->json([
            'id' => $game->getId(),
            'externalGameId' => $game->getExternalGameId(),
            'gameDate' => $game->getGameDate()->format('Y-m-d H:i:s'),
            'competition' => $game->getCompetition(),
            'localTeam' => $game->getLocalTeam()->getTeamName(),
            'awayTeam' => $game->getAwayTeam()->getTeamName(),
        ]);
    }
}
