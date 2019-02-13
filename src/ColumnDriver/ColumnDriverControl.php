<?php

/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 */

namespace Bajzany\Table\ColumnDriver;

use Bajzany\Table\ITable;
use Nette\Application\UI\Control;

class ColumnDriverControl extends Control
{

	/**
	 * @var ITable
	 */
	private $table;

	public function __construct(ITable $table, $name = NULL)
	{
		parent::__construct($name);
		$this->table = $table;
		$this->build();
	}

	private function build()
	{
		$this->table->addPreRender(function (ITable $table) {
			$enabledColumns = $table->getColumnDriver()->getEnabledColumns();
			$disabledColumns = $table->getColumnDriver()->getDisabledColumns();
			if (!empty($enabledColumns)) {
				foreach ($table->getColumns() as $key => $tableColumn) {
					$exist = FALSE;
					foreach ($enabledColumns as $enabledKey => $enabledColumn) {
						if ($key == $enabledKey) {
							$exist = TRUE;
						}
					}
					if (!$exist) {
						$table->getColumn($key)->setAllowRender(FALSE);
					}
				}
			}

			foreach ($disabledColumns as $key => $disabledColumn) {
				$table->getColumn($key)->setAllowRender(FALSE);
			}
		});
	}

	public function render()
	{
		//TODO:: waiting for userSettingsBundle
	}

	/**
	 * @return ITable
	 */
	public function getTable(): ITable
	{
		return $this->table;
	}

}
