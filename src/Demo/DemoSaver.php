<?php declare(strict_types=1);

namespace Demostf\API\Demo;

use Demostf\API\Data\Kill;
use Demostf\API\Data\ParsedDemo;
use Demostf\API\Data\Player;
use Demostf\API\Data\StoredDemo;
use Demostf\API\Data\Upload;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoProvider;
use Demostf\API\Providers\KillProvider;
use Demostf\API\Providers\PlayerProvider;
use Demostf\API\Providers\UserProvider;

class DemoSaver {
	/** @var KillProvider */
	private $killProvider;
	/** @var PlayerProvider */
	private $playerProvider;
	/** @var ChatProvider */
	private $chatProvider;
	/** @var UserProvider */
	private $userProvider;
	/** @var DemoProvider */
	private $demoProvider;

	public function __construct(KillProvider $killProvider, PlayerProvider $playerProvider, ChatProvider $chatProvider, UserProvider $userProvider, DemoProvider $demoProvider) {
		$this->killProvider = $killProvider;
		$this->playerProvider = $playerProvider;
		$this->chatProvider = $chatProvider;
		$this->userProvider = $userProvider;
		$this->demoProvider = $demoProvider;
	}

	public function saveDemo(ParsedDemo $demo, Header $header, StoredDemo $storedDemo, Upload $upload): int {
		/** @var int[] $userMap [$demoUserId => $dbUserId] */
		$userMap = [0 => 0];

		$demoId = $this->demoProvider->storeDemo(new Demo(
			0,
			$storedDemo->getUrl(),
			$upload->getName(),
			$header->getServer(),
			$header->getDuration(),
			$header->getNick(),
			$header->getMap(),
			new \DateTime(),
			$upload->getRed(),
			$upload->getBlue(),
			$demo->getRedScore(),
			$demo->getBlueScore(),
			count($demo->getPlayers()),
			$upload->getUploaderId(),
			$upload->getHash(),
			$storedDemo->getBackend(),
			$storedDemo->getPath()
		), $storedDemo->getBackend(), $storedDemo->getPath());

		foreach ($demo->getPlayers() as $player) {
			$userId = $this->userProvider->getUserId($player->getSteamId());
			$userMap[$player->getDemoUserId()] = $userId;

			$this->playerProvider->store(new Player(
				0,
				$demoId,
				$player->getDemoUserId(),
				$userId,
				$player->getName(),
				$player->getTeam(),
				$player->getClass()
			));
		}

		foreach ($demo->getKills() as $kill) {
			$this->killProvider->store(new Kill(
				0,
				$demoId,
				$userMap[$kill->getAttackerDemoId()],
				$userMap[$kill->getAssisterDemoId()],
				$userMap[$kill->getVictimDemoId()],
				$kill->getWeapon()
			));
		}

		foreach ($demo->getChat() as $chat) {
			$this->chatProvider->storeChatMessage($demoId, $chat);
		}

		return $demoId;
	}
}
