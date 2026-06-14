<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

/**
 * Vérification de l'adresse email à l'inscription (CDC §X).
 *
 * Le lien envoyé par email pointe directement vers cette route backend (URL signée
 * par VerifyEmailHelper). Après validation de la signature, le compte est marqué
 * comme vérifié puis l'utilisateur est redirigé vers la page frontend /verify-email
 * qui affiche le statut (succès / erreur).
 */
#[Route('/api/verify-email', name: 'app_verify_email', methods: ['GET'])]
class EmailVerificationController extends AbstractController
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private EntityManagerInterface $entityManager,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $frontendUrl = rtrim($_ENV['FRONTEND_URL'] ?? 'http://localhost:3000', '/');

        $userId = $request->query->get('id');
        $user = $userId
            ? $this->entityManager->getRepository(User::class)->find((int) $userId)
            : null;

        if (!$user instanceof User) {
            return new RedirectResponse($frontendUrl . '/verify-email?status=error');
        }

        // Compte déjà vérifié : on évite une erreur et on redirige en succès.
        if ($user->isVerified()) {
            return new RedirectResponse($frontendUrl . '/verify-email?status=already');
        }

        try {
            $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                (string) $user->getId(),
                (string) $user->getEmail(),
            );
        } catch (VerifyEmailExceptionInterface $e) {
            return new RedirectResponse($frontendUrl . '/verify-email?status=error');
        }

        $user->setIsVerified(true);
        $this->entityManager->flush();

        return new RedirectResponse($frontendUrl . '/verify-email?status=success');
    }
}
