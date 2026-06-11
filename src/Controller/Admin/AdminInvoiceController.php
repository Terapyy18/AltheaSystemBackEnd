<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use App\Service\InvoiceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminInvoiceController extends AbstractController
{
    #[Route('/admin/orders/{id}/invoice', name: 'admin_order_invoice_download', methods: ['GET'])]
    public function download(
        int $id,
        OrderRepository $orderRepo,
        InvoiceService $invoiceService,
    ): Response {
        $order = $orderRepo->find($id);
        if ($order === null) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        $absolutePath = $invoiceService->getAbsolutePath($order);
        if ($absolutePath === null) {
            throw $this->createNotFoundException(
                sprintf('Aucune facture disponible pour la commande #%d.', $id)
            );
        }

        $filename = ($order->getInvoiceNumber() ?? ('facture-' . $id)) . '.pdf';

        $response = new BinaryFileResponse($absolutePath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }
}
