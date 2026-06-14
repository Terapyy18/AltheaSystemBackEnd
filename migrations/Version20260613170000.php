<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260613170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add is_verified flag to user (email confirmation on registration). Existing users are marked verified to avoid locking them out.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD is_verified BOOLEAN DEFAULT FALSE NOT NULL');
        // Les comptes déjà existants ne doivent pas être bloqués par la nouvelle contrainte.
        $this->addSql('UPDATE "user" SET is_verified = TRUE');
        $this->addSql('ALTER TABLE "user" ALTER is_verified DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP is_verified');
    }
}
