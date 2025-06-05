<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/comments')]
class CommentController extends AbstractController
{
    #[Route('', name: 'comment_get', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $comments = $commentRepository->findBy([], ['commentDate' => 'DESC']);
        $data = [];

        foreach ($comments as $comment) {
            $user = $comment->getUsers();
            $publication = $comment->getPublication();

            $data[] = [
                'id'             => $comment->getId(),
                'content'        => $comment->getCommentContent(),
                'date'           => $comment->getCommentDate()->format('Y-m-d H:i:s'),
                'user_id'        => $user ? $user->getId() : null,
                'username'       => $user ? $user->getUsername() : 'Usuario Desconocido',
                'email'          => $user ? $user->getEmail() : 'email@desconocido.com',
                'publication_id' => $publication ? $publication->getId() : null,
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'comment_show', methods: ['GET'])]
    public function show(Comment $comment): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $comment->getUsers();
        $publication = $comment->getPublication();

        return $this->json([
            'id'             => $comment->getId(),
            'content'        => $comment->getCommentContent(),
            'date'           => $comment->getCommentDate()->format('Y-m-d H:i:s'),
            'user_id'        => $user ? $user->getId() : null,
            'username'       => $user ? $user->getUsername() : 'Usuario Desconocido',
            'email'          => $user ? $user->getEmail() : 'email@desconocido.com',
            'publication_id' => $publication ? $publication->getId() : null,
        ]);
    }

    #[Route('', name: 'comment_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, PublicationRepository $pubRepo): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        $content = $data['content'] ?? '';
        $publicationId = $data['publication_id'] ?? null;

        if (trim($content) === '') {
            return $this->json(['error' => 'El contenido es obligatorio'], 400);
        }

        if (!$publicationId) {
            return $this->json(['error' => 'El ID de la publicación es obligatorio'], 400);
        }

        $publication = $pubRepo->find($publicationId);
        if (!$publication) {
            return $this->json(['error' => 'Publicación no encontrada'], 404);
        }

        $comment = new Comment();
        $comment->setCommentContent($content);
        $comment->setCommentDate(new \DateTime());
        $comment->setUsers($user);
        $comment->setPublication($publication);

        $em->persist($comment);
        $em->flush();

        return $this->json(['message' => 'Comentario creado', 'id' => $comment->getId()], 201);
    }

    #[Route('/{id}', name: 'comment_update', methods: ['PUT'])]
    public function update(Comment $comment, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $data = json_decode($request->getContent(), true);

        if (isset($data['content']) && trim($data['content']) !== '') {
            $comment->setCommentContent($data['content']);
        } else {
            return $this->json(['error' => 'El contenido es obligatorio'], 400);
        }

        $em->flush();

        return $this->json(['message' => 'Comentario actualizado']);
    }

    #[Route('/{id}', name: 'comment_delete', methods: ['DELETE'])]
    public function delete(Comment $comment, EntityManagerInterface $em): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em->remove($comment);
        $em->flush();

        return $this->json(['message' => 'Comentario eliminado']);
    }
}
