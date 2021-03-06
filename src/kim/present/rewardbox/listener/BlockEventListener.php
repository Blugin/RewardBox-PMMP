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

use kim\present\rewardbox\act\PlayerAct;
use kim\present\rewardbox\inventory\RewardInventory;
use kim\present\rewardbox\RewardBox;
use kim\present\rewardbox\utils\HashUtils;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class BlockEventListener implements Listener{
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
	 * @priority LOWEST
	 *
	 * @param BlockBreakEvent $event
	 */
	public function onBlockBreakEvent(BlockBreakEvent $event) : void{
		if($this->plugin->getRewardBox($event->getBlock()) !== null){
			$event->getPlayer()->sendMessage($this->plugin->getLanguage()->translate("prevent.destroy"));
			$event->setCancelled();
		}
	}

	/**
	 * @priority LOWEST
	 *
	 * @param PlayerInteractEvent $event
	 */
	public function onPlayerInteractEvent(PlayerInteractEvent $event) : void{
		if($event->isCancelled()){
			return;
		}

		$player = $event->getPlayer();
		$task = PlayerAct::getAct($player);
		if($task !== null){
			$task->onPlayerInteractEvent($event);
		}elseif(!$player->isSneaking() && $event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$rewardBoxInventory = $this->plugin->getRewardBox($event->getBlock(), true);
			if($rewardBoxInventory === null){
				return;
			}

			$rewardInventory = RewardInventory::readPlayerData($player, HashUtils::positionHash($rewardBoxInventory->getHolder()));
			if($rewardInventory !== null && $rewardInventory->getCreationTime() === $rewardBoxInventory->getCreationTime()){
				$rewardInventory->setCustomName($rewardBoxInventory->getCustomName());
				$player->addWindow($rewardInventory);
			}else{
				$player->addWindow(RewardInventory::fromRewardBox($player, $rewardBoxInventory));
			}
			$event->setCancelled();
		}
	}
}
