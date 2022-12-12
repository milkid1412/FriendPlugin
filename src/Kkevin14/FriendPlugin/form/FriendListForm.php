<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin\form;

use Kkevin14\FriendPlugin\Main;
use pocketmine\form\Form;
use pocketmine\player\Player;

class FriendListForm implements Form
{
    private Main $owner;

    private Player $player;

    private int $type;

    public function __construct(Main $owner, Player $player, int $type)
    {
        $this->owner = $owner;
        $this->player = $player;
        $this->type = $type;
    }

    public function jsonSerialize()
    {
        $buttons = [];
        foreach($this->owner->getFriends($this->player) as $friend){
            if($this->owner->isOnline($friend)){
                $text = '§a' . $friend;
            }else{
                $text = '§c' . $friend . "\n" . '§r마지막 접속: ' . $this->owner->getConnectGap($this->player) . ' 전';
            }
            $buttons[] = [
                'text' => $text
            ];
        }
        return [
            'type' => 'form',
            'title' => $this->owner->title,
            'content' => "\n" . '작업을 진행할 플레이어를 선택해주세요.' . "\n\n",
            'buttons' => $buttons
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        $name_p = strtolower($player->getName());
        if($data === null || $this->type === 3) return;
        $target = $this->owner->getFriends($player)[$data];
        if($this->type === 2){
            $player->sendForm(new CheckDeleteFriendForm($this->owner, $target));
        }elseif($this->type === 4){
            if(!$this->owner->isOnline($target)){
                $this->owner->msg($player, '대상이 오프라인입니다.');
                return;
            }
            $target = $this->owner->getServer()->getPlayerExact($target);
            $player->teleport($target->getLocation()->asVector3());
            $this->owner->msg($player, '이동했습니다.');//TODO: add custom setting feature
        }elseif($this->type === 5){
            if(!$this->owner->isOnline($target)){
                $this->owner->msg($player, '대상이 오프라인입니다.');
                return;
            }
            $this->owner->db[$name_p]['chat'] = $target;
            $this->owner->msg($player, '현재 ' . $target . '님을 대상으로 귓속말 모드를 활성화 했습니다.');
            $this->owner->msg($player, '귓속말 모드를 해제하려면 /전채채팅을 입력해주세요.');
        }
    }
}