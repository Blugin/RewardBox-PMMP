<?php

/*
 *
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the MIT License. see <https://opensource.org/licenses/MIT>.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://opensource.org/licenses/MIT MIT License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace kim\present\rewardbox\inventory;

use kim\present\rewardbox\RewardBox;
use kim\present\rewardbox\utils\HashUtils;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class RewardInventory extends RewardBoxInventory{
	/** @var Player */
	protected $player;

	/**
	 * RewardBoxInventory constructor.
	 *
	 * @param Position $holder
	 * @param Player   $player
	 * @param Item[]   $items        = []
	 * @param string   $customName   = "RewardBox"
	 * @param int      $creationTime = null
	 */
	public function __construct(Position $holder, Player $player, array $items = [], string $customName = "RewardBox", int $creationTime = null){
		parent::__construct($holder, $items, $customName, $creationTime);
		$this->player = $player;
	}

	/**
	 * @param Player $who
	 */
	public function onClose(Player $who) : void{
		parent::onClose($who);
		$this->writePlayerData($who);
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "RewardInventory";
	}

	/**
	 * @param Player $player
	 *
	 * @return string
	 */
	public function getCustomNameTranslate(Player $player = null) : string{
		return RewardBox::getInstance()->getLanguage()->translate("chest.name.reward", [$this->customName, $player !== null ? $player->getName() : ""]);
	}

	/**
	 * @param Player $player
	 */
	public function writePlayerData(Player $player) : void{
		$dataPath = RewardBox::getInstance()->getDataFolder() . "players/";
		if(!file_exists($dataPath)){
			mkdir($dataPath, 0777, true);
		}

		$filename = "{$dataPath}/{$player->getLowerCaseName()}.dat";
		$namedTag = null;
		if(file_exists($filename)){
			$namedTag = (new BigEndianNBTStream())->readCompressed(file_get_contents($filename));
		}
		if(!$namedTag instanceof CompoundTag){
			$namedTag = new CompoundTag();
		}

		$pos = $this->holder instanceof Position ? $this->holder : Position::fromObject($this->holder, $player->level);
		$namedTag->setTag($this->nbtSerialize(HashUtils::positionHash($pos)));
		file_put_contents($filename, (new BigEndianNBTStream())->writeCompressed($namedTag));
	}

	/**
	 * @param Player $player
	 * @param string $chestHash
	 *
	 * @return null|RewardInventory
	 */
	public static function readPlayerData(Player $player, string $chestHash) : ?RewardInventory{
		$filename = RewardBox::getInstance()->getDataFolder() . "players/{$player->getLowerCaseName()}.dat";
		if(!file_exists($filename)){
			return null;
		}

		$namedTag = (new BigEndianNBTStream())->readCompressed(file_get_contents($filename));
		if(!$namedTag instanceof CompoundTag){
			throw new \RuntimeException("Invalid data found in \"{$filename}\", expected " . CompoundTag::class . ", got " . (is_object($namedTag) ? get_class($namedTag) : gettype($namedTag)));
		}

		$tag = $namedTag->getCompoundTag($chestHash);
		if($tag === null){
			return null;
		}

		return self::fromRewardBox($player, parent::nbtDeserialize($tag));
	}

	/**
	 * @param Player             $player
	 * @param RewardBoxInventory $rewardBox
	 *
	 * @return null|RewardInventory
	 */
	public static function fromRewardBox(Player $player, RewardBoxInventory $rewardBox) : ?RewardInventory{
		return new RewardInventory(
			$rewardBox->getHolder(),
			$player,
			$rewardBox->getContents(true),
			$rewardBox->getCustomName(),
			$rewardBox->getCreationTime()
		);
	}
}
