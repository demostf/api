<?php namespace Demo;

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

	/**
	 * @return float
	 */
	public function getDuration() {
		return $this->duration;
	}

	/**
	 * @return int
	 */
	public function getFrames() {
		return $this->frames;
	}

	/**
	 * @return string
	 */
	public function getGame() {
		return $this->game;
	}

	/**
	 * @return string
	 */
	public function getMap() {
		return $this->map;
	}

	/**
	 * @return string
	 */
	public function getNick() {
		return $this->nick;
	}

	/**
	 * @return int
	 */
	public function getProtocol() {
		return $this->protocol;
	}

	/**
	 * @return string
	 */
	public function getServer() {
		return $this->server;
	}

	/**
	 * @return int
	 */
	public function getSigon() {
		return $this->sigon;
	}

	/**
	 * @return int
	 */
	public function getTicks() {
		return $this->ticks;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return int
	 */
	public function getVersion() {
		return $this->version;
	}

}
