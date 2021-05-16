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
use Tungst_mailwithitem\sendmail;
use Tungst_mailwithitem\sendcustommail;
use Tungst_mailwithitem\customlistmail;
use Tungst_mailwithitem\mymail;
use Tungst_mailwithitem\sendedmail;
class Main extends PluginBase implements Listener {
    public $temp_item = [];
    public $isonadd = [];
    public static $instance;
	public function onEnable(){
		self::$instance = $this; 
		$this->getLogger()->info("Mail with item");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function onJoin(PlayerJoinEvent $e){
		$name = strtolower($e->getPlayer()->getName());
		if(null !== $this->getConfig()->getNested("Joined")){
			if(!in_array($name,$this->getConfig()->getNested("Joined"))){
				$a = $this->getConfig()->getNested("Joined");
				array_push($a,$name);
				$this->getConfig()->setNested("Joined",$a);
				$this->getConfig()->setAll($this->getConfig()->getAll());
				$this->getConfig()->save();	
			}
		}else{
			$this->getConfig()->setNested("Joined",[$name]);
			$this->getConfig()->setAll($this->getConfig()->getAll());
			$this->getConfig()->save();	
		}
		if(!$this->getConfig()->getNested("isreadnewmail.$name")){
			$e->getPlayer("§eYou have mails, check at§a /mail");
			$this->getConfig()->setNested("isreadnewmail.$name",true);
			$this->getConfig()->setAll($this->getConfig()->getAll());
            $this->getConfig()->save(); 		  
		}
	}
	public function onCommand(CommandSender $sender, Command $command, String $label, array $args) : bool {
	    if($sender instanceof Player){
		   if(strtolower($command->getName()) == "mail"){
              if($sender->isOp()){
              	//$this->memmainform($sender,"");
              	$this->opmainform($sender,"");
              }else{
              	$this->memmainform($sender,"");            
              }
           }
		   return true;
		}else{
		   if(strtolower($command->getName()) == "mail"){

		   }
		   return true;
		}
	}
	public function memmainform($player,$err){
	    $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				//$this->mainform($player);
			}
			switch($result){				
					case "0";
                     $a = new sendmail($this,$player);
                     $this->getServer()->getPluginManager()->registerEvents($a, $this);
                   //  $this->memsendmailform($player,"");					
					break;	
					case "1";
					 $a = new mymail($this,$player);
                     $this->getServer()->getPluginManager()->registerEvents($a, $this);
					 //$this->mymailform($player,"");
					break;
					case "2";				
					 $a = new sentmail($this,$player);
                     $this->getServer()->getPluginManager()->registerEvents($a, $this);
					break;
					default:
					break;
			}
			});
		    if($err == ""){
              $err = "§7Send mail with item or not";
            }
			$form->setTitle("§fMail §fWith I§0tem");
			$form->setContent(
				"$err"
			);
			$form->addButton("§fSend M§0ail",0,"textures/ui/ps4_face_button_right");
			$form->addButton("§fMy M§0ail",0,"textures/ui/ps4_face_button_down");
			$form->addButton("§fSended M§0ail",0,"textures/ui/ps4_face_button_right");
			$form->addButton("§0Thanks,Im good!",0,"textures/ui/permissions_visitor_hand_hover");
			$form->sendToPlayer($player); 
			return $form;
	}
	public function opmainform($player,$err){
	    $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
			$result = $data;
			if($result === null){
				//$this->mainform($player);
			}
			switch($result){				
					case "0";
                     $a = new sendmail($this,$player);
                     $this->getServer()->getPluginManager()->registerEvents($a, $this);
                   //  $this->memsendmailform($player,"");					
					break;	
					case "1";
					 $a = new mymail($this,$player);
                     $this->getServer()->getPluginManager()->registerEvents($a, $this);
					 //$this->mymailform($player,"");
					break;
					case "2";				
					 $a = new sentmail($this,$player);
                     $this->getServer()->getPluginManager()->registerEvents($a, $this);
					break;
					case "3";				
					 $a = new sendcustommail($this,$player);
                     $this->getServer()->getPluginManager()->registerEvents($a, $this);
					break;
					case "4";				
					 $a = new customlistmail($this,$player);
                     $this->getServer()->getPluginManager()->registerEvents($a, $this);
					break;
					default:
					break;
			}
			});		
		    if($err == ""){
              $err = "§7Send mail with item or not";
            }	
			$form->setTitle("§fMail §fWith I§0tem");
			$form->setContent(
				"$err"
			);
			$form->addButton("§fSend M§0ail",0,"textures/ui/ps4_face_button_right");
			$form->addButton("§fMy M§0ail",0,"textures/ui/ps4_face_button_down");
			$form->addButton("§fSended M§0ail",0,"textures/ui/ps4_face_button_right");
			$form->addButton("§bSend Custom Item M§0ail",0,"textures/ui/ps4_face_button_left");
			$form->addButton("§bCustom Item L§0ist",0,"textures/ui/ps4_face_button_up");
			$form->addButton("§0Thanks,Im good!",0,"textures/ui/permissions_visitor_hand_hover");
			$form->sendToPlayer($player); 
			return $form;
	}
}