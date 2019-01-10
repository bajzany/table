<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 13.12.2018
 */

namespace Bajzany\Table\DI;

use Bajzany\Table\ITableControl;
use Bajzany\Table\TableFactory;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class TableExtensions extends CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('tableManager'))
			->setFactory(TableFactory::class);

		$builder->addDefinition($this->prefix('tableControl'))
			->setImplement(ITableControl::class);
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
