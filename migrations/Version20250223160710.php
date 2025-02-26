<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223160710 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE album CHANGE apple_id apple_id VARCHAR(255) DEFAULT NULL, CHANGE apple_url apple_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE artist CHANGE apple_id apple_id INT DEFAULT NULL, CHANGE apple_url apple_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE genre CHANGE apple_id apple_id INT DEFAULT NULL, CHANGE apple_url apple_url VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE artist CHANGE apple_id apple_id INT NOT NULL, CHANGE apple_url apple_url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE album CHANGE apple_id apple_id VARCHAR(255) NOT NULL, CHANGE apple_url apple_url VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE genre CHANGE apple_id apple_id INT NOT NULL, CHANGE apple_url apple_url VARCHAR(255) NOT NULL');
    }
}
