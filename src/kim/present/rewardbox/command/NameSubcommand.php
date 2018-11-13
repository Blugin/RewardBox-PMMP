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

namespace kim\present\rewardbox\command;

use kim\present\rewardbox\act\PlayerAct;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\tile\Chest;

class NameSubcommand extends Subcommand{
	public const LABEL = "name";

	/**
	 * @param CommandSender $sender
	 * @param string[]      $args = []
	 */
	public function execute(CommandSender $sender, array $args = []) : void{
		if(!$sender instanceof Player){
			$sender->sendMessage($this->plugin->getLanguage()->translate("commands.generic.onlyPlayer"));
			return;
		}

		if(empty($args)){
			$sender->sendMessage($this->plugin->getLanguage()->translate("commands.rewardbox.name.usage"));
			return;
		}

		$name = implode(" ", $args);
		$closure = function(Chest $chest) use ($sender, $name) : bool{
			$rewardBoxInventory = $this->plugin->getRewardBox($chest, true);
			if($rewardBoxInventory === null){
				$sender->sendMessage($this->plugin->getLanguage()->translate("acts.generic.notRewardBox"));
				return true;
			}

			$rewardBoxInventory->setCustomName($name);
			$sender->sendMessage($this->plugin->getLanguage()->translate("acts.name.success", [$name]));
			return true;
		};
		PlayerAct::registerAct(new PlayerAct($this->plugin, $sender, $closure));
		$sender->sendMessage($this->plugin->getLanguage()->translate("commands.rewardbox.name"));
	}
}
