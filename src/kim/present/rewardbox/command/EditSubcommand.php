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

namespace kim\present\rewardbox\command;

use kim\present\rewardbox\act\child\EditAct;
use kim\present\rewardbox\act\PlayerAct;
use kim\present\rewardbox\RewardBox;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class EditSubcommand extends Subcommand{
	/**
	 * EditSubcommand constructor.
	 *
	 * @param RewardBox $plugin
	 */
	public function __construct(RewardBox $plugin){
		parent::__construct($plugin, "edit");
	}

	/**
	 * @param CommandSender $sender
	 * @param string[]      $args = []
	 */
	public function execute(CommandSender $sender, array $args = []) : void{
		if($sender instanceof Player){
			PlayerAct::registerAct(new EditAct($this->plugin, $sender));
			$sender->sendMessage($this->plugin->getLanguage()->translateString("commands.rewardbox.edit"));
		}else{
			$sender->sendMessage($this->plugin->getLanguage()->translateString("commands.generic.onlyPlayer"));
		}
	}
}