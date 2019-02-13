<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 09.01.2019
 */

namespace Bajzany\Table;

use Bajzany\Paginator\IPaginator;
use Bajzany\Table\ColumnDriver\ColumnDriver;
use Bajzany\Table\EntityTable\IColumn;
use Nette\ComponentModel\IContainer;

interface ITable
{
	/**
	 * @param IContainer $container
	 * @return ITable
	 */
	public function build(IContainer $container): ITable;

	/**
	 * @param TableControl $control
	 */
	public function execute(TableControl $control);

	/**
	 * @return bool
	 */
	public function isBuild(): bool;

	/**
	 * @return IPaginator|null
	 */
	public function getPaginator(): ?IPaginator;

	/**
	 * @return ColumnDriver
	 */
	public function getColumnDriver(): ColumnDriver;

	/**
	 * @return TableControl
	 */
	public function getControl(): TableControl;

	/**
	 * @return \Nette\Application\UI\Presenter|null
	 */
	public function getPresenter();

	/**
	 * @param callable $preRender
	 */
	public function addPreRender(callable $preRender);

	/**
	 * @param callable $preRender
	 */
	public function addPostRender(callable $preRender);

	/**
	 * @return IColumn[]
	 */
	public function getColumns(): array;

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function removeColumn(string $key): void;

	/**
	 * @param string $key
	 * @return IColumn|null
	 */
	public function getColumn(string $key): ?IColumn;

}
