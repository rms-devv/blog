<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsEntityListener(event: Events::postUpdate, method:'sendInscriptionEmail', entity: User::class)]
class UserEmailEditSubscriber
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
            ->subject('Profil modifié')
            ->text('Bonjour, votre profil a bien été modifié !')
            ->html("<h2>Bonjour, {$user->getFirstname()}</h2></br><p>Bonjour, votre profil a bien été modifié !</p>");
        $this->mailer->send($email);
    }
}
