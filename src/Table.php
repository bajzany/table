<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 09.01.2019
 */

namespace Bajzany\Table;

use Bajzany\Paginator\IPaginator;
use Bajzany\Paginator\Paginator;
use Bajzany\Table\Exceptions\TableException;
use Bajzany\Table\TableObjects\TableWrapped;
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

	public function __construct()
	{
		$this->tableWrapped = new TableWrapped($this);
	}

	/**
	 * @param IContainer $container
	 * @return ITable
	 */
	public function build(IContainer $container): ITable
	{
		$this->build = TRUE;
		return $this;
	}

	/**
	 * @param TableControl $control
	 */
	public function execute(TableControl $control)
	{
		$this->control = $control;
		$this->emitPreRender();
		$this->getTableWrapped()->render();
		$this->emitPostRender();
	}
	
	protected function emitPreRender()
	{
		foreach ($this->preRender as $event) {
			call_user_func_array($event['callable'], [$this]);
		}
	}

	protected function emitPostRender()
	{
		foreach ($this->postRender as $event) {
			call_user_func_array($event['callable'], [$this]);
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

}
