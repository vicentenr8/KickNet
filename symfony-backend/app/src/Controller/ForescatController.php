<?php

namespace App\Controller;

use App\Entity\Forecast;
use App\Repository\ForecastRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/forecasts')]
class ForecastController extends AbstractController
{
    #[Route('', name: 'forecast_list', methods: ['GET'])]
    public function list(ForecastRepository $forecastRepository): JsonResponse
    {
        $forecasts = $forecastRepository->findAll();

        $data = [];
        foreach ($forecasts as $forecast) {
            $data[] = [
                'id' => $forecast->getId(),
                'result' => $forecast->getResult(),
                'forecastDate' => $forecast->getForecastDate()->format('Y-m-d H:i:s'),
                'user_id' => $forecast->getUsers()->getId(),
            ];
        }

        return $this->json($data);
    }

    #[Route('', name: 'forecast_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $userRepository->find($data['user_id'] ?? null);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        if (!isset($data['result'])) {
            return $this->json(['error' => 'Result is required'], 400);
        }

        $forecast = new Forecast();
        $forecast->setResult($data['result']);
        $forecast->setForecastDate(new \DateTime()); // Fecha actual, o puedes aceptar una fecha del cliente si quieres
        $forecast->setUsers($user);

        $em->persist($forecast);
        $em->flush();

        return $this->json([
            'message' => 'Forecast created',
            'id' => $forecast->getId()
        ], 201);
    }
}
