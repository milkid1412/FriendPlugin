<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin\form;

use Kkevin14\FriendPlugin\Main;
use pocketmine\form\Form;
use pocketmine\player\Player;

class EnterPlayerNameForm implements Form
{
    private Main $owner;


    public function __construct(Main $owner)
    {
        $this->owner = $owner;
    }

    public function jsonSerialize()
    {
        return [
            'type' => 'custom_form',
            'title' => $this->owner->title,
            'content' => [
                [
                    'type' => 'input',
                    'text' => '플레이어의 닉네임을 입력해주세요. (온라인, 3글자 이상)'
                ]
            ]
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        $name_p = strtolower($player->getName());
        if($data === null || strlen($data[0]) < 3){
            $this->owner->msg($player, '현재 접속하고 있는 유저의 이름을 검색해주세요. (3글자 이상)');
            return;
        }
        $players = $this->owner->getPlayersByPrefix($data[0]);
        /** @var Player $target */
        foreach($players as $key => $target){
            $name_t = strtolower($target->getName());
            if($name_t === $name_p || in_array($name_t, $this->owner->queue[$name_p]) || $this->owner->isFriend($name_p, $name_t))
                unset($players[$key]);
        }
        if(empty($players)){
            $this->owner->msg($player, '" ' . $data[0] . ' "에 해당하는 플레이어를 찾을 수 없습니다.');
            return;
        }
        $player->sendForm(new SelectPlayerForm($this->owner, array_values($players)));
    }
}