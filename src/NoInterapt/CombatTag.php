<?php

namespace NoInterapt;

use NoInterapt\Main;
use pocketmine\scheduler\Task;

class CombatTag extends Task {

    public $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick){
        foreach($this->plugin->combatTag as $playerName => $cooldown){
            $player = $this->plugin->getServer()->getPlayer($playerName);
            if($cooldown > 0){
                $player->sendPopup("§cYou are still in combat for ".$cooldown."s!");
                $this->plugin->combatTag[$playerName]--;
            } elseif($cooldown == 0) {
                $player->sendPopup("§aYou are not in combat anymore!");
                unset($this->plugin->opponent[$playerName]);
                foreach($this->plugin->getServer()->getOnlinePlayers() as $pl){
                    $player->showPlayer($pl);
                }
                $this->plugin->combatTag[$playerName]--;
            }
        }

        foreach($this->plugin->respawn as $playerName => $cooldown){
            $player = $this->plugin->getServer()->getPlayer($playerName);
            if($cooldown !== 0){
                $player->sendPopup("§cYou are going to respawn in ".$cooldown."s!");
                $this->plugin->respawn[$playerName]--;
            } else {
                unset($this->plugin->respawn[$playerName]);
                $player->setGamemode(0);
                $player->teleport($this->plugin->getServer()->getDefaultLevel()->getSafeSpawn());
                $player->sendPopup("§aYou respawned!");
            }
        }
    }
}