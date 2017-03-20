<?php namespace Demostf\API\Providers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use LessQL\Database;

class BaseProvider {
	/**
	 * @var Connection
	 */
	protected $connection;

	/**
	 * @var \LessQL\Database
	 */
	protected $db;

	public function __construct(Connection $connection) {
		$this->connection = $connection;
		$this->db = new Database($connection->getWrappedConnection());
		$this->dbConfig();
	}

	private function dbConfig() {
		$platform = $this->connection->getDatabasePlatform();
		if ($platform instanceof MySqlPlatform) {
			$this->db->setIdentifierDelimiter("`");
		} else {
			$this->db->setIdentifierDelimiter('"');
		}

		$this->db->setRewrite(function ($table) {
			$rawNames = ['chat'];
			$aliases = [

			];
			if (isset($aliases[$table])) {
				return $aliases[$table];
			} else if (array_search($table, $rawNames) === false) {
				return $table . 's';
			} else {
				return $table;
			}
		});
	}

	protected function query($sql, array $params = []) {
		$delimiter = $this->db->getIdentifierDelimiter();
		$platform = $this->connection->getDatabasePlatform();
		$sql = str_replace('`', $delimiter, $sql);

		if ($platform instanceof PostgreSqlPlatform) {
			$sql = str_replace('FROM_UNIXTIME(', 'to_timestamp(', $sql);
		}

		$query = $this->connection->prepare($sql);
		$query->execute($params);

		return $query;
	}

	/**
	 * @return QueryBuilder
	 */
	protected function getQueryBuilder() {
		return new QueryBuilder($this->connection);
	}
}
