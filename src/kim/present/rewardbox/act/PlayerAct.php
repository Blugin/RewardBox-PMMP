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

namespace kim\present\rewardbox\act;

use kim\present\rewardbox\RewardBox;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;

abstract class PlayerAct{
	/** @var PlayerAct[] */
	private static $acts = [];

	/**
	 * @return PlayerAct[]
	 */
	public static function getActs() : array{
		return PlayerAct::$acts;
	}

	/**
	 * @param Player $player
	 *
	 * @return PlayerAct|null
	 */
	public static function getAct(Player $player) : ?PlayerAct{
		return PlayerAct::$acts[$player->getLowerCaseName()] ?? null;
	}

	public static function unregisterAll() : void{
		PlayerAct::$acts = [];
	}

	/**
	 * @param PlayerAct $task
	 */
	public static function registerAct(PlayerAct $task) : void{
		PlayerAct::$acts[$task->getKey()] = $task;
	}

	/**
	 * @param PlayerAct $task
	 */
	public static function unregsiterAct(PlayerAct $task) : void{
		unset(PlayerAct::$acts[$task->getKey()]);
	}

	/** @var RewardBox */
	protected $plugin;

	/** @var Player */
	protected $player;

	/**
	 * PlayerAct constructor.
	 *
	 * @param RewardBox $plugin
	 * @param Player    $player
	 */
	public function __construct(RewardBox $plugin, Player $player){
		$this->plugin = $plugin;
		$this->player = $player;
	}

	/**
	 * @return string
	 */
	public function getKey() : string{
		return $this->player->getLowerCaseName();
	}

	/**
	 * @param PlayerInteractEvent $event
	 */
	public abstract function onPlayerInteractEvent(PlayerInteractEvent $event) : void;
}