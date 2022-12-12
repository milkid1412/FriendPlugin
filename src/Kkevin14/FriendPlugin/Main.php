<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin;

use JsonException;
use Kkevin14\FriendPlugin\command\ChatModeCommand;
use Kkevin14\FriendPlugin\command\FriendMenuCommand;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase
{
    private Config $database;

    public array $db, $queue;

    public string $title = '§l§7[ §f친구 §7]';

    private static ?self $instance = null;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public static function getInstance(): ?self
    {
        return self::$instance;
    }

    protected function onEnable(): void
    {
        $this->database = new Config($this->getDataFolder() . 'data.yml', Config::YAML, []);
        $this->db = $this->database->getAll();

        $this->getServer()->getCommandMap()->registerAll('Kkevin14', [
            new FriendMenuCommand($this), new ChatModeCommand($this)
        ]);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function msg(?Player $player, string $msg)
    {
        if($player === null || !$player->isOnline()) return;
        $player->sendMessage('§b◈ §f' . $msg);
    }

    public function getPlayersByPrefix(string $key): array
    {
        $players = [];
        foreach($this->getServer()->getOnlinePlayers() as $player){
            if(str_contains(strtolower($player->getName()), strtolower($key))) $players[] = $player;
        }
        return $players;
    }

    public function addFriend(string|Player $player, string|Player $target)
    {

        $name_p = strtolower($player instanceof Player ? $player->getName() : $player);
        $name_t = strtolower($target instanceof Player ? $target->getName() : $target);
        $key = array_search($name_t, $this->queue[$name_p]);
        unset($this->queue[$name_p][$key]);
        $player = $this->getServer()->getPlayerExact($player);
        if($this->isFriend($player, $target)){
            $this->msg($player, $name_t . '님과는 이미 친구입니다.');
            return;
        }
        $this->db[$name_p]['friends'][] = $name_t;
        $this->db[$name_t]['friends'][] = $name_p;
        $this->queue[$name_p] = array_values($this->queue[$name_p]);
    }

    public function refuseFriend(string|Player $player, string|Player $target)
    {
        $name_p = strtolower($player instanceof Player ? $player->getName() : $player);
        $name_t = strtolower($target instanceof Player ? $target->getName() : $target);
        $key = array_search($name_t, $this->queue[$name_p]);
        unset($this->queue[$name_p][$key]);
        $this->queue[$name_p] = array_values($this->queue[$name_p]);
    }

    public function isFriend(string|Player $player, string|Player $target): bool
    {
        $name_p = strtolower($player instanceof Player ? $player->getName() : $player);
        $name_t = strtolower($target instanceof Player ? $target->getName() : $target);
        return in_array($name_t, $this->db[$name_p]['friends']);
    }

    public function getFriends(Player|string $player): array
    {
        $name = strtolower($player instanceof Player ? $player->getName() : $player);
        return $this->db[$name]['friends'];
    }

    public function isOnline(string $name): bool
    {
        $player = $this->getServer()->getPlayerExact($name);
        if($player === null) return false;
        return $player->isOnline();
    }

    public function requestFriend(Player $player, Player $target)
    {
        if($this->inQueue($player, $target) || $this->inQueue($target, $player)){
            $this->msg($player, '이미 보냈거나 받은 친구 요청이 있습니다.');
            return;
        }
        $name = strtolower($player->getName());
        $this->queue[strtolower($target->getName())][] = $name;
        $this->msg($player, $target->getName() . '님에게 친구 요청을 보냈습니다.');
        if($target->isOnline()){
            $this->msg($target, $player->getName() . '님이 친구 요청을 보냈습니다. (§b/친구§f)');
            $this->msg($target, '서버가 §c재부팅§f되면 요청은 초기화됩니다.');
        }
    }

    public function getQueue(Player|string $player): array
    {
        $name = strtolower($player instanceof Player ? $player->getName() : $player);
        return $this->queue[$name];
    }

    public function inQueue(Player|string $player, Player|string $target): bool
    {
        $name_p = strtolower($player instanceof Player ? $player->getName() : $player);
        return in_array($name_p, $this->getQueue($target));
    }

    public function setWhisperTarget(Player|string $player, string $target = '')
    {
        $name = strtolower($player instanceof Player ? $player->getName() : $player);
        $this->db[$name]['chat'] = $target;
    }

    public function getWhisperTarget(Player|string $player): string
    {
        $name = strtolower($player instanceof Player ? $player->getName() : $player);
        return $this->db[$name]['chat'];
    }


    public function getConnectGap(Player|string $player): string
    {
        $name = strtolower($player instanceof Player ? $player->getName() : $player);
        $time_gap =  time() - $this->db[$name]['last_connect'];
        if($time_gap > 60 * 60 * 24) return "24시간 이상";
        $h = floor($time_gap / 3600);
        $m = floor(($time_gap - 3600 * $h) / 60);
        return $h . '시간 ' . $m . '분';
    }

    /**
     * @throws JsonException
     */
    public function onDisable(): void
    {
        $this->database->setAll($this->db);
        $this->database->save();
    }
}