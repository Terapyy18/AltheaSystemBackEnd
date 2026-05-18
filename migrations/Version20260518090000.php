<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds stripe_session_id to "order" so the success page can look up an order
 * by its Stripe Checkout Session id (passed as ?session_id= on success_url).
 */
final class Version20260518090000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add stripe_session_id (unique, nullable) on "order" for Stripe success lookup';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ADD stripe_session_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F52993983B0E1B5C ON "order" (stripe_session_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_F52993983B0E1B5C');
        $this->addSql('ALTER TABLE "order" DROP stripe_session_id');
    }
}
