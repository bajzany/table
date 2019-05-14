<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 13.12.2018
 */

namespace Bajzany\Table\DI;

use Bajzany\Table\ColumnDriver\IColumnDriverControl;
use Bajzany\Table\Events\TableEvents;
use Bajzany\Table\ITableControl;
use Bajzany\Table\TableFactory;
use Nette\Configurator;
use Nette\DI\Compiler;
use Nette\DI\CompilerExtension;

class TableExtensions extends CompilerExtension
{

	const TAG_EVENT = 'entityTable.events';

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('tableManager'))
			->setFactory(TableFactory::class);

		$builder->addDefinition($this->prefix('tableControl'))
			->setImplement(ITableControl::class);

		$builder->addDefinition($this->prefix('columnDriver'))
			->setImplement(IColumnDriverControl::class);

		$builder->addDefinition($this->prefix("TableEvents"))
			->setFactory(TableEvents::class);
	}

	public function beforeCompile()
	{
		$builder = $this->getContainerBuilder();
		$event = $builder->getDefinition($this->prefix("TableEvents"));
		foreach ($builder->findByTag(self::TAG_EVENT) as $name => $entityClass) {
			$event->addSetup("addEntityGroupEvent", [$entityClass, $builder->getDefinition($name)]);
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
