<?php
namespace HideNSeek\Tasks\Ingame;

use Core\BaseFiles\BaseTask;
use HideNSeek\HNSGame;

class BlockPlaceTask extends BaseTask{
    /** @var HNSGame $game */
    private $game;

    /** @var bool $state */
    private $state;

    public function __construct($plugin, HNSGame $game, $state){
        parent::__construct($plugin);
        $this->game = $game;
        $this->state = $state;
    }

    /*
     * This task is executed every 1 second,
     * with the purpose of checking all players' last movement
     * time, stored in their 'Session',
     * and check if it is pretty near,
     * or it's over, the default Idling limit.
     *
     * If so, block will be placed
     */

    public function onRun($currentTick){
        if($this->state){
            foreach($this->game->getAllPlayers() as $p){
                $last = $this->game->getLastPlayerMovement($p);
                $timeToBlock = time() - $last;
                if ($last !== null && !$this->game->isPlaced($p) && $timeToBlock >= 5) {
                    $this->game->placeBlock($p);
                    $p->sendTip("You are now block!");
                    //TODO: loop for players bla bla bla
                }else{
                    $p->sendTip("Seconds to become block: $timeToBlock");
                }
            }
            // Re-Schedule the task xD
            $this->game->scheduleBlockPlacer();
        }
    }
}