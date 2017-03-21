<?php namespace Demostf\API\Demo;

class ChatMessage {
	/** @var string */
	private $user;

	/** @var integer */
	private $time;

	/** @var string */
	private $message;

	/**
	 * ChatMessage constructor.
	 *
	 * @param string $user
	 * @param int $time
	 * @param string $message
	 */
	public function __construct(string $user, int $time, string $message) {
		$this->user = $user;
		$this->time = $time;
		$this->message = $message;
	}

	/**
	 * @return string
	 */
	public function getUser(): string {
		return $this->user;
	}

	/**
	 * @return int
	 */
	public function getTime(): int {
		return $this->time;
	}

	/**
	 * @return string
	 */
	public function getMessage(): string {
		return $this->message;
	}
}
