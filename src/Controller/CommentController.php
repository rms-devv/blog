<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Comments;
use App\Form\CommentType;
use App\Repository\CommentsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(
        CommentsRepository $commentsRepository
    ): Response {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentsRepository->findAll(),
        ]);
    }

    #[Route('/comment/delete/{id<\d+>}', name: 'app_comment_delete')]

    public function delete(
        Comments $comment,
        EntityManagerInterface $em,
    ): Response {

        if ($this->getUser() != $comment->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $em->remove($comment);
        $em->flush();

        $this->addFlash('notice', 'Commentaire supprimé');

        return $this->redirectToRoute('app_article_show', [
            'id' => $comment->getArticle()->getId(),
        ]);
    }

    #[Route('/comment/edit/{id<\d+>}', name: 'app_comment_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        Comments $comment,

    ): Response {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();
            $this->addFlash('notice', 'Commentaire modifié');
            return $this->redirectToRoute('app_article_show', [
                'id' => $comment->getArticle()->getId()

            ]);
        }

        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form,
        ]);
    }
}
