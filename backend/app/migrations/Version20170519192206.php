<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170519192206 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE ticket_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE ticket (id BIGINT NOT NULL, created_by BIGINT DEFAULT NULL, managed_by BIGINT DEFAULT NULL, customer_id BIGINT NOT NULL, category_id VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_question_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_answer_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, voided_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, current_status VARCHAR(20) NOT NULL, title TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_97A0ADA3DE12AB56 ON ticket (created_by)');
        $this->addSql('CREATE INDEX IDX_97A0ADA38DBE8932 ON ticket (managed_by)');
        $this->addSql('CREATE INDEX IDX_97A0ADA39395C3F3 ON ticket (customer_id)');
        $this->addSql('CREATE INDEX IDX_97A0ADA312469DE2 ON ticket (category_id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3DE12AB56 FOREIGN KEY (created_by) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA38DBE8932 FOREIGN KEY (managed_by) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA39395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA312469DE2 FOREIGN KEY (category_id) REFERENCES ticket_category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE ticket_id_seq CASCADE');
        $this->addSql('DROP TABLE ticket');
    }
}
