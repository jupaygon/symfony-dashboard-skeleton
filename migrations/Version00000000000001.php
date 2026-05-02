<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds the `rememberme_token` table required by the Doctrine remember-me token
 * provider. Switching to a server-side provider lets logout / password change
 * invalidate persistent cookies instead of relying on the (stateless) signed
 * cookie surviving until its lifetime expires.
 */
final class Version00000000000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add rememberme_token table for Doctrine remember-me token provider';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE rememberme_token (
            series CHAR(88) NOT NULL,
            value CHAR(88) NOT NULL,
            lastUsed DATETIME NOT NULL,
            class VARCHAR(100) NOT NULL,
            username VARCHAR(200) NOT NULL,
            PRIMARY KEY(series)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE rememberme_token');
    }
}
