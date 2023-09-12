<?php

namespace App\Controller;

use App\Entity\Comments;
use App\Form\CommentType;
use App\Repository\CommentsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Comment;
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

    #[Route('/comment/delete/{id<\d+>}', name: 'app_comment_delete')]
    public function delete(
        Comments $comment,
        EntityManagerInterface $em,

    ): Response 
    {   
        
       if ($this->getUser() == $comment->getUser()) {
        $em->remove($comment);
        $em->flush();
        
        $this->addFlash('notice', 'Commentaire supprimé');
        
        return $this->redirectToRoute('app_article_show', [
            'id' => $comment->getArticle()->getId()
            
        ]);
        
        }

        return $this->redirectToRoute('app_article_show');
    }
}

