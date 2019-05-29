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
use Bajzany\Table\TableObjects\Item;
use Bajzany\Table\TableObjects\TableWrapped;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;

class Table implements ITable
{

	const PATTERN_REGEX = "~{{\s([a-zA-Z_0-9]+)\s}}~";

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

	/**
	 * @var RowsCollection|null
	 */
	private $rowsCollection;

	public function __construct(?RowsCollection $rowsCollection = NULL)
	{
		$this->tableWrapped = new TableWrapped($this);
		$this->paginator = new Paginator($rowsCollection ? $rowsCollection->count() : 0);
		$this->columnDriver = new ColumnDriver();
		$this->rowsCollection = $rowsCollection;
	}

	/**
	 * @param IContainer $container
	 * @return ITable
	 */
	public function build(IContainer $container): ITable
	{
		$this->control = $container;
		$container->getComponent(TableControl::COLUMN_DRIVER_NAME);
		$container->getComponent(TableControl::PAGINATOR_NAME);
		$this->build = TRUE;
		return $this;
	}

	/**
	 * @param TableControl $control
	 * @throws TableException
	 * @throws \ReflectionException
	 */
	public function execute(TableControl $control)
	{
		$this->emitPreRender();

		$this->createHeader();
		$this->createBody();
		$this->createFooter();


		$this->getTableWrapped()->render();
		$this->emitPostRender();
	}

	protected function createHeader()
	{
		$header = $this->getTableWrapped()->getHeader();
		foreach ($this->getColumns() as $column) {
			if (!$column->isAllowRender()) {
				continue;
			}
			$item = $header->createItem();
			$item->setHtml($column->getLabel());
			$listener = $column->getListener();
			$listener->emit(Column::ON_HEADER_CREATE, $item, $column);
		}
	}

	/**
	 * @throws TableException
	 * @throws \ReflectionException
	 */
	protected function createBody()
	{
		$body = $this->getTableWrapped()->getBody();

		$from = ($this->paginator->getPageSize() * $this->paginator->getCurrentPage()) - $this->paginator->getPageSize();
		$list = array_slice($this->rowsCollection->toArray(), $from, $this->paginator->getPageSize());

		foreach ($list as $data) {
			$origin = [];
			if (is_object($data)) {
				$ref = new \ReflectionClass($data);
				foreach ($ref->getProperties() as $property) {
					$property->setAccessible(TRUE);
					$origin[$property->getName()] = $property->getValue($data);
				}
				foreach ($ref->getMethods() as $method) {
					if (substr($method->getName(), 0, 2) == "__") {
						continue;
					}
					if (count($method->getParameters()) > 0) {
						continue;
					}
					if (!$method->isPublic()) {
						continue;
					}
					$method->setAccessible(TRUE);
					$origin[$method->getName()] = function () use ($method, $data) {
						return $method->invoke($data);
					};
				}
			}

			$row = $body->createRow();
			foreach ($this->getColumns() as $column) {
				if (!$column->isAllowRender()) {
					continue;
				}
				$item = $row->createItem();
				$this->usePattern($column, $origin, $item);
				$listener = $column->getListener();
				$listener->emit(Column::ON_BODY_CREATE, $item, $data);
			}
		}
	}

	protected function createFooter()
	{
		$footer = $this->getTableWrapped()->getFooter();
		foreach ($this->getColumns() as $column) {
			if (!$column->getFooter()) {
				continue;
			}
			if (!$column->isAllowRender()) {
				continue;
			}
			$item = $footer->createItem();
			$item->setHtml($column->getFooter());

			$listener = $column->getListener();
			$listener->emit(Column::ON_FOOTER_CREATE, $item);
		}
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
	 * @param IColumn $column
	 * @param array $origin
	 * @param Item $item
	 * @throws TableException
	 */
	protected function usePattern(IColumn $column, array $origin, Item $item)
	{
		if (!empty($column->getPattern())) {
			$labelItem = $column->getPattern();
			$templateKeys = $this->getPatternsKeys($column->getPattern());
			foreach ($templateKeys as $i => $key) {
				if (!array_key_exists($key["name"], $origin)) {
					throw TableException::doesNotExistField($key["name"], $this->getEntityClass());
				}

				$value = $origin[$key["name"]];
				$value = $this->applyFilters($column, $value);

				$templateKeys[$i]["translate"] = $value;
			}

			foreach ($templateKeys as $key) {
				$translate = $key['translate'];

				if ($translate instanceof \Closure) {
					$translate = call_user_func($translate);
				}

				if (!$translate instanceof Html) {
					$translate = htmlspecialchars($translate);
				}
				$labelItem = str_replace($key["search"], $translate, $labelItem);
			}

			$item->setHtml($labelItem);
		}
	}

	/**
	 * @param IColumn $column
	 * @param mixed $value
	 * @return mixed
	 */
	protected function applyFilters(IColumn $column, $value)
	{
		foreach ($column->getFilters() as $filter) {
			$value = call_user_func_array($filter["callable"], array_merge([$value, $column], $filter["config"]));
		}
		return $value;
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

	/**
	 * @param string $template
	 * @return array
	 */
	protected function getPatternsKeys(string $template): array
	{
		$keys = [];
		preg_match_all(self::PATTERN_REGEX, $template, $matches);
		foreach ($matches[0] as $index => $value) {
			$name = $matches[1][$index];
			$keys[$name] = [
				"search" => $value,
				"name" => $name,
				"translate" => "",
			];
		}
		return $keys;
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

}
