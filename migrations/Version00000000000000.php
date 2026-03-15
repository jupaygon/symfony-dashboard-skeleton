<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Initial schema + seed data.
 *
 * Creates all tables and inserts demo users and organizations:
 *   - superadmin@example.com / superadmin (ROLE_SUPER_ADMIN)
 *   - admin@example.com / admin (ROLE_ADMIN, Acme Corp)
 *   - user@example.com / user (ROLE_USER, Acme Corp)
 *
 * Change passwords after first install:
 *   php bin/console app:user:change-password superadmin@example.com
 */
final class Version00000000000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema and seed data';
    }

    public function up(Schema $schema): void
    {
        // Organizations
        $this->addSql('CREATE TABLE organization (
            id INT AUTO_INCREMENT NOT NULL,
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
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Users
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(200) NOT NULL,
            active TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // User-Organization M2M
        $this->addSql('CREATE TABLE user_organization (
            user_id INT NOT NULL,
            organization_id INT NOT NULL,
            INDEX IDX_41221F7EA76ED395 (user_id),
            INDEX IDX_41221F7E32C8A3DE (organization_id),
            PRIMARY KEY(user_id, organization_id),
            CONSTRAINT FK_41221F7EA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE,
            CONSTRAINT FK_41221F7E32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // User Preferences
        $this->addSql('CREATE TABLE user_preference (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            field VARCHAR(50) NOT NULL,
            value VARCHAR(255) NOT NULL,
            INDEX IDX_FA0E76BFA76ED395 (user_id),
            UNIQUE INDEX user_preference_unique (user_id, field),
            PRIMARY KEY(id),
            CONSTRAINT FK_FA0E76BFA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Seed: Organizations
        $this->addSql("INSERT INTO organization (name, active, created_at) VALUES ('Acme Corp', 1, NOW())");
        $this->addSql("INSERT INTO organization (name, active, created_at) VALUES ('Globex Inc', 1, NOW())");

        // Seed: Users (passwords: superadmin, admin, user)
        $this->addSql("INSERT INTO `user` (email, roles, password, name, active, created_at) VALUES ('superadmin@example.com', '[\"ROLE_SUPER_ADMIN\"]', '\$2y\$13\$a9OoTXTCJHVRywwRqUk2jO/ENSRRX6QGXfT4FSPTuYKlPJ0iOFxuC', 'Super Admin', 1, NOW())");
        $this->addSql("INSERT INTO `user` (email, roles, password, name, active, created_at) VALUES ('admin@example.com', '[\"ROLE_ADMIN\"]', '\$2y\$13\$MHxplahE.EssoqO9ZxiHfOiYoY9bMt3V5HaVnA0daFrJHdugwTBtG', 'Admin User', 1, NOW())");
        $this->addSql("INSERT INTO `user` (email, roles, password, name, active, created_at) VALUES ('user@example.com', '[]', '\$2y\$13\$0bALRFAHTTWOdgonFeURCOXhQjLH32a2OjWX9pLczWcg8GmV/IKxG', 'Regular User', 1, NOW())");

        // Seed: User-Organization relations
        $this->addSql("INSERT INTO user_organization (user_id, organization_id) VALUES (2, 1)");
        $this->addSql("INSERT INTO user_organization (user_id, organization_id) VALUES (3, 1)");
        $this->addSql("INSERT INTO user_organization (user_id, organization_id) VALUES (3, 2)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_preference');
        $this->addSql('DROP TABLE user_organization');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE organization');
    }
}
