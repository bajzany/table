<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table;

use Bajzany\Paginator\IPaginationControl;
use Bajzany\Table\Exceptions\TableException;
use Nette\Application\UI\Control;

class TableControl extends Control
{

	const PAGINATOR_NAME = 'paginator';

	/**
	 * @var ITable
	 */
	private $table;

	/**
	 * @var IPaginationControl
	 */
	private $paginationControl;

	public function __construct(ITable $table, IPaginationControl $paginationControl, $name = NULL)
	{
		parent::__construct($name);
		$this->table = $table;
		$this->paginationControl = $paginationControl;
	}

	public function render()
	{
		$this->table->execute();
	}
	
	public function attached($presenter)
	{
		parent::attached($presenter);
		$this->table->build($this);
	}


	public function renderPaginator()
	{
		$paginatorComponent = $this->getComponent(self::PAGINATOR_NAME);
		$paginatorComponent->render();
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

}
