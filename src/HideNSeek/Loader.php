<?php
/**
 * Copyright (c) Yuriy Shnitkovskiy and Jorge Gonzalez, 2016. Hide N Seek Plugin for PocketMine by Yuriy Shnitkovskiy and Jorge Gonzalez is licensed under the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/4.0/ .
 * Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use.
 * NonCommercial — You may not use the material for commercial purposes.
 * NoDerivatives — If you remix, transform, or build upon the material, you may not distribute the modified material.
 */

namespace HideNSeek;

use pocketmine\level\Level;
use GamesCore\BaseFiles\MiniGameProject;
use GamesCore\Loader as Core;
use pocketmine\tile\Sign;
use HideNSeek\Commands\sethnsspawnpoint;

class Loader extends MiniGameProject {
	public function onEnable() {
		if( !is_dir( $this->getDataFolder() ) ) {
			mkdir( $this->getDataFolder() );
		}
        $this->getCore()->getServer()->getCommandMap()->registerAll( "HideNSeek", [
                new sethnsspawnpoint( $this )
            ]

        );
        // MiniGame registration :3
        /** @var Core $core */
        $core = $this->getServer()->getPluginManager()->getPlugin( "GamesCore" );
        $core->registerMiniGame( $this );


	}

    public function updaterName() {
        return "HideNSeek";
    }

	/**           _____ _____
	 *      /\   |  __ |_   _|
	 *     /  \  | |__) || |
	 *    / /\ \ |  ___/ | |
	 *   / ____ \| |    _| |_
	 *  /_/    \_|_|   |_____|
	 */

    /**
     * @param Core $core
     * @param Level $level
     * @param Sign $sign
     * @return HNSGame
     */
    public function generateMiniGame( Core $core, Level $level, Sign $sign ) {
        return new HNSGame( $core, $this, $level, $sign );
    }
}