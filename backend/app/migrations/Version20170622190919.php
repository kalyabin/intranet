<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170622190919 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE service_customer_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_tariff_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE service_activated (customer_id BIGINT NOT NULL, service_id VARCHAR(20) NOT NULL, tariff_id BIGINT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(customer_id, service_id))');
        $this->addSql('CREATE INDEX IDX_689DC9D79395C3F3 ON service_activated (customer_id)');
        $this->addSql('CREATE INDEX IDX_689DC9D7ED5CA9E6 ON service_activated (service_id)');
        $this->addSql('CREATE INDEX IDX_689DC9D792348FD2 ON service_activated (tariff_id)');
        $this->addSql('CREATE TABLE "service" (id VARCHAR(20) NOT NULL, is_active BOOLEAN NOT NULL, title VARCHAR(50) NOT NULL, description TEXT DEFAULT NULL, customer_role VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E19D9AD2BF396750 ON "service" (id)');
        $this->addSql('CREATE TABLE service_customer_history (id BIGINT NOT NULL, customer_id BIGINT NOT NULL, service_id VARCHAR(20) NOT NULL, tariff_id BIGINT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, voided_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9C22F3E4BF396750 ON service_customer_history (id)');
        $this->addSql('CREATE INDEX IDX_9C22F3E49395C3F3 ON service_customer_history (customer_id)');
        $this->addSql('CREATE INDEX IDX_9C22F3E4ED5CA9E6 ON service_customer_history (service_id)');
        $this->addSql('CREATE INDEX IDX_9C22F3E492348FD2 ON service_customer_history (tariff_id)');
        $this->addSql('CREATE TABLE service_tariff (id BIGINT NOT NULL, service_id VARCHAR(20) NOT NULL, is_active BOOLEAN NOT NULL, title VARCHAR(50) NOT NULL, monthly_cost DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AAA2254BBF396750 ON service_tariff (id)');
        $this->addSql('CREATE INDEX IDX_AAA2254BED5CA9E6 ON service_tariff (service_id)');
        $this->addSql('ALTER TABLE service_activated ADD CONSTRAINT FK_689DC9D79395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_activated ADD CONSTRAINT FK_689DC9D7ED5CA9E6 FOREIGN KEY (service_id) REFERENCES "service" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_activated ADD CONSTRAINT FK_689DC9D792348FD2 FOREIGN KEY (tariff_id) REFERENCES service_tariff (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_customer_history ADD CONSTRAINT FK_9C22F3E49395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_customer_history ADD CONSTRAINT FK_9C22F3E4ED5CA9E6 FOREIGN KEY (service_id) REFERENCES "service" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_customer_history ADD CONSTRAINT FK_9C22F3E492348FD2 FOREIGN KEY (tariff_id) REFERENCES service_tariff (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_tariff ADD CONSTRAINT FK_AAA2254BED5CA9E6 FOREIGN KEY (service_id) REFERENCES "service" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE service_activated DROP CONSTRAINT FK_689DC9D7ED5CA9E6');
        $this->addSql('ALTER TABLE service_customer_history DROP CONSTRAINT FK_9C22F3E4ED5CA9E6');
        $this->addSql('ALTER TABLE service_tariff DROP CONSTRAINT FK_AAA2254BED5CA9E6');
        $this->addSql('ALTER TABLE service_activated DROP CONSTRAINT FK_689DC9D792348FD2');
        $this->addSql('ALTER TABLE service_customer_history DROP CONSTRAINT FK_9C22F3E492348FD2');
        $this->addSql('DROP SEQUENCE service_customer_history_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_tariff_id_seq CASCADE');
        $this->addSql('DROP TABLE service_activated');
        $this->addSql('DROP TABLE "service"');
        $this->addSql('DROP TABLE service_customer_history');
        $this->addSql('DROP TABLE service_tariff');
    }
}
