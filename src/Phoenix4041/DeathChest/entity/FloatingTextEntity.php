<?php

declare(strict_types=1);

namespace Phoenix4041\DeathChest\entity;

use pocketmine\entity\Human;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\Skin;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

final class FloatingTextEntity extends Human {

    private const TAG_TEXT = "FloatingText";
    
    private string $text = "";

    protected function initEntity(CompoundTag $nbt): void {
        parent::initEntity($nbt);
        
        $this->text = $nbt->getString(self::TAG_TEXT, "");
        if ($this->text !== "") {
            $this->setNameTag($this->text);
            $this->setNameTagAlwaysVisible(true);
            $this->setNameTagVisible(true);
        }
    }

    public function saveNBT(): CompoundTag {
        $nbt = parent::saveNBT();
        $nbt->setString(self::TAG_TEXT, $this->text);
        return $nbt;
    }

    protected function getInitialSizeInfo(): EntitySizeInfo {
        return new EntitySizeInfo(0.001, 0.001);
    }

    protected function getInitialDragMultiplier(): float {
        return 0.0;
    }

    protected function getInitialGravity(): float {
        return 0.0;
    }

    public static function create(World $world, Vector3 $position, string $playerName, string $killerName): self {
        $text = "§ePlayer§f: §c{$playerName}\n§eKiller§f: §c{$killerName}";
        
        $location = Location::fromObject($position, $world);
        $skin = new Skin("Standard_Custom", str_repeat("\x00", 8192));
        
        $entity = new self($location, $skin);
        
        $entity->text = $text;
        $entity->setNameTag($text);
        $entity->setNameTagAlwaysVisible(true);
        $entity->setNameTagVisible(true);
        
        return $entity;
    }

    protected function syncNetworkData(EntityMetadataCollection $properties): void {
        parent::syncNetworkData($properties);
        
        $properties->setGenericFlag(EntityMetadataFlags::IMMOBILE, true);
        $properties->setGenericFlag(EntityMetadataFlags::NO_AI, true);
        $properties->setGenericFlag(EntityMetadataFlags::SILENT, true);
        $properties->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, true);
        $properties->setGenericFlag(EntityMetadataFlags::ALWAYS_SHOW_NAMETAG, true);
        
        $properties->setString(EntityMetadataProperties::NAMETAG, $this->text);
        $properties->setFloat(EntityMetadataProperties::SCALE, 0.001);
    }

    public function attack(EntityDamageEvent $source): void {
        $source->cancel();
    }

    public function canBeCollidedWith(): bool {
        return false;
    }

    public function canCollideWith(Entity $entity): bool {
        return false;
    }

    protected function entityBaseTick(int $tickDiff = 1): bool {
        return false;
    }

    public function canSaveWithChunk(): bool {
        return false;
    }

    public function hasGravity(): bool {
        return false;
    }
}