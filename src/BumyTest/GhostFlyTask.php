<?php

namespace BumyTest;

use BumyTest\Main;
use pocketmine\scheduler\Task;

class GhostFlyTask extends Task {

    public $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick){

        foreach($this->plugin->ghostsTime as $playerName => $time){
            if($time == 40){
                foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
                    $this->plugin->ghostsNPC[$playerName]->move($p, 0.5);
                }
                $this->plugin->ghostsTime[$playerName]--;
            } elseif($time < 40 and $time !== 0){
        		foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
        			$this->plugin->ghostsNPC[$playerName]->move($p, 0.1);
        		}
        		$this->plugin->ghostsTime[$playerName]--;
        	} elseif($time == 0){
        		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
        			$this->plugin->ghostsNPC[$playerName]->despawnFrom($player);
        			
        		}
        		unset($this->plugin->ghostsTime[$playerName]);
        		$this->plugin->npc->removeNPC($this->plugin->ghostsNPC[$playerName]);
        		unset($this->plugin->ghostsNPC[$playerName]);
        	}
        	
        }
    }
}