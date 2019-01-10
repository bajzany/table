<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table;

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
	 * @param AppWebLoader $appWebLoader
	 * @param ITableControl $tableControl
	 * @param EntityManager $entityManager
	 * @throws \Chomenko\AppWebLoader\Exceptions\AppWebLoaderException
	 * @throws \ReflectionException
	 */
	public function __construct(AppWebLoader $appWebLoader, ITableControl $tableControl, EntityManager $entityManager)
	{
		$this->tableControl = $tableControl;
		$this->entityManager = $entityManager;
		$collection = $appWebLoader->createCollection("bajzanyTable");
		$collection->addScript(__DIR__ . "/template/table.js");
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
	 * @return Table
	 */
	public function createTable()
	{
		return new Table();
	}

	/**
	 * @param string $className
	 * @return EntityTable
	 */
	public function createEntityTable(string $className)
	{
		return new EntityTable($className, $this->entityManager);
	}

}
