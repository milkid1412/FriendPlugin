<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin;

use JsonException;
use Kkevin14\FriendPlugin\command\ChatModeCommand;
use Kkevin14\FriendPlugin\command\FriendMenuCommand;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Main extends PluginBase
{
    private Config $database;

    public array $db, $queue;

    public string $title = '§l§7[ §f친구 §7]';

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
        $this->db[$name_p]['friends'][] = $name_t;
        $this->db[$name_t]['friends'][] = $name_p;
        $key = array_search($name_t, $this->queue[$name_p]);
        unset($this->queue[$name_p][$key]);
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

    public function getQueue(Player|string $player): array
    {
        $name = strtolower($player instanceof Player ? $player->getName() : $player);
        return $this->queue[$name];
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