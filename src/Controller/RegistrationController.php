<?php

namespace App\Controller;
use App\Entity\Plan;
use App\Entity\User;
use App\Entity\Subscription;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpFoundation\StreamedResponse;
class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $mail = $user->getEmail();
            $email = (new Email())
                ->from('admin@admin.com')
                ->to($mail)
                ->subject('Inscription validée!')
                ->text('Merci pour votre inscription et bienvenue sur notre blog!')
                ->html('<p>Merci pour votre inscription et bienvenue sur notre blog!</p>');
            $entityManager->persist($user);
            $entityManager->flush();
            $mailer->send($email);

            return $this->redirectToRoute('app_categorie');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/monprofil', name: 'app_user_show')]
    public function show(
        #[CurrentUser()]
        User $user,
        ManagerRegistry $doctrine
    ): Response 
    {   $plans = $doctrine->getRepository(Plan::class)->findAll();
        $activeSub = $doctrine->getRepository(Subscription::class)->findActiveSub($this->getUser()->getId());
        $articles = $user->getArticles();

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'articles' => $articles,
            'activeSub' => $activeSub,
            'plans' => $plans,
        ]);
    }

    #[Route('/monprofil/download/{id}', name: 'app_user_article_download')]
    public function download(User $user): StreamedResponse
    {
        $articles = $user->getArticles();
     
        if (empty($articles)) {
            return new Response('Aucun article à exporter.');
        }
    
        $response = new StreamedResponse(function () use ($articles) {
            $file = fopen('php://output', 'w');
    
            fputcsv($file, ['Title', 'Content']);

            foreach ($articles as $article) {
                fputcsv($file, [$article->getTitle(), $article->getContent()]);
            }
    
            fclose($file);
        });
    
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="mes_articles.csv"');
    
        return $response;
    }

    #[Route('/user/edit', name: 'app_user_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        #[CurrentUser()]
        User $user,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(RegistrationFormType::class, $user);
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
                $user->setImage("$newFilename");
            }
            
            $em->flush();
            $this->addFlash('notice', 'Profil modifié');
            return $this->redirectToRoute('app_article');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    
}
