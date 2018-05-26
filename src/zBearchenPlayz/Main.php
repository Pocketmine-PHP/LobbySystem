<?php

namespace zBearchenPlayz;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\Textformat as Color;

class Main extends PluginBase implements Listener
{

	public $prefix = "";
	public $hideall = [];

	public function onEnable()
	{
		$this->getLogger()->notice(base64_decode("UGx1Z2luIEVuYWJsZWQuIEZpeGVkIGJ5IEphY2tNRC4="));

		$prefix = new Config($this->getDataFolder() . "prefix.yml" , Config::YAML);
		if (empty($prefix->get("Prefix"))) {
			$prefix->set("Prefix" , "§7[§6§lSystem§r§7]");
		}
		$prefix->save();

		$this->saveResource("config.yml");
		@mkdir($this->getDataFolder());
		$this->prefix = $prefix->get("Prefix");
		$this->getServer()->getPluginManager()->registerEvents($this , $this);

		$config = new Config($this->getDataFolder() . "config.yml" , Config::YAML);
		if (empty($config->get("JoinBroadcast"))) {
			$config->set("JoinBroadcast1" , "§7=======================");
			$config->set("LEER" , "");
			$config->set("JoinBroadcast2" , " §8» §6Willkommen auf unserem Netzwerk");
			$config->set("JoinBroadcast3" , " §8» §fWebseite§7 × §4Coming Soon");
			$config->set("JoinBroadcast4" , " §8» §fDiscord§7 × §5Unseren Discord: https://discord.gg/N9JEsAE");
			$config->set("LEER2" , "");
			$config->set("JoinBroadcast5" , "§7=======================");
			$config->set("BlockBreakMessage" , " §cDu darfst hier nicht abbauen!");
			$config->set("Hub/Lobby" , " §c Willkommen in der Lobby");
			$config->set("JoinTitle" , " §7[§a»§7] §aWillkommen");
			$config->set("Prefix" , "§7[§6§lSystem§r§7]");
			$config->set("Chat" , " §7Du musst den Rang §6Premium§7 besitzen um schreiben zu können!");
		}
		$config->save();

		$info = new Config($this->getDataFolder() . "info.yml" , Config::YAML);
		if (empty($info->get("infoline1"))) {
			$info->set("infoline1" , "§7===§7[§a§lVaronPE§r§7]===");
			$info->set("infoline2" , "§7» §1Bei Weiteren Fragen melde dich im Discord");
			$info->set("infoline3" , "§7» §1https://discord.gg/N9JEsAE");
			$info->set("infoline4" , "§7» §1Dazu,wird dich dann ein Supporter oder Moderator dich moven und dir helfen.");
			$info->set("infoline5" , "§7=================");
			$info->set("Popup" , "» §6Vielen Dank");
		}
		$info->save();

		$LobbyTitle = new Config($this->getDataFolder() . "Title.yml" , Config::YAML);
		if (empty($LobbyTitle->get("LobbySendigBackTitle"))) {
			$LobbyTitle->set("LobbySendigBackTitle" , "§7» §5§lLobby");
		}
		$LobbyTitle->save();


	}

	public function onJoin(PlayerJoinEvent $ev)
	{

		$config = new Config($this->getDataFolder() . "config.yml" , Config::YAML);

		$player = $ev->getPlayer();
		$player->getInventory()->clearAll();
		$ev->setJoinMessage("");
		$player->setFood(20);
		$player->setHealth(20);
		$player->setGamemode(0);
		$player->getlevel()->addSound(new AnvilUseSound($player));
		$player->addTitle("§7[§a»§7] §aWillkommen" , "");
		$player->sendPopup("§7× §6Willkommen " . Color::WHITE . $player->getDisplayName() . Color::DARK_GRAY . " ×");
		$player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
		$player->sendMessage($config->get("JoinBroadcast1"));
		$player->sendMessage($config->get("LEER"));
		$player->sendMessage($config->get("JoinBroadcast2"));
		$player->sendMessage($config->get("JoinBroadcast3"));
		$player->sendMessage($config->get("JoinBroadcast4"));
		$player->sendMessage($config->get("LEER2"));
		$player->sendMessage($config->get("JoinBroadcast5"));

		$player->getInventory()->clearAll();
		$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
		$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
		$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
		$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
		$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
		if ($player->hasPermission("lobby.yt")) {
			$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
		} else {
			$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
		}
		$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));

	}

	public function onJump(PlayerInteractEvent $event)
	{
		$item = $event->getItem();
		$player = $event->getPlayer();
		if ($item->getId() === Item::SLIMEBALL) {
			$player->setMotion(new Vector3(1 * (-sin($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI)) , -sin($player->pitch / 180 * M_PI) + 0.10 , 1 * (cos($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI))));
		}

		$player->getInventory()->setSize(9);
		$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
		$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
		$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
		$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
		$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
		if ($player->hasPermission("lobby.yt")) {
			$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
		} else {
			$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
		}
		$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));

	}

	public function onBreak(BlockBreakEvent $ev)
	{

		$config = new Config($this->getDataFolder() . "config.yml" , Config::YAML);
		$player = $ev->getPlayer();
		$ev->setCancelled(true);
		$player->sendMessage($this->prefix . $config->get("BlockBreakMessage"));
	}

	public function onQuit(PlayerQuitEvent $ev)
	{
		$player = $ev->getPlayer();
		$name = $player->getName();
		$ev->setQuitMessage("");
		$player->sendPopup("§7[§c-§7] " . Color::DARK_GRAY . $name);
	}

	public function onPlace(BlockPlaceEvent $ev)
	{
		$ev->setCancelled(true);
	}

	public function Hunger(PlayerExhaustEvent $ev)
	{
		$ev->setCancelled(true);
	}

	public function ItemMove(PlayerDropItemEvent $ev)
	{
		$ev->setCancelled(true);
	}

	public function onConsume(PlayerItemConsumeEvent $ev)
	{
		$ev->setCancelled(true);
	}

	public function onCommand(CommandSender $sender , Command $cmd , string $label , array $args) : bool
	{
		if ($sender instanceof Player) {
			switch ($cmd->getName()) {

				case "hub":
				case "lobby":

					$LobbyTitle = new Config($this->getDataFolder() . "Title.yml" , Config::YAML);
					$config = new Config($this->getDataFolder() . "config.yml" , Config::YAML);

					$sender->
					$sender->sendMessage($this->prefix . $config->get("Hub/Lobby"));
					$sender->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
					$sender->addTitle($LobbyTitle->get("LobbySendigBackTitle"));

					return true;
			}
		}

		return true;
	}

	public function onDamage(EntityDamageEvent $ev)
	{

		if ($ev->getCause() === EntityDamageEvent::CAUSE_FALL) {
			$ev->setCancelled(true);
		}

	}

	public function onChat(PlayerChatEvent $ev)
	{

		$p = $ev->getPlayer();
		$config = new Config($this->getDataFolder() . "config.yml" , Config::YAML);

		if ($p->hasPermission("lobby.chat")) {
			$ev->setCancelled(false);
		} else {
			$p->sendMessage($this->prefix . $config->get("Chat"));
			$ev->setCancelled(true);
		}

	}

	public function onInteract(PlayerInteractEvent $ev)
	{

		$player = $ev->getPlayer();
		$item = $ev->getItem();
		$info = new Config($this->getDataFolder() . "info.yml" , Config::YAML);
		$config = new Config($this->getDataFolder() . "config.yml" , Config::YAML);

		if ($item->getCustomName() == "§aInfos") {
			$player->sendMessage($info->get("infoline1"));
			$player->sendMessage($info->get("infoline2"));
			$player->sendMessage($info->get("infoline3"));
			$player->sendMessage($info->get("infoline4"));
			$player->sendMessage($info->get("infoline5"));
			$player->sendPopup($info->get("Popup"));

		} elseif ($item->getCustomName() == "§4Teleporter") {

			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(160)->setCustomName("§7-"));
			$player->getInventory()->setItem(1 , Item::get(267)->setCustomName("§eMiniGames"));
			$player->getInventory()->setItem(2 , Item::get(160)->setCustomName("§7-"));
			$player->getInventory()->setItem(3 , Item::get(138)->setCustomName("§bCityBuild"));
			$player->getInventory()->setItem(4 , Item::get(160)->setCustomName("§7-"));
			$player->getInventory()->setItem(5 , Item::get(399)->setCustomName("§5§lLobby"));
			$player->getInventory()->setItem(6 , Item::get(160)->setCustomName("§7-"));
			$player->getInventory()->setItem(7 , Item::get(346)->setCustomName("§cFFA"));
			$player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§cZurück"));

		} elseif ($item->getCustomName() == "§4Silent Hub") {
			if ($player->hasPermission("Lobby.silent")){
				$event->getPlayer()->transfer("skyminepe.ga","25589");
			}
              
                } elseif ($item->getCustomName() == "§eLobby Switcher") {
            $player->getInventory()->clearAll();
            $player->getInventory()->setItem(0 , Item::get(41)->setCustomName("§6Premium Lobby 1"));
            $player->getInventory()->setItem(2 , Item::get(42)->setCustomName("§bLobby 1"));
            $player->getInventory()->setItem(3 , Item::get(42)->setCustomName("§bLobby 2"));
            $player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§4Zurück"));
                 
		} elseif ($item->getCustomName() == "§9Gadgets") {

			$player->sendPopup("§8» §6Hier findest du alle Gadgets & Effekte");
			$player->getlevel()->addSound(new AnvilUseSound($player));
			$player->removeAllEffects();
			$player->getInventory()->clearAll();
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(0 , Item::get(377)->setCustomName("§6Effekte"));
				$player->getInventory()->setItem(2 , Item::get(38)->setCustomName("§dBoots"));
				$player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§cZurück"));
				$player->getInventory()->setItem(1 , Item::get(287)->setCustomName("§aPartikel"));
				$player->getInventory()->setItem(4 , Item::get(341)->setCustomName("§aJump Slime"));
				$player->getInventory()->setItem(3 , Item::get(378)->setCustomName("§bKöpfe"));
				$player->getInventory()->setItem(5 , Item::get(131)->setCustomName("§eGrößen"));
			} else {
				$player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§cZurück"));
				$player->getInventory()->setItem(1 , Item::get(160)->setCustomName("§aPartikel §7[§6Premium§7]"));
				$player->getInventory()->setItem(0 , Item::get(377)->setCustomName("§6Effekte §7[§6Premium§7]"));
				$player->getInventory()->setItem(2 , Item::get(38)->setCustomName("§dBoots §7[§6Premium§7]"));
				$player->getInventory()->setItem(4 , Item::get(341)->setCustomName("§aJump Slime §7[§6Premium§7]"));
				$player->getInventory()->setItem(3 , Item::get(378)->setCustomName("§bKöpfe §7[§6Premium§7]"));
				$player->getInventory()->setItem(5 , Item::get(131)->setCustomName("§eGrößen §7[§6Premium§7]"));
			}

		} elseif ($item->getCustomName() == "§6Effekte") {

			$player->sendPopup("§8» §6Hier findest du alle Extras & Effekte");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(265)->setCustomName("§8§l»§r §aJumpboost"));
			$player->getInventory()->setItem(1 , Item::get(388)->setCustomName("§8§l»§r §6Übelkeit"));
			$player->getInventory()->setItem(2 , Item::get(266)->setCustomName("§8§l»§r §3Speedboost"));
			$player->getInventory()->setItem(3 , Item::get(331)->setCustomName("§8§l»§r §5Blindheit"));
			$player->getInventory()->setItem(4 , Item::get(264)->setCustomName("§8§l»§r §fGhost"));
			$player->getInventory()->setItem(6 , Item::get(32)->setCustomName("§8» §c§lausschalten"));
			$player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§cZurück"));
			
			} elseif ($item->getCustomName() == "§bKöpfe") {

			$player->sendPopup("§8» §6Hier findest du alle Köpfe");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(397 , 2)->setCustomName("§8»§r §aZombieKopf"));
			$player->getInventory()->setItem(1 , Item::get(160)->setCustomName("-"));
			$player->getInventory()->setItem(2 , Item::get(397 , 3)->setCustomName("§8»§r §5Steve Kopf"));
			$player->getInventory()->setItem(3 , Item::get(160)->setCustomName("-"));
			$player->getInventory()->setItem(4 , Item::get(397 , 4)->setCustomName("§8»§r §cCreeper Kopf"));
			$player->getInventory()->setItem(6 , Item::get(409)->setCustomName("§8» §c§lKöpfe Absetzen"));
			$player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§cZurück"));
			
			} elseif ($item->getCustomName() == "§5Steve Kopf") {

			$player->getInventory()->clearAll();
			$player->getArmorInventory()->setHead(Item::get(397, 4 , 4));
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));
			$player->sendMessage($this->prefix . " §7Du hast den §a§lSteve Kopf§r §7angezogen");


		} elseif ($item->getCustomName() == "§eMiniGames") {

			$player->sendMessage("");
			$player->sendMessage($this->prefix . Color::RED . " Du wurdest zu §eMiniGames §r§cteleportiert");
			$player->teleport(new Vector3(60 , 26 , 99));
			$player->getlevel()->addSound(new EndermanTeleportSound($player));
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));

		} elseif ($item->getCustomName() == "§bCityBuild") {

			$player->sendMessage("");
			$player->sendMessage($this->prefix . Color::RED . " Du wurdest zu §bCityBuild §r§cteleportiert");
			$player->teleport(new Vector3(-57 , 26 , 112));
			$player->getlevel()->addSound(new EndermanTeleportSound($player));
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));
			
			} elseif ($item->getCustomName() == "§cFFA") {

			$player->sendMessage("");
			$player->sendMessage($this->prefix . Color::RED . " Du wurdest zu §4FFA §r§cteleportiert");
			$player->teleport(new Vector3(68 , 26 , 107));
			$player->getlevel()->addSound(new EndermanTeleportSound($player));
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));

		} elseif ($item->getCustomName() == "§cZurück") {

			$player->getInventory()->clearAll();
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));

		} elseif ($item->getCustomName() == "§8§l»§r §aJumpboost") {

			$player->removeAllEffects();
			$eff = new EffectInstance(Effect::getEffect(Effect::JUMP) , 500 * 20 , 5 , false);
			$player->addEffect($eff);
			$player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §a§lJumpBoots§r §7ausgewählt");
			$player->sendPopup("§8§l»§r §aJumpboost§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));

		} elseif ($item->getCustomName() == "§8§l»§r §3Speedboost") {

			$player->removeAllEffects();
			$eff = new EffectInstance(Effect::getEffect(Effect::SPEED) , 500 * 20 , 5 , false);
			$player->addEffect($eff);
			$player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §3§lSpeedboots§r §7ausgewählt");
			$player->sendPopup("§8§l»§r §3Speedboost§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));

		} elseif ($item->getCustomName() == "§8§l»§r §fGhost") {

			$player->removeAllEffects();
			$eff = new EffectInstance(Effect::getEffect(Effect::INVISIBILITY) , 500 * 20 , 5 , false);
			$player->addEffect($eff);
			$player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §f§lGhost§r §7ausgewählt");
			$player->sendPopup("§8§l»§r §fGhost§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));
			
			} elseif ($item->getCustomName() == "§8§l»§r §6Übelkeit") {

			$player->removeAllEffects();
			$eff = new EffectInstance(Effect::getEffect(Effect::NAUSEA) , 500 * 20 , 5 , false);
			$player->addEffect($eff);
			$player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §a§lÜbelkeit§r §7ausgewählt");
			$player->sendPopup("§8§l»§r §6Übelkeit§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));
			
			} elseif ($item->getCustomName() == "§8§l»§r §5Blindheit") {

			$player->removeAllEffects();
			$eff = new EffectInstance(Effect::getEffect(Effect::BLINDNESS) , 500 * 20 , 5 , false);
			$player->addEffect($eff);
			$player->sendMessage($this->prefix . Color::WHITE . " §7Du hast den Effekt §5§lBlindheit§r §7ausgewählt");
			$player->sendPopup("§8§l»§r §5Blindheit§7: §cAktiviert");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));

		} elseif ($item->getCustomName() == "§fFly") {


			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(341)->setCustomName("§8§l»§r §aAKTIVIEREN"));
			$player->getInventory()->setItem(4 , Item::get(376)->setCustomName("§8§l»§r §4DEAKTIVIEREN"));
			$player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§cZurück"));

		} elseif ($item->getCustomName() == "§8§l»§r §aAKTIVIEREN") {

			$player->setAllowFlight(true);
			$player->sendMessage($this->prefix . Color::WHITE . " §7Du hast §b§lFly§r §7aktiviert.");
			$player->sendPopup("§8§l»§r §bFly§7: §aAktiviert");

		} elseif ($item->getCustomName() == "§8§l»§r §4DEAKTIVIEREN") {

			$player->setAllowFlight(false);
			$player->setHealth(20);
			$player->setFood(20);
			$player->sendMessage($this->prefix . Color::WHITE . " §7Du hast §b§lFly§r §7deaktiviert.");
			$player->sendPopup("§8§l»§r §bFly§7: §cDeaktiviert");

		} elseif ($item->getCustomName() == "§eSpieler verstecken §8[§aSichtbar§8]") {

			$player->getInventory()->setItem(6 , Item::get(280)->setCustomName("§eSpieler verstecken §8[§cUnsichtbar§8]"));
			$this->hideall[] = $player;
			$player->sendMessage($this->prefix . "§7 Die Spieler sind jetzt §8[§c§lUnsichtbar§r§8]");

		} elseif ($item->getCustomName() == "§eSpieler verstecken §8[§cUnsichtbar§8]") {

			unset($this->hideall[array_search($player , $this->hideall)]);
			foreach ($this->getServer()->getOnlinePlayers() as $p) {
				$player->showPlayer($p);
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));
			$player->sendMessage($this->prefix . "§7 Die Spieler sind jetzt §8[§a§lSichtbar§r§8]");

		} elseif ($item->getCustomName() == "§dBoots") {

			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(309)->setCustomName("§7§lEISENSCHUHE"));
			$player->getInventory()->setItem(1 , Item::get(313)->setCustomName("§1§lDIAMANTSCHUHE"));
			$player->getInventory()->setItem(2 , Item::get(317)->setCustomname("§e§lGOLDENESCHUHE"));
			$player->getInventory()->setItem(3 , Item::get(301)->setCustomName("§c§lLEDERSCHUHE"));
			$player->getInventory()->setItem(4 , Item::get(305)->setCustomName("§9§lKETTENSCHUHE"));
			$player->getInventory()->setItem(7 , Item::get(339)->setCustomName("§cSeite 2"));
			$player->getInventory()->setItem(6 , Item::get(32)->setCustomName("§8» §4§lausschalten"));
			$player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§cZurück"));

		} elseif ($item->getCustomName() == "§fFly §7[§6Premium§7]") {

			$player->sendMessage($this->prefix . " §7Dieses Funktion dürfen nur §6Premium§7 Spieler verwenden");

		} elseif ($item->getCustomName() == "§5§lLobby") {

			$player->sendMessage($this->prefix . $config->get("Hub/Lobby"));
			$player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
			$player->addTitle("§7» §6Lobby" , "");
			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));

		} elseif ($item->getCustomName() == "§6Effekte §7[§6Premium§7]") {

			$player->sendMessage($this->prefix . " §7Dieses Funktion dürfen nur §6Premium§7 Spieler verwenden");

		} elseif ($item->getCustomName() == "§dBoots §7[§6Premium§7]") {

			$player->sendMessage($this->prefix . " §7Dieses Funktion dürfen nur §6Premium§7 Spieler verwenden");

		} elseif ($item->getCustomName() == "§7§lEISENSCHUHE") {

			$player->getInventory()->clearAll();
			$player->getArmorInventory()->setBoots(Item::get(309 , 0 , 1));
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));
			$player->sendMessage($this->prefix . " §7Du hast die §a§lEisenSchuhe§r §7angezogen");

		} elseif ($item->getCustomName() == "§1§lDIAMANTSCHUHE") {

			$player->getInventory()->clearAll();
			$player->getArmorInventory()->setBoots(Item::get(313 , 0 , 1));
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));
			$player->sendMessage($this->prefix . " §7Du hast die §a§lDiamantschuhe§r §7angezogen");

		} elseif ($item->getCustomName() == "§e§lGOLDENESCHUHE") {

			$player->getInventory()->clearAll();
			$player->getArmorInventory()->setBoots(Item::get(317 , 0 , 1));
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));
			$player->sendMessage($this->prefix . " §7Du hast die §a§lGoldenenschuhe§r §7angezogen");
			
			} elseif ($item->getCustomName() == "§c§lLEDERSCHUHE") {

			$player->getInventory()->clearAll();
			$player->getArmorInventory()->setBoots(Item::get(301 , 0 , 1));
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));
			$player->sendMessage($this->prefix . " §7Du hast die §a§lLederSchuhe§r §7angezogen");
			
			
           } elseif ($item->getCustomName() == "§9§lKETTENSCHUHE") {

			$player->getInventory()->clearAll();
			$player->getArmorInventory()->setBoots(Item::get(305 , 0 , 1));
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(339)->setCustomName("§aInfos"));
			$player->getInventory()->setItem(4 , Item::get(345)->setCustomName("§4Teleporter"));
			$player->getInventory()->setItem(1 , Item::get(46)->setCustomName("§4Silent Hub"));
			$player->getInventory()->setItem(2 , Item::get(347)->setCustomName("§eLobby Switcher"));
			$player->getInventory()->setItem(8 , Item::get(54)->setCustomName("§9Gadgets"));
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(7 , Item::get(288)->setCustomName("§fFly"));
			} else {
				$player->getInventory()->setItem(7 , Item::get(152)->setCustomName("§fFly §7[§6Premium§7]"));
			}
			$player->getInventory()->setItem(6 , Item::get(369)->setCustomName("§eSpieler verstecken §8[§aSichtbar§8]"));
			$player->sendMessage($this->prefix . " §7Du hast die §a§lKettenSchuhe§r §7angezogen");




		} elseif ($item->getCustomName() == "§8» §c§lausschalten") {

			$player->removeAllEffects();
			$player->sendMessage($this->prefix . " §7Du hast alle Effekte oder Boots §c§lDeaktiviert§r");

		} elseif ($item->getCustomName() == "§8» §4§lausschalten") {

			$player->getInventory()->clearAll();
			$player->sendMessage($this->prefix . " §7Du hast alle Effekte oder Boots §c§lDeaktiviert§r");
			$player->getInventory()->setSize(9);
			$player->getInventory()->setItem(0 , Item::get(309)->setCustomName("§7§lEISENSCHUHE"));
			$player->getInventory()->setItem(1 , Item::get(313)->setCustomName("§1§lDIAMANTSCHUHE"));
			$player->getInventory()->setItem(2 , Item::get(317)->setCustomName("§e§lGOLDENESCHUHE"));
			$player->getInventory()->setItem(3 , Item::get(301)->setCustomName("§c§lLEDERSCHUHE"));
			$player->getInventory()->setItem(4 , Item::get(305)->setCustomName("§9§lKETTENSCHUHE"));
			$player->getInventory()->setItem(6 , Item::get(32)->setCustomName("§8» §4§lausschalten"));
			$player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§cZurück"));

		} elseif ($item->getCustomName() == "§cZurück") {

			$player->getInventory()->clearAll();
			$player->getInventory()->setSize(9);
			if ($player->hasPermission("lobby.yt")) {
				$player->getInventory()->setItem(0 , Item::get(377)->setCustomName("§6Effekte"));
				$player->getInventory()->setItem(2 , Item::get(38)->setCustomName("§dBoots"));
				$player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§cZurück"));
				$player->getInventory()->setItem(4 , Item::get(341)->setCustomName("§aJump Slime"));
				$player->getInventory()->setItem(1 , Item::get(160)->setCustomName("§7-"));
				$player->getInventory()->setItem(3 , Item::get(378)->setCustomName("§bKöpfe"));
			} else {
				$player->getInventory()->setItem(8 , Item::get(351 , 1)->setCustomName("§cZurück"));
				$player->getInventory()->setItem(1 , Item::get(160)->setCustomName("§7-"));
				$player->getInventory()->setItem(4 , Item::get(341)->setCustomName("§aJump Slime §7[§6Premium§7]"));
				$player->getInventory()->setItem(0 , Item::get(377)->setCustomName("§6Effekte §7[§6Premium§7]"));
				$player->getInventory()->setItem(2 , Item::get(38)->setCustomName("§dBoots §7[§6Premium§7]"));
				$player->getInventory()->setitem(3 , Item::get(378)->setCustomName("§bKöpfe §7[§6Premium§7]"));
			}

		}

	}

}