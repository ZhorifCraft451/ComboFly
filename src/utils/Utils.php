<?php
declare(strict_types=1);

/*
 *   _____                _           ______ _       
 *  / ____|              | |         |  ____| |      
 * | |     ___  _ __ ___ | |__   ___ | |__  | |_   _ 
 * | |    / _ \| '_ ` _ \| '_ \ / _ \|  __| | | | | |
 * | |___| (_) | | | | | | |_) | (_) | |    | | |_| |
 *  \_____\___/|_| |_| |_|_.__/ \___/|_|    |_|\__, |
 *                                             __/ |
 *                                            |___/ 
 */

namespace combofly\utils;

use combofly\Loader;
use pocketmine\player\Player;
use pocketmine\player\GameMode;
use pocketmine\math\Vector3;
use pocketmine\world\Location;
use pocketmine\entity\Entity;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;

class Utils {

    public static function resetPlayer(Player $player): void {
        $player->setSprinting(false);
        $player->setSneaking(false);

        $player->extinguish();

        $player->getInventory()->clearAll();
        $player->getArmorInventory()->clearAll();

        $player->getEffects()->clear();
        $player->setHealth($player->getMaxHealth());    

        $player->setAllowFlight(false);
        $player->setFlying(false);
               
        $player->setGamemode(GameMode::SURVIVAL());
    }

    public static function addSound(Vector3 $vector, string $sound, ?array $players = null) {
        $pk = new PlaySoundPacket();
        $pk->soundName = $sound;
        $pk->x = $vector->getX();
        $pk->y = $vector->getY();
        $pk->z = $vector->getZ();
        $pk->volume = 1;
        $pk->pitch = 1;

        if(!is_null($players))
            Loader::getInstance()->getServer()->broadcastPackets($players, [$pk]);

        return $pk;
    } 

    /** It is currently fixing

    public static function strikeLightning(Location $location, Player $killer): void{
        $level = $location->getWorld();

        $light = new AddActorPacket();
        $light->metadata = [];

        $light->type = AddActorPacket::LEGACY_ID_MAP_BC[93];
        $light->entityRuntimeId = Entity::$entityCount++;
        $light->entityUniqueId = 0;

        $light->position = $location->asPosition();
        $light->motion = new Vector3();

        $light->yaw = $location->getYaw();
        $light->pitch = $location->getPitch();

        Loader::getInstance()->getServer()->broadcastPackets($level->getPlayers(), [$light]);
		
        $sound = self::addSound($location, "ambient.weather.thunder");

        foreach($level->getPlayers() as $player) {
            if($player->getUniqueId()->toString() !== $killer->getUniqueId()->toString())
                $player->getNetworkSession()->sendDataPacket($sound);
        }
    } */

    public static function sendAdventureSettings(Player $player): void {
        $player->setAllowFlight(true);

        $pk = new AdventureSettingsPacket();

        $pk->setFlag(AdventureSettingsPacket::WORLD_IMMUTABLE, true);
        $pk->setFlag(AdventureSettingsPacket::NO_PVP, true);
        $pk->setFlag(AdventureSettingsPacket::AUTO_JUMP, $player->hasAutoJump());
        $pk->setFlag(AdventureSettingsPacket::ALLOW_FLIGHT, $player->getAllowFlight());
        $pk->setFlag(AdventureSettingsPacket::NO_CLIP, false);
        $pk->setFlag(AdventureSettingsPacket::FLYING, $player->isFlying());

        $pk->commandPermission = ($player->getServer()->isOp($player->getName()) ? AdventureSettingsPacket::PERMISSION_OPERATOR : AdventureSettingsPacket::PERMISSION_NORMAL);
        $pk->playerPermission = ($player->getServer()->isOp($player->getName()) ? PlayerPermissions::OPERATOR : PlayerPermissions::MEMBER);
        $pk->targetActorUniqueId = $player->getId();

        $player->getNetworkSession()->sendDataPacket($pk);
    }
}