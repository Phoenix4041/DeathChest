<?php

declare(strict_types=1);

namespace Phoenix4041\DeathChest;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Skin;
use pocketmine\world\World;
use pocketmine\nbt\tag\CompoundTag;
use Phoenix4041\DeathChest\manager\DeathChestManager;
use Phoenix4041\DeathChest\listener\PlayerDeathListener;
use Phoenix4041\DeathChest\entity\FloatingTextEntity;

final class Loader extends PluginBase {

    private static self $instance;
    private Config $messages;
    private DeathChestManager $chestManager;

    public function onEnable(): void {
        self::$instance = $this;
        
        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");
        
        $this->messages = new Config($this->getDataFolder() . "messages.yml", Config::YAML);
        
        $this->registerEntities();
        
        $this->chestManager = new DeathChestManager($this);
        
        $pm = $this->getServer()->getPluginManager();
        $pm->registerEvents(new PlayerDeathListener($this, $this->chestManager), $this);
        $pm->registerEvents(new listener\ChestInteractionListener($this, $this->chestManager), $this);
        
        $this->getLogger()->info("DeathChest plugin enabled successfully!");
    }

    private function registerEntities(): void {
        EntityFactory::getInstance()->register(
            FloatingTextEntity::class,
            function(World $world, CompoundTag $nbt): FloatingTextEntity {
                return new FloatingTextEntity(
                    EntityDataHelper::parseLocation($nbt, $world),
                    new Skin("Standard_Custom", str_repeat("\x00", 8192)),
                    $nbt
                );
            },
            ['FloatingTextEntity', 'deathchest:floating_text']
        );
    }

    public function onDisable(): void {
        $this->chestManager->removeAllChests();
        $this->getLogger()->info("DeathChest plugin disabled!");
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public function getMessages(): Config {
        return $this->messages;
    }

    public function getChestManager(): DeathChestManager {
        return $this->chestManager;
    }
}