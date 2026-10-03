<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261003082352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD meta_description VARCHAR(320) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD primary_keyword VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD secondary_keywords JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP meta_title');
        $this->addSql('ALTER TABLE post DROP meta_description');
        $this->addSql('ALTER TABLE post DROP primary_keyword');
        $this->addSql('ALTER TABLE post DROP secondary_keywords');
    }
}
