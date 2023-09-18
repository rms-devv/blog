<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        MailerInterface $mailer
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
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

    #[Route('/monprofil/{id<\d+>}', name: 'app_user_show')]
    public function show(
        User $user,
        EntityManagerInterface $entityManager
    ): Response {
        $articles = $user->getArticles();


        return $this->render('user/show.html.twig', [
            'user' => $user,
            'articles' => $articles,
        ]);
    }

    #[Route('/monprofil/download', name: 'app_user_article_download')]
    public function download(User $user): BinaryFileResponse
    {
        $articles = $user->getArticles();

        if (empty($articles)) {
            return new Response('Aucun article à exporter.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 2;
        foreach ($articles as $article) {
            $sheet->setCellValue('A' . $row, $article->getTitle());
            $sheet->setCellValue('B' . $row, $article->getContent());
            $row++; //

        }
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="mes_articles.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
    }



    #[Route('/user/edit/{id<\d+>}', name: 'app_user_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        User $user,
        UserPasswordHasherInterface $userPasswordHasher,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('image')->getData();


            if ($brochureFile) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

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
                $user->setImage("$newFilename");
            }
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
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
