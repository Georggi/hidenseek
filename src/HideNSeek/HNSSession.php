<?php
/**
 * Copyright(c) Yuriy Shnitkovskiy and Jorge Gonzalez, 2016. Hide N Seek Plugin for PocketMine by Yuriy Shnitkovskiy and Jorge Gonzalez is licensed under the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/4.0/.
 * Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use.
 * NonCommercial — You may not use the material for commercial purposes.
 * NoDerivatives — If you remix, transform, or build upon the material, you may not distribute the modified material.
 */

namespace HideNSeek;

use GamesCore\BaseFiles\BaseSession;
use GamesCore\GamesPlayer;
use pocketmine\block\Block;

/**
 * Class HNSSession
 * @package HideNSeek
 */
class HNSSession extends BaseSession {
	/** @var bool */
	private $isHidden;
	/** @var int */
	private $id;
	/** @var int */
	private $meta;
	/** @var bool|Block */
	private $block = false;

	public function __construct( GamesPlayer $player, HNSGame $game, $blockId, $blockMeta ) {
		parent::__construct( $player, $game );
		$this->id = $blockId;
		$this->meta = $blockMeta;
		$this->block = new HNSBlock( $blockId, $blockMeta, $player );
	}

	/**
	 * @return HNSGame
	 */
	public function getGame() {
		return parent::getGame();
	}

	/**
	 * @return bool
	 */
	public function isBlock() {
		return $this->getBlock()->isVanished();
	}

	/**
	 * @return HNSBlock
	 */
	public function getBlock() {
		return $this->block;
	}

	/**
	 * @param bool $mode
	 */
	public function setHidden( $mode ) {
		$this->isHidden = $mode;
		if ( $mode ) {
			$this->getPlayer()->startDisguise( GamesPlayer::DISGUISE_ENTITY_FALLING_BLOCK, [ "TileID" => $this->getID(), "Data" => $this->getMeta() ] );
		} else {
			if ( $this->getPlayer()->isDisguised() ) {
				$this->getPlayer()->stopDisguise();
			}
			$this->getBlock()->setVanished( false );
		}
	}

	/**
	 * @return int
	 */
	public function getID() {
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getMeta() {
		return $this->meta;
	}

	public function onGameEnd() {
		if ( $this->isHidden() ) {
			$this->getPlayer()->setDataFlag( GamesPlayer::DATA_FLAGS, GamesPlayer::DATA_FLAG_INVISIBLE, false );
			$this->getPlayer()->setDataProperty( GamesPlayer::DATA_SHOW_NAMETAG, GamesPlayer::DATA_TYPE_BYTE, true );
		}
		$this->getPlayer()->removeAllEffects();
		parent::onGameEnd();
	}

	/**
	 * @return bool
	 */
	public function isHidden() {
		return $this->isHidden;
	}
}