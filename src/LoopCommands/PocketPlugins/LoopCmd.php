<?php
namespace PocketPlugins\LoopCommands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class LoopCmd extends Command {
	private $core;
	public function __construct(Core $core) {
		parent::__construct('loopcmd', "LoopCommands control", '/loopcmd <help | sub-cmd> [arguments]', array('lc'));
		$this->setPermission('loop.cmd');
		$this->core = $core;
	}
	public function getCore() : Core {
		return $this->core;
	}
	public function execute(CommandSender $sender, $commandLabel, array $args) {
		if($commandLabel != $this->getName()) return;
		if(!isset($args[0])) {
			$sender->sendMessage($this->getUsage());
			return;
		}
		switch(strtolower($args[0])) {
			case 'disable':
			case 'off':
				if(!isset($args[1])) {
					$sender->sendMessage(TextFormat::RED . "Tell me loop's name as argument");
					return;
				}
				$result = $this->getCore()->disableLoop($args[1], 'An order from ' . $sender->getName());
				$result ? $sender->sendMessage(TextFormat::GREEN . $args[1] . ' disabled!') : $sender->sendMessage(TextFormat::RED . 'Disabling ' . $args[1] . ' failed!');
				break;
			case 'enable':
			case 'on':
				if(!isset($args[1])) {
					$sender->sendMessage(TextFormat::RED . "Tell me loop's name as argument");
					return;
				}
				$result = $this->getCore()->enableLoop($args[1], 'An order from ' . $sender->getName());
				$result ? $sender->sendMessage(TextFormat::GREEN . $args[1] . ' enabled!') : $sender->sendMessage(TextFormat::RED . 'Enabling ' . $args[1] . ' failed!');
				break;
			case 'list':
				$sender->sendMessage($this->getCore()->listLoops());
				break;
			case 'help':
				$sender->sendMessage(TextFormat::GOLD . "LoopCommands\n" . TextFormat::AQUA . "Sub commands:\n1. disable [name]: disables loop named [name]\n2. enable [name]: enables loop named [name]\n3. list: information about loops\n4. help: shows this message");
				break;
			default:
				$sender->sendMessage($this->getUsage());
		}
	}
}
