<?php
namespace HideNSeek;

use GamesCore\GamesPlayer;
use pocketmine\block\Air;
use pocketmine\block\Block;

class HNSBlock extends Block{
	/** @var GamesPlayer */
	private $owner;
	/** @var bool */
	private $vanished = false;
	/** @var Air */
	private $tempBlock;

	public function __construct($id, $meta, GamesPlayer $player){
		parent::__construct($id, $meta);
		$this->owner = $player;
		$this->tempBlock = new Air();
	}

	public function getOwner(){
		return $this->owner;
	}

	public function isVanished(){
		return $this->vanished;
	}

	public function setVanished($value = true){
		$this->vanished = $value;
		$this->getOwner()->stopDisguise(true, false);
		$this->getLevel()->setBlock($this, $value ? $this->tempBlock : $this, true, false);
	}

	/**
	 * This is an example of "Optimized code" since PocketMine always tick blocks using this function...
	 */
	public function onUpdate(){
		if($this->isVanished() === $this->getOwner()->isSneaking()){ // Inverted check... Vanished = !Sneaking, !Vanished = Sneaking
			$this->setVanished(!$this->getOwner()->isSneaking());
		}
		if(!$this->equals($this->getOwner())){
			$this->setComponents($this->getOwner()->getFloorX(), $this->getOwner()->getFloorY(), $this->getOwner()->getFloorZ());
			if(!$this->isVanished()){
				$this->getLevel()->setBlock($this, $this->tempBlock, true, false);
				$this->getLevel()->setBlock($this->getOwner(), $this, true, false);
			}
		}
	}
}