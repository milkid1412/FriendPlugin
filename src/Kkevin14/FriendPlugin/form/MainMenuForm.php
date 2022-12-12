<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin\form;

use Kkevin14\FriendPlugin\Main;
use pocketmine\form\Form;
use pocketmine\player\Player;

class MainMenuForm implements Form
{
    private Main $owner;

    public function __construct(Main $owner)
    {
        $this->owner = $owner;
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'form',
            'title' => $this->owner->title,
            'content' => "\n" . '원하시는 기능을 선택해주세요.' . "\n\n",
            'buttons' => [
                [
                    'text' => '§6▣ §f친구 추가 관리'
                ],
                [
                    'text' => '§e▣ §f친구 추가 요청'
                ],
                [
                    'text' => '§c▣ §f친구 삭제'
                ],
                [
                    'text' => '§a▣ §f친구 목록'
                ],
                [
                    'text' => '§b▣ §f친구 티피'
                ],
                [
                    'text' => '§g▣ §f친구 채팅'
                ]
            ]
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if($data === null) return;
        if($data === 0){
            $queue = $this->owner->getQueue($player);
            if(empty($queue)){
                $this->owner->msg($player, '받은 친구 요청이 없습니다.');
                return;
            }
            $player->sendForm(new FriendRequestListForm($this->owner, $queue));
        }elseif($data === 1){
            $player->sendForm(new EnterPlayerNameForm($this->owner));
        }elseif($data < 6){
            $player->sendForm(new FriendListForm($this->owner, $player, $data));
        }
    }
}