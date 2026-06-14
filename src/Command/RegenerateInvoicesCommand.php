<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\OrderRepository;
use App\Service\InvoiceService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:invoices:regenerate',
    description: 'Génère les factures PDF manquantes pour toutes les commandes payées (statut paid ou suspicious)',
)]
class RegenerateInvoicesCommand extends Command
{
    public function __construct(
        private readonly OrderRepository $orderRepo,
        private readonly InvoiceService $invoiceService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Régénération des factures manquantes');

        $orders = $this->orderRepo->findPaidOrdersWithoutInvoice();

        if (empty($orders)) {
            $io->success('Aucune facture manquante — toutes les commandes payées ont déjà leur PDF.');
            return Command::SUCCESS;
        }

        $io->note(sprintf('%d commande(s) sans facture détectée(s).', count($orders)));
        $io->progressStart(count($orders));

        $success = 0;
        $failed  = 0;
        $errors  = [];

        foreach ($orders as $order) {
            $path = $this->invoiceService->generateForOrder($order);
            if ($path !== null) {
                $success++;
            } else {
                $failed++;
                $errors[] = sprintf('Commande #%d (statut: %s)', $order->getId(), $order->getStatus());
            }
            $io->progressAdvance();
        }

        $io->progressFinish();

        if ($success > 0) {
            $io->success(sprintf('%d facture(s) générée(s) avec succès.', $success));
        }

        if ($failed > 0) {
            $io->error(sprintf('%d facture(s) en échec :', $failed));
            foreach ($errors as $err) {
                $io->writeln('  • ' . $err);
            }
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
