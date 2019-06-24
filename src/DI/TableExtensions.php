<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 13.12.2018
 */

namespace Bajzany\Table\DI;

use Bajzany\Table\ColumnDriver\IColumnDriverControl;
use Bajzany\Table\Config;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class TableExtensions extends CompilerExtension
{

	/**
	 * @var array
	 */
	private $defaults = [
		"translator" => NULL,
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('columnDriver'))
			->setImplement(IColumnDriverControl::class);

		$builder->addDefinition($this->prefix('config'))
			->setFactory(Config::class);
	}

	public function beforeCompile()
	{
		parent::beforeCompile();
		$builder = $this->getContainerBuilder();
		$config = $this->validateConfig($this->defaults);

		$configDef = $builder->getDefinition($this->prefix("config"));

		if ($config["translator"]) {
			$translator = $builder->getByType($config["translator"]);
			$configDef->addSetup("setTranslator", [$builder->getDefinition($translator)]);
		}
	}

	/**
	 * @param Configurator $configurator
	 */
	public static function register(Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Compiler $compiler) {
			$compiler->addExtension('bajzanyTable', new TableExtensions());
		};
	}

}
