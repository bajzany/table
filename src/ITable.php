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

	/**
	 * @param string $destination
	 * @param array $parameters
	 * @return string
	 */
	public function createLink(string $destination, array $parameters = []);

	/**
	 * @param IContainer $control
	 * @param string $name
	 * @return string
	 */
	public function getComponentName(IContainer $control, string $name = '');

	/**
	 * @param array $parameters
	 * @param IContainer $container
	 * @return array
	 */
	public function getComponentParameters(array $parameters, IContainer $container);

}
