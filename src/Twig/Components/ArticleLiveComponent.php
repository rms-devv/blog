<?php

namespace App\Twig\Components;

use App\Repository\ArticleRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent()]
final class ArticleLiveComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $q = "";

    public function __construct(private ArticleRepository $articleRepository) 
    {   
    }

    public function getArticles() : array 
    {
        return $this->articleRepository->createQueryBuilder('p')
                        ->andWhere('p.title LIKE :title')
                        ->setParameter('title', '%' .$this->q. '%')
                        ->getQuery()
                        ->getResult();
    }
}
