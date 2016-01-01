<?php
namespace HideNSeek\Inventories;

use GamesCore\GamesPlayer;
use Core\BaseFiles\BaseInventory;
use pocketmine\item\Apple;
use pocketmine\item\Bow;
use pocketmine\item\Carrot;
use pocketmine\item\Item;
use pocketmine\item\WoodenSword;
use pocketmine\Player;

class Hider extends BaseInventory{
    public function __construct(GamesPlayer $player){
        parent::__construct($player, [
            new WoodenSword(),
            new Bow(),
            new Item(Item::ARROW, 0, 32),
            new Apple(0, 5),
            new Carrot(0, 5),
        ]);
    }
}