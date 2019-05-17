<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 09.01.2019
 */

namespace Bajzany\Table;

use Bajzany\Paginator\IPaginator;
use Bajzany\Paginator\Paginator;
use Bajzany\Table\ColumnDriver\ColumnDriver;
use Bajzany\Table\EntityTable\Column;
use Bajzany\Table\EntityTable\IColumn;
use Bajzany\Table\EntityTable\ISearchColumn;
use Bajzany\Table\EntityTable\SearchColumn;
use Bajzany\Table\EntityTable\SearchSelectColumn;
use Bajzany\Table\EntityTable\SearchTextColumn;
use Bajzany\Table\Exceptions\TableException;
use Bajzany\Table\TableObjects\TableWrapped;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;

class Table implements ITable
{
	/**
	 * @var TableWrapped
	 */
	private $tableWrapped;

	/**
	 * @var callable[]
	 */
	protected $preRender = [];

	/**
	 * @var callable[]
	 */
	protected $postRender = [];

	/**
	 * @var bool
	 */
	protected $build = FALSE;

	/**
	 * @var TableControl
	 */
	protected $control;

	/**
	 * @var Paginator
	 */
	protected $paginator;

	/**
	 * @var ColumnDriver
	 */
	protected $columnDriver;

	/**
	 * @var IColumn[]
	 */
	private $columns = [];

	public function __construct()
	{
		$this->tableWrapped = new TableWrapped($this);
		$this->paginator = new Paginator();
		$this->columnDriver = new ColumnDriver();
	}

	/**
	 * @param IContainer $container
	 * @return ITable
	 */
	public function build(IContainer $container): ITable
	{
		$this->control = $container;
		$container->getComponent(TableControl::COLUMN_DRIVER_NAME);
		$this->build = TRUE;
		return $this;
	}

	/**
	 * @param TableControl $control
	 */
	public function execute(TableControl $control)
	{
		$this->emitPreRender();
		$this->getTableWrapped()->render();
		$this->emitPostRender();
	}
	
	protected function emitPreRender()
	{
		foreach ($this->preRender as $event) {
			call_user_func_array($event, [$this]);
		}
	}

	protected function emitPostRender()
	{
		foreach ($this->postRender as $event) {
			call_user_func_array($event, [$this]);
		}
	}

	/**
	 * @return TableWrapped
	 */
	public function getTableWrapped(): TableWrapped
	{
		return $this->tableWrapped;
	}

	/**
	 * @return callable[]
	 */
	public function getPreRender(): array
	{
		return $this->preRender;
	}

	/**
	 * @param callable $preRender
	 * @return $this
	 */
	public function addPreRender(callable $preRender)
	{
		$this->preRender[] = $preRender;
		return $this;
	}

	/**
	 * @return callable[]
	 */
	public function getPostRender(): array
	{
		return $this->postRender;
	}

	/**
	 * @param callable $postRender
	 * @return $this
	 */
	public function addPostRender(callable $postRender)
	{
		$this->postRender[] = $postRender;
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
	 * @return IPaginator
	 */
	public function getPaginator(): ?IPaginator
	{
		return $this->paginator;
	}

	/**
	 * @return TableControl
	 * @throws TableException
	 */
	public function getControl(): TableControl
	{
		if (empty($this->control)) {
			throw TableException::tableNotExecute();
		}
		return $this->control;
	}

	/**
	 * @return \Nette\Application\UI\Presenter|null
	 * @throws TableException
	 */
	public function getPresenter()
	{
		return $this->getControl()->getPresenter();
	}

	/**
	 * @return ColumnDriver
	 */
	public function getColumnDriver(): ColumnDriver
	{
		return $this->columnDriver;
	}

	/**
	 * @return IColumn[]
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}

	/**
	 * @param string $key
	 * @return IColumn|null
	 */
	public function getColumn(string $key): ?IColumn
	{
		if ($this->issetColumnKey($key)) {
			return $this->columns[$key];
		}
		return NULL;
	}

	/**
	 * @param string $key
	 */
	public function removeColumn(string $key): void
	{
		if ($this->issetColumnKey($key)) {
			unset($this->columns[$key]);
			$this->columnDriver->removeAvailableColumn($key);
		}
	}

	/**
	 * @param string $key
	 * @return Column
	 * @throws TableException
	 */
	public function createColumn(string $key)
	{
		if ($this->issetColumnKey($key)) {
			throw TableException::columnKeyExist($key);
		}

		$column = new Column($key);
		$this->columns[$key] = $column;
		$this->columnDriver->addAvailableColumn($key, $column);
		return $column;
	}

	/**
	 * @param string $key
	 * @return SearchTextColumn
	 * @throws TableException
	 */
	public function createSearchTextColumn(string $key)
	{
		if ($this->issetColumnKey($key)) {
			throw TableException::columnKeyExist($key);
		}

		$column = new SearchTextColumn($key);
		$this->columns[$key] = $column;
		$this->columnDriver->addAvailableColumn($key, $column);
		return $column;
	}

	/**
	 * @param string $key
	 * @return SearchSelectColumn
	 * @throws TableException
	 */
	public function createSearchSelectColumn(string $key)
	{
		if ($this->issetColumnKey($key)) {
			throw TableException::columnKeyExist($key);
		}

		$column = new SearchSelectColumn($key);
		$this->columns[$key] = $column;
		$this->columnDriver->addAvailableColumn($key, $column);
		return $column;
	}

	/**
	 * @return SearchColumn[]
	 */
	public function getSearchColumns()
	{
		$searchColumns = [];
		foreach ($this->columns as $column) {
			if ($column instanceof SearchColumn) {
				$searchColumns[] = $column;
			}
		}

		return $searchColumns;
	}

	/**
	 * @param IColumn $column
	 * @return $this
	 * @throws TableException
	 */
	public function addColumn(IColumn $column)
	{
		if ($this->issetColumnKey($column->getKey())) {
			throw TableException::columnKeyExist($column->getKey());
		}
		$this->columns[$column->getKey()] = $column;
		$this->columnDriver->addAvailableColumn($column->getKey(), $column);
		return $this;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function issetColumnKey(string $key): bool
	{
		if (array_key_exists($key, $this->columns)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param string $destination
	 * @param array $parameters
	 * @return string
	 * @throws TableException
	 * @throws \Nette\Application\UI\InvalidLinkException
	 */
	public function createLink(string $destination, array $parameters = [])
	{
		/** BUILD PAGINATOR PARAMS */
		$paginatorControl = $this->getControl()->getComponent(TableControl::PAGINATOR_NAME);
		$paginatorParameters = $this->getComponentParameters($paginatorControl->getParameters(), $paginatorControl);

		/** BUILD SEARCH PARAMS */
		$searchColumns = $this->getSearchColumns();
		$params = [];
		foreach ($searchColumns as $searchColumn) {
			$params[$searchColumn->getInputName()] = $searchColumn->getSelectedValue();
		}

		$buildSearchParams = $this->getComponentParameters($params, $this->getControl());

		$parameters = array_merge($parameters, $paginatorParameters);
		$parameters = array_merge($parameters, $buildSearchParams);

		return $this->getPresenter()->link($destination, $parameters);
	}

	/**
	 * @param array $parameters
	 * @param IContainer $container
	 * @return array
	 */
	public function getComponentParameters(array $parameters, IContainer $container)
	{
		$name = $this->getComponentName($container);
		$params = [];
		foreach ($parameters as $parameter => $value) {
			if (!$value) {
				continue;
			}
			$params[$name . '-' . $parameter] = $value;
		}

		return $params;
	}

	/**
	 * @param IContainer $control
	 * @param string $name
	 * @return string
	 */
	public function getComponentName(IContainer $control, string $name = '')
	{
		if ($control instanceof Presenter) {
			return $name;
		}

		if (empty($name)) {
			$controlName = $control->getName();
		} else {
			$controlName = $control->getName() . '-' . $name;
		}

		return $this->getComponentName($control->getParent(), $controlName);
	}


}
