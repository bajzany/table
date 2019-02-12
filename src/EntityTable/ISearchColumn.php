<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\EntityTable;

use Bajzany\Table\EntityTable;
use Bajzany\Table\TableObjects\HeaderItem;

interface ISearchColumn
{

	/**
	 * @param EntityTable $entityTable
	 */
	public function build(EntityTable $entityTable);

	/**
	 * @param HeaderItem $item
	 */
	public function render(HeaderItem $item);

}
