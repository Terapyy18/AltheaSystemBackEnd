<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

final class InvoiceService
{
    private const TVA_RATE = 0.20;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly Environment $twig,
        private readonly LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * Génère la facture PDF pour une commande et met à jour invoicePath + invoiceNumber.
     * À appeler APRÈS le commit Doctrine (post-transaction).
     *
     * @return string|null Le chemin relatif du PDF (var/invoices/FAC-…pdf), ou null en cas d'échec.
     */
    public function generateForOrder(Order $order): ?string
    {
        $logCtx = ['order_id' => $order->getId()];

        try {
            // Préparer les données de rendu avant d'allouer le numéro
            $lines       = $this->buildLines($order);
            $totalTtc    = (float) ($order->getTotalPrice() ?? 0.0);
            $totalHt     = $totalTtc / (1 + self::TVA_RATE);
            $totalTva    = $totalTtc - $totalHt;
            $invoiceDate = $order->getPayedAt() ?? $order->getCreatedAt() ?? new \DateTime();

            // Allouer le numéro de facture (séquence PostgreSQL — non transactionnel)
            $invoiceNumber = $this->allocateInvoiceNumber($order, $invoiceDate);

            $html = $this->twig->render('invoices/invoice.html.twig', [
                'order'          => $order,
                'invoice_number' => $invoiceNumber,
                'invoice_date'   => $invoiceDate,
                'lines'          => $lines,
                'total_ht'       => $totalHt,
                'total_tva'      => $totalTva,
                'total_ttc'      => $totalTtc,
                'tva_rate_pct'   => (int) (self::TVA_RATE * 100),
            ]);

            $dir = $this->projectDir . '/var/invoices';
            if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Impossible de créer le dossier %s', $dir));
            }

            $filename     = $invoiceNumber . '.pdf';
            $absolutePath = $dir . '/' . $filename;
            $relativePath = 'var/invoices/' . $filename;

            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', false);
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            file_put_contents($absolutePath, $dompdf->output());

            $order->setInvoicePath($relativePath);
            $this->em->flush();

            $this->logger->info('[Invoice] Generated', $logCtx + [
                'invoice_number' => $invoiceNumber,
                'path'           => $absolutePath,
            ]);

            return $relativePath;
        } catch (\Throwable $e) {
            $this->logger->error('[Invoice] Generation failed', $logCtx + [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Retourne le chemin absolu du PDF si le fichier existe, null sinon.
     */
    public function getAbsolutePath(Order $order): ?string
    {
        $relativePath = $order->getInvoicePath();
        if ($relativePath === null) {
            return null;
        }

        $absolutePath = $this->projectDir . '/' . $relativePath;
        return file_exists($absolutePath) ? $absolutePath : null;
    }

    private function allocateInvoiceNumber(Order $order, \DateTimeInterface $date): string
    {
        if ($order->getInvoiceNumber() !== null) {
            return $order->getInvoiceNumber();
        }

        $conn    = $this->em->getConnection();
        $counter = (int) $conn->fetchOne("SELECT nextval('invoice_number_seq')");
        $year    = $date->format('Y');

        $invoiceNumber = sprintf('FAC-%s-%06d', $year, $counter);
        $order->setInvoiceNumber($invoiceNumber);

        return $invoiceNumber;
    }

    /**
     * @return array<int, array{sku: string, title: string, quantity: int, unit_price_ttc: float, unit_price_ht: float, line_ttc: float, line_ht: float}>
     */
    private function buildLines(Order $order): array
    {
        $lines = [];

        foreach ($order->getItemsOrders() as $item) {
            $product = $item->getProduct();

            $title = null;
            $sku   = '';
            if ($product !== null) {
                $sku = (string) ($product->getSku() ?? '');
                foreach ($product->getProductTranslations() as $tr) {
                    if ($tr->getLanguage() === 'fr') {
                        $title = $tr->getTitle();
                        break;
                    }
                }
                if ($title === null) {
                    $title = $product->getProductTranslations()->first()
                        ? $product->getProductTranslations()->first()->getTitle()
                        : $sku;
                }
            }

            $unitTtc  = (float) $item->getPrice();
            $unitHt   = $unitTtc / (1 + self::TVA_RATE);
            $qty      = (int) $item->getQuantity();
            $lineTtc  = $unitTtc * $qty;
            $lineHt   = $unitHt * $qty;

            $lines[] = [
                'sku'            => $sku,
                'title'          => $title ?? ('Produit #' . ($product?->getId() ?? '?')),
                'quantity'       => $qty,
                'unit_price_ttc' => $unitTtc,
                'unit_price_ht'  => $unitHt,
                'line_ttc'       => $lineTtc,
                'line_ht'        => $lineHt,
            ];
        }

        return $lines;
    }
}
