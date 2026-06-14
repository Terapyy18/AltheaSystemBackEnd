<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260613160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add optional product relation to carousel_slide';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE carousel_slide ADD COLUMN product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE carousel_slide ADD CONSTRAINT FK_BD7937A44584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_BD7937A44584665A ON carousel_slide (product_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE carousel_slide DROP CONSTRAINT FK_BD7937A44584665A');
        $this->addSql('DROP INDEX IDX_BD7937A44584665A');
        $this->addSql('ALTER TABLE carousel_slide DROP COLUMN product_id');
    }
}
