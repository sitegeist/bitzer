<?php
declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * The migration for providing the basic task table structure
 */
class Version20200707164857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'The migration for adding the agenttype field to basic task table structure';
    }

    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE sitegeist_bitzer_domain_task_task ADD COLUMN agenttype INT NOT NULL AFTER actionstatus');
        $this->addSql('UPDATE sitegeist_bitzer_domain_task_task SET agenttype = 1');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE sitegeist_bitzer_domain_task_task DROP COLUMN agenttype');
    }
}
