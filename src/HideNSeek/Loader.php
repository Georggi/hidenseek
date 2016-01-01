<?php
namespace HideNSeek;

use pocketmine\level\Level;
use GamesCore\BaseFiles\MiniGameProject;
use GamesCore\Loader as Core;
use pocketmine\tile\Sign;
use HideNSeek\Commands\sethnsspawnpoint;

class Loader extends MiniGameProject{
	public function onEnable(){
		if(!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
        $this->getCore()->getServer()->getCommandMap()->registerAll("HideNSeek", [
                new sethnsspawnpoint($this)
            ]

        );
        // MiniGame registration :3
        /** @var Core $core */
        $core = $this->getServer()->getPluginManager()->getPlugin("GamesCore");
        $core->registerMiniGame($this);


	}

    public function updaterName(){
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
    public function generateMiniGame(Core $core, Level $level, Sign $sign){
        return new HNSGame($core, $this, $level, $sign);
    }
}