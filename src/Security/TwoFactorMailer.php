<?php

namespace App\Security;

use App\Entity\User;
use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class TwoFactorMailer implements AuthCodeMailerInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
    ) {}

    public function sendAuthCode(TwoFactorInterface $user): void
    {
        /** @var User $user */
        $html = $this->twig->render('security/2fa_email.html.twig', [
            'user'     => $user,
            'authCode' => $user->getEmailAuthCode(),
        ]);

        $email = (new Email())
            ->from('noreply@altheasystem.com')
            ->to($user->getEmailAuthRecipient())
            ->subject('AltheaSystem — Votre code de connexion')
            ->html($html);

        $this->mailer->send($email);
    }
}