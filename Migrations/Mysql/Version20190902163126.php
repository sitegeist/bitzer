<?php
declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * The migration for providing the basic task table structure
 */
class Version20190902163126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'The migration for providing the basic task table structure';
    }

    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on "mysql".');

        $this->addSql('CREATE TABLE sitegeist_bitzer_domain_task_task (identifier VARCHAR(40) NOT NULL, classname VARCHAR(255) NOT NULL, properties TEXT NOT NULL, scheduledtime DATETIME NOT NULL, actionstatus VARCHAR(255) NOT NULL, agent VARCHAR(255) NOT NULL, object VARCHAR(255) NULL, target VARCHAR(255) NULL, PRIMARY KEY(identifier), INDEX `OBJECT` (`object`)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql',
            'Migration can only be executed safely on "mysql".');

        $this->addSql('DROP TABLE sitegeist_bitzer_domain_task_task');
    }
}
