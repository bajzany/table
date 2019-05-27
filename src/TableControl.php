<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table;

use Bajzany\Paginator\IPaginationControl;
use Bajzany\Paginator\PaginationControl;
use Bajzany\Table\ColumnDriver\ColumnDriverControl;
use Bajzany\Table\ColumnDriver\IColumnDriverControl;
use Bajzany\Table\Exceptions\TableException;
use Nette\Application\UI\Control;
use Nette\DI\Container;

class TableControl extends Control
{

	const PAGINATOR_NAME = 'paginator';
	const COLUMN_DRIVER_NAME = 'columnDriver';

	/**
	 * @var ITable
	 */
	private $table;

	/**
	 * @var IPaginationControl
	 */
	private $paginationControl;

	/**
	 * @var IColumnDriverControl
	 */
	private $columnDriver;

	/**
	 * @var Container
	 */
	public $container;

	public function __construct(ITable $table, IPaginationControl $paginationControl, IColumnDriverControl $columnDriver, Container $container, $name = NULL)
	{
		parent::__construct($name);
		$this->table = $table;
		$this->paginationControl = $paginationControl;
		$this->columnDriver = $columnDriver;
		$this->container = $container;
	}

	public function render()
	{
		$this->table->execute($this);
	}

	public function attached($presenter)
	{
		parent::attached($presenter);
		$this->table->build($this);
	}

	public function renderPaginator()
	{
		$paginatorComponent = $this->getComponent(self::PAGINATOR_NAME);

		if ($paginatorComponent instanceof PaginationControl) {
			$paginatorComponent->getListener()->create(PaginationControl::ON_LINK_CREATE, function (PaginationControl $control, $currentPage, &$link) {

				$searchColumns = $this->getTable()->getSearchColumns();
				$params = [];
				foreach ($searchColumns as $searchColumn) {
					$params[$searchColumn->getInputName()] = $searchColumn->getSelectedValue();
				}

				$buildSearchParams = $this->getTable()->getComponentParameters($params, $this);
				$buildPaginatorParams = $this->getTable()->getComponentParameters(['currentPage' => $currentPage], $control);

				$params = array_merge($buildSearchParams, $buildPaginatorParams);

				$link = $this->getPresenter()->link('this', $params);
			});
		}

		$paginatorComponent->render();
	}

	public function renderColumnDriver()
	{
		$columnDriverComponent = $this->getComponent(self::COLUMN_DRIVER_NAME);
		$columnDriverComponent->render();
	}

	public function getComponent($name, $throw = TRUE, $args = [])
	{
		$component = parent::getComponent($name, $throw);
		// FOR COMPONENT INTO TABLE RENDER
		$table = $this->table;
		if (!$component && $table instanceof EntityTable) {
			$components = explode("-", $name);
			$componentName = $components[0];
			$exp = explode("_", $componentName);
			$prefix = $exp[0];
			$entityId = isset($exp[1]) ? $exp[1] : NULL;

			if (!$entityId) {
				return NULL;
			}

			$component = $table->getRegisterComponentByName($prefix);
			$service = $this->container->getByType($component->getComponentInterface());
			$component = $service->create(...$args);
			$this->addComponent($component, $componentName);
			$component = parent::getComponent($name, $throw);
		}

		return $component;

	}

	/**
	 * @return \Bajzany\Paginator\PaginationControl
	 * @throws TableException
	 */
	public function createComponentPaginator()
	{
		$paginator = $this->table->getPaginator();
		if (empty($paginator)) {
			throw TableException::paginatorIsNotSet(get_class($this->table));
		}

		return $this->getPaginationControl()->create($paginator);
	}

	/**
	 * @return ColumnDriverControl
	 */
	public function createComponentColumnDriver()
	{
		return $this->columnDriver->create($this->table);
	}


	/**
	 * @return ITable
	 */
	public function getTable(): ITable
	{
		return $this->table;
	}

	/**
	 * @return IPaginationControl
	 */
	public function getPaginationControl(): IPaginationControl
	{
		return $this->paginationControl;
	}

	/**
	 * @return IColumnDriverControl
	 */
	public function getColumnDriver(): IColumnDriverControl
	{
		return $this->columnDriver;
	}

}
