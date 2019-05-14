<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 */

namespace Bajzany\Table\Events;

interface ITableSubscriber
{

	public function getSubscribedEvents(): array;

}
