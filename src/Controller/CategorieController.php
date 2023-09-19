<?php

namespace App\Controller;
use App\Entity\Categorie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;

class CategorieController extends AbstractController
{
    #[Route('/', name: 'app_categorie')]
    public function index(
        CategorieRepository $CategorieRepository,
    ): Response
    {
        return $this->render('categorie/index.html.twig', [
            'categories' => $CategorieRepository->findAll(),
        ]);
    }

}
