<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds stripe_payment_intent_id to "order".
 *
 * Needed to correlate later Stripe events (payment_intent.payment_failed,
 * charge.refunded) back to an order. The session_id alone is not enough —
 * those events do not carry a checkout_session reference.
 */
final class Version20260518100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add stripe_payment_intent_id (unique, nullable) on "order" for downstream Stripe events';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ADD stripe_payment_intent_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F529939873A7B4CB ON "order" (stripe_payment_intent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_F529939873A7B4CB');
        $this->addSql('ALTER TABLE "order" DROP stripe_payment_intent_id');
    }
}
