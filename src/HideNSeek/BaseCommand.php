<?php
namespace HideNSeek;

use pocketmine\command\Command;
use pocketmine\command\PluginIdentifiableCommand;

abstract class BaseCommand extends Command implements PluginIdentifiableCommand{
    /** @var Loader  */
    private $plugin;

    public function __construct(Loader $plugin, $name, $description = "", $usageMessage = null, array $aliases = []){
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->plugin = $plugin;
    }

    /**
     * @return Loader
     */
    public final function getPlugin(){
        return $this->plugin;
    }
}