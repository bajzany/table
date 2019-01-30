<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 09.01.2019
 */

namespace Bajzany\Table;

use Bajzany\Paginator\IPaginator;
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
	 * @return TableControl
	 */
	public function getControl(): TableControl;

	/**
	 * @return \Nette\Application\UI\Presenter|null
	 */
	public function getPresenter();

}
