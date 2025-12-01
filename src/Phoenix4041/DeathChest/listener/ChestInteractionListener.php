<?php

declare(strict_types=1);

namespace Phoenix4041\DeathChest\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\sound\IgniteSound;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\scheduler\ClosureTask;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use Phoenix4041\DeathChest\Loader;
use Phoenix4041\DeathChest\manager\DeathChestManager;

final class ChestInteractionListener implements Listener {

    public function __construct(
        private Loader $plugin,
        private DeathChestManager $chestManager
    ) {}

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();

        if ($block->getTypeId() !== VanillaBlocks::CHEST()->getTypeId()) {
            return;
        }

        $chestId = $this->chestManager->getChestAtPosition($block->getPosition());
        if ($chestId === null) {
            return;
        }

        $event->cancel();
        
        $this->triggerExplosionEffect($event, $chestId);
    }

    public function onBlockBreak(BlockBreakEvent $event): void {
        $block = $event->getBlock();

        if ($block->getTypeId() !== VanillaBlocks::CHEST()->getTypeId()) {
            return;
        }

        $chestId = $this->chestManager->getChestAtPosition($block->getPosition());
        if ($chestId === null) {
            return;
        }

        $event->cancel();
        
        $this->triggerExplosionEffect($event, $chestId);
    }

    private function triggerExplosionEffect(PlayerInteractEvent|BlockBreakEvent $event, string $chestId): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $position = $block->getPosition();
        $world = $position->getWorld();

        $config = $this->plugin->getConfig();
        
        if ($config->get("explosion.enable_fuse_sound", true)) {
            $world->addSound($position, new IgniteSound());
        }

        $fuseDelay = $config->get("explosion.fuse_delay", 20);
        
        $this->plugin->getScheduler()->scheduleDelayedTask(
            new ClosureTask(function() use ($world, $position, $chestId, $player, $config): void {
                $chestData = $this->chestManager->getChestData($chestId);
                if ($chestData === null) {
                    return;
                }

                if ($config->get("explosion.enable_sound", true)) {
                    $world->addSound($position, new ExplodeSound());
                }
                
                if ($config->get("explosion.enable_particles", true)) {
                    $world->addParticle($position->add(0.5, 0.5, 0.5), new HugeExplodeParticle());
                }

                $tile = $world->getTile($position);
                if ($tile instanceof TileChest) {
                    $inventory = $tile->getInventory();
                    foreach ($inventory->getContents() as $item) {
                        $world->dropItem($position->add(0.5, 0.5, 0.5), $item);
                    }
                    $inventory->clearAll();
                }

                $this->chestManager->removeChest($chestId);

                if ($config->get("enable_toast_notifications", true)) {
                    $this->sendToastNotification($player, $chestData['playerName']);
                }
            }),
            $fuseDelay
        );
    }

    private function sendToastNotification(mixed $player, string $playerName): void {
        $messages = $this->plugin->getMessages();
        $title = str_replace("{player}", $playerName, $messages->get("toast.title", "Death Chest Opened"));
        $message = str_replace("{player}", $playerName, $messages->get("toast.message", "You opened {player}'s death chest!"));

        $pk = ToastRequestPacket::create($title, $message);
        $player->getNetworkSession()->sendDataPacket($pk);
    }
}