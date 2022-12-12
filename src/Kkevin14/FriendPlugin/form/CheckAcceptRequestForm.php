<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin\form;

use Kkevin14\FriendPlugin\Main;
use pocketmine\form\Form;
use pocketmine\player\Player;

class CheckAcceptRequestForm implements Form
{
    private Main $owner;

    private string $target;

    public function __construct(Main $owner, string $target)
    {
        $this->owner = $owner;
        $this->target = $target;
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'modal',
            'title' => $this->owner->title,
            'content' => $this->target . '님의 친구신청을 수락하시겠습니까?',
            'button1' => '§e▼ §f수락',
            'button2' => '§7▼ §f거절'
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        $name_t = $this->target;
        $target = $this->owner->getServer()->getPlayerExact($name_t);
        $b = $target !== null && $target->isOnline();
        $name_t = $b ? $target->getName() : $name_t;
        if($data){
            $this->owner->addFriend(strtolower($player->getName()), $name_t);
            if($b){
                $this->owner->msg($target, $player->getName() . '님이 친구 요청을 수락했습니다.');
            }
            $this->owner->msg($player, $name_t . '님의 친구 요청을 수락했습니다.');
        }else{
            $this->owner->refuseFriend(strtolower($player->getName()), $name_t);
            $this->owner->msg($player, $name_t . '님의 친구 요청을 거절했습니다.');
            if($b){
                $this->owner->msg($target, $player->getName() . '님이 친구 요청을 거절했습니다.');
            }
        }
    }
}