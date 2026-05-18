<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds stock_decremented to "order". Guarded boolean used by the Stripe
 * webhook flow to make stock decrementation strictly idempotent: even if
 * the same checkout.session.completed event is delivered twice, we never
 * decrement Product.stock more than once for the same order.
 */
final class Version20260518093000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add stock_decremented flag on "order" for webhook idempotency';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ADD stock_decremented BOOLEAN DEFAULT FALSE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" DROP stock_decremented');
    }
}
