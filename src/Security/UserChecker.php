<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Bloque la connexion des comptes dont l'email n'a pas encore été confirmé (CDC §X).
 *
 * Les administrateurs (ROLE_ADMIN) ne sont jamais bloqués : ils sont créés via les
 * fixtures et n'ont pas à passer par le flux de confirmation d'inscription.
 */
class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return;
        }

        if (!$user->isVerified()) {
            throw new CustomUserMessageAuthenticationException('account_not_verified');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Aucune vérification post-authentification nécessaire.
    }
}
