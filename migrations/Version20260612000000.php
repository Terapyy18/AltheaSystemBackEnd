<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Enable unaccent + pg_trgm extensions and add GIN trigram indexes on product_translation for fast full-text search';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS unaccent');
        $this->addSql('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        // Immutable wrapper required to use unaccent() in a functional index
        $this->addSql("
            CREATE OR REPLACE FUNCTION f_unaccent(text) RETURNS text
                LANGUAGE sql IMMUTABLE PARALLEL SAFE STRICT AS
                \$func\$ SELECT public.unaccent('public.unaccent', \$1) \$func\$
        ");

        $this->addSql('CREATE INDEX IF NOT EXISTS idx_pt_title_trgm ON product_translation USING GIN (f_unaccent(title) gin_trgm_ops)');
        $this->addSql('CREATE INDEX IF NOT EXISTS idx_pt_description_trgm ON product_translation USING GIN (f_unaccent(description) gin_trgm_ops)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_pt_title_trgm');
        $this->addSql('DROP INDEX IF EXISTS idx_pt_description_trgm');
        $this->addSql('DROP FUNCTION IF EXISTS f_unaccent(text)');
        // Extensions partagées : ne pas DROP si d'autres objets en dépendent
    }
}
