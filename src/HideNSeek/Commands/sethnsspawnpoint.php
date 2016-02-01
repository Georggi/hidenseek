<?php
/**
 * Copyright (c) Yuriy Shnitkovskiy and Jorge Gonzalez, 2016. Hide N Seek Plugin for PocketMine by Yuriy Shnitkovskiy and Jorge Gonzalez is licensed under the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/4.0/.
 * Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use.
 * NonCommercial — You may not use the material for commercial purposes.
 * NoDerivatives — If you remix, transform, or build upon the material, you may not distribute the modified material.
 */

/**
 * Created by PhpStorm.
 * User: Georggi
 * Date: 8/11/2015
 * Time: 8:14 AM
 */

namespace HideNSeek\Commands;


use HideNSeek\BaseCommand;
use HideNSeek\Loader;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\math\Vector3;
use pocketmine\Player;

class sethnsspawnpoint extends BaseCommand{

    public function __construct(Loader $plugin){
        parent::__construct($plugin, "sethnsspawnpoint", "Command for map-making!!! Creates file for plugin spawnpoints", "/sethnsspawnpoint [Type of player]", ["sethnssp"]);
        $this->setPermission("hns.sethnssp");
    }

    public function execute(CommandSender $sender, $alias, array $args){
        if(!$sender->hasPermission("hns.sethnssp")){
            $sender->sendMessage("You don't have permission to use this command!");
            return false;
        }
        if($sender instanceof ConsoleCommandSender){
            $sender->sendMessage("You can only use this command as ingame player!");
            return false;
        }
        if(count($args) !== 1){
            $sender->sendMessage($this->getUsage());
            return false;
        }
        switch($args[0]){
            case "seekers":
                if(is_dir($this->getPlugin()->getDataFolder())){
                    mkdir($this->getPlugin()->getDataFolder());
                }
                if(!file_exists($this->getPlugin()->getDataFolder())){
                    $pos = $this->getPlugin()->getServer()->getPlayer($sender->getName())->getPosition();
                    file_put_contents($this->getPlugin()->getDataFolder() . $this->getPlugin()->getServer()->getPlayer($sender->getName())->getLevel()->getName() . ".seekerspawn" , new Vector3($pos->getX(), $pos->getY(), $pos->getZ()));
                }
                break;
            case "hiders":
                if(is_dir($this->getPlugin()->getDataFolder())){
                    mkdir($this->getPlugin()->getDataFolder());
                }
                if(!file_exists($this->getPlugin()->getDataFolder())){
                    $pos = $this->getPlugin()->getServer()->getPlayer($sender->getName())->getPosition();
                    file_put_contents($this->getPlugin()->getDataFolder() . $this->getPlugin()->getServer()->getPlayer($sender->getName())->getLevel()->getName() . ".hiderspawn" , new Vector3($pos->getX(), $pos->getY(), $pos->getZ()));
                }
                break;
            default:
                $sender->sendMessage($sender instanceof Player ? $this->getUsage() : "You can only use this command as ingame player!");
                return false;
                break;
        }

        return true;
    }
}