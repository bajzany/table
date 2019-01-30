<?php
/**
 * Author: Mykola Chomenko
 * Email: mykola.chomenko@dipcom.cz
 */

namespace Bajzany\Table\Events;

class Listener
{

	/**
	 * @var Event[]
	 */
	private $events = [];

	/**
	 * @param Event $event
	 */
	public function add(Event $event)
	{
		$this->events[] = $event;
	}

	/**
	 * @param string $type
	 * @param callable $callable
	 * @return Event
	 */
	public function create(string $type, callable $callable)
	{
		$event = new Event($type, $callable);
		$this->add($event);
		return $event;
	}

	/**
	 * @param string $type
	 * @return Event[]
	 */
	public function getByType(string $type): array
	{
		$list = [];
		foreach ($this->events as $event) {
			if (!$event->hasType($type)) {
				continue;
			}
			$list[] = $event;
		}
		return $list;
	}

	/**
	 * @return Event[]
	 */
	public function getEvents(): array
	{
		return $this->events;
	}

	/**
	 * @param string $type
	 * @param array ...$args
	 */
	public function emit(string $type, &...$args)
	{
		foreach ($this->getByType($type) as $event) {
			$event->emit($args);
		}
	}

}
