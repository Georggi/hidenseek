<?php
/**
 * Copyright(c) Yuriy Shnitkovskiy and Jorge Gonzalez, 2016. Hide N Seek Plugin for PocketMine by Yuriy Shnitkovskiy and Jorge Gonzalez is licensed under the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/4.0/.
 * Attribution — You must give appropriate credit, provide a link to the license, and indicate if changes were made. You may do so in any reasonable manner, but not in any way that suggests the licensor endorses you or your use.
 * NonCommercial — You may not use the material for commercial purposes.
 * NoDerivatives — If you remix, transform, or build upon the material, you may not distribute the modified material.
 */

namespace HideNSeek;

use GamesCore\BaseFiles\BaseMiniGame;
use GamesCore\GamesPlayer;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\level\Level;
use pocketmine\tile\Sign;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use HideNSeek\Inventories\Hider;
use HideNSeek\Inventories\Seeker;

class HNSGame extends BaseMiniGame{
    /** @var GamesPlayer */
    private $lastHider;
    /** @var Vector3 */
    private $SeekersSpawnPoint;
    /** @var array */
    private $blocks = [
        "57:0",
        "1:0",
        "3:0"
    ];
	/** @var array */
	private $blockSelection = [];
    //private $HidersSpawnPoint;


    public function __construct($core, $plugin, Level $level, Sign $sign){
        // Loader, MinigameProject, Level, Sign, maximum players, minimum players, time of game, number of rounds, ??, game end message
        parent::__construct($core, $plugin, $level, $sign, 10, 2, 5, 1, false);
    }

	/**
	 * @return \GamesCore\BaseFiles\MiniGameProject
	 */
	public function getPlugin(){
		return parent::getPlugin();
	}

    public function onGameStart(){
        //$this->SeekersSpawnPoint = json_decode(stream_get_contents($resource = $this->getPlugin()->getResource($this->getLevel()->getName() . ".seekerspawn")));
        //fclose($resource);
        //$this->HidersSpawnPoint = json_decode(stream_get_contents($resource = $this->getPlugin()->getResource($this->getLevel()->getName() . ".hiderspawn")));
        //fclose($resource);
        $seeker = $this->getAllPlayers()[array_rand($this->getAllPlayers())];
        $this->getSession($seeker)->setHidden(false);
        $seeker->addWindow(new Seeker($seeker));
		foreach($this->getAllPlayers() as $p){
			if($this->getSession($p)->isHidden() === null){
				$this->getSession($p)->setHidden(true);
				$p->addWindow(new Hider($p));
			}
		}
    }

    public function onGameEnd(){
        /** @var GamesPlayer[] $Hiders */
        $Hiders = [];
        /** @var GamesPlayer[] $Seekers */
        $Seekers = [];
        var_dump("game ended");
        foreach($this->getAllSessions() as $s){
            if($s->isHidden()){
                $Hiders[] = $s->getPlayer();
            }else{
                $Seekers[] = $s->getPlayer();
            }

            /*foreach($this->getPlugin()->getServer()->getDefaultLevel()->getPlayers() as $p){
                // Show the current players to all the players in the lobby...
                if(!$this->getCore()->getCore()->isMagicClockEnabled($p)){
                    $p->showPlayer($s->getPlayer());
                }else{
                    $p->hidePlayer($s->getPlayer());
                }*/

                // Show the players in the lobby to the current player...
                /*if(!$this->getCore()->getCore()->isMagicClockEnabled($s->getPlayer())){
                    $s->getPlayer()->showPlayer($p);
                }else{
                    $s->getPlayer()->hidePlayer($p);
                }
            }*/
        }
        // Reward seekers players
        foreach($Seekers as $s){
            $reward = 3;
            if(count($Hiders) < 1 &&($this->lastHider instanceof GamesPlayer && $s === $this->lastHider)){
                $reward = 5;
            }
            //$this->getCore()->getCore()->addPlayerCoins($s, $reward);
            $s->sendTip(TextFormat::AQUA . "You won " . TextFormat::LIGHT_PURPLE . $reward . TextFormat::AQUA . " coins!");
        }
        // Reward hiders players
        foreach($Hiders as $h){
            //$this->getCore()->getCore()->addPlayerCoins($h, 10);
            $h->sendTip(TextFormat::AQUA . "You won " . TextFormat::LIGHT_PURPLE . "10" . TextFormat::AQUA . " coins!");// TODO: Language
        }
    }

    /**
     * @param GamesPlayer $player
     */
    public function onPlayerJoin(GamesPlayer $player){
	}

	/**
	 * @param GamesPlayer $player
	 * @param Block $block
	 */
	public function selectBlock(GamesPlayer $player, Block $block){
		$this->blockSelection[$player->getId()] = [$block->getId(), $block->getDamage()];
	}

    /**
     * @return HNSSession[]
     */
    public function getAllSessions(){
        return parent::getAllSessions();
    }

	/**
	 * @param GamesPlayer $player
	 * @return HNSSession
	 */
    public function generateSession(GamesPlayer $player){
		$block = isset($this->blockSelection[$player->getId()]) ? $this->blockSelection[$player->getId()] : explode(":", $this->getMapBlocks()[array_rand($this->getMapBlocks())]);
		$blockId = $block[0];
		$blockMeta = $block[1];

        return new HNSSession($player, $this, $blockId, $blockMeta);
    }

    /**
     * @param GamesPlayer $player
     * @return bool|HNSSession
     */
    public function getSession(GamesPlayer $player){
        return parent::getSession($player);
    }

    /**
     * @return array
     */
    public function getMapBlocks(){
        return $this->blocks;
    }


    /**
     * @param GamesPlayer $player
     * @return bool|Block
     */
    public function getBlock(GamesPlayer $player){
        return $this->getSession($player)->getBlock();
    }

    /**
     * @param GamesPlayer $player
     * @return bool
     */
    public function isHidden(GamesPlayer $player){
        return $this->getSession($player)->isHidden();
    }

    /**
     * @param GamesPlayer $player
     */
    public function setSeeker(GamesPlayer $player){
        $this->getSession($player)->setHidden(false);
    }

    /**
     * @var array
     */
    private $damageTable = [
        Item::WOODEN_SWORD => 4,
        Item::IRON_SWORD => 6,
    ];

    /**
     * @param Item $item
     * @param GamesPlayer $player
     */
    public function sendDamageToPlayer(Item $item, GamesPlayer $player){
        $damage = [
            EntityDamageEvent::MODIFIER_BASE => isset($this->damageTable[ $item->getId() ]) ? $this->damageTable[ $item->getId() ] : 1
        ];
        new EntityDamageEvent($player, "Ponies", $damage[ 0 ]);
    }

    /*  _______________________________
     * | ______               _        |
     * ||  ____|             | |       |
     * || |____   _____ _ __ | |_ ___  |
     * ||  __\ \ / / _ | '_ \| __/ __| |
     * || |___\ V |  __| | | | |_\__ \ |
     * ||______\_/ \___|_| |_|\__|___/ |
     * |_______________________________|
     */

    /**
     * @param PlayerInteractEvent $event
	 *
	 * @priority MONITOR
     */
    public function onPlayerInteract(PlayerInteractEvent $event){
		if($this->validEvent($event)){
			/** @var GamesPlayer $player */
			$player = $event->getPlayer();
			$block = $event->getBlock();
			if($block instanceof HNSBlock){
				$this->getCore()->getServer()->getPluginManager()->callEvent($ev = new EntityDamageByEntityEvent($player, $block->getOwner(), EntityDamageByEntityEvent::CAUSE_CONTACT, 1));
				$block->getOwner()->attack($ev->getDamage(), $ev);
			}
		}
	}

    /**
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event){
		if($this->validEvent($event)){
			/** @var GamesPlayer $player */
			$player = $event->getPlayer();
			if($this->getSession($player)->isBlock()){
				$this->getSession($player)->getBlock()->onUpdate();
			}
		}
    }

    /**
     * @param EntityDamageEvent $event
	 * @priority HIGHEST
     */
    public function onEntityDamage(EntityDamageEvent $event){
		if($this->validEvent($event) && $event instanceof EntityDamageByEntityEvent){
			/** @var GamesPlayer $damager */
			$damager = $event->getDamager();
			/** @var GamesPlayer $victim */
			$victim = $event->getEntity();
			if($this->getSession($victim)->isHidden() === $this->getSession($damager)->isHidden()){
				$event->setCancelled(); // Cancel the event if both players are from the same team(Hiders or seekers)
			}elseif($this->getSession($victim)->isHidden() && !$this->getSession($damager)->isHidden()){
				$this->getSession($victim)->setHidden(true);
			}
		}
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onPlayerRespawn(PlayerRespawnEvent $event){
		if($this->validEvent($event)){
			$event->setRespawnPosition($this->getRandomSpawnPoint());
			/** @var GamesPlayer $player */
			$player = $event->getPlayer();
			$player->setPosition($this->SeekersSpawnPoint);
			if($this->getSession($player)->isHidden()){
				$this->getSession($player)->setHidden(false);
				$this->lastHider = $player;
			}
		}
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event){
		if($this->validEvent($event)){
			/** @var GamesPlayer $player */
			$player = $event->getPlayer();
			$this->getSession($player)->setHidden(false);
			$this->removePlayer($player);
			$this->broadcastMessage($event->getPlayer()->getName() . " left the game");
		}
    }

	public function onPlayerSneak(PlayerToggleSneakEvent $event){
		if($this->validEvent($event)){
			/** @var GamesPlayer $player */
			$player = $event->getPlayer();
			if(!$event->isCancelled() && $this->getSession($player)->isHidden()){
				$this->getSession($player)->getBlock()->setVanished(!$event->isSneaking());
			}
		}
	}
}