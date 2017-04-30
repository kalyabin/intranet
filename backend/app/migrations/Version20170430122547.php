<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170430122547 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE user_checker_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_checker (id BIGINT NOT NULL, user_id BIGINT NOT NULL, code VARCHAR(32) NOT NULL, data VARCHAR(255) DEFAULT NULL, attempts SMALLINT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1DA11D8BF396750 ON user_checker (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1DA11D877153098 ON user_checker (code)');
        $this->addSql('CREATE INDEX IDX_1DA11D8A76ED395 ON user_checker (user_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_type ON user_checker (user_id, type)');
        $this->addSql('CREATE TABLE "user" (id BIGINT NOT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(100) NOT NULL, salt VARCHAR(10) NOT NULL, status SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE user_checker ADD CONSTRAINT FK_1DA11D8A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE user_checker DROP CONSTRAINT FK_1DA11D8A76ED395');
        $this->addSql('DROP SEQUENCE user_checker_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('DROP TABLE user_checker');
        $this->addSql('DROP TABLE "user"');
    }
}
