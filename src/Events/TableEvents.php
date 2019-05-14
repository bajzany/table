<?php

/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 */

namespace Bajzany\Table\Events;

class TableEvents
{

	/**
	 * @var EntityGroup[]
	 */
	private $groups = [];

	/**
	 * @param string $entityClass
	 * @param ITableSubscriber $tableEvent
	 */
	public function addEntityGroupEvent(string $entityClass, ITableSubscriber $tableEvent)
	{
		$group = new EntityGroup($entityClass);
		if ($this->getEntityGroup($entityClass)) {
			$group = $this->getEntityGroup($entityClass);
		}

		$group->addEvent($tableEvent);
		$this->groups[] = $group;
	}

	/**
	 * @param string $entityClass
	 * @return EntityGroup|null
	 */
	public function getEntityGroup(string $entityClass): ?EntityGroup
	{
		foreach ($this->groups as $group) {
			if ($group->getEntityClass() === $entityClass) {
				return $group;
			}
		}

		return NULL;
	}

	/**
	 * @return EntityGroup[]
	 */
	public function getGroups(): array
	{
		return $this->groups;
	}

}
