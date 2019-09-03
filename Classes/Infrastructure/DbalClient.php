<?php
declare(strict_types=1);

namespace Sitegeist\Bitzer\Infrastructure;

/*
 * This file is part of the Sitegeist.Bitzer package.
 */

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Neos\Flow\Annotations as Flow;

/**
 * The Doctrine DBAL client adapter
 *
 * @Flow\Scope("singleton")
 */
class DbalClient
{
    /**
     * @Flow\InjectConfiguration(package="Neos.Flow", path="persistence.backendOptions")
     * @var array
     */
    protected $backendOptions;

    /**
     * @Flow\InjectConfiguration(package="Neos.Flow", path="persistence.doctrine.sqlLogger")
     * @var string
     */
    protected $sqlLogger;

    /**
     * @var Connection
     */
    protected $connection;

    public function initializeObject()
    {
        $configuration = new Configuration();
        if (!empty($this->sqlLogger)) {
            $configuredSqlLogger = $this->sqlLogger;
            $configuration->setSQLLogger(new $configuredSqlLogger());
        }
        $this->connection = DriverManager::getConnection($this->backendOptions, $configuration);
    }

    public function getConnection(): Connection
    {
        if (!$this->connection->isConnected()) {
            $this->connection->connect();
        }

        return $this->connection;
    }
}
