<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260615120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add value_ar (Arabic, RTL) and value_zh (Chinese) columns to site_settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings ADD value_ar TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE site_settings ADD value_zh TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP value_ar');
        $this->addSql('ALTER TABLE site_settings DROP value_zh');
    }
}
