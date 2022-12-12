<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin\form;

use Kkevin14\FriendPlugin\Main;
use pocketmine\form\Form;
use pocketmine\player\Player;

class FriendRequestListForm implements Form
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
        foreach($this->players as $name){
            $buttons[] = [
                'text' => $name
            ];
        }
        return [
            'type' => 'form',
            'title' => $this->owner->title,
            'content' => '§b▲ §f닉네임을 터치/클릭 하여 친구 신청을 관리하세요.',
            'buttons' => $buttons
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if($data === null) return;
        $player->sendForm(new CheckAcceptRequestForm($this->owner, $this->players[$data]));
    }
}