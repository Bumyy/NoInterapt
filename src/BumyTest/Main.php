<?php

namespace BumyTest;

use pocketmine\plugin\PluginBase;
use pocketmine\entity\Entity;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\level\Explosion;
use pocketmine\entity\projectile\Throwable;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\math\Vector3;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
use pocketmine\level\Position;

use BumyTest\CombatTag;
use BumyTest\entity\EntityManager;

class Main extends PluginBase implements Listener{

    public $opponent = [];

    public $combatTag = [];

    public $respawn = [];

    public $npc;

    //PlayerName => NPC
    public $ghostsNPC = [];
    public $ghostsTime = [];

    public function onEnable() {
       $this->getServer()->getPluginManager()->registerEvents($this, $this);
       $this->getScheduler()->scheduleRepeatingTask(new CombatTag($this), 20);
       $this->getScheduler()->scheduleRepeatingTask(new GhostFlyTask($this), 1);
       $this->npc = new EntityManager($this);
    }

    public function onDamage(EntityDamageEvent $event){
        
        $player = $event->getEntity();

        if($player->getGamemode() == 2){
            $event->setCancelled(true);
            return;
        }
      
        if($event->getCause() === EntityDamageEvent::CAUSE_FALL){
            $event->setCancelled(true);
            return;
        }

        if($event->getCause() === EntityDamageEvent::CAUSE_SUFFOCATION){
            $event->setCancelled(true);
            return;

        }

        if($event instanceof EntityDamageByChildEntityEvent){
            $damager = $event->getDamager();

        } elseif($event->getCause() === EntityDamageEvent::CAUSE_ENTITY_EXPLOSION){
            $damager = $event->getDamager();

        } else {
            $damager = $event->getDamager();
        }

        if(!isset($this->opponent[$player->getName()]) and !isset($this->opponent[$damager->getName()])){
            $this->opponent[$player->getName()] = $damager->getName();
            $this->opponent[$damager->getName()] = $player->getName();
            $this->combatTag[$player->getName()] = 15;
            $this->combatTag[$damager->getName()] = 15;
            foreach($this->getServer()->getOnlinePlayers() as $pl){
                if($pl !== $damager){
                    $player->hidePlayer($pl);
                }

                if($pl !== $player){
                    $damager->hidePlayer($pl);
                }
                
            }
            
        } elseif(isset($this->opponent[$player->getName()]) and !isset($this->opponent[$damager->getName()])){
            $event->setCancelled();
            return;
        } elseif(!isset($this->opponent[$player->getName()]) and isset($this->opponent[$damager->getName()])){
            $event->setCancelled();
            return;
        } elseif(isset($this->opponent[$player->getName()]) and isset($this->opponent[$damager->getName()]) and $this->opponent[$player->getName()] !== $damager->getName() and $this->opponent[$damager->getName()] !== $player->getName()){
            $event->setCancelled();
            return;
        } elseif($this->opponent[$player->getName()] == $damager->getName() and $this->opponent[$damager->getName()] == $player->getName()){
            $this->combatTag[$player->getName()] = 15;
            $this->combatTag[$damager->getName()] = 15;
            $event->setCancelled(false);
        }


        if($player instanceof Player and $player->getHealth() - $event->getFinalDamage() <= 0){
            
            if($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK and !$event instanceof EntityDamageByChildEntityEvent and $event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_EXPLOSION){
                return;
            }
            $event->setCancelled(true);
            unset($this->opponent[$player->getName()]);
            unset($this->opponent[$damager->getName()]);
            $this->combatTag[$player->getName()] = -1;
            $this->combatTag[$damager->getName()] = -1;
            $player->setGamemode(3);
            $this->respawn[$player->getName()] = 5;
            $player->setHealth(20);
            foreach($this->getServer()->getOnlinePlayers() as $pl){
                $pl->sendMessage("§e".$damager->getName()." killed ".$player->getName());
            }
            foreach($this->getServer()->getOnlinePlayers() as $pl){
                $player->showPlayer($pl);
                $damager->showPlayer($pl);
            }
            $player->sendTitle("§l§cYOU DIED");
            $this->npc->createGhost(new Position($player->getX(), $player->getY(), $player->getZ(), $player->getLevel()), $player);
        } else {

        }

        
    }

    
}