<?php namespace Demostf\API\Providers;

use Doctrine\DBAL\Connection;
use RandomLib\Generator;

class UploadProvider extends BaseProvider {
	/**
	 * @var Generator
	 */
	private $generator;

	public function __construct(Connection $db, Generator $generator) {
		parent::__construct($db);
		$this->generator = $generator;
	}
}
