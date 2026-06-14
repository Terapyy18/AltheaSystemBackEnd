<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Mode « commande invité » (guest checkout) :
 *  - order.guest_email : email de l'acheteur quand aucun compte n'est rattaché
 *  - order.user_id devient nullable (une commande invité a user = null)
 */
final class Version20260613180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Guest checkout: add order.guest_email and make order.user_id nullable';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ADD guest_email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "order" ALTER user_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" DROP guest_email');
        // Restaure la contrainte NOT NULL (échouera s'il existe des commandes invité)
        $this->addSql('ALTER TABLE "order" ALTER user_id SET NOT NULL');
    }
}
