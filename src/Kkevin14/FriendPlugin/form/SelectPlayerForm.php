<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin\form;

use Kkevin14\FriendPlugin\Main;
use pocketmine\form\Form;
use pocketmine\player\Player;

class SelectPlayerForm implements Form
{
    private Main $owner;

    private array $players;


    public function __construct(Main $owner, array $players)
    {
        $this->owner = $owner;
        $this->players = $players;
    }

    public function jsonSerialize()
    {
        $buttons = [];
        /* @var Player $player */
        foreach($this->players as $player){
            $buttons[] = [
                'text' => $player->getName()
            ];
        }
        return [
            'type' => 'form',
            'title' => $this->owner->title,
            'content' => '§b▲ §f친구 요청을 보낼 플레이어를 선택해주세요.',
            'buttons' => $buttons
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        $name = strtolower($player->getName());
        if($data === null) return;
        /** @var Player $target */
        $target = $this->players[$data];
        $this->owner->queue[strtolower($target->getName())][] = $name;
        $this->owner->msg($player, $target->getName() . '님에게 친구 요청을 보냈습니다.');
        if($target->isOnline()){
            $this->owner->msg($target, $player->getName() . '님이 친구 요청을 보냈습니다. (§b/친구§f)');
            $this->owner->msg($target, '서버가 §c재부팅§f되면 요청은 초기화됩니다.');
        }
    }
}