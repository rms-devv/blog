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
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
        $comments = $article->getComments();
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $comment->setArticle($article); 
            $user = $this->getUser();
            if ($user) {
                $comment->setUser($user);
                
            }
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }
    
        return $this->render('article/show.html.twig', [
            'article' => $article, 
            'commentForm' => $form->createView(),
            'comments' => $comments
        ]);
    }
    

    #[Route('/article/new', name: 'app_article_new')]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        $article = new Article();
        $user = $this->getUser();
        if ($user) {
            $article->setUser($user);
        }
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('image')->getData();


            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('article_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $article->setImage("$newFilename");
           
            }
            $entityManager->persist($article);
            $entityManager->flush();
            $this->addFlash('notice', 'Article ajouté');
            return $this->redirectToRoute('app_article', ['id' => $article->getId()]);
          
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/article/delete/{id<\d+>}', name: 'app_article_delete')]
    public function delete(
        Article $article,
        EntityManagerInterface $em
    ): Response 
    {   
       if ($this->getUser() == $article->getUser()) {
        $em->remove($article);
        $em->flush();
        $this->addFlash('notice', 'Commentaire supprimé');
        return $this->redirectToRoute('app_article');
        }

        return $this->redirectToRoute('app_article');
    }

    #[Route('/article/edit/{id<\d+>}', name: 'app_article_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        Article $article
    ): Response 
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('notice', 'Article modifié');
            return $this->redirectToRoute('app_article');
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }
    
}
