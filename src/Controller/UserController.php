<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Plan;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
 
    #[Route('/subscription', name: 'app_user_subscription')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $plans = $doctrine->getRepository(Plan::class)->findAll();
        return $this->render('stripe/index.html.twig', [
            'plans' => $plans,
        ]);
    }
}
