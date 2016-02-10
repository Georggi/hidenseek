<?php
/**
 * Copyright (c) Yuriy Shnitkovskiy and Jorge Gonzalez, 2016. Hide N Seek Plugin for PocketMine by Yuriy Shnitkovskiy and Jorge Gonzalez is licensed under the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/4.0/.
 * Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use.
 * NonCommercial — You may not use the material for commercial purposes.
 * NoDerivatives — If you remix, transform, or build upon the material, you may not distribute the modified material.
 */

namespace HideNSeek\Inventories;

use Core\BaseFiles\BaseInventory;
use GamesCore\GamesPlayer;
use pocketmine\item\Carrot;
use pocketmine\item\IronBoots;
use pocketmine\item\IronChestplate;
use pocketmine\item\IronLeggings;
use pocketmine\item\IronSword;

class Seeker extends BaseInventory {
	public function __construct( GamesPlayer $player ) {
		parent::__construct( $player, [
			new IronSword(),
			new Carrot( 0, 5 ),
		] );
		$this->setBoots( new IronBoots() );
		$this->setChestplate( new IronChestplate() );
		$this->setLeggings( new IronLeggings() );
	}
}