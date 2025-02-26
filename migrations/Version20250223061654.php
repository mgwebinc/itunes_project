<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223061654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_39986E43C2428192 ON album');
        $this->addSql('DROP INDEX IDX_39986E431F48AE04 ON album');
        $this->addSql('ALTER TABLE album ADD artist_id INT NOT NULL, ADD genre_id INT NOT NULL, DROP artist_id_id, DROP genre_id_id');
        $this->addSql('CREATE INDEX IDX_39986E43B7970CF8 ON album (artist_id)');
        $this->addSql('CREATE INDEX IDX_39986E434296D31F ON album (genre_id)');
        $this->addSql('DROP INDEX IDX_B3854E799FCD471 ON album_image');
        $this->addSql('ALTER TABLE album_image CHANGE album_id_id album_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_B3854E791137ABCF ON album_image (album_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_B3854E791137ABCF ON album_image');
        $this->addSql('ALTER TABLE album_image CHANGE album_id album_id_id INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_B3854E799FCD471 ON album_image (album_id_id)');
        $this->addSql('DROP INDEX IDX_39986E43B7970CF8 ON album');
        $this->addSql('DROP INDEX IDX_39986E434296D31F ON album');
        $this->addSql('ALTER TABLE album ADD artist_id_id INT NOT NULL, ADD genre_id_id INT NOT NULL, DROP artist_id, DROP genre_id');
        $this->addSql('CREATE INDEX IDX_39986E43C2428192 ON album (genre_id_id)');
        $this->addSql('CREATE INDEX IDX_39986E431F48AE04 ON album (artist_id_id)');
    }
}
