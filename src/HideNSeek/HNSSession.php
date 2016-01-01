<?php
namespace HideNSeek;

use GamesCore\GamesPlayer;
use GamesCore\BaseFiles\BaseSession;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\Player;

/**
 * Class HNSSession
 * @package HideNSeek
 */
class HNSSession extends BaseSession{
    public function __construct(GamesPlayer $player, HNSGame $game){
        parent::__construct($player, $game);
    }

    /**
     * @return HNSGame
     */
    public function getGame(){
        return parent::getGame();
    }

    /** @var bool */
    private $isHidden;
    /** @var null */
    private $lastMovement = null;
    /** @var bool */
    private $isBlock;
    /** @var int */
    private $id;
    /** @var int */
    private $meta;
    /** @var bool */
    private $hasSelectedBlock;
    /** @var string */
    private $Selectedblock;

    /**
     * @return int
     */
    public function getID(){
        return $this->id;
    }

    /**
     * @return int
     */
    public function getMeta(){
        return $this->meta;
    }

    /**
     * @return bool
     */
    public function isPlaced(){
        return $this->isBlock;
    }

    /**
     * @param bool $mode
     */
    public function setPlaced($mode){
        $this->isBlock = $mode;
    }

    /**
     * @return bool
     */
    public function isHidden(){
        return $this->isHidden;
    }

    /**
     * @param bool $mode
     */
    public function setHidden($mode){
        $this->isHidden = $mode;
        if($mode){
            if ($this->hasSelectedBlock){
                $block = $this->Selectedblock; // If player will select a block, he will get that block, if not - random block linked to map TODO:Implement block selection
            } else {
                $block = explode(":" , $this->getGame()->getMapBlocks()[array_rand($this->getGame()->getMapBlocks(), 1)]);
            }
            $id = (int) $block[0];
            if(isset($block[1])){
                $meta = (int) $block[1];
            } else{
                $meta = 0;
            }
            parent::startDisguise(66, ["TileID" => $id, "Data" => $meta]);
            $this->id = $id;
            $this->meta = $meta;
            $this->isBlock = false;
        }
        if(!$mode){
            if($this->isDisguised() !== NULL){
                $this->stopDisguise(false, true);
            }
        }
    }

    /** @var bool|Block */
    private $block = false;

    /**
     * @return bool|Block
     */
    public function getBlock(){
        if(!$this->block){
            $this->block = new Block($this->getID(), $this->getMeta());
        }
        return $this->block;
    }

    /*
     * This function removes block that placed in the world when entity stays still for 5 seconds
     */

    /**
     * @param Player $player
     */
    public function removeBlock(Player $player){
        if($this->block !== false){
            $player->getLevel()->setBlock(new Vector3($this->getBlock()->getFloorX(), $this->getBlock()->getFloorY(), $this->getBlock()->getFloorZ()), new Block(0), true, false);
            $this->block = false;
            $this->setPlaced(false);
            $this->startDisguise(66, ["TileID" => $this->getID(), "Data" => $this->getMeta()]);
        }
    }
	
    /**
     * @return int|null
     */
    public function getLastMovement(){
        return $this->lastMovement;
    }

    /**
     * @param $time
     */
    public function setLastMovement($time){
        $this->lastMovement = (int) $time;
    }

    public function onGameEnd(){
        if($this->isHidden()){
            $this->getPlayer()->setDataFlag(Player::DATA_FLAGS, Player::DATA_FLAG_INVISIBLE, false);
            $this->getPlayer()->setDataProperty(Player::DATA_SHOW_NAMETAG, Player::DATA_TYPE_BYTE, 1);
        }
        $this->getPlayer()->removeAllEffects();
        parent::onGameEnd();
    }
}