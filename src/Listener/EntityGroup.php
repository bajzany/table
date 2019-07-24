<?php

/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 */

namespace Bajzany\Table\Listener;

class EntityGroup
{

	/**
	 * @var string
	 */
	private $entityClass;

	/**
	 * @var ITableSubscriber[]
	 */
	private $events;

	public function __construct(string $entityClass)
	{
		$this->entityClass = $entityClass;
	}

	/**
	 * @param ITableSubscriber $event
	 */
	public function addEvent(ITableSubscriber $event)
	{
		$this->events[] = $event;
	}

	/**
	 * @return string
	 */
	public function getEntityClass(): string
	{
		return $this->entityClass;
	}

	/**
	 * @return ITableSubscriber[]
	 */
	public function getEvents(): array
	{
		return $this->events;
	}

}
