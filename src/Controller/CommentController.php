<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Form\CommentType;
use App\Repository\CommentsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(
        CommentsRepository $commentsRepository
    ): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentsRepository->findAll(),
        ]);
    }

    #[Route('/comment/new', name: 'app_comment_new')]
    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        $comment = new Comments();

        $user = $this->getUser();

        if ($user) {
            $comment->setUser($user);
        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_show', ['id' => $comment->getId()]);
        }

        return $this->render('comment/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
