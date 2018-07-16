<?php

namespace kim\present\rewardbox\act\child;

use kim\present\rewardbox\act\PlayerAct;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Chest;

class EditAct extends PlayerAct{
	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onPlayerInteractEvent(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$player = $event->getPlayer();
			$chest = $player->level->getTile($event->getBlock());
			if($chest instanceof Chest){
				$rewardBoxInventory = $this->plugin->getRewardBox($chest, true);
				if($rewardBoxInventory !== null){
					$player->addWindow($rewardBoxInventory);
				}else{
					$player->sendMessage($this->plugin->getLanguage()->translateString("acts.generic.notRewardBox"));
				}
			}else{
				$player->sendMessage($this->plugin->getLanguage()->translateString("acts.generic.notChest"));
			}

			$event->setCancelled();
			self::unregsiterAct($this);
		}
	}
}