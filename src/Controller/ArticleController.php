<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

// Liste entiers des articles 
class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(
        ArticleRepository $ArticleRepository,
    ): Response {

        return $this->render('article/index.html.twig', [
            'articles' => $ArticleRepository->findAll(),

        ]);
    }

    // Voir tous les articles d'une categorie séléctionnée 
    #[Route('/categorie/article/{id<\d+>}', name: 'app_article_show_by_categorie_id')]
    public function showArticlesByCategory(
        EntityManagerInterface $entityManager,
        Categorie $categorie
    ) {
        $articles = $entityManager->getRepository(Article::class)->findBy(
            ['Categorie' => $categorie->getId()],
            ['title' => 'ASC']
        );

        return $this->render('article/index.html.twig', [
            'articles' => $articles,
            'categorie' => $categorie,

        ]);
    }

    // Voir le detail d'un article 
    #[Route('/article/{id<\d+>}', name: 'app_article_show')]
    public function show(
        Article $article,
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ) {
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
            $user = $comment->getArticle()->getUser();
            $mail = $user->getEmail();
            $email = (new Email())
                ->from('admin@admin.com')
                ->to($mail)
                ->subject('Nouveau commentaire')
                ->text('Un commentaire a été ajouté a votre article !')
                ->html('<p>Un commentaire a été ajouté a votre article !</p>');
            $mailer->send($email);

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView(),
            'comments' => $comments
        ]);
    }


    // Nouvelle article
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

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();


                try {
                    $brochureFile->move(
                        $this->getParameter('article_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }


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

    // Supprimer un article
    #[Route('/article/delete/{id<\d+>}', name: 'app_article_delete')]
    public function delete(
        Article $article,
        EntityManagerInterface $em
    ): Response {
        if ($this->getUser() == $article->getUser()) {
            $em->remove($article);
            $em->flush();
            $this->addFlash('notice', 'Article supprimé');

            return $this->redirectToRoute('app_article');
        }

        return $this->redirectToRoute('app_article');
    }

    // Modifier un article
    #[Route('/article/edit/{id<\d+>}', name: 'app_article_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        Article $article,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('image')->getData();


            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                try {
                    $brochureFile->move(
                        $this->getParameter('article_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }

                $article->setImage("$newFilename");
            }
            $em->flush();
            $this->addFlash('notice', 'Article modifié');

            return $this->redirectToRoute('app_article_show', ['id' => $article->getId()]);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }
}
