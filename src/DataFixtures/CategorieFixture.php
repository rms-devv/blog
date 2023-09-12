<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixture extends Fixture
{
    public const Symfony = 'Symfony';
    public const Design = 'Design';
    public const Angular = 'Angular';
    public const JS = 'JS';
    public const Html_Css = 'Html Css';
    public const React_Js = 'React Js';
    public const Autre = 'Autre...';

    public function load(ObjectManager $manager): void
    {
        $categorie = new Categorie();
        $categorie->setName("Symfony");
        $this->addReference(self::Symfony, $categorie);
        $manager->persist($categorie);  
        $manager->flush();

        $categorie = new Categorie();
        $categorie->setName("Design");
        $this->addReference(self::Design, $categorie);
        $manager->persist($categorie);
        $manager->flush();

        $categorie = new Categorie();
        $categorie->setName("Angular");
        $this->addReference(self::Angular, $categorie);
        $manager->persist($categorie);
        $manager->flush();

        $categorie = new Categorie();
        $categorie->setName("JS");
        $manager->persist($categorie);
        $this->addReference(self::JS, $categorie);
        $manager->flush();

        $categorie = new Categorie();
        $categorie->setName("Html/Css");
        $this->addReference(self::Html_Css, $categorie);
        $manager->persist($categorie);
        $manager->flush();

        $categorie = new Categorie();
        $categorie->setName("React Js");
        $this->addReference(self::React_Js, $categorie);
        $manager->persist($categorie);
        $manager->flush();
        $categorie = new Categorie();
        $categorie->setName("Autre...");
        $this->addReference(self::Autre, $categorie);
        $manager->persist($categorie);
        $manager->flush();
    }
}
