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

namespace kim\present\rewardbox\utils;

use pocketmine\level\Position;
use pocketmine\Player;

final class HashUtils{
	/**
	 * @param Position $pos
	 *
	 * @return string
	 */
	public static function positionHash(Position $pos) : string{
		return $pos->x . ":" . $pos->y . ":" . $pos->z . ":" . $pos->level->getFolderName();
	}

	public static function playerHash(Player $player) : string{
		return $player->getLowerCaseName();
	}
}
