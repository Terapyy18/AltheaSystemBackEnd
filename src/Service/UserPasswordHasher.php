<?php

namespace App\Service;

use App\Entity\User;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $processor,
        private UserPasswordHasherInterface $passwordHasher,
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof User || !$data->getPassword()) {
            return $this->processor->process($data, $operation, $uriVariables, $context);
        }

        $data->setPassword(
            $this->passwordHasher->hashPassword($data, $data->getPassword())
        );

        $data->setRoles(['ROLE_USER']);
        // Le compte reste inactif tant que l'email n'est pas confirmé (CDC §X).
        $data->setIsVerified(false);

        // Persiste l'utilisateur (lui attribue un id) avant de générer le lien signé.
        $result = $this->processor->process($data, $operation, $uriVariables, $context);

        $this->sendConfirmationEmail($data);

        return $result;
    }

    private function sendConfirmationEmail(User $user): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'app_verify_email',
            (string) $user->getId(),
            (string) $user->getEmail(),
            ['id' => $user->getId()],
        );

        $email = (new TemplatedEmail())
            ->from(new Address($_ENV['MAILER_FROM'] ?? 'contact.altheasysteme@gmail.com', 'AltheaSystem'))
            ->to((string) $user->getEmail())
            ->subject('Confirmez votre adresse email — AltheaSystem')
            ->htmlTemplate('emails/registration_confirmation.html.twig')
            ->context([
                'userName'       => $user->getFirstName() ?? 'Client',
                'verifyUrl'      => $signatureComponents->getSignedUrl(),
                'expiresAtMessageKey'  => $signatureComponents->getExpirationMessageKey(),
                'expiresAtMessageData' => $signatureComponents->getExpirationMessageData(),
            ]);

        $this->mailer->send($email);
    }
}
