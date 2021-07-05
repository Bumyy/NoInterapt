<?php

declare(strict_types = 1);

namespace BumyTest\entity;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\utils\UUID;

class NPC {


    /** @var Skin */
    private $skin;

    /** @var int */
    private $entityId;

    /** @var UUID */
    private $uuid;

    /** @var Position */
    private $position;

    private $yaw = [];

    private $pitch = [];

    /**
     * NPC constructor.
     *
     * @param Skin $skin
     * @param Position $position
     * @param string $nameTag
     */
    public function __construct(Skin $skin, Position $position) {
        $this->skin = $skin;
        $this->position = $position;
        $this->entityId = Entity::$entityCount++;
        $this->uuid = UUID::fromRandom();
        #$this->scale = $scale;
    }

    /**
     * @param Player $player
     */
    public function spawnTo(Player $player): void {
        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_ADD;
        $pk->entries = [PlayerListEntry::createAdditionEntry($this->uuid, $this->entityId, "", SkinAdapterSingleton::get()->toSkinData($this->skin))];
        $player->dataPacket($pk);
        $xdiff = $player->x - $this->position->x;
        $zdiff = $player->z - $this->position->z;
        $angle = atan2($zdiff, $xdiff);
        $yaw = (($angle * 180) / M_PI) - 90;
        $ydiff = $player->y - $this->position->y;
        $v = new Vector2($this->position->x, $this->position->z);
        $dist = $v->distance($player->x, $player->z);
        $angle = atan2($dist, $ydiff);
        $pitch = (($angle * 180) / M_PI) - 90;
        $pk = new AddPlayerPacket();
        $pk->uuid = $this->getUniqueId();
        $pk->username = "";
        $pk->entityRuntimeId = $this->entityId;
        $pk->position = $this->position->asVector3();
        $pk->yaw = $yaw;
        $pk->pitch = $pitch;
        $pk->item = Item::get(Item::AIR);
        $pk->metadata = [
            Entity::DATA_FLAGS => [
                Entity::DATA_TYPE_LONG,
                 0 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG
                ^ 0 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG
                ^ 1 << Entity::DATA_FLAG_CAN_FLY
            ],
            Entity::DATA_NAMETAG => [
                Entity::DATA_TYPE_STRING, ""
            ],
            Entity::DATA_LEAD_HOLDER_EID => [
                Entity::DATA_TYPE_LONG, -1
            ]
        ];
        $player->dataPacket($pk);
        #$this->setNameTag($player);
        $pk = new PlayerListPacket();
        $pk->type = PlayerListPacket::TYPE_REMOVE; 
        $pk->entries = [PlayerListEntry::createRemovalEntry($this->uuid)];
        $player->dataPacket($pk);
    }

    
    public function move(Player $player, $y): void {
        #$xdiff = $player->x - $this->position->x;
        #$zdiff = $player->z - $this->position->z;
        #$angle = atan2($zdiff, $xdiff);
        #$yaw = (($angle * 180) / M_PI) - 90;
        #$ydiff = $player->y - $this->position->y;
        #$v = new Vector2($this->position->x, $this->position->z);
        #$dist = $v->distance($player->x, $player->z);
        #$angle = atan2($dist, $ydiff);
        #$pitch = (($angle * 180) / M_PI) - 9;
        
        $pk = new MovePlayerPacket();
        $pk->entityRuntimeId = $this->entityId;
        $pk->position = $this->position->asVector3()->add(0, $y, 0);
        $this->position = $this->position->asVector3()->add(0, $y, 0);
        #$pk->yaw = $yaw;
        #$pk->pitch = $pitch;
        #$pk->headYaw = $yaw;
        $pk->onGround = false;
        $player->sendDataPacket($pk);
    }

    /**
     * @param Player $player
     */
    public function despawnFrom(Player $player): void {
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->entityId;
        $player->sendDataPacket($pk);
    }

    /**
     * @return Skin
     */
    public function getSkin(): Skin {
        return $this->skin;
    }


    /**
     * @return int
     */
    public function getEntityId(): int {
        return $this->entityId;
    }

    /**
     * @return UUID
     */
    public function getUniqueId(): UUID {
        return $this->uuid;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position {
        return $this->position;
    }
}