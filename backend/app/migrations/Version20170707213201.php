<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170707213201 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_notification ADD customer BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_notification ADD "from" TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE user_notification ALTER type TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC881398E09 FOREIGN KEY (customer) REFERENCES customer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_3F980AC881398E09 ON user_notification (customer)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE user_notification DROP CONSTRAINT FK_3F980AC881398E09');
        $this->addSql('DROP INDEX IDX_3F980AC881398E09');
        $this->addSql('ALTER TABLE user_notification DROP customer');
        $this->addSql('ALTER TABLE user_notification DROP "from"');
        $this->addSql('ALTER TABLE user_notification ALTER "type" TYPE VARCHAR(20)');
    }
}
