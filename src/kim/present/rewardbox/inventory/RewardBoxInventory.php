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
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0.0
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
use pocketmine\math\Vector3;
use pocketmine\nbt\{
	NBT, NetworkLittleEndianNBTStream
};
use pocketmine\nbt\tag\{
	CompoundTag, IntTag, ListTag, StringTag
};
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use pocketmine\tile\Tile;

class RewardBoxInventory extends CustomInventory{
	public const TAG_ITEMS = "Items";
	public const TAG_CUSTOME_NAME = "CustomName";
	public const TAG_CREATION_TIME = "CreationTime";

	/** @var string */
	protected $customName;

	/** @var int */
	protected $creationTime;

	/**
	 * RewardBoxInventory constructor.
	 *
	 * @param Vector3  $holder
	 * @param Item[]   $items        = []
	 * @param string   $customName   = "RewardBox"
	 * @param null|int $creationTime = null
	 */
	public function __construct(Vector3 $holder, array $items = [], string $customName = "RewardBox", ?int $creationTime = null){
		parent::__construct($holder, $items);
		$this->customName = $customName;
		if($creationTime === null){
			$creationTime = time();
		}
		$this->creationTime = $creationTime;
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
			new StringTag("id", "Chest"),
			new IntTag("x", $this->holder->x),
			new IntTag("y", $this->holder->y),
			new IntTag("z", $this->holder->z),
			new StringTag("CustomName", $this->getCustomName($who))
		]));
		$who->sendDataPacket($pk);
		$this->sendContents($who);

		parent::onOpen($who);
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
		return 27;
	}

	/**
	 * @return int
	 */
	public function getNetworkType() : int{
		return WindowTypes::CONTAINER;
	}

	/**
	 * @param Player $player
	 *
	 * @return string
	 */
	public function getCustomName(Player $player = null) : string{
		return RewardBox::getInstance()->getLanguage()->translateString("chest.name.edit", [$this->customName, $player !== null ? $player->getName() : ""]);
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
		$itemsTag = new ListTag(self::TAG_ITEMS, NBT::TAG_Compound);
		for($slot = 0; $slot < 27; ++$slot){
			$item = $this->getItem($slot);
			if(!$item->isNull()){
				$itemsTag->push($item->nbtSerialize($slot));
			}
		}
		return new CompoundTag($tagName, [
			new IntTag(Tile::TAG_X, $this->holder->x),
			new IntTag(Tile::TAG_Y, $this->holder->y),
			new IntTag(Tile::TAG_Z, $this->holder->z),
			$itemsTag,
			new StringTag(self::TAG_CUSTOME_NAME, $this->customName),
			new IntTag(self::TAG_CREATION_TIME, $this->creationTime)
		]);
	}

	/**
	 * @param CompoundTag $tag
	 *
	 * @return RewardBoxInventory
	 */
	public static function nbtDeserialize(CompoundTag $tag) : RewardBoxInventory{
		$itemsTag = $tag->getListTag(self::TAG_ITEMS);
		$items = [];
		/** @var CompoundTag $itemTag */
		foreach($itemsTag as $i => $itemTag){
			$items[$itemTag->getByte("Slot")] = Item::nbtDeserialize($itemTag);
		}

		return new RewardBoxInventory(
			new Vector3(
				$tag->getInt(Tile::TAG_X),
				$tag->getInt(Tile::TAG_Y),
				$tag->getInt(Tile::TAG_Z)
			),
			$items,
			$tag->getString(self::TAG_CUSTOME_NAME),
			$tag->getInt(self::TAG_CREATION_TIME)
		);
	}
}