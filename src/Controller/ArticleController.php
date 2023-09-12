<?php

namespace App\Controller;
use App\Entity\Article;
use App\Entity\Comments;
use App\Form\CommentType;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(
        ArticleRepository $ArticleRepository
    ): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $ArticleRepository->findAll(),
        ]);
    }

    #[Route('/article/{id<\d+>}', name: 'app_article_show')]
    public function show(Article $article, Request $request, EntityManagerInterface $entityManager)
    {
 
        $comment = new Comments();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $comment = $article->getComments();
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if ($user) {
                $comment->setTitle();
            }
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }
        
        return $this->render('article/show.html.twig', [
            'articles' => $article,
            'commentForm' => $form->createView(),
            'comments' => $comment
        ]);
    }

    #[Route('/article/new', name: 'app_article_new')]
    public function create(Request $request, EntityManagerInterface $entityManager)
    {
        $article = new Article();
        $user = $this->getUser();
        if ($user) {
            $article->setUser($user);
        }
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('app_article', ['id' => $article->getId()]);
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
}
