<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table;

interface ITableControl
{
	/**
	 * @param ITable $table
	 * @return TableControl
	 */
	public function create(ITable $table): TableControl;
}
