<?php

namespace App\DataFixtures;
use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;


class ArticleFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $article = new Article();
        $article->setCategorie($this->getReference(CategorieFixture::Football));
        $article->setTitle("L'histoire du PSG");
        $article->setContent("Le PSG crée en 1970, est le meilleur club du monde");
        $article->setUser($this->getReference(UserFixture::Marvin));
        $manager->persist($article);

        $manager->flush();
    }
    function getDependencies()
    {
        return [CategorieFixture::class,
    ];
    }
}
