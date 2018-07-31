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

namespace kim\present\rewardbox\listener;

use kim\present\rewardbox\act\PlayerAct;
use kim\present\rewardbox\inventory\RewardInventory;
use kim\present\rewardbox\RewardBox;
use kim\present\rewardbox\utils\HashUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class PlayerEventListener implements Listener{
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
	 * @param PlayerInteractEvent $event
	 */
	public function onPlayerInteractEvent(PlayerInteractEvent $event) : void{
		$player = $event->getPlayer();
		if(!$event->isCancelled()){
			$task = PlayerAct::getAct($player);
			if($task !== null){
				$task->onPlayerInteractEvent($event);
			}elseif(!$player->isSneaking() && $event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
				$rewardBoxInventory = $this->plugin->getRewardBox($event->getBlock(), true);
				if($rewardBoxInventory !== null){
					$rewardInventory = RewardInventory::fromPlayer($player, HashUtils::positionHash($rewardBoxInventory->getHolder()));
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
	}
}
