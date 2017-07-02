<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170702133218 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE room_request_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE room_request (id BIGINT NOT NULL, room_id BIGINT NOT NULL, customer_id BIGINT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) NOT NULL, "from" TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, "to" TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, manager_comment TEXT DEFAULT NULL, customer_comment TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D7B5337F54177093 ON room_request (room_id)');
        $this->addSql('CREATE INDEX IDX_D7B5337F9395C3F3 ON room_request (customer_id)');
        $this->addSql('ALTER TABLE room_request ADD CONSTRAINT FK_D7B5337F54177093 FOREIGN KEY (room_id) REFERENCES rent_room (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE room_request ADD CONSTRAINT FK_D7B5337F9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE room_request_id_seq CASCADE');
        $this->addSql('DROP TABLE room_request');
    }
}
