<?php

namespace kim\present\rewardbox\act\child;

use kim\present\rewardbox\act\PlayerAct;
use kim\present\rewardbox\RewardBox;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;
use pocketmine\tile\Chest;

class CreateAct extends PlayerAct{
	/** @var string */
	private $name;

	/**
	 * NameAct constructor.
	 *
	 * @param RewardBox $plugin
	 * @param Player    $player
	 * @param string    $name
	 */
	public function __construct(RewardBox $plugin, Player $player, string $name){
		parent::__construct($plugin, $player);
		$this->name = $name;
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public function onPlayerInteractEvent(PlayerInteractEvent $event) : void{
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$player = $event->getPlayer();
			$chest = $player->level->getTile($event->getBlock());
			if($chest instanceof Chest){
				if($this->plugin->createRewardBox($chest, $this->name)){
					$player->sendMessage($this->plugin->getLanguage()->translateString("acts.create.success"));
				}else{
					$player->sendMessage($this->plugin->getLanguage()->translateString("acts.create.already"));
				}
			}else{
				$player->sendMessage($this->plugin->getLanguage()->translateString("acts.generic.notChest"));
			}

			$event->setCancelled();
			self::unregsiterAct($this);
		}
	}
}