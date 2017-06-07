<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170607182341 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE user_notification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE user_notification (id BIGINT NOT NULL, receiver BIGINT NOT NULL, author BIGINT DEFAULT NULL, ticket BIGINT DEFAULT NULL, ticket_message BIGINT DEFAULT NULL, ticket_manager BIGINT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, "type" VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3F980AC83DB88C96 ON user_notification (receiver)');
        $this->addSql('CREATE INDEX IDX_3F980AC8BDAFD8C8 ON user_notification (author)');
        $this->addSql('CREATE INDEX IDX_3F980AC897A0ADA3 ON user_notification (ticket)');
        $this->addSql('CREATE INDEX IDX_3F980AC8BA71692D ON user_notification (ticket_message)');
        $this->addSql('CREATE INDEX IDX_3F980AC8F6E87CEB ON user_notification (ticket_manager)');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC83DB88C96 FOREIGN KEY (receiver) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC8BDAFD8C8 FOREIGN KEY (author) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC897A0ADA3 FOREIGN KEY (ticket) REFERENCES ticket (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC8BA71692D FOREIGN KEY (ticket_message) REFERENCES ticket_message (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_notification ADD CONSTRAINT FK_3F980AC8F6E87CEB FOREIGN KEY (ticket_manager) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE user_notification_id_seq CASCADE');
        $this->addSql('DROP TABLE user_notification');
    }
}
