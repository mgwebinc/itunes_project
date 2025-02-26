<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223024701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE album (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, song_count INT NOT NULL, rights VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, release_date DATETIME NOT NULL, apple_id VARCHAR(255) NOT NULL, apple_url VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, currency VARCHAR(10) NOT NULL, artist_id_id INT NOT NULL, genre_id_id INT NOT NULL, INDEX IDX_39986E431F48AE04 (artist_id_id), INDEX IDX_39986E43C2428192 (genre_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE album_image (id INT AUTO_INCREMENT NOT NULL, image_url VARCHAR(255) NOT NULL, height INT NOT NULL, album_id_id INT NOT NULL, INDEX IDX_B3854E799FCD471 (album_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE artist (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, apple_id INT NOT NULL, apple_url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE genre (id INT AUTO_INCREMENT NOT NULL, apple_id INT NOT NULL, name VARCHAR(255) NOT NULL, apple_url VARCHAR(255) NOT NULL, category VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE album_image');
        $this->addSql('DROP TABLE artist');
        $this->addSql('DROP TABLE genre');
    }
}
