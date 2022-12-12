<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin\command;

use Kkevin14\FriendPlugin\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;

class ChatModeCommand extends Command
{
    private Main $owner;

    public function __construct(Main $owner)
    {
        parent::__construct('전채채팅', '전채채팅 모드를 활성화합니다.', '/전채채팅', ['global-chat']);
        $this->owner = $owner;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;
        $name = strtolower($sender->getName());
        $this->owner->db[$name]['chat'] = '';
        $this->owner->msg($sender, '전채채팅 모드를 활성화했습니다.');
    }
}