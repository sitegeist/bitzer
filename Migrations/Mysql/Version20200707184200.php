<?php declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * The migration for providing the basic task table structure
 */
class Version20200707184200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'The migration for removing the agenttype field and adds a "role:" prefix to the agent field in the basic task table structure';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE sitegeist_bitzer_domain_task_task DROP COLUMN agenttype');
        $this->addSql('UPDATE sitegeist_bitzer_domain_task_task SET agent = concat("role:", agent)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE sitegeist_bitzer_domain_task_task ADD COLUMN agenttype INT NOT NULL AFTER actionstatus');
        $this->addSql('UPDATE sitegeist_bitzer_domain_task_task SET agenttype = 1');
    }
}
