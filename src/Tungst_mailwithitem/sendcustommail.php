<?php
namespace Tungst_mailwithitem;

use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\inventory\PlayerInventory;
use Tungst_mailwithitem\Main;
class sendcustommail implements Listener
{
    public $main;
    public $sender;
    public function __construct(Main $main, $sender)
    {
        $this->main = $main;
        $this->sender = $sender;
        $this->memsendmailform($sender, "");
    }
    public function memsendmailform($player, $err)
    {
        $api = $this
            ->main
            ->getServer()
            ->getPluginManager()
            ->getPlugin("FormAPI");
        $form = $api->createCustomForm(function (Player $player, $data)
        {
            $api2 = $this
                ->main
                ->getServer()
                ->getPluginManager()
                ->getPlugin("EconomyAPI");
            if ($data === null)
            {
                Main::$instance->opmainform($player, "");
                return false;
            }
            else
            {
                if ($data[1] == "" || $data[2] == "" || $data[3] == "")
                {
                    $this->memsendmailform($player, "§c•Please fill all form\n");
                    return false;
                }
                //var_dump($data[1]);
                $name = strtolower($data[1]);
                if (!in_array($name, $this
                    ->main
                    ->getConfig()
                    ->getNested("Joined")))
                {
                    $this->memsendmailform($player, "§c•Player §b" . $name . "§c has not joined the sv before§7\n");
                    return false;
                }
                $count = 0;
                if (null != $this
                    ->main
                    ->getConfig()
                    ->getNested("take.$name"))
                {
                    $count = count($this
                        ->main
                        ->getConfig()
                        ->getNested("take.$name"));
                }
            }
            $count2 = 0;
            $name2 = strtolower($player->getName());

            $info = $this->getInfo($data[3]);
            if ($info === false)
            {
                $this->memsendmailform($player, "§c•Dont find ItemList ID :§b" . $data[3] . "\n");
                return false;
            }
            if (null != $this
                ->main
                ->getConfig()
                ->getNested("send.$name2"))
            {
                $count2 = count($this
                    ->main
                    ->getConfig()
                    ->getNested("send.$name2"));
            }
            $content = ["sender" => $player->getName() , "msg" => $data[2], "iteminfo" => $info["iteminfo"], "itemlist" => $info["itemlist"], "time" => date('d/m/Y - H:i') ];
            $content2 = ["taker" => $name, "msg" => $data[2], "iteminfo" => $info["iteminfo"], "time" => date('d/m/Y - H:i') ];
            $this
                ->main
                ->getConfig()
                ->setNested("take.$name.$count", $content);
            $this
                ->main
                ->getConfig()
                ->setNested("send.$name.$count2", $content2);
            $this
                ->main
                ->getConfig()
                ->setAll($this
                ->main
                ->getConfig()
                ->getAll());
            $this
                ->main
                ->getConfig()
                ->save();
            $id = $data[3];
            Main::$instance->opmainform($player, "§a•Successfully send a custom mail with itemlist id $id to §a$name\n");
            if (null !== $this
                ->main
                ->getServer()
                ->getPlayer($name))
            {
                $this
                    ->main
                    ->getServer()
                    ->getPlayer($name)->sendMessage("§a•You have got a mail from a player");
            }
        });
        if ($err == "")
        {
            $err = "§7Fill these form:";
        }
        $form->setTitle("§bSend Custom M§0ail");
        $form->addLabel("$err");
        $form->addInput("§7Type §cFULL§7 Of Your Player's Name:");
        $form->addInput("§7Type Your Message:");
        $form->addInput("§7Type ItemList ID:");
        $form->sendToPlayer($player);
        return $form;
    }
    public function getInfo($id)
    {
        if (null != $this
            ->main
            ->getConfig()
            ->getNested("SaveItem.$id"))
        {
            $a = ["iteminfo" => $this
                ->main
                ->getConfig()
                ->getNested("SaveItem.$id.iteminfo") , "itemlist" => $this
                ->main
                ->getConfig()
                ->getNested("SaveItem.$id.itemlist") ];
            return $a;
        }
        else
        {
            return false;
        }
    }
}

