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
    #[Route('/get-or-create/{externalGameId}', name: 'game_get_or_create', methods: ['GET'])]
public function getOrCreateGame(
    int $externalGameId, 
    GameRepository $gameRepository,
    EntityManagerInterface $em
): JsonResponse
{
    // Buscar juego existente
    $game = $gameRepository->findOneByExternalGameId($externalGameId);

    if (!$game) {
        $externalGameData = [
            'gameDate' => new \DateTime(), // fecha actual por ejemplo
            'competition' => 'Premier League',
            'localTeamName' => 'Team A',
            'awayTeamName' => 'Team B',
        ];

        // Crear nuevo objeto Game y setear campos con datos externos
        $game = new \App\Entity\Game();
        $game->setExternalGameId($externalGameId);
        $game->setGameDate($externalGameData['gameDate']);
        $game->setCompetition($externalGameData['competition']);

        $em->persist($game);
        $em->flush();
    }

    // Devolver el partido, puede ser para usar luego en el pronóstico
    return $this->json([
        'id' => $game->getId(),
        'externalGameId' => $game->getExternalGameId(),
        'gameDate' => $game->getGameDate()->format('Y-m-d H:i:s'),
        'competition' => $game->getCompetition(),
        // Añade lo que necesites
    ]);
}

}
