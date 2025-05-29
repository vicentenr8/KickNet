<?php

namespace App\Controller;

use App\Entity\Forecast;
use App\Entity\Game;
use App\Repository\ForecastRepository;
use App\Repository\GameRepository;
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

    public function __construct(EntityManagerInterface $em, ForecastRepository $forecastRepository, GameRepository $gameRepository)
    {
        $this->em = $em;
        $this->forecastRepository = $forecastRepository;
        $this->gameRepository = $gameRepository;
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validar campos mínimos
        if (!isset($data['result'], $data['user_id'], $data['game_id'])) {
            return $this->json(['error' => 'Campos requeridos faltantes'], 400);
        }

        $game = $this->gameRepository->find($data['game_id']);
        if (!$game) {
            return $this->json(['error' => 'Partido no encontrado'], 404);
        }

        $user = $this->getDoctrine()->getRepository('App:User')->find($data['user_id']);
        if (!$user) {
            return $this->json(['error' => 'Usuario no encontrado'], 404);
        }

        $forecast = new Forecast();
        $forecast->setResult($data['result']);
        $forecast->setUsers($user);
        $forecast->setGame($game);
        $forecast->setForecastDate(new \DateTime());

        $this->em->persist($forecast);
        $this->em->flush();

        return $this->json(['status' => 'Pronóstico guardado correctamente'], 201);
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $forecasts = $this->forecastRepository->findAll();
        // Aquí puedes mapear y devolver solo lo que quieras

        return $this->json($forecasts);
    }
}
?>