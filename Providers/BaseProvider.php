<?php namespace Providers;

use LessQL\Database;

class BaseProvider {
	/**
	 * @var \PDO
	 */
	protected $pdo;

	/**
	 * @var \LessQL\Database
	 */
	protected $db;

	public function __construct(\PDO $pdo) {
		$this->pdo = $pdo;
		$this->db = new Database($pdo);
		$this->dbConfig();
	}

	private function dbConfig() {
		$driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
		if ($driver === 'mysql') {
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
		$driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
		$sql = str_replace('`', $delimiter, $sql);

		if ($driver === 'pgsql') {
			$sql = str_replace('FROM_UNIXTIME(', 'to_timestamp(', $sql);
		}

		$query = $this->pdo->prepare($sql, $params);
		$query->execute($params);

		return $query;
	}
}
