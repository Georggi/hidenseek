<?php
namespace HideNSeek;

use GamesCore\BaseFiles\BaseMiniGame;
use GamesCore\BaseFiles\BaseSession;
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
use pocketmine\event\entity\EntityBlockChangeEvent;
use HideNSeek\Tasks\Ingame\BlockPlaceTask;
use HideNSeek\Inventories\Hider;
use HideNSeek\Inventories\Seeker;

class HNSGame extends BaseMiniGame{
    /** @var Loader */
    private $plugin;
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
    private $HidersSpawnPoint;


    public function __construct($core, $plugin, Level $level, Sign $sign){
        // Loader, MinigameProject, Level, Sign, maximum players, minimum players, time of game, number of rounds, ??, game end message
        parent::__construct($core, $plugin, $level, $sign, 1, 1, 1, 1, false);
    }

    /**
     * @return Loader
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
        foreach($this->getAllPlayers() as $hider){
            if($this->getSession($hider)->isHidden() === NULL){
                $this->getSession($hider)->setHidden(true);
                $hider->addWindow(new Hider($hider));
            }
        }
        $this->scheduleBlockPlacer();
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

            /*foreach($this->getPlugin()->getServer()->getDefaultLevel()->getPlayers() as $p) {
                // Show the current players to all the players in the lobby...
                if(!$this->getCore()->getCore()->isMagicClockEnabled($p)){
                    $p->showPlayer($s->getPlayer());
                }else{
                    $p->hidePlayer($s->getPlayer());
                }*/

                // Show the layers in the lobby to the current player...
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
            if(count($Hiders) < 1 && ($this->lastHider instanceof GamesPlayer && $s === $this->lastHider)){
                $reward = 5;
            }
            //$this->getCore()->getCore()->addPlayerCoins($s, $reward);
            $s->sendTip(TextFormat::AQUA . "You won " . TextFormat::LIGHT_PURPLE . $reward . TextFormat::AQUA . " coins!");
        }
        // Reward hiders players
        foreach($Hiders as $h){
            //$this->getCore()->getCore()->addPlayerCoins($h, 10);
            $h->sendTip(TextFormat::AQUA . "You won " . TextFormat::LIGHT_PURPLE . "10" . TextFormat::AQUA . " coins!");
        }
    }

    /**
     * @param GamesPlayer $player
     */
    public function onPlayerJoin(GamesPlayer $player)
    {
    }

    /**
     * @return HNSSession[]
     */
    public function getAllSessions(){
        return parent::getAllSessions();
    }

    /**
     * @param GamesPlayer $player
     * @return BaseSession
     */
    public function generateSession(GamesPlayer $player){
        return new HNSSession($player, $this);
    }

    /**
     * @param GamesPlayer $player
     * @return bool|HNSSession
     */
    public function getSession(GamesPlayer $player){
        return parent::getSession($player);
    }

    /**
     * For internal use ONLY
     *
     * This function schedules block placer
     */
    public function scheduleBlockPlacer(){
        $this->getPlugin()->getServer()->getScheduler()->scheduleDelayedTask(new BlockPlaceTask($this->getCore(), $this, $this->hasStarted()), (20)); // Check every 1 second...
    }

    /**
     * @param GamesPlayer $player
     * @return bool
     */
    public function isPlaced(GamesPlayer $player){
        return $this->getSession($player)->isPlaced();
    }

    /**
     * @param GamesPlayer $player
     * @return int
     */
    public function getID(GamesPlayer $player){
        return $this->getSession($player)->getID();
    }

    /**
     * @param GamesPlayer $player
     * @return int
     */
    public function getMeta(GamesPlayer $player){
        return $this->getSession($player)->getMeta();
    }

    /**
     * @param GamesPlayer $player
     * @param $mode
     */
    public function setPlaced(GamesPlayer $player, $mode){
        $this->getSession($player)->setPlaced($mode);
    }

    /**
     * @return array
     */
    public function getMapBlocks(){
        return $this->blocks;
    }

    /**
     * @param GamesPlayer $player
     */
    public function placeBlock(GamesPlayer $player){
        if(in_array($player->getLevel()->getBlock($player->getPosition())->getId(), [0, 8, 9])){
            $player->getLevel()->setBlock($player->getPosition()/*->add(0, 5)*/, $this->getBlock($player), true, false);
            // This allow to place the entity in the right position, added a "Y axis" trick at the end of the function to correct the position in "slabs" xD
            $this->setPlaced($player, true);
            $this->id = $this->getBlock($player)->getId();
            $this->meta = $this->getBlock($player)->getDamage();
            $this->getSession($player)->setHidden(false, true);
        }else{
            $player->sendMessage("You can't become block there!");
        }
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
            EntityDamageEvent::MODIFIER_BASE => isset($this->damageTable[$item->getId()]) ? $this->damageTable[$item->getId()] : 1
        ];
        new EntityDamageEvent($player, "Ponies", $damage[0]);
    }

    /**
     * Change the last time that a player moved
     *
     * @param GamesPlayer $player
     * @param int $time
     */
    public function setLastPlayerMovement(GamesPlayer $player, $time){
        if($this->isHidden($player)){
            $this->getSession($player)->setLastMovement($time);
        }
    }

    /**
     * Get the last time that a player moved
     *
     * @param GamesPlayer $player
     * @return int|null
     */
    public function getLastPlayerMovement(GamesPlayer $player){
        if($this->isHidden($player)){
            return $this->getSession($player)->getLastMovement();
        }else{
            return true;
        }
    }

    /** @var int */
    private $id;

    /** @var int */
    private $meta;

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
     */
    public function onPlayerInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        foreach($this->getAllPlayers() as $TEMP_PLAYER){
            if($TEMP_PLAYER->getName() === $player->getName()){
                $player = $TEMP_PLAYER;
                break;
            }
        }
        foreach($this->getAllPlayers() as $p){
            if  ($p !== $player &&
                ($this->isHidden($player) === false) &&
                ($this->isHidden($p) === true) &&
                $event->getBlock()->getFloorX() === $this->getBlock($p)->getFloorX() &&
                $event->getBlock()->getFloorY() === $this->getBlock($p)->getFloorY() &&
                $event->getBlock()->getFloorZ() === $this->getBlock($p)->getFloorZ()
            ) {
                $this->sendDamageToPlayer($event->getItem(), $p);
            }
        }

    }

    /**
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        foreach($this->getAllPlayers() as $TEMP_PLAYER){
            if($TEMP_PLAYER->getName() === $player->getName()){
                $player = $TEMP_PLAYER;
                break;
            }
        }
        if ($this->isPlaced($player) && $player->distance(new Vector3(
                $this->getBlock($player)->getX(),
                $this->getBlock($player)->getY(),
                $this->getBlock($player)->getZ())) > 1
        ) {
            $this->getSession($player)->removeBlock($player);
        }
        $this->setLastPlayerMovement($player, time());
    }

    /**
     * @param EntityDamageEvent $event
     */
    public function onEntityDamage(EntityDamageEvent $event){
        if($event instanceof EntityDamageByEntityEvent){
            $damager = $event->getDamager();
            $victim = $event->getEntity();
            foreach($this->getAllPlayers() as $TEMP_PLAYER){
                if($damager->getPosition() === $TEMP_PLAYER->getPosition()){
                    $damager = $TEMP_PLAYER;
                } elseif($victim->getPosition() === $TEMP_PLAYER->getPosition()) {
                    $victim = $TEMP_PLAYER;
                }
            }
            if($this->getSession($victim)->isHidden() === $this->getSession($damager)->isHidden()){
                $event->setCancelled(true); // Cancel the event if both players are from the same team (Hiders or seekers)
            }
            if($this->getSession($victim)->isHidden()){ // Pass the damage to the player instead of the block entity
                $victim->attack($event->getFinalDamage(), $event);
                //$victim->knockBack($damager, $event->getFinalDamage(), $damager->getX(), $damager->getY());
                $event->setDamage(0);
            }
        }
    }

    /**
     * @param PlayerRespawnEvent $event
     */
    public function onPlayerRespawn(PlayerRespawnEvent $event){
        $event->setRespawnPosition($this->getRandomSpawnPoint());
        $player = $event->getPlayer();
        $player->setPosition($this->SeekersSpawnPoint);
        foreach($this->getAllPlayers() as $TEMP_PLAYER){
            if($TEMP_PLAYER->getName() === $player->getName()){
                $player = $TEMP_PLAYER;
                break;
            }
        }
        if ($this->getSession($player)->isHidden()) {
            $this->getSession($player)->setHidden(false);
            $this->lastHider = $player;
        }
    }

    /**
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        foreach($this->getAllPlayers() as $TEMP_PLAYER){
            if($TEMP_PLAYER->getName() === $player->getName()){
                $player = $TEMP_PLAYER;
                break;
            }
        }
        $this->removePlayer($player);
        $this->broadcastMessage($event->getPlayer()->getName() . " left the game");
    }

    /**
     * @param EntityBlockChangeEvent $event
     */
    public function onFallingSandConvert(EntityBlockChangeEvent $event){
        $event->setCancelled(true);
    }
}