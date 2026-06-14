<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\OrderRepository;
use App\Service\InvoiceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class InvoiceController extends AbstractController
{
    #[Route('/api/orders/{id}/invoice', name: 'api_order_invoice_download', methods: ['GET'])]
    public function download(
        int $id,
        OrderRepository $orderRepo,
        InvoiceService $invoiceService,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $order = $orderRepo->find($id);
        if ($order === null) {
            return new JsonResponse(['error' => 'Commande introuvable'], Response::HTTP_NOT_FOUND);
        }

        $currentUser = $this->getUser();
        $isAdmin     = $this->isGranted('ROLE_ADMIN');

        if (!$isAdmin && $order->getUser() !== $currentUser) {
            return new JsonResponse(['error' => 'Accès refusé'], Response::HTTP_FORBIDDEN);
        }

        $absolutePath = $invoiceService->getAbsolutePath($order);
        if ($absolutePath === null) {
            return new JsonResponse(
                ['error' => 'Facture non disponible pour cette commande'],
                Response::HTTP_NOT_FOUND
            );
        }

        $filename = ($order->getInvoiceNumber() ?? ('facture-' . $id)) . '.pdf';

        $response = new BinaryFileResponse($absolutePath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }

    /**
     * Téléchargement public de la facture d'une commande invité.
     *
     * Pas de JWT : l'autorisation repose sur le `session_id` Stripe (non
     * devinable) et l'accès est restreint aux commandes invité (user === null).
     */
    #[Route(
        '/api/orders/guest/by-stripe-session/{sessionId}/invoice',
        name: 'api_guest_invoice_download',
        requirements: ['sessionId' => '[A-Za-z0-9_]+'],
        methods: ['GET']
    )]
    public function downloadGuest(
        string $sessionId,
        OrderRepository $orderRepo,
        InvoiceService $invoiceService,
    ): Response {
        $order = $orderRepo->findOneBy(['stripeSessionId' => $sessionId]);
        if ($order === null || $order->getUser() !== null) {
            return new JsonResponse(['error' => 'Commande introuvable'], Response::HTTP_NOT_FOUND);
        }

        $absolutePath = $invoiceService->getAbsolutePath($order);
        if ($absolutePath === null) {
            return new JsonResponse(
                ['error' => 'Facture non disponible pour cette commande'],
                Response::HTTP_NOT_FOUND
            );
        }

        $filename = ($order->getInvoiceNumber() ?? ('facture-' . $order->getId())) . '.pdf';

        $response = new BinaryFileResponse($absolutePath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }
}
