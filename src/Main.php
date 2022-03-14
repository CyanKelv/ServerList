<?php

declare(strict_types=1);

namespace Kelvinlolz\ServerList;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

    public function onEnable() : void
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveResource("config.yml");
        $this->getLogger()->info("Server List plugin enabled!");
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		switch($command->getName()){
			case "servers":
                if(!isset($args[0])){
                    if($sender instanceof ConsoleCommandSender){
                        $sender->sendMessage(TextFormat::RED . "Command can only be used in-game!");
                        return true;
                    }
                    $servers = $this->getConfig()->get("servers");
                    $form = new SimpleForm(function (Player $player, $data) use($servers){
                        if($data === null){
                            return false;
                        }

                        $server = $servers[$data];
                        $player->transfer($server["ip"], $server["port"], "Transferring to server: " . $data);
                        return true;
                    });
                    $form->setTitle($this->getConfig()->getNested("settings.formTitle"));
                    $form->setContent($this->getConfig()->getNested("settings.formContent"));
                    foreach ($servers as $name => $data){
                        $displayName = $name;
                        if($this->getConfig()->getNested("settings.displayDetails")){
                            $displayName .= "\n" . $data["ip"] . ":" . $data["port"];
                        }
                        $form->addButton($displayName, ($data["imageURL"] !== -1 ? SimpleForm::IMAGE_TYPE_URL : -1), ($data["imageURL"] == -1 ? "" : $data["imageURL"]), $name);
                    }
                    $sender->sendForm($form);
                } else {
                    switch($args[0]){
                        case "reload":
                            if($sender->hasPermission("servers.reload")){
                                $this->reloadConfig();
                                $sender->sendMessage(TextFormat::GREEN . "Server list config reloaded");
                                return true;
                            }
                            return false;
                    }
                }
				return true;
			default:
				throw new \AssertionError("This line will never be executed");
		}
	}

}
