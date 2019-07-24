<?php

/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 */

namespace Bajzany\Table\Listener;

use Bajzany\Table\EntityTable;

interface ITableSubscriber
{

	public function onBuildQuery(EntityTable $entityTable);

}
