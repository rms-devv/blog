<?php

namespace App\DataFixtures;
use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class ArticleFixture extends Fixture implements DependentFixtureInterface
{   public const ProblemeSymfony = "Code d'erreur Symfony";
    public function load(ObjectManager $manager): void
    {
        $article = new Article();
        $article->setCategorie($this->getReference(CategorieFixture::Symfony));
        $article->setTitle("Code d'erreur Symfony");
        $article->setContent("Probleme code d'erreur 505");
        $article->setUser($this->getReference(UserFixture::Marvin));
        $this->addReference(self::ProblemeSymfony, $article);
        $manager->persist($article);

        $manager->flush();
    }
    function getDependencies()
    {
        return [CategorieFixture::class,
    ];
    }
}
