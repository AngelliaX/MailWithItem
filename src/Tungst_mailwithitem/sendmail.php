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
class sendmail implements Listener
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
                Main::$instance->memmainform($player, "");
                return false;
            }
            else
            {
                if ($data[1] == "" || $data[2] == "")
                {
                    if ($player->isOp() == false)
                    {
                        $this->memsendmailform($player, "§c•Please fill the first and second form\n");
                        return false;
                    }
                }
                #var_dump($data[1]);
                $name = strtolower($data[1]);
                if (!in_array($name, $this
                    ->main
                    ->getConfig()
                    ->getNested("Joined")))
                {
                    if ($player->isOp() == false)
                    {
                        $this->memsendmailform($player, "§c•Player §b" . $name . "§c has not joined the sv before§7\n");
                        return false;
                    }
                    else
                    {
                        if ($data[3])
                        {
                        }
                        else
                        {
                            $this->memsendmailform($player, "§c•Turn on send item to go to save mode§7\n");
                            return false;
                        }
                    }
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
            if ($data[3])
            {
                $this->addItemForm($player, $name, $data[2]);
            }
            else
            {
                $content = ["sender" => $player->getName() , "msg" => $data[2], "iteminfo" => "", "itemlist" => [], "time" => date('d/m/Y - H:i') ];
                $content2 = ["taker" => $name, "msg" => $data[2], "iteminfo" => "", "time" => date('d/m/Y - H:i') ];
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
                Main::$instance->memmainform($player, "§a•Successfully send a mail without item to §a$name\n");
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
            }
        });
        if ($err == "")
        {
            $err = "§7Fill these form:";
        }
        $form->setTitle("§fSend M§0ail");
        $form->addLabel("$err");
        $form->addInput("§7Type §cFULL§7 Of Your Player's Name:");
        $form->addInput("§7Type Your Message:");
        $form->addToggle("Send mail with item: No/Yes");
        $form->sendToPlayer($player);
        return $form;
    }
    public function addItemForm($sender, $taker = "", $msg)
    {
        $this->menu = $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->getInventory()
            ->setItem(18, Item::get(35, 14, 1));
        $menu->getInventory()
            ->setItem(18, Item::get(35, 14, 1));
        $n1 = $sender->getName();
        if ($taker != "")
        {
            $menu->send($sender, "§cSended Item To §a$taker");
        }
        else
        {
            $menu->send($sender, "§aSave item mode");
        }
        if ($taker != "")
        {
            $this->isonadd["$n1"] = ["taker" => $taker, "msg" => $msg, ];
        }
        else
        {
            $this->isonadd["$n1"] = "save";
        }
    }
    public function onQuit(PlayerQuitEvent $e)
    {
        if (!isset($this->temp_item[$e->getPlayer()
            ->getName() ]))
        {
            return false;
        }
        unset($this->isonadd[$e->getPlayer()
            ->getName() ]);
        if (isset($this->temp_item[$e->getPlayer()
            ->getName() ]))
        {
            foreach ($this->temp_item[$e->getPlayer()
                ->getName() ] as $item)
            {
                $itemtoadd = unserialize(utf8_decode($item));
                $e->getPlayer()
                    ->getInventory()
                    ->addItem($itemtoadd);

            }
        }
        unset($this->temp_item[$e->getPlayer()
            ->getName() ]);
    }
    public function onClose(InventoryCloseEvent $e)
    {
        if (!isset($this->temp_item[$e->getPlayer()
            ->getName() ]))
        {
            return false;
        }
        unset($this->isonadd[$e->getPlayer()
            ->getName() ]);
        if (isset($this->temp_item[$e->getPlayer()
            ->getName() ]))
        {
            foreach ($this->temp_item[$e->getPlayer()
                ->getName() ] as $item)
            {
                $itemtoadd = unserialize(utf8_decode($item));
                $e->getPlayer()
                    ->getInventory()
                    ->addItem($itemtoadd);

            }
        }
        unset($this->temp_item[$e->getPlayer()
            ->getName() ]);
    }
    public function onTransaction(InventoryTransactionEvent $e)
    {
        $trans = $e->getTransaction();
        $inv = array_values($e->getTransaction()
            ->getInventories());
        $act = array_values($e->getTransaction()
            ->getActions());
        $itemholder = $trans->getSource();
        //  print("test 1");
        if (!isset($this->isonadd[$itemholder->getName() ]))
        {
            return false;
        }
        $count = count($inv);
        //  print("test 2");
        if ($count == 1)
        {
            return false;
        }
        // var_dump($inv[0]);
        //print("=================\n");
        if ($inv[0] instanceof PlayerInventory)
        {
            #print("call1\n");
            $this->addItem($act[0]->getSourceItem() , $act[0]->getTargetItem() , $itemholder->getName() , $e);
        }
        else
        {
            #print("call2\n");
            $this->takeItem($act[0]->getSourceItem() , $act[0]->getTargetItem() , $itemholder->getName() , $e);
        }
    }
    public function addItem($old, $new, $adder, $e)
    {
        if ($new->getId() != 0 && $old->getId() != 0)
        {
            $e->setCancelled();
            return false;
        }
        $item = $old;
        if ($item->getId() == 0)
        {
            $item = $new;
        }
        $n = $adder;
        if (isset($this->temp_item[$n]))
        {
            $id = count($this->temp_item[$n]);
            array_push($this->temp_item[$n], utf8_encode(serialize($item)));
        }
        else
        {
            $this->temp_item[$n] = [utf8_encode(serialize($item)) ];
        }
    }
    public function takeItem($old, $new, $taker, $e)
    {
        if ($new->getId() != 0 && $old->getId() != 0)
        {
            $e->setCancelled();
            return false;
        }
        $item = $old;
        if ($item->getId() == 0)
        {
            $item = $new;
        }
        $n = $taker;
        if (isset($this->temp_item[$n]))
        {
            $array = $this->temp_item[$n];
            //  var_dump(utf8_encode(serialize($item)));
            if (!in_array(utf8_encode(serialize($item)) , $array))
            {
                $e->setCancelled();
                //var_dump($item->getId());
                if ($item->getId() == 35)
                {
                    $this->onDone($n);
                }
                return false;
            }
            $a = utf8_encode(serialize($item));
            unset($array[array_search($a, $array) ]);
        }
        else
        {
            $this->temp_item[$n] = [];
            if ($item->getId() == 35)
            {
                $this->onDone($n);
            }
            $e->setCancelled();
            return false;
        }
    }
    public function onDone($name)
    {
        $player = $this
            ->main
            ->getServer()
            ->getPlayer($name);
        if (null == $player)
        {
            print ("Error code 1.\n");
            return false;
        }
        if (null != $this->isonadd["$name"])
        {
            if ($this->isonadd["$name"] == "save")
            {
                $itemcount = count($this->temp_item[$name]);
                $num = 1;
                $txt = "There are $itemcount items";
                foreach ($this->temp_item[$name] as $item)
                {
                    $item = unserialize(utf8_decode(($item)));
                    $itname = $item->getName();
                    $itcount = $item->getCount();
                    $txt = $txt . "\n$num.Item: $itname/$itcount\n   Info:";
                    foreach ($item->getEnchantments() as $ench)
                    {
                        $txt = $txt . "\n   -" . $ench->getType()
                            ->getName() . " " . $ench->getLevel();
                    }
                    foreach ($item->getLore() as $ench)
                    {
                        $txt = $txt . "\n   -" . $ench;
                    }
                    $num++;
                }
                $count = 0;
                if (null !== $this
                    ->main
                    ->getConfig()
                    ->getNested("SaveItem"))
                {
                    $count = count($this
                        ->main
                        ->getConfig()
                        ->getNested("SaveItem"));
                }
                $cnt = ["id" => $count, "itemlist" => $this->temp_item[$name], "iteminfo" => $txt];
                $this
                    ->main
                    ->getConfig()
                    ->setNested("SaveItem.$count", $cnt);
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
                unset($this->temp_item[$name]);
                unset($this->isonadd[$name]);
                $player->sendMessage("§e•Successfully save item to use later");
                $packet = new \pocketmine\network\protocol\ContainerClosePacket();
                $packet->windowid = 0;
                $player->dataPacket($packet);
				#$player->removeWindow($this->menu->getInventory($player));
            }
            else if ($this->isonadd["$name"] != "save")
            {
                $taker = $this->isonadd["$name"]["taker"];
                $id = 0;
                if (null != $this
                    ->main
                    ->getConfig()
                    ->getNested("take.$taker"))
                {
                    $id = count($this
                        ->main
                        ->getConfig()
                        ->getNested("take.$taker"));
                }
                $num = 1;
                $itemcount = count($this->temp_item[$name]);
                $txt = "There are $itemcount items for you";
                foreach ($this->temp_item[$name] as $item)
                {
                    $item = unserialize(utf8_decode(($item)));
                    $itname = $item->getName();
                    $itcount = $item->getCount();
                    $txt = $txt . " \n$num.Item: $itname/$itcount\n   Info:";
                    foreach ($item->getEnchantments() as $ench)
                    {
                        $txt = $txt . "\n   -" . $ench->getType()
                            ->getName() . " " . $ench->getLevel();
                    }
                    foreach ($item->getLore() as $ench)
                    {
                        $txt = $txt . "\n   -" . $ench;
                    }
                    $num++;
                }
                $content = ["sender" => $name, "msg" => $this->isonadd[$name]["msg"], "iteminfo" => $txt, "itemlist" => $this->temp_item[$name], "time" => date('d/m/Y - H:i') ];
                $content2 = ["taker" => $taker, "msg" => $this->isonadd[$name]["msg"], "iteminfo" => $txt, "time" => date('d/m/Y - H:i') ];
                $count2 = 0;
                $name2 = strtolower($name);
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
                $this
                    ->main
                    ->getConfig()
                    ->setNested("send.$name2.$count2", $content2);
                $this
                    ->main
                    ->getConfig()
                    ->setNested("take.$taker.$id", $content);
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
                $player->sendMessage("§a•Successfully send a mail to $taker with item");
                unset($this->temp_item[$name]);
                unset($this->isonadd[$name]);
                if (null !== $this
                    ->main
                    ->getServer()
                    ->getPlayer($taker))
                {
                    $this
                        ->main
                        ->getServer()
                        ->getPlayer($taker)->sendMessage("§a•You have got a mail from a player");
                    $this
                        ->main
                        ->getConfig()
                        ->setNested("isreadnewmail.$taker", true);
                }
                else
                {
                    $this
                        ->main
                        ->getConfig()
                        ->setNested("isreadnewmail.$taker", false);
                }
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
                $player->removeWindow($this
                    ->menu
                    ->getInventory($player));
            }
            else
            {
                print ("error code 2.\n");
            }
        }
    }
}

