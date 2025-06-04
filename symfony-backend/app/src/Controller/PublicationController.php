<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Entity\User;
use App\Repository\PublicationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/publications')]
class PublicationController extends AbstractController
{
    #[Route('', name: 'publication_get', methods: ['GET'])]
    public function index(PublicationRepository $publicationRepo): JsonResponse
    {
        $publications = $publicationRepo->findBy([], ['publicationDate' => 'DESC']);

        $data = [];

        foreach ($publications as $publication) {
            $user = $publication->getUsers(); // Get the User object
            $data[] = [
                'id' => $publication->getId(),
                'content' => $publication->getContent(),
                'date' => $publication->getPublicationDate()->format('Y-m-d H:i:s'),
                'user_id' => $user ? $user->getId() : null,
                'username' => $user ? $user->getUsername() : 'Usuario Desconocido',
                'email' => $user ? $user->getEmail() : 'email@desconocido.com',
                'comments' => array_map(function ($comment) {
                    $commentUser = $comment->getUsers(); // Get the Comment's User object
                    return [
                        'id' => $comment->getId(),
                        'content' => $comment->getCommentContent(),
                        'date' => $comment->getCommentDate()->format('Y-m-d H:i:s'),
                        // Safely get comment user_id
                        'user_id' => $commentUser ? $commentUser->getId() : null,
                        'username' => $commentUser ? $commentUser->getUsername() : 'Anónimo',
                        'email' => $commentUser ? $commentUser->getEmail() : 'anonimo@ejemplo.com',
                    ];
                }, $publication->getComments()->toArray())
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'publication_show', methods: ['GET'])]
    public function show(Publication $publication): JsonResponse
    {
        $comments = [];

        foreach ($publication->getComments() as $comment) {
            $comments[] = [
                'id' => $comment->getId(),
                'content' => $comment->getCommentContent(),
                'date' => $comment->getCommentDate()->format('Y-m-d H:i:s'),
                'user_id' => $comment->getUsers()->getId(),
            ];
        }

        return $this->json([
            'id' => $publication->getId(),
            'content' => $publication->getContent(),
            'date' => $publication->getPublicationDate()->format('Y-m-d H:i:s'),
            'user_id' => $publication->getUsers()->getId(),
            'comments' => $comments,
        ]);
    }

    #[Route('', name: 'publication_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $userRepo->find($data['user_id'] ?? null);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $publication = new Publication();
        $publication->setContent($data['content'] ?? '');
        $publication->setPublicationDate(new \DateTime());
        $publication->setUsers($user);

        $em->persist($publication);
        $em->flush();

        return $this->json(['message' => 'Publication created', 'id' => $publication->getId()], 201);
    }

    #[Route('/{id}', name: 'publication_update', methods: ['PUT'])]
    public function update(Publication $publication, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['content'])) {
            $publication->setContent($data['content']);
        }

        $em->flush();

        return $this->json(['message' => 'Publication updated']);
    }

    #[Route('/{id}', name: 'publication_delete', methods: ['DELETE'])]
    public function delete(Publication $publication, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($publication);
        $em->flush();

        return $this->json(['message' => 'Publication deleted']);
    }
}
