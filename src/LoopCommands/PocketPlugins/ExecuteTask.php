<?php
namespace PocketPlugins\LoopCommands;

use pocketmine\scheduler\Task;
use pocketmine\command\ConsoleCommandSender;

class ExecuteTask extends Task {
	private $core, $data, $name;
	private $runLimit = 0;
	private $runs = 0;
	public function __construct(Core $core, array $data, string $name) {
		$this->core = $core;
		$this->data = $data;
		$this->name = $name;
		$this->runLimit = $this->data['runs'];
	}
	public function getCore() : Core {
		return $this->core;
	}
	public function getCommands() : array {
		return $this->data['commands'];
	}
	public function getInterval() : int {
		return abs($this->data['interval']) * 20;
	}
	public function getAs() : string {
		return $this->data['as'];
	}
	public function onRun($currentTick) {
		$this->runs++;
		if($this->runLimit > 0 AND $this->runs > $this->runLimit) {
			$this->getCore()->disableLoop($this->name, 'Run limit reached');
			return;
		}
		foreach($this->getCommands() as $cmd) {
			switch($this->getAs()) {
				case 'onlineplayers':
					foreach($this->getServer()->getOnlinePlayers() as $player) {
						$this->getCore()->getServer()->dispatchCommand($player, $cmd);
					}
					break;
				case 'onlineopplayers':
					foreach($this->getServer()->getOnlinePlayers() as $player) {
						if($player->isOp() == true) {
							$this->getCore()->getServer()->dispatchCommand($player, $cmd);
						}
					}
					break;
				case 'console':
				default:
					$this->getCore()->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
					break;
			}
		}
	}
}
