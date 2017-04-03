<?php declare(strict_types = 1);

namespace Demostf\API\Data;

class User implements \JsonSerializable {
	/** @var int */
	private $id;
	/** @var string */
	private $steamId;
	/** @var string */
	private $name;
	/** @var string */
	private $avatar;
	/** @var string */
	private $token;

	public function __construct(int $id, string $steamId, string $name, string $avatar, string $token) {
		$this->id = $id;
		$this->steamId = $steamId;
		$this->name = $name;
		$this->avatar = $avatar;
		$this->token = $token;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getSteamId(): string {
		return $this->steamId;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getAvatar(): string {
		return $this->avatar;
	}

	public function getToken(): string {
		return $this->token;
	}

	public function jsonSerialize() {
		return [
			'id' => $this->getId(),
			'steamid' => $this->getSteamId(),
			'name' => $this->getName(),
			'avatar' => $this->getAvatar()
		];
	}

	public static function fromRow(array $row): User {
		return new User(
			(int)$row['id'],
			$row['steamid'],
			$row['name'],
			$row['avatar'],
			$row['token'] ?? ''
		);
	}
}
