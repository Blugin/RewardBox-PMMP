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

namespace kim\present\rewardbox\listener;

use kim\present\rewardbox\inventory\RewardInventory;
use kim\present\rewardbox\RewardBox;
use pocketmine\event\inventory\{InventoryOpenEvent, InventoryTransactionEvent};
use pocketmine\event\Listener;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;

class InventoryEventListener implements Listener{
	/** @var RewardBox */
	private $plugin;

	/**
	 * InventoryEventListener constructor.
	 *
	 * @param RewardBox $plugin
	 */
	public function __construct(RewardBox $plugin){
		$this->plugin = $plugin;
	}

	/**
	 * @priority MONITOR
	 *
	 * @param InventoryTransactionEvent $event
	 */
	public function onInventoryTransactionEvent(InventoryTransactionEvent $event) : void{
		foreach($event->getTransaction()->getActions() as $key => $action){
			if($action instanceof SlotChangeAction && $action->getSourceItem()->count < $action->getTargetItem()->count){
				$inventory = $action->getInventory();
				if($inventory instanceof RewardInventory){
					$event->setCancelled();
					return;
				}elseif($inventory instanceof ChestInventory && $this->plugin->getRewardBox($inventory->getHolder(), true) !== null){
					$event->setCancelled();
				}
			}
		}
	}

	/**
	 * @priority LOWEST
	 *
	 * @param InventoryOpenEvent $event
	 */
	public function onInventoryOpenEvent(InventoryOpenEvent $event) : void{
		$inventory = $event->getInventory();
		if($inventory instanceof ChestInventory && $this->plugin->getRewardBox($inventory->getHolder(), true) !== null){
			$event->setCancelled();
		}
	}
}
