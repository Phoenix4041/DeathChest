# DeathChest

### Advanced death chest system plugin for PocketMine-MP that creates protected chests when players die, featuring: Floating text displays, Explosion effects, Toast notifications, and Automatic expiration.

## Features

* **Death Chests**: Automatic chest creation on player death with inventory items
* **Floating Text**: Custom floating text entity showing player and killer names
* **Explosion Effects**: Configurable explosion sounds, particles, and fuse effects
* **Toast Notifications**: In-game toast notifications when opening death chests
* **Auto-Expiration**: Configurable chest expiration with automatic cleanup
* **Persistent Storage**: Tracks all active death chests across server restarts

## Requirements

* PocketMine-MP 5.0.0+
* PHP 8.0+

## Installation

1. Download the plugin
2. Place in your server's `plugins/` folder
3. Restart the server
4. Plugin will generate `config.yml` and `messages.yml` automatically

## Configuration

The plugin creates a `config.yml` upon first run. Edit to customize plugin behavior:
```yaml
enable_floating_text: true
floating_text_height: 1.2
chest_expire_time: 300  # seconds
enable_toast_notifications: true
explosion:
  enable_fuse_sound: true
  enable_sound: true
  enable_particles: true
  fuse_delay: 20  # ticks
```

Edit `messages.yml` to customize all plugin messages in any language.

## Usage

### How It Works

**Death Chest Creation**:

1. Player dies naturally or is killed
2. Death chest automatically spawns at death location
3. All inventory items are stored in the chest
4. Floating text displays player and killer information

**Chest Interaction**:

1. Right-click or break the death chest
2. Explosion effect triggers after fuse delay
3. All items drop for collection
4. Toast notification appears
5. Chest and floating text are removed

**Floating Text Display**:
```
§ePlayer§f: §c{PlayerName}
§eKiller§f: §c{KillerName}
```

### Technical Details

The plugin uses custom entities and managers:

### Details

* **Entity System**: Custom FloatingTextEntity using Human base class
* **Persistence**: Active chest tracking with world reference
* **Explosion**: Configurable fuse delay with sounds and particles
* **Storage**: In-memory storage with automatic cleanup
* **Notifications**: Native Minecraft toast notifications

### Known Limitations

* Floating text despawns if chunk unloads
* Explosion effects require chunk to be loaded
* Maximum one death chest per death event
* Items drop if chest cannot be placed

## Architecture

This plugin is designed with modular components:
```
DeathChest/
├── plugin.yml
├── resources/
│   ├── config.yml
│   └── messages.yml
└── src/
    └── Phoenix4041/
        └── DeathChest/
            ├── Loader.php
            ├── entity/
            │   └── FloatingTextEntity.php
            ├── manager/
            │   └── DeathChestManager.php
            └── listener/
                ├── PlayerDeathListener.php
                └── ChestInteractionListener.php
```

## Contributing

This plugin was created for educational purposes.

## License

This project was created for educational purposes.

## Support

For issues or suggestions, contact Phoenix4041.

## Updates & Improvements

### v1.0.0 - Initial Release

**Features:**

* Death chest automatic creation on player death
* Custom floating text entity with player/killer info
* Explosion effects with configurable fuse delay
* Toast notifications for chest interactions
* Configurable chest expiration timer
* Full inventory transfer to death chest
* Automatic cleanup on chest removal

**Technical Implementation:**

* FloatingTextEntity using Human base class for reliable rendering
* Transparent skin with invisible body, visible nametag
* Entity registration with proper NBT and Skin initialization
* Task-based chest expiration system
* Event-driven architecture for death and interaction handling

## Version Support

| Version | Release Date | Status | Support |
|---------|-------------|--------|---------|
| 1.0.0 | December 2025 | 🟢 Active | Full support |

**Made with ❤️ by Phoenix4041**
