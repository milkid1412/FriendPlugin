<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin\form;

use Kkevin14\FriendPlugin\Main;
use pocketmine\form\Form;
use pocketmine\player\Player;

class CheckDeleteFriendForm implements Form
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
            'content' => '※ ' . $this->target . '님을 친구 목록에서 삭제합니다.',
            'button1' => '§c▼ §f삭제하기',
            'button2' => '§b▼ §f취소하기'
        ];
    }

    public function handleResponse(Player $player, $data): void
    {
        if($data){
            $name_p = strtolower($player->getName());
            $name_t = $this->target;
            $key_p = array_search($name_t, $this->owner->db[$name_p]['friends']);
            $key_t = array_search($name_p, $this->owner->db[$name_t]['friends']);
            unset($this->owner->db[$name_p]['friends'][$key_p]);
            unset($this->owner->db[$name_t]['friends'][$key_t]);
            $this->owner->db[$name_p]['friends'] = array_values($this->owner->db[$name_p]['friends']);
            $this->owner->db[$name_t]['friends'] = array_values($this->owner->db[$name_t]['friends']);
            $target = $this->owner->getServer()->getPlayerExact($this->target);
            if($this->owner->getWhisperTarget($player) === $name_t){
                $this->owner->setWhisperTarget($player);
            }
            if($this->owner->getWhisperTarget($name_t) === $name_p){
                $this->owner->setWhisperTarget($name_t);
            }
            $this->owner->msg($player, $this->target . '님을 친구 목록에서 삭제했습니다.');
            $this->owner->msg($target, $player->getName() . '님이 플레이어님을 친구 목록에서 삭제했습니다.');
        }else{
            $this->owner->msg($player, '작업을 취소했습니다.');
        }
    }
}