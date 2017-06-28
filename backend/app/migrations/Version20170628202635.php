<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170628202635 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE service_activated ALTER service_id TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE service ALTER id TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE service_customer_history ALTER service_id TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE service_tariff ALTER service_id TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE user_notification ALTER service TYPE VARCHAR(50)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_notification ALTER "service" TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE service_activated ALTER service_id TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE service_customer_history ALTER service_id TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE service_tariff ALTER service_id TYPE VARCHAR(20)');
        $this->addSql('ALTER TABLE "service" ALTER id TYPE VARCHAR(20)');
    }
}
