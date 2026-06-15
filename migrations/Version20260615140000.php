<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Aligne SiteSettings sur les langues réellement supportées par le frontend
 * (fr, en, zh, he). La colonne value_ar (arabe, jamais affichée côté front)
 * est renommée en value_he (hébreu, langue RTL effectivement proposée).
 */
final class Version20260615140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename site_settings.value_ar to value_he (frontend RTL language is Hebrew, not Arabic)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings RENAME COLUMN value_ar TO value_he');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings RENAME COLUMN value_he TO value_ar');
    }
}
