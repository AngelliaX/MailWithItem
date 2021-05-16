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
use muqsit\invmenu\InvMenu;
use pocketmine\item\Item;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\inventory\PlayerInventory;
use Tungst_mailwithitem\Main;
class customlistmail implements Listener
{
    public $main;
    public $sender;
    public $menu;
    public function __construct(Main $main, $sender)
    {
        $this->main = $main;
        $this->sender = $sender;
        $this->mainform($sender, "");
    }
    public function mainform($player, $err)
    {
        $api = $this
            ->main
            ->getServer()
            ->getPluginManager()
            ->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null)
        {
            if ($data === null)
            {
                Main::$instance->opmainform($player, "");
                return false;
            }
            else
            {
                $n = strtolower($player->getName());
                if (null == $this
                    ->main
                    ->getConfig()
                    ->getNested("SaveItem"))
                {
                    Main::$instance->opmainform($player, "");
                    return false;
                }
                $this->mailform($player, "", $data);
            }
        });
        $n = strtolower($player->getName());
        $form->setTitle("§bCustom Item L§0ist");
        $txt = "§c•Dont have any saved item list";
        if (null != $this
            ->main
            ->getConfig()
            ->getNested("SaveItem"))
        {
            $txt = "";
            $id = 0;
            foreach ($this
                ->main
                ->getConfig()
                ->getNested("SaveItem") as $value)
            {
                $num = $id;
                $cc = 1;
                if ($cc == 1)
                {
                    $form->addButton("§0ID: §b$num", 0, "textures/ui/ps4_face_button_right");
                    $cc = 2;
                }
                else
                {
                    $form->addButton("§0ID: §b$num", 0, "textures/ui/ps4_face_button_down");
                    $cc = 1;
                }
                $id++;
            }
        }
        else
        {
            $form->addButton("§7Back", 0, "textures/ui/ps4_face_button_left");
        }
        $form->setContent("$err" . "$txt");
        $form->sendToPlayer($player);
        return $form;
    }
    public function mailform($player, $err, $id)
    {
        $api = $this
            ->main
            ->getServer()
            ->getPluginManager()
            ->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) use ($id)
        {
            if ($data === null)
            {
                return false;
            }
            else
            {
                switch ($data)
                {
                    case "0":
                        $this->confirmdeletemail($player, $id);
                    break;
                    case "1":
                        $this->mainform($player, "");
                    break;
                    default:

                    break;
                }
            }
        });
        $n = strtolower($player->getName());
        $iteminfo = $this
            ->main
            ->getConfig()
            ->getNested("SaveItem.$id.iteminfo");

        $form->setTitle("§bCustom Item L§0ist");
        $form->setContent("$err" . "§7Item Info: \n  $iteminfo");
        $form->addButton("Delete Mail", 0, "textures/ui/ps4_face_button_right");
        $form->addButton("Back", 0, "textures/ui/ps4_face_button_down");
        $form->sendToPlayer($player);
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
        return $form;
    }
    public function confirmdeletemail($player, $id)
    {
        $api = $this
            ->main
            ->getServer()
            ->getPluginManager()
            ->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (Player $player, int $data = null) use ($id)
        {
            if ($data === null)
            {
                return false;
            }
            else
            {
                switch ($data)
                {
                    case "0":
                        $this->deleteitem($player, $id);
                    break;
                    case "1":
                        $this->mailform($player, "", $id);
                    break;
                    default:

                    break;
                }
            }
        });
        $n = strtolower($player->getName());
        $form->setTitle("§bCustom Item L§0ist");
        $form->setContent("" . "§7Are you sure to delete this item list\n");
        $form->addButton("Yes", 0, "textures/ui/ps4_face_button_down");
        $form->addButton("No", 0, "textures/ui/ps4_face_button_right");
        $form->sendToPlayer($player);
        return $form;
    }
    public function deleteitem($player, $id)
    {
        $n = strtolower($player->getName());
        $a = $this
            ->main
            ->getConfig()
            ->getNested("SaveItem");
        unset($a[$id]);
        $b = array_values($a);
        $this
            ->main
            ->getConfig()
            ->setNested("SaveItem", $b);
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
        $this->mainform($player, "Successfully deleted a item list");
    }
}

