<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create site_settings table with initial home_tagline value';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE site_settings (
            id SERIAL NOT NULL,
            setting_key VARCHAR(100) NOT NULL,
            value_fr TEXT NOT NULL,
            value_en TEXT NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_SITE_SETTINGS_KEY ON site_settings (setting_key)');

        $this->addSql("INSERT INTO site_settings (setting_key, value_fr, value_en, description) VALUES (
            'home_tagline',
            'Votre partenaire de confiance pour le matériel médical professionnel. Des équipements certifiés CE, livrés partout en France.',
            'Your trusted partner for professional medical equipment. CE-certified devices, delivered across France.',
            'Texte affiché sous le carrousel de la page d''accueil'
        )");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE site_settings');
    }
}
