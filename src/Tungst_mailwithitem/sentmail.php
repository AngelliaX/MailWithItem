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
class sentmail implements Listener
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
                    ->getNested("send.$n"))
                {
                    Main::$instance->memmainform($player, "");
                    return false;
                }
                $this->mailform($player, "", $data);
            }
        });
        $n = strtolower($player->getName());
        $form->setTitle("§fSent M§0ail");
        $txt = "§c•You have not sent any mail";
        if (null != $this
            ->main
            ->getConfig()
            ->getNested("send.$n"))
        {
            $txt = "";
            $id = 0;
            foreach ($this
                ->main
                ->getConfig()
                ->getNested("send.$n") as $value)
            {
                $num = $id + 1;
                $taker = $value["taker"];
                $msg = "No item";
                if (isset($value["itemlist"]) and count($value["itemlist"]) > 1)
                {
                    $msg = "Item inside";
                }
                $form->addButton("$num.$taker\n$msg", 0, "textures/ui/ps4_face_button_left");
                $id++;
            }
        }
        else
        {
            $form->addButton("§0Back", 0, "textures/ui/ps4_face_button_down");
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
        $msg = $this
            ->main
            ->getConfig()
            ->getNested("send.$n.$id.msg");
        $sender = $this
            ->main
            ->getConfig()
            ->getNested("send.$n.$id.taker");
        $iteminfo = $this
            ->main
            ->getConfig()
            ->getNested("send.$n.$id.iteminfo");
        $time = $this
            ->main
            ->getConfig()
            ->getNested("send.$n.$id.time");
        $form->setTitle("§fMy M§0ail");
        $form->setContent("$err" . "§7Taker: §a$sender\n" . "§7Time: §b$time\n" . "§7Msg: §7$msg\n" . "§7Item Info: \n  $iteminfo");
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
        $form->setTitle("§fSent M§0ail");
        $form->setContent("" . "§7Are you sure to delete this message\n");
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
            ->getNested("send.$n");
        unset($a[$id]);
        $b = array_values($a);
        $this
            ->main
            ->getConfig()
            ->setNested("send.$n", $b);
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

