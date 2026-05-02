<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial schema + seed data.
 *
 * Identifiers are UUID v7 stored as BINARY(16). Seed data uses fixed UUIDs
 * so a fresh install is deterministic.
 *
 * Demo users (rotate before any deploy):
 *   - superadmin@example.com / superadmin (ROLE_SUPER_ADMIN)
 *   - admin@example.com / admin (ROLE_ADMIN, Acme Corp)
 *   - user@example.com / user (ROLE_USER, Acme Corp + Globex Inc)
 */
final class Version00000000000000 extends AbstractMigration
{
    private const ORG_ACME       = '01956E76000170008000000000000001';
    private const ORG_GLOBEX     = '01956E76000270008000000000000002';
    private const USER_SUPER     = '01956E76000370008000000000000001';
    private const USER_ADMIN     = '01956E76000370008000000000000002';
    private const USER_REGULAR   = '01956E76000370008000000000000003';

    public function getDescription(): string
    {
        return 'Initial schema (UUID v7 BINARY(16) ids) and seed data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE organization (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            active TINYINT(1) NOT NULL,
            name VARCHAR(200) NOT NULL,
            legal_name VARCHAR(255) DEFAULT NULL,
            vat_number VARCHAR(20) DEFAULT NULL,
            address VARCHAR(255) DEFAULT NULL,
            city VARCHAR(100) DEFAULT NULL,
            state VARCHAR(100) DEFAULT NULL,
            zip VARCHAR(15) DEFAULT NULL,
            country VARCHAR(100) DEFAULT NULL,
            web VARCHAR(255) DEFAULT NULL,
            comments LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE `user` (
            id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(200) NOT NULL,
            active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL,
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE user_organization (
            user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            organization_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            INDEX IDX_41221F7EA76ED395 (user_id),
            INDEX IDX_41221F7E32C8A3DE (organization_id),
            PRIMARY KEY(user_id, organization_id),
            CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE,
            CONSTRAINT FK_41221F7E32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE user_preference (
            id INT AUTO_INCREMENT NOT NULL,
            user_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\',
            field VARCHAR(50) NOT NULL,
            value VARCHAR(255) NOT NULL,
            INDEX IDX_FA0E76BFA76ED395 (user_id),
            UNIQUE INDEX UNIQ_FA0E76BFA76ED3955BF54558 (user_id, field),
            PRIMARY KEY(id),
            CONSTRAINT FK_FA0E76BFA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Seed: Organizations
        $this->addSql(sprintf(
            "INSERT INTO organization (id, name, active, created_at) VALUES (UNHEX('%s'), 'Acme Corp', 1, NOW())",
            self::ORG_ACME,
        ));
        $this->addSql(sprintf(
            "INSERT INTO organization (id, name, active, created_at) VALUES (UNHEX('%s'), 'Globex Inc', 1, NOW())",
            self::ORG_GLOBEX,
        ));

        // Seed: Users — passwords hashed with bcrypt cost=13 (rotate before any deploy)
        $this->addSql(sprintf(
            "INSERT INTO `user` (id, email, roles, password, name, active, created_at) VALUES (UNHEX('%s'), 'superadmin@example.com', '[\"ROLE_SUPER_ADMIN\"]', '\$2y\$13\$a9OoTXTCJHVRywwRqUk2jO/ENSRRX6QGXfT4FSPTuYKlPJ0iOFxuC', 'Super Admin', 1, NOW())",
            self::USER_SUPER,
        ));
        $this->addSql(sprintf(
            "INSERT INTO `user` (id, email, roles, password, name, active, created_at) VALUES (UNHEX('%s'), 'admin@example.com', '[\"ROLE_ADMIN\"]', '\$2y\$13\$MHxplahE.EssoqO9ZxiHfOiYoY9bMt3V5HaVnA0daFrJHdugwTBtG', 'Admin User', 1, NOW())",
            self::USER_ADMIN,
        ));
        $this->addSql(sprintf(
            "INSERT INTO `user` (id, email, roles, password, name, active, created_at) VALUES (UNHEX('%s'), 'user@example.com', '[]', '\$2y\$13\$0bALRFAHTTWOdgonFeURCOXhQjLH32a2OjWX9pLczWcg8GmV/IKxG', 'Regular User', 1, NOW())",
            self::USER_REGULAR,
        ));

        // Seed: User-Organization relations
        $this->addSql(sprintf(
            "INSERT INTO user_organization (user_id, organization_id) VALUES (UNHEX('%s'), UNHEX('%s'))",
            self::USER_ADMIN, self::ORG_ACME,
        ));
        $this->addSql(sprintf(
            "INSERT INTO user_organization (user_id, organization_id) VALUES (UNHEX('%s'), UNHEX('%s'))",
            self::USER_REGULAR, self::ORG_ACME,
        ));
        $this->addSql(sprintf(
            "INSERT INTO user_organization (user_id, organization_id) VALUES (UNHEX('%s'), UNHEX('%s'))",
            self::USER_REGULAR, self::ORG_GLOBEX,
        ));
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_preference');
        $this->addSql('DROP TABLE user_organization');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE organization');
    }
}
