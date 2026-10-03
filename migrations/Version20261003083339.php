<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261003083339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post_post_category (post_id INT NOT NULL, post_category_id INT NOT NULL, PRIMARY KEY (post_id, post_category_id))');
        $this->addSql('CREATE INDEX IDX_A6D02E734B89032C ON post_post_category (post_id)');
        $this->addSql('CREATE INDEX IDX_A6D02E73FE0617CD ON post_post_category (post_category_id)');
        $this->addSql('ALTER TABLE post_post_category ADD CONSTRAINT FK_A6D02E734B89032C FOREIGN KEY (post_id) REFERENCES post (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post_post_category ADD CONSTRAINT FK_A6D02E73FE0617CD FOREIGN KEY (post_category_id) REFERENCES post_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT fk_5a8a6c8d12469de2');
        $this->addSql('DROP INDEX idx_5a8a6c8d12469de2');
        $this->addSql('INSERT INTO post_post_category (post_id, post_category_id) SELECT id, category_id FROM post WHERE category_id IS NOT NULL');
        $this->addSql('ALTER TABLE post DROP category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_post_category DROP CONSTRAINT FK_A6D02E734B89032C');
        $this->addSql('ALTER TABLE post_post_category DROP CONSTRAINT FK_A6D02E73FE0617CD');
        $this->addSql('DROP TABLE post_post_category');
        $this->addSql('ALTER TABLE post ADD category_id INT NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT fk_5a8a6c8d12469de2 FOREIGN KEY (category_id) REFERENCES post_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_5a8a6c8d12469de2 ON post (category_id)');
    }
}
