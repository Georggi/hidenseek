<?php
namespace HideNSeek\Inventories;

use GamesCore\GamesPlayer;
use Core\BaseFiles\BaseInventory;
use pocketmine\item\Carrot;
use pocketmine\item\IronBoots;
use pocketmine\item\IronChestplate;
use pocketmine\item\IronLeggings;
use pocketmine\item\IronSword;

class Seeker extends BaseInventory{
    public function __construct(GamesPlayer $player){
        parent::__construct($player, [
            new IronSword(),
            new Carrot(0, 5),
        ]);
        $this->setBoots(new IronBoots());
        $this->setChestplate(new IronChestplate());
        $this->setLeggings(new IronLeggings());
    }
}