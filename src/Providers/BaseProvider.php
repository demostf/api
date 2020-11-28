<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class BaseProvider {
    protected Connection $connection;

    /**
     * BaseProvider constructor.
     *
     * @param connection $connection
     *
     * The DBAL connection used will always be a PDO
     * but phan isn't aware of this
     *
     * @suppress PhanTypeMismatchArgument
     */
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder() {
        return new QueryBuilder($this->connection);
    }
}
