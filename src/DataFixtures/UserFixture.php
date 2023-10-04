<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public const Marvin = "Marvin";

    public function load(ObjectManager $manager): void
    {   
        
        $user = new User();
        $user->setFirstname("Marvin");
        $user->setLastname("Ramos");
        $user->setEmail("test@test.com");
        $user->setPlainPassword("demo");
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPseudo("RMS");

        $this->addReference(self::Marvin, $user);
        $manager->persist($user);

        $manager->flush();
    }
}
