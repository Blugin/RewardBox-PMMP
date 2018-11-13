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
use pocketmine\inventory\CustomInventory;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\nbt\{NBT, NetworkLittleEndianNBTStream};
use pocketmine\nbt\tag\{CompoundTag, IntTag, ListTag, StringTag};
use pocketmine\network\mcpe\protocol\{BlockEntityDataPacket, InventoryContentPacket};
use pocketmine\network\mcpe\protocol\types\{ContainerIds, WindowTypes};
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Chest;
use pocketmine\tile\Tile;

class RewardBoxInventory extends CustomInventory{
	public const TAG_WORLD = "World";
	public const TAG_CREATION_TIME = "CreationTime";

	/** @var string */
	protected $customName;

	/** @var int */
	protected $creationTime;

	/**
	 * RewardBoxInventory constructor.
	 *
	 * @param Position $holder
	 * @param Item[]   $items        = []
	 * @param string   $customName   = "RewardBox"
	 * @param null|int $creationTime = null
	 */
	public function __construct(Position $holder, array $items = [], string $customName = "RewardBox", ?int $creationTime = null){
		parent::__construct($holder, $items);
		$this->customName = $customName;
		$this->creationTime = $creationTime ?? time();
	}

	/**
	 * @param Player $who
	 */
	public function onOpen(Player $who) : void{
		$pk = new BlockEntityDataPacket();
		$pk->x = $this->holder->x;
		$pk->y = $this->holder->y;
		$pk->z = $this->holder->z;
		$pk->namedtag = (new NetworkLittleEndianNBTStream())->write(new CompoundTag("", [
			new StringTag(TILE::TAG_ID, TILE::CHEST),
			new IntTag(TILE::TAG_X, $pk->x),
			new IntTag(TILE::TAG_Y, $pk->y),
			new IntTag(TILE::TAG_Z, $pk->z),
			new StringTag(Chest::TAG_CUSTOM_NAME, $this->getCustomNameTranslate($who))
		]));
		$who->sendDataPacket($pk);

		parent::onOpen($who);
	}

	/**
	 * @param Player $who
	 */
	public function onClose(Player $who) : void{
		$chest = $who->getLevel()->getTile($this->holder);
		if($chest instanceof Chest){
			$namedTag = $chest->getSpawnCompound();
			if(!$namedTag->hasTag(Chest::TAG_CUSTOM_NAME)){
				$namedTag->setString(Chest::TAG_CUSTOM_NAME, "%container.chest" . ($chest->isPaired() ? "Double" : ""));
			}
			$pk = new BlockEntityDataPacket();
			$pk->x = $this->holder->x;
			$pk->y = $this->holder->y;
			$pk->z = $this->holder->z;
			$pk->namedtag = (new NetworkLittleEndianNBTStream())->write($namedTag);
			$who->sendDataPacket($pk);
		}

		parent::onClose($who);
	}

	/**
	 * @Override for prevent inventory size error in client
	 *
	 * @param Player|Player[] $target
	 */
	public function sendContents($target) : void{
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new InventoryContentPacket();
		$pk->items = $this->getContents(true);
		$chest = $this->getHolder()->level->getTile($this->holder);
		if($chest instanceof Chest && !$chest->isPaired()){
			$pk->items = array_slice($pk->items, 0, 27);
		}

		foreach($target as $player){
			$id = $player->getWindowId($this);
			if($id === ContainerIds::NONE){
				$this->close($player);
				continue;
			}
			$pk->windowId = $id;
			$player->sendDataPacket($pk);
		}
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return "RewardBoxInventory";
	}

	/**
	 * @return int
	 */
	public function getDefaultSize() : int{
		return 54;
	}

	/**
	 * @return int
	 */
	public function getNetworkType() : int{
		return WindowTypes::CONTAINER;
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 *
	 * @return Position|Vector3
	 */
	public function getHolder(){
		return $this->holder;
	}

	/**
	 * @return string
	 */
	public function getCustomName() : string{
		return $this->customName;
	}

	/**
	 * @param Player $player
	 *
	 * @return string
	 */
	public function getCustomNameTranslate(Player $player = null) : string{
		return RewardBox::getInstance()->getLanguage()->translate("chest.name.edit", [$this->customName, $player !== null ? $player->getName() : ""]);
	}

	/**
	 * @param string $customName
	 */
	public function setCustomName(string $customName) : void{
		$this->customName = $customName;
	}

	/**
	 * @return int
	 */
	public function getCreationTime() : int{
		return $this->creationTime;
	}

	/**
	 * @param int $creationTime
	 */
	public function setCreationTime(int $creationTime) : void{
		$this->creationTime = $creationTime;
	}

	/**
	 * @param string $tagName =  "RewardBox"
	 *
	 * @return CompoundTag
	 */
	public function nbtSerialize(string $tagName = "RewardBox") : CompoundTag{
		$itemsTag = new ListTag(Chest::TAG_ITEMS, [], NBT::TAG_Compound);
		for($slot = 0; $slot < $this->getSize(); ++$slot){
			$item = $this->getItem($slot);
			if(!$item->isNull()){
				$itemsTag->push($item->nbtSerialize($slot));
			}
		}
		return new CompoundTag($tagName, [
			new IntTag(Tile::TAG_X, $this->holder->x),
			new IntTag(Tile::TAG_Y, $this->holder->y),
			new IntTag(Tile::TAG_Z, $this->holder->z),
			new StringTag(self::TAG_WORLD, $this->getHolder()->level->getFolderName()),
			$itemsTag,
			new StringTag(Chest::TAG_CUSTOM_NAME, $this->customName),
			new IntTag(self::TAG_CREATION_TIME, $this->creationTime)
		]);
	}

	/**
	 * @param CompoundTag $tag
	 *
	 * @return RewardBoxInventory
	 */
	public static function nbtDeserialize(CompoundTag $tag) : RewardBoxInventory{
		$itemsTag = $tag->getListTag(Chest::TAG_ITEMS);
		$items = [];
		/** @var CompoundTag $itemTag */
		foreach($itemsTag as $i => $itemTag){
			$items[$itemTag->getByte("Slot")] = Item::nbtDeserialize($itemTag);
		}
		return new RewardBoxInventory(
			new Position(
				$tag->getInt(Tile::TAG_X),
				$tag->getInt(Tile::TAG_Y),
				$tag->getInt(Tile::TAG_Z),
				Server::getInstance()->getLevelByName($tag->getString(self::TAG_WORLD))
			),
			$items,
			$tag->getString(Chest::TAG_CUSTOM_NAME),
			$tag->getInt(self::TAG_CREATION_TIME)
		);
	}
}
