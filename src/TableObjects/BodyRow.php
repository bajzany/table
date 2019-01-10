<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table\TableObjects;

use Bajzany\Table\TableHtml;

class BodyRow extends TableHtml
{
	/**
	 * @var TableHtml
	 */
	private $wrapped;

	/**
	 * @var object|null
	 */
	private $entity;

	public function __construct()
	{
		$this->wrapped = $this->createWrapped();
	}

	/**
	 * @return TableHtml
	 */
	public function createWrapped(): TableHtml
	{
		$this->setName('tr');

		return $this;
	}

	/**
	 * @return TableHtml
	 */
	public function getWrapped(): TableHtml
	{
		return $this->wrapped;
	}

	/**
	 * @return Item[]
	 */
	public function getItems(): array
	{
		return $this->getWrapped()->getChildren();
	}

	/**
	 * @param null $title
	 * @return Item
	 */
	public function createItem($title = NULL)
	{
		$item = new Item();
		$item->setText($title);
		$this->getWrapped()->addHtml($item);
		return $item;
	}

	/**
	 * @param Item $item
	 * @return $this
	 */
	public function addItem(Item $item)
	{
		$this->getWrapped()->addHtml($item);
		return $this;
	}

	/**
	 * @return object|null
	 */
	public function getEntity(): ?object
	{
		return $this->entity;
	}

	/**
	 * @param object|null $entity
	 * @return $this
	 */
	public function setEntity(object $entity = NULL)
	{
		$this->entity = $entity;
		return $this;
	}

}
