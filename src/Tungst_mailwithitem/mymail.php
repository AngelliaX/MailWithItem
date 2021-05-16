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
class mymail implements Listener
{
    public $temp_item = [];
    public $isonadd = [];
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
                Main::$instance->memmainform($player, "");
                return false;
            }
            else
            {
                $n = strtolower($player->getName());
                if (null == $this
                    ->main
                    ->getConfig()
                    ->getNested("take.$n"))
                {
                    Main::$instance->memmainform($player, "");
                    return false;
                }
                $this->mailform($player, "", $data);
            }
        });
        $n = strtolower($player->getName());
        $form->setTitle("§fMy M§0ail");
        $txt = "§c•You dont have any mail";
        if (null != $this
            ->main
            ->getConfig()
            ->getNested("take.$n"))
        {
            $txt = "";
            $id = 0;
            foreach ($this
                ->main
                ->getConfig()
                ->getNested("take.$n") as $value)
            {
                $num = $id + 1;
                $mailer = $value["sender"];
                $msg = "No item";
                if (isset($value["itemlist"]) and count($value["itemlist"]) > 1)
                {
					$msg = "Item inside";
                }
                if (null == $this
                    ->main
                    ->getConfig()
                    ->getNested("take.$n.$id.isread"))
                {
                    $form->addButton("$num.$mailer\n$msg §cNew", 0, "textures/ui/ps4_face_button_right");
                }
                else
                {
                    $form->addButton("$num.$mailer\n$msg §aSeen", 0, "textures/ui/ps4_face_button_down");
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
                        $this->takeitem($player, $id);
                    break;
                    case "1":
                        $this->confirmdeletemail($player, $id);
                    break;
                    case "2":
                        $this->mainform($player, "");
                    break;
                    default:

                    break;
                }
            }
        });
        $n = strtolower($player->getName());
        $msg = $this
            ->main
            ->getConfig()
            ->getNested("take.$n.$id.msg");
        $sender = $this
            ->main
            ->getConfig()
            ->getNested("take.$n.$id.sender");
        $iteminfo = $this
            ->main
            ->getConfig()
            ->getNested("take.$n.$id.iteminfo");
        $time = $this
            ->main
            ->getConfig()
            ->getNested("take.$n.$id.time");
        $count = count($this
            ->main
            ->getConfig()
            ->getNested("take.$n.$id.itemlist"));
        $info = "";
        if ($count == 0)
        {
            if ($this
                ->main
                ->getConfig()
                ->getNested("take.$n.$id.iteminfo") == "")
            {
                $info = "There is nothing to take";
            }
            else
            {
                $info = "Have already toke";
            }
        }
        else
        {
            $info = "There is item to take";
        }

        $form->setTitle("§7My M§0ail");
        $form->setContent("$err" . "§7Sender: §a$sender\n" . "§7Time: §b$time\n" . "§7Status: §a$info\n" . "§7Msg: §7$msg\n" . "§7Item Info: \n  $iteminfo");
        $form->addButton("Take item", 0, "textures/ui/ps4_face_button_down");
        $form->addButton("Delete Mail", 0, "textures/ui/ps4_face_button_right");
        $form->addButton("Back", 0, "textures/ui/ps4_face_button_down");
        $form->sendToPlayer($player);
        $this
            ->main
            ->getConfig()
            ->setNested("take.$n.$id.isread", true);
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
    public function takeitem($player, $id)
    {
        $n = strtolower($player->getName());
        $count = count($this
            ->main
            ->getConfig()
            ->getNested("take.$n.$id.itemlist"));
        if ($count == 0)
        {
            if ($this
                ->main
                ->getConfig()
                ->getNested("take.$n.$id.iteminfo") == "")
            {
                $this->mailform($player, "This mail dont have item\n", $id);
                return false;
            }
            else
            {
                $this->mailform($player, "You had already toke those items\n", $id);
                return false;
            }
        }
        $checkcount = 64 * $count;
        if ($player->getInventory()
            ->canAddItem(Item::get(1, 0, $checkcount)))
        {
            foreach ($this
                ->main
                ->getConfig()
                ->getNested("take.$n.$id.itemlist") as $item)
            {
                $itemtoadd = unserialize(utf8_decode($item));
                $player->getInventory()
                    ->addItem($itemtoadd);
                // $p->sendMessage("\n§aYou have receive an item from auction\n");
                
            }
            $this
                ->main
                ->getConfig()
                ->setNested("take.$n.$id.itemlist", []);
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
            $this->mainform($player, "Successfully take those item.");
        }
        else
        {
            $this->mainform($player, "Dont have enought space on your inven");
        }

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
        $form->setTitle("§fMy M§0ail");
        $form->setContent("" . "§7Are you sure to delete this message\n");
        $form->addButton("Yes", 0, "textures/ui/ps4_face_button_down");
        $form->addButton("No", 0, "textures/ui/ps4_face_button_right");
        $form->sendToPlayer($player);
        return $form;
    }
    public function deleteitem($player, $id)
    {
        $n = strtolower($player->getName());
        $count = count($this
            ->main
            ->getConfig()
            ->getNested("take.$n.$id.itemlist"));
        if ($count != 0)
        {
            $this->mailform($player, "There is item that you have not receive!!!\n", $id);
            return false;
        }
        $a = $this
            ->main
            ->getConfig()
            ->getNested("take.$n");
        unset($a[$id]);
        $b = array_values($a);
        $this
            ->main
            ->getConfig()
            ->setNested("take.$n", $b);
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
        $this->mainform($player, "Successfully deleted a mail");
    }
}

