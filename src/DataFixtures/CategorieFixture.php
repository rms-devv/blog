<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategorieFixture extends Fixture
{
    public const Football = 'Football';
    public function load(ObjectManager $manager): void
    {
        $categorie = new Categorie();
        $categorie->setName("Football");
        $manager->persist($categorie);
        $this->addReference(self::Football, $categorie);
        $manager->flush();
    }
}
