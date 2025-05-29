<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Publication;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\PublicationRepository;
use App\Repository\UserRepository;
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
        $comments = $commentRepository->findAll();

        $data = [];
        foreach ($comments as $comment) {
            $data[] = [
                'id' => $comment->getId(),
                'content' => $comment->getCommentContent(),
                'date' => $comment->getCommentDate()->format('Y-m-d H:i:s'),
                'user_id' => $comment->getUsers()->getId(),
                'publication_id' => $comment->getPublication()->getId(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'comment_show', methods: ['GET'])]
    public function show(Comment $comment): JsonResponse
    {
        return $this->json([
            'id' => $comment->getId(),
            'content' => $comment->getCommentContent(),
            'date' => $comment->getCommentDate()->format('Y-m-d H:i:s'),
            'user_id' => $comment->getUsers()->getId(),
            'publication_id' => $comment->getPublication()->getId(),
        ]);
    }

    #[Route('/', name: 'comment_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo, PublicationRepository $pubRepo): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = $userRepo->find($data['user_id'] ?? null);
        $publication = $pubRepo->find($data['publication_id'] ?? null);

        if (!$user || !$publication) {
            return $this->json(['error' => 'User or Publication not found'], 404);
        }

        $comment = new Comment();
        $comment->setCommentContent($data['content'] ?? '');
        $comment->setCommentDate(new \DateTime());
        $comment->setUsers($user);
        $comment->setPublication($publication);

        $em->persist($comment);
        $em->flush();

        return $this->json(['message' => 'Comment created', 'id' => $comment->getId()], 201);
    }

    #[Route('/{id}', name: 'comment_update', methods: ['PUT'])]
    public function update(Comment $comment, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['content'])) {
            $comment->setCommentContent($data['content']);
        }

        $em->flush();

        return $this->json(['message' => 'Comment updated']);
    }

    #[Route('/{id}', name: 'comment_delete', methods: ['DELETE'])]
    public function delete(Comment $comment, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($comment);
        $em->flush();

        return $this->json(['message' => 'Comment deleted']);
    }
}
