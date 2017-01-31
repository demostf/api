<?php namespace Demo;

interface IDemoStore {
	/**
	 * @param resource $stream
	 * @param string $name
	 * @return StoredDemo
	 */
	public function store($stream, $name);
}
