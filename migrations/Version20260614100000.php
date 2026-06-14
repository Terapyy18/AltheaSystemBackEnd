<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Commande invité B2B : raison sociale + numéro SIREN de l'entreprise.
 *  - order.guest_company : nom de l'entreprise acheteuse (invité, user = null)
 *  - order.guest_siren   : SIREN (9) ou SIRET (14) de l'entreprise, pour la facture
 */
final class Version20260614100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Guest checkout B2B: add order.guest_company and order.guest_siren';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ADD guest_company VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ADD guest_siren VARCHAR(14) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" DROP guest_company');
        $this->addSql('ALTER TABLE "order" DROP guest_siren');
    }
}
