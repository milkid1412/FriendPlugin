<?php
declare(strict_types=1);

namespace Kkevin14\FriendPlugin\command;

use Kkevin14\FriendPlugin\form\MainMenuForm;
use Kkevin14\FriendPlugin\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;

class FriendMenuCommand extends Command
{
    private Main $owner;

    public function __construct(Main $owner)
    {
        parent::__construct('친구', '친구 메뉴를 오픈합니다.', '/친구', ['friend']);
        $this->owner = $owner;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player) return;
        $sender->sendForm(new MainMenuForm($this->owner));
    }
}