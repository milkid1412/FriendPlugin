<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerQuitEvent;

class EventListener implements Listener
{
    private Main $owner;

    public function __construct(Main $owner)
    {
        $this->owner = $owner;
    }

    public function onPlayerJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        if(!isset($this->owner->queue[$name])) $this->owner->queue[$name] = [];
        if(!isset($this->owner->db[$name])) $this->owner->db[$name] = [
            'friends' => [],
            'last_connect' => null,
            'chat' => ''
        ];
    }

    public function onPlayerChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        $name_p = strtolower($player->getName());
        if($this->owner->db[$name_p]['chat'] === '') return;
        $event->cancel();
        $name_t = $this->owner->db[$name_p]['chat'];
        if(!$this->owner->isOnline($name_t)){
            $this->owner->msg($player, '상대가 오프라인이 되어 귓속말을 종료합니다.');
            $this->owner->setWhisperTarget($player);
            return;
        }
        $target = $this->owner->getServer()->getPlayerExact($name_t);
        $target->sendMessage('§l§b친구) §f' . $player->getName() . ' §b-> §f' . $target->getName() . ': §7' . $event->getMessage());
        $player->sendMessage('§l§b친구) §f' . $player->getName() . ' §b-> §f' . $target->getName() . ': §7' . $event->getMessage());
    }

    public function onPlayerQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $this->owner->db[$name]['last_connect'] = time();
    }

    public function onPlayerKick(PlayerKickEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $this->owner->db[$name]['last_connect'] = time();
    }
}