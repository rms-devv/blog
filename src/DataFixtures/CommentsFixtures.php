<?php

namespace App\DataFixtures;
use App\Entity\Comments;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentsFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $comment = new Comments();
        $comment->setTitle("problème récupération projet");
        $comment->setContent("Décrivez ici votre problème ou ce que vous cherchez à faire.
        Bonjour, je souhaite récupérer un projet symfony sur github mais je n'arrive pas à lancer le serveur
        Ce que je veux
        avoir le projet symfony sur mon pc
        Ce que j'obtiens       
        Symfony local serve");
        $comment->setUser($this->getReference(UserFixture::Marvin));
        $comment->setArticle($this->getReference(ArticleFixture::ProblemeSymfony));
        $manager->persist($comment);
        $manager->flush();
    }
    function getDependencies()
    {
        return [CategorieFixture::class, UserFixture::class
    ];
    }
}
