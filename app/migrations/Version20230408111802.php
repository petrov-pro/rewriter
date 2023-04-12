<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230408111802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE context_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE translate_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE context (id INT NOT NULL, source_url TEXT NOT NULL, image_url TEXT DEFAULT NULL, title VARCHAR(255) NOT NULL, source_name VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, sentiment VARCHAR(255) DEFAULT NULL, category TEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, hash TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E25D857E5FA9FB05D1B862B8 ON context (source_name, hash)');
        $this->addSql('COMMENT ON COLUMN context.category IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE translate (id INT NOT NULL, context_id INT NOT NULL, title TEXT NOT NULL, text TEXT DEFAULT NULL, description TEXT NOT NULL, lang VARCHAR(2) NOT NULL, type VARCHAR(10) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4A1063776B00C1CF ON translate (context_id)');
        $this->addSql('ALTER TABLE translate ADD CONSTRAINT FK_4A1063776B00C1CF FOREIGN KEY (context_id) REFERENCES context (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE context_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE translate_id_seq CASCADE');
        $this->addSql('ALTER TABLE translate DROP CONSTRAINT FK_4A1063776B00C1CF');
        $this->addSql('DROP TABLE context');
        $this->addSql('DROP TABLE translate');
    }
}
