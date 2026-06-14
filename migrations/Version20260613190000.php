<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Harmonisation des statuts de commande sur des slugs anglais minuscules.
 *
 * Le backend (OrderService / RefundService) écrivait des libellés français
 * ('Payée', 'Echec paiement', 'Remboursée', 'Suspicious') alors qu'EasyAdmin
 * et le frontend attendent des slugs anglais ('paid', 'payment_failed',
 * 'refunded', 'suspicious'). Cette migration normalise les données existantes.
 */
final class Version20260613190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Normalize order.status French labels to English slugs (paid/payment_failed/refunded/suspicious)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE \"order\" SET status = 'paid' WHERE status = 'Payée'");
        $this->addSql("UPDATE \"order\" SET status = 'payment_failed' WHERE status = 'Echec paiement'");
        $this->addSql("UPDATE \"order\" SET status = 'refunded' WHERE status = 'Remboursée'");
        $this->addSql("UPDATE \"order\" SET status = 'suspicious' WHERE status = 'Suspicious'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE \"order\" SET status = 'Payée' WHERE status = 'paid'");
        $this->addSql("UPDATE \"order\" SET status = 'Echec paiement' WHERE status = 'payment_failed'");
        $this->addSql("UPDATE \"order\" SET status = 'Remboursée' WHERE status = 'refunded'");
        $this->addSql("UPDATE \"order\" SET status = 'Suspicious' WHERE status = 'suspicious'");
    }
}
