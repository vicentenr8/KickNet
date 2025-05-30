<?php

namespace App\Controller;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/teams')]
class TeamController extends AbstractController
{
    #[Route('', name: 'team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): JsonResponse
    {
        $teams = $teamRepository->findAll();

        $data = [];
        foreach ($teams as $team) {
            $data[] = [
                'id' => $team->getId(),
                'teamName' => $team->getTeamName(),
                'country' => $team->getCountry(),
                'league' => $team->getLeague(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'team_show', methods: ['GET'])]
    public function show(Team $team): JsonResponse
    {
        $data = [
            'id' => $team->getId(),
            'teamName' => $team->getTeamName(),
            'country' => $team->getCountry(),
            'league' => $team->getLeague(),
        ];

        return $this->json($data);
    }

    #[Route('', name: 'team_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $team = new Team();
        $team->setTeamName($data['teamName'] ?? '');
        $team->setCountry($data['country'] ?? '');
        $team->setLeague($data['league'] ?? '');

        $em->persist($team);
        $em->flush();

        return $this->json(['message' => 'Team created successfully', 'id' => $team->getId()], 201);
    }

    #[Route('/{id}', name: 'team_update', methods: ['PUT'])]
    public function update(Request $request, Team $team, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $team->setTeamName($data['teamName'] ?? $team->getTeamName());
        $team->setCountry($data['country'] ?? $team->getCountry());
        $team->setLeague($data['league'] ?? $team->getLeague());

        $em->flush();

        return $this->json(['message' => 'Team updated successfully']);
    }

    #[Route('/{id}', name: 'team_delete', methods: ['DELETE'])]
    public function delete(Team $team, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($team);
        $em->flush();

        return $this->json(['message' => 'Team deleted successfully']);
    }
}
