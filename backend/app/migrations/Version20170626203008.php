<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170626203008 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_notification ADD service_tariff BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_notification ADD "service" VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC84072E1E9 FOREIGN KEY ("service") REFERENCES "service" ("id") ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC8AAA2254B FOREIGN KEY (service_tariff) REFERENCES service_tariff (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3F980AC84072E1E9 ON user_notification ("service")');
        $this->addSql('CREATE INDEX IDX_3F980AC8AAA2254B ON user_notification (service_tariff)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_notification DROP CONSTRAINT FK_3F980AC84072E1E9');
        $this->addSql('ALTER TABLE user_notification DROP CONSTRAINT FK_3F980AC8AAA2254B');
        $this->addSql('DROP INDEX IDX_3F980AC84072E1E9');
        $this->addSql('DROP INDEX IDX_3F980AC8AAA2254B');
        $this->addSql('ALTER TABLE user_notification DROP service_tariff');
        $this->addSql('ALTER TABLE user_notification DROP "service"');
    }
}
