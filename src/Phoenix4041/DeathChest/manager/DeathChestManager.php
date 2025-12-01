<?php

declare(strict_types=1);

namespace Phoenix4041\DeathChest\manager;

use pocketmine\world\Position;
use pocketmine\item\Item;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\Chest as TileChest;
use pocketmine\scheduler\ClosureTask;
use Phoenix4041\DeathChest\Loader;
use Phoenix4041\DeathChest\entity\FloatingTextEntity;

final class DeathChestManager {

    private array $activeChests = [];

    public function __construct(
        private Loader $plugin
    ) {}

    /**
     * @param Position $position
     * @param Item[] $items
     * @param string $playerName
     * @param string $killerName
     */
    public function createDeathChest(Position $position, array $items, string $playerName, string $killerName): void {
        $world = $position->getWorld();
        $blockPos = $position->floor();
        
        $world->setBlock($blockPos, VanillaBlocks::CHEST());
        
        $tile = $world->getTile($blockPos);
        if (!$tile instanceof TileChest) {
            return;
        }

        $inventory = $tile->getInventory();
        foreach ($items as $item) {
            $inventory->addItem($item);
        }

        $floatingText = null;
        if ($this->plugin->getConfig()->get("enable_floating_text", true)) {
            $textHeight = $this->plugin->getConfig()->get("floating_text_height", 1.2);
            $textPosition = $blockPos->add(0.5, $textHeight, 0.5);
            
            $floatingText = FloatingTextEntity::create(
                $world,
                $textPosition,
                $playerName,
                $killerName
            );
            $floatingText->spawnToAll();
        }

        $chestId = uniqid("chest_", true);
        $this->activeChests[$chestId] = [
            'position' => $blockPos,
            'world' => $world,
            'items' => $items,
            'text' => $floatingText,
            'playerName' => $playerName,
            'killerName' => $killerName
        ];

        $this->scheduleChestRemoval($chestId);
    }

    private function scheduleChestRemoval(string $chestId): void {
        $expireTime = $this->plugin->getConfig()->get("chest_expire_time", 300);
        $this->plugin->getScheduler()->scheduleDelayedTask(
            new ClosureTask(function() use ($chestId): void {
                $this->removeChest($chestId);
            }),
            $expireTime * 20
        );
    }

    public function removeChest(string $chestId): void {
        if (!isset($this->activeChests[$chestId])) {
            return;
        }

        $chestData = $this->activeChests[$chestId];
        $position = $chestData['position'];
        $world = $chestData['world'] ?? null;
        
        if ($world !== null && $world->isChunkLoaded($position->getFloorX() >> 4, $position->getFloorZ() >> 4)) {
            $block = $world->getBlockAt($position->getFloorX(), $position->getFloorY(), $position->getFloorZ());
            if ($block->getTypeId() === VanillaBlocks::CHEST()->getTypeId()) {
                $world->setBlockAt($position->getFloorX(), $position->getFloorY(), $position->getFloorZ(), VanillaBlocks::AIR());
            }
        }

        if (isset($chestData['text']) && $chestData['text'] instanceof FloatingTextEntity) {
            if (!$chestData['text']->isClosed()) {
                $chestData['text']->flagForDespawn();
            }
        }

        unset($this->activeChests[$chestId]);
    }

    public function removeAllChests(): void {
        foreach (array_keys($this->activeChests) as $chestId) {
            $this->removeChest($chestId);
        }
    }

    public function getChestAtPosition(Position $position): ?string {
        foreach ($this->activeChests as $chestId => $data) {
            if ($data['position']->equals($position->floor())) {
                return $chestId;
            }
        }
        return null;
    }

    public function getChestData(string $chestId): ?array {
        return $this->activeChests[$chestId] ?? null;
    }
}