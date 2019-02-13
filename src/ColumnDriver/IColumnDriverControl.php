<?php

/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 */

namespace Bajzany\Table\ColumnDriver;

use Bajzany\Table\ITable;

interface IColumnDriverControl
{

	/**
	 * @param ITable $table
	 * @return ColumnDriverControl
	 */
	public function create(ITable $table): ColumnDriverControl;

}
