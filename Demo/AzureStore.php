<?php namespace Demo;

use MicrosoftAzure\Storage\Blob\Internal\IBlob;

class AzureStore implements IDemoStore {
	/**
	 * @var IBlob
	 */
	private $blobStorage;

	/**
	 * @param IBlob $blobStorage
	 */
	public function __construct(IBlob $blobStorage) {
		$this->blobStorage = $blobStorage;
	}

	/**
	 * @param resource $stream
	 * @param string $name
	 * @return StoredDemo
	 */
	public function store($stream, $name) {
		$name = preg_replace("/[^A-Za-z0-9\\.\\-]/", '', $name);
		if (substr($name, -4) !== '.dem') {
			$name .= '.dem';
		}
		$id = uniqid() . $name;
		$this->upload($stream, $id);
		$url = 'https://demostf.blob.core.windows.net/demos/' . $id;
		return new StoredDemo('azure', $id, $url);
	}

	/**
	 * @param resource $stream
	 * @param string $id
	 * @return string mixed
	 */
	private function upload($stream, $id) {
		$this->blobStorage->createBlockBlob('demos', $id, $stream);
	}
}
