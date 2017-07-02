<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170702130641 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE rent_room_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE rent_room (id BIGINT NOT NULL, "type" VARCHAR(20) NOT NULL, title VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, address VARCHAR(255) NOT NULL, hourly_cost DOUBLE PRECISION NOT NULL, schedule JSON NOT NULL, schedule_break JSON NOT NULL, holidays JSON NOT NULL, request_pause INT DEFAULT NULL, PRIMARY KEY(id))');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE rent_room_id_seq CASCADE');
        $this->addSql('DROP TABLE rent_room');
    }
}
