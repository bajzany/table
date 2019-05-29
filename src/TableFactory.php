<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table;

use Bajzany\Table\Events\TableEvents;
use Chomenko\AppWebLoader\AppWebLoader;
use Kdyby\Doctrine\EntityManager;

class TableFactory
{

	/**
	 * @var ITableControl
	 */
	private $tableControl;

	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * @var TableEvents
	 */
	private $tableEvents;

	/**
	 * @param AppWebLoader $appWebLoader
	 * @param ITableControl $tableControl
	 * @param EntityManager $entityManager
	 * @param TableEvents $tableEvents
	 * @throws \Chomenko\AppWebLoader\Exceptions\AppWebLoaderException
	 * @throws \ReflectionException
	 */
	public function __construct(AppWebLoader $appWebLoader, ITableControl $tableControl, EntityManager $entityManager, TableEvents $tableEvents)
	{
		$this->tableControl = $tableControl;
		$this->entityManager = $entityManager;
		$collection = $appWebLoader->createCollection("bajzanyTable");
		$collection->addScript(__DIR__ . "/template/table.js");
		$collection->addStyles(__DIR__ . "/template/table.less");
		$this->tableEvents = $tableEvents;
	}

	/**
	 * @param ITable $table
	 * @return TableControl
	 */
	public function createComponentTable(ITable $table)
	{
		return $this->tableControl->create($table);
	}

	/**
	 * @param RowsCollection|null $collection
	 * @return Table
	 */
	public function createTable(?RowsCollection $collection = NULL)
	{
		return new Table($collection);
	}

	/**
	 * @param string $className
	 * @return EntityTable
	 */
	public function createEntityTable(string $className)
	{
		return new EntityTable($className, $this->entityManager, $this->tableEvents);
	}

}
