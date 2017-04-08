<?php declare(strict_types=1);

namespace Demostf\API\Demo;

/**
 * HL2 demo metadata
 */
class Header {
	/**
	 * @var string
	 */
	protected $type;

	/**
	 * @var int
	 */
	protected $version;

	/**
	 * @var int
	 */
	protected $protocol;

	/**
	 * @var string
	 */
	protected $server;

	/**
	 * @var string
	 */
	protected $nick;

	/**
	 * @var string
	 */
	protected $map;

	/**
	 * @var string
	 */
	protected $game;

	/**
	 * @var float
	 */
	protected $duration;

	/**
	 * @var int
	 */
	protected $ticks;

	/**
	 * @var int
	 */
	protected $frames;

	/**
	 * @var int
	 */
	protected $sigon;

	/**
	 * @param array $info
	 */
	public function __construct($info) {
		$this->type = $info['type'];
		$this->version = $info['version'];
		$this->protocol = $info['protocol'];
		$this->server = $info['server'];
		$this->nick = $info['nick'];
		$this->map = $info['map'];
		$this->game = $info['game'];
		$this->duration = $info['duration'];
		$this->ticks = $info['ticks'];
		$this->frames = $info['frames'];
		$this->sigon = $info['sigon'];
	}

	public function getDuration(): float {
		return $this->duration;
	}

	public function getFrames(): int {
		return $this->frames;
	}

	public function getGame(): string {
		return $this->game;
	}

	public function getMap(): string {
		return $this->map;
	}

	public function getNick(): string {
		return $this->nick;
	}

	public function getProtocol(): int {
		return $this->protocol;
	}

	public function getServer(): string {
		return $this->server;
	}

	public function getSigon(): int {
		return $this->sigon;
	}

	public function getTicks(): int {
		return $this->ticks;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getVersion(): int {
		return $this->version;
	}

}
