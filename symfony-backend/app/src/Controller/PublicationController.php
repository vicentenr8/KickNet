<?php

namespace App\Controller;

use App\Entity\Publication;
use App\Repository\PublicationRepository;
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $publications = $publicationRepo->findBy([], ['publicationDate' => 'DESC']);
        $data = [];

        foreach ($publications as $publication) {
            $user = $publication->getUsers();
            $data[] = [
                'id'       => $publication->getId(),
                'content'  => $publication->getContent(),
                'date'     => $publication->getPublicationDate()->format('Y-m-d H:i:s'),
                'user_id'  => $user ? $user->getId() : null,
                'username' => $user ? $user->getUsername() : 'Usuario Desconocido',
                'email'    => $user ? $user->getEmail() : 'email@desconocido.com',
                'comments' => array_map(function ($comment) {
                    $commentUser = $comment->getUsers();
                    return [
                        'id'       => $comment->getId(),
                        'content'  => $comment->getCommentContent(),
                        'date'     => $comment->getCommentDate()->format('Y-m-d H:i:s'),
                        'user_id'  => $commentUser ? $commentUser->getId() : null,
                        'username' => $commentUser ? $commentUser->getUsername() : 'Anónimo',
                        'email'    => $commentUser ? $commentUser->getEmail() : 'anonimo@ejemplo.com',
                    ];
                }, $publication->getComments()->toArray()),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'publication_show', methods: ['GET'])]
    public function show(Publication $publication): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $comments = [];
        foreach ($publication->getComments() as $comment) {
            $commentUser = $comment->getUsers();
            $comments[] = [
                'id'       => $comment->getId(),
                'content'  => $comment->getCommentContent(),
                'date'     => $comment->getCommentDate()->format('Y-m-d H:i:s'),
                'user_id'  => $commentUser ? $commentUser->getId() : null,
                'username' => $commentUser ? $commentUser->getUsername() : 'Anónimo',
                'email'    => $commentUser ? $commentUser->getEmail() : 'anonimo@ejemplo.com',
            ];
        }

        $user = $publication->getUsers();

        return $this->json([
            'id'       => $publication->getId(),
            'content'  => $publication->getContent(),
            'date'     => $publication->getPublicationDate()->format('Y-m-d H:i:s'),
            'user_id'  => $user ? $user->getId() : null,
            'username' => $user ? $user->getUsername() : 'Usuario Desconocido',
            'email'    => $user ? $user->getEmail() : 'email@desconocido.com',
            'comments' => $comments,
        ]);
    }

    #[Route('', name: 'publication_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Obtenemos el usuario directamente del token validado
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);
        $content = $data['content'] ?? '';
        if (trim($content) === '') {
            return $this->json(['error' => 'Falta contenido'], 400);
        }

        $publication = new Publication();
        $publication->setContent($content);
        $publication->setPublicationDate(new \DateTime());
        $publication->setUsers($user);

        $em->persist($publication);
        $em->flush();

        return $this->json(['message' => 'Publication created', 'id' => $publication->getId()], 201);
    }

    #[Route('/{id}', name: 'publication_update', methods: ['PUT'])]
    public function update(Publication $publication, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em->remove($publication);
        $em->flush();

        return $this->json(['message' => 'Publication deleted']);
    }
}