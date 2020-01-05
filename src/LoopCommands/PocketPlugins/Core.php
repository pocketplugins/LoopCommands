<?php
namespace PocketPlugins\LoopCommands;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;

class Core extends PluginBase {
	private $config;
	/** @var ExecuteTask[] $tasks*/
	private $tasks;
	public function onEnable() {
		$default = array(
				'loop1' => array(
						'commands' => array(
								'version',
								'status'
						),
						'interval' => 10,
						'as' => 'OnlineOpPlayers',
						'runs' => 3
				),
				'startup' => array(
						'commands' => array(
								'help'
						),
						'as' => 'Console'
				)
		);
		$this->saveResource('loops.guide.yml');
		$this->getServer()->getCommandMap()->register('[LoopCommands]', new LoopCmd($this));
		$this->config = new Config($this->getDataFolder() . 'loops.yml', Config::YAML, $default);
		foreach($this->config->getAll() as $name => $data) {
			if(strtolower($name) == 'startup') {
				$this->startup($data);
				continue;
			}
			$this->tasks[$name] = new ExecuteTask($this, $data, $name);
		}
		foreach($this->tasks as $task){
			$this->getScheduler()->scheduleRepeatingTask($task, $task->getInterval());
		}
	}
	public function startup($data) {
		foreach($data['commands'] as $cmd) {
			switch(strtolower($data['as'])) {
				case 'onlineplayers':
					foreach($this->getServer()->getOnlinePlayers() as $player) {
						$this->getServer()->dispatchCommand($player, $cmd);
					}
					break;
				case 'onlineopplayers':
					foreach($this->getServer()->getOnlinePlayers() as $player) {
						if($player->isOp() == true) {
							$this->getServer()->dispatchCommand($player, $cmd);
						}
					}
					break;
				case 'console':
				default:
					$this->getServer()->dispatchCommand(new ConsoleCommandSender(), $cmd);
					break;
			}
		}
		return;
	}
	public function enableLoop(string $name, string $reason = 'Unknown') : bool {
			if(strtolower($name) == 'startup') return false;
			if(!isset($this->config->getAll()[$name])) return false;
			$this->tasks[$name] = new ExecuteTask($this, $this->config->get($name), $name);
			$this->getScheduler()->scheduleRepeatingTask($this->tasks[$name], $this->tasks[$name]->getInterval());
			$this->getServer()->getLogger()->notice('Loop named ' . $name . ' enabled due to "' . $reason . '"');
			return true;
	}
	public function disableLoop(string $name, string $reason = 'Unknown') : bool {
		if($this->tasks[$name]->getHandler() != null) {
			$this->tasks[$name]->getHandler()->remove();
			$this->getServer()->getLogger()->notice('Loop named ' . $name . ' disabled due to "' . $reason . '"');
			return true;
		}
		return false;
	}
	public function listLoops() : string {
		$names = array_keys($this->tasks);
		$text = TextFormat::GOLD . "Loops list:\n" . TextFormat::AQUA;
		foreach($names as $name) {
			$text = $text . ' - ' .  $name . ': ' . TextFormat::GOLD . 'Interval: ' . TextFormat::AQUA . $this->tasks[$name]->getInterval() / 20 . "\n";
		}
		return $text;
	}
}
