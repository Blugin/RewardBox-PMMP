<?php

namespace kim\present\rewardbox\act\child;

use kim\present\rewardbox\act\PlayerAct;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Chest;

class RemoveAct extends PlayerAct{
	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onPlayerInteractEvent(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$player = $event->getPlayer();
			$chest = $player->level->getTile($event->getBlock());
			if($chest instanceof Chest){
				if($this->plugin->removeRewardBox($chest, true)){
					$player->sendMessage($this->plugin->getLanguage()->translate("acts.remove.success"));
				}else{
					$player->sendMessage($this->plugin->getLanguage()->translate("acts.generic.notRewardBox"));
				}
			}else{
				$player->sendMessage($this->plugin->getLanguage()->translate("acts.generic.notChest"));
			}

			$event->setCancelled();
			self::unregsiterAct($this);
		}
	}
}