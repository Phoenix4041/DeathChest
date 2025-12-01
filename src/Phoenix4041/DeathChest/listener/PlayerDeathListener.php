<?php

declare(strict_types=1);

namespace Phoenix4041\DeathChest\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\player\Player;
use Phoenix4041\DeathChest\Loader;
use Phoenix4041\DeathChest\manager\DeathChestManager;

final class PlayerDeathListener implements Listener {

    public function __construct(
        private Loader $plugin,
        private DeathChestManager $chestManager
    ) {}

    public function onPlayerDeath(PlayerDeathEvent $event): void {
        $player = $event->getPlayer();
        $drops = $event->getDrops();
        
        if (empty($drops)) {
            return;
        }

        $killer = $this->getKillerName($player);
        
        $this->chestManager->createDeathChest(
            $player->getPosition(),
            $drops,
            $player->getName(),
            $killer
        );
        
        $event->setDrops([]);
    }

    private function getKillerName(Player $player): string {
        $lastDamageCause = $player->getLastDamageCause();
        
        if ($lastDamageCause instanceof EntityDamageByEntityEvent) {
            $damager = $lastDamageCause->getDamager();
            if ($damager instanceof Player) {
                return $damager->getName();
            }
            return $damager->getName();
        }
        
        if ($lastDamageCause instanceof EntityDamageEvent) {
            return match ($lastDamageCause->getCause()) {
                EntityDamageEvent::CAUSE_FALL => "Fall Damage",
                EntityDamageEvent::CAUSE_FIRE => "Fire",
                EntityDamageEvent::CAUSE_LAVA => "Lava",
                EntityDamageEvent::CAUSE_DROWNING => "Drowning",
                EntityDamageEvent::CAUSE_VOID => "Void",
                EntityDamageEvent::CAUSE_SUICIDE => "Suicide",
                EntityDamageEvent::CAUSE_MAGIC => "Magic",
                EntityDamageEvent::CAUSE_CONTACT => "Cactus",
                EntityDamageEvent::CAUSE_ENTITY_EXPLOSION, EntityDamageEvent::CAUSE_BLOCK_EXPLOSION => "Explosion",
                default => "Unknown"
            };
        }
        
        return "Unknown";
    }
}