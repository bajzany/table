<?php

/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 */

namespace Bajzany\Table\EntityTable;

use Bajzany\Table\EntityTable;
use Bajzany\Table\Exceptions\TableException;
use Nette\ComponentModel\IContainer;

class SearchColumn extends Column implements IColumn
{

	const NAME_PREFIX = 'searchTable_';

	const ON_SEARCH_ACTION = "onSearchAction";

	/**
	 * @var string|null
	 */
	private $selectedValue;

	/**
	 * @var bool
	 */
	protected $build = FALSE;

	/**
	 * @var EntityTable
	 */
	private $entityTable;

	/**
	 * @internal
	 *
	 * @param EntityTable $entityTable
	 * @throws TableException
	 */
	public function build(EntityTable $entityTable)
	{
		if ($this->isBuild()) {
			throw TableException::searchColumnIsAlreadyBuild($this->getKey());
		}

		$this->entityTable = $entityTable;

		$this->setSelectedValue($this->getDefaultSearchValue($entityTable->getControl()));
	}

	/**
	 * @param IContainer $container
	 * @return string|null
	 */
	public function getDefaultSearchValue(IContainer $container)
	{
		$defaultValue = $container->getParameter($this->getInputName());
		return $defaultValue;
	}

	/**
	 * @return string
	 */
	public function getInputName()
	{
		return self::NAME_PREFIX . $this->getKey();
	}

	/**
	 * @param callable $callable
	 * @return $this
	 */
	public function onSearchAction(callable $callable)
	{
		$this->getListener()->create(self::ON_SEARCH_ACTION, $callable);
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getSelectedValue(): ?string
	{
		return $this->selectedValue;
	}

	/**
	 * @param string|null $selectedValue
	 * @return $this
	 */
	public function setSelectedValue($selectedValue)
	{
		$this->selectedValue = $selectedValue;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isBuild(): bool
	{
		return $this->build;
	}

	/**
	 * @return EntityTable
	 */
	public function getEntityTable(): EntityTable
	{
		return $this->entityTable;
	}

}
