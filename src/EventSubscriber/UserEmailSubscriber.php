<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\Events;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Symfony\Component\Mailer\MailerInterface;

#[AsEntityListener(event:Events::postPersist, method:'sendInscriptionEmail', entity:User::class)]
class UserEmailSubscriber
{
    public function __construct(private MailerInterface $mailer)
    {
        
    }
    public function sendInscriptionEmail(User $user): void
    {   
        $mail = $user->getEmail();
        $email = (new Email())
            ->from('admin@admin.com')
            ->to($mail)
            ->subject('Bienvenue')
            ->text('Bienvenue sur notre blog, et merci pour votre inscription')
            ->html('<p>Bienvenue sur notre blog, et merci pour votre inscription</p>');
        $this->mailer->send($email);
    }
}
