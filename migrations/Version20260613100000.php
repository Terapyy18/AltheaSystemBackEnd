<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260613100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add invoice_number_seq sequence and invoice_number column to order table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE invoice_number_seq START 1 INCREMENT 1 MINVALUE 1 NO CYCLE');
        $this->addSql('ALTER TABLE "order" ADD COLUMN invoice_number VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" DROP COLUMN invoice_number');
        $this->addSql('DROP SEQUENCE IF EXISTS invoice_number_seq');
    }
}
