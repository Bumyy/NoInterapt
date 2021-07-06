<?php

declare(strict_types = 1);

namespace NoInterapt\entity;

use NoInterapt\entity\NPC;
use NoInterapt\Main;

use libs\utils\Utils;
use pocketmine\block\Bedrock;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockToolType;
use pocketmine\block\Obsidian;
use pocketmine\entity\Entity;
use pocketmine\entity\Explosive;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\item\TieredTool;
use pocketmine\level\Position;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\entity\Skin;

class EntityManager {

    /** @var Main */
    private $core;

    /** @var NPC[] */
    private $npcs = [];

    public function __construct(Main $core) {
        $this->core = $core;
    }


    public function createGhost($pos, Player $player){
        $path = $this->core->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . "ghost.png";
        $this->addNPC(new NPC($this->createSkin($this->getSkinDataFromPNG($path)), $pos), $player);
        //spawn it to everyone
        foreach($this->core->getServer()->getOnlinePlayers() as $p){
            $this->core->ghostsNPC[$player->getName()]->spawnTo($p);
        }
    }

    public function createSkin(string $skinData) {
        return new Skin("Standard_Custom", $skinData, "", "geometry.humanoid.custom");
    }

    public function getSkinDataFromPNG(string $path): string {
        $image = imagecreatefrompng($path);
        $data = "";
        for($y = 0, $height = imagesy($image); $y < $height; $y++) {
            for($x = 0, $width = imagesx($image); $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                $data .= pack("c", ($color >> 16) & 0xFF)
                    . pack("c", ($color >> 8) & 0xFF)
                    . pack("c", $color & 0xFF)
                    . pack("c", 255 - (($color & 0x7F000000) >> 23));
            }
        }
        return $data;
    }


    public function getNPCs(): array {
        return $this->npcs;
    }

 
    public function getNPC(int $entityId): ?NPC {
        return $this->npcs[$entityId] ?? null;
    }

 
    public function addNPC(NPC $npc, Player $player): void {
        $this->npcs[$npc->getEntityId()] = $npc;
        $this->core->ghostsNPC[$player->getName()] = $npc;
        $this->core->ghostsTime[$player->getName()] = 40;
    }

    public function removeNPC(NPC $npc): void {
        unset($this->npcs[$npc->getEntityId()]);
    }

    
}