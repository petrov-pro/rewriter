<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230502163626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE apitoken_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE billing_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE context_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE image_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE site_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE translate_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE account (id INT NOT NULL, customer_id INT NOT NULL, balance BIGINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7D3656A49395C3F3 ON account (customer_id)');
        $this->addSql('CREATE TABLE apitoken (id INT NOT NULL, customer_id INT NOT NULL, token TEXT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_valid BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_23E5A7D39395C3F3 ON apitoken (customer_id)');
        $this->addSql('CREATE TABLE billing (id INT NOT NULL, customer_id INT NOT NULL, account_id INT NOT NULL, sum BIGINT NOT NULL, type VARCHAR(15) NOT NULL, system VARCHAR(20) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EC224CAA9395C3F3 ON billing (customer_id)');
        $this->addSql('CREATE INDEX IDX_EC224CAA9B6B5FBA ON billing (account_id)');
        $this->addSql('CREATE TABLE context (id INT NOT NULL, source_url TEXT NOT NULL, image_url TEXT DEFAULT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, text TEXT DEFAULT NULL, lang VARCHAR(2) NOT NULL, source_name VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, sentiment VARCHAR(255) DEFAULT NULL, category TEXT DEFAULT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(10) NOT NULL, provider VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E25D857E5FA9FB052B36786B ON context (source_name, title)');
        $this->addSql('COMMENT ON COLUMN context.category IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE image (id INT NOT NULL, context_id INT NOT NULL, customer_id INT NOT NULL, site_id INT DEFAULT NULL, keywords TEXT NOT NULL, data TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C53D045F6B00C1CF ON image (context_id)');
        $this->addSql('CREATE INDEX IDX_C53D045F9395C3F3 ON image (customer_id)');
        $this->addSql('CREATE INDEX IDX_C53D045FF6BD1646 ON image (site_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045F6B00C1CF9395C3F3 ON image (context_id, customer_id)');
        $this->addSql('COMMENT ON COLUMN image.data IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE site (id INT NOT NULL, customer_id INT NOT NULL, url TEXT NOT NULL, is_valid BOOLEAN NOT NULL, setting JSON DEFAULT NULL, type VARCHAR(255) NOT NULL, html_tag VARCHAR(255) DEFAULT NULL, is_image BOOLEAN NOT NULL, category TEXT NOT NULL, lang TEXT NOT NULL, is_send BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_694309E49395C3F3 ON site (customer_id)');
        $this->addSql('COMMENT ON COLUMN site.category IS \'(DC2Type:simple_array)\'');
        $this->addSql('COMMENT ON COLUMN site.lang IS \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE translate (id INT NOT NULL, context_id INT NOT NULL, customer_id INT NOT NULL, site_id INT NOT NULL, title TEXT NOT NULL, text TEXT DEFAULT NULL, description TEXT NOT NULL, lang VARCHAR(2) NOT NULL, token INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4A1063776B00C1CF ON translate (context_id)');
        $this->addSql('CREATE INDEX IDX_4A1063779395C3F3 ON translate (customer_id)');
        $this->addSql('CREATE INDEX IDX_4A106377F6BD1646 ON translate (site_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4A106377310984626B00C1CF9395C3F3 ON translate (lang, context_id, customer_id, site_id)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, max_site INT NOT NULL, company TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE account ADD CONSTRAINT FK_7D3656A49395C3F3 FOREIGN KEY (customer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE apitoken ADD CONSTRAINT FK_23E5A7D39395C3F3 FOREIGN KEY (customer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE billing ADD CONSTRAINT FK_EC224CAA9395C3F3 FOREIGN KEY (customer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE billing ADD CONSTRAINT FK_EC224CAA9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F6B00C1CF FOREIGN KEY (context_id) REFERENCES context (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F9395C3F3 FOREIGN KEY (customer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E49395C3F3 FOREIGN KEY (customer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE translate ADD CONSTRAINT FK_4A1063776B00C1CF FOREIGN KEY (context_id) REFERENCES context (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE translate ADD CONSTRAINT FK_4A1063779395C3F3 FOREIGN KEY (customer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE translate ADD CONSTRAINT FK_4A106377F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE apitoken_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE billing_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE context_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE image_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE site_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE translate_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE account DROP CONSTRAINT FK_7D3656A49395C3F3');
        $this->addSql('ALTER TABLE apitoken DROP CONSTRAINT FK_23E5A7D39395C3F3');
        $this->addSql('ALTER TABLE billing DROP CONSTRAINT FK_EC224CAA9395C3F3');
        $this->addSql('ALTER TABLE billing DROP CONSTRAINT FK_EC224CAA9B6B5FBA');
        $this->addSql('ALTER TABLE image DROP CONSTRAINT FK_C53D045F6B00C1CF');
        $this->addSql('ALTER TABLE image DROP CONSTRAINT FK_C53D045F9395C3F3');
        $this->addSql('ALTER TABLE image DROP CONSTRAINT FK_C53D045FF6BD1646');
        $this->addSql('ALTER TABLE site DROP CONSTRAINT FK_694309E49395C3F3');
        $this->addSql('ALTER TABLE translate DROP CONSTRAINT FK_4A1063776B00C1CF');
        $this->addSql('ALTER TABLE translate DROP CONSTRAINT FK_4A1063779395C3F3');
        $this->addSql('ALTER TABLE translate DROP CONSTRAINT FK_4A106377F6BD1646');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE apitoken');
        $this->addSql('DROP TABLE billing');
        $this->addSql('DROP TABLE context');
        $this->addSql('DROP TABLE image');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE translate');
        $this->addSql('DROP TABLE "user"');
    }
}
