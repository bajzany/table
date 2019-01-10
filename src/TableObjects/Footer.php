<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table\TableObjects;

use Bajzany\Table\TableHtml;

class Footer extends TableHtml
{

	/**
	 * @var TableHtml
	 */
	private $tFooter;

	/**
	 * @var TableHtml
	 */
	private $rowWrapped;

	public function __construct()
	{
		$this->rowWrapped = $this->createRowWrapped();
		$this->tFooter = $this->createTFooter();
	}

	/**
	 * @return TableHtml
	 */
	public function createTFooter(): TableHtml
	{
		$this->setName('tfoot');
		$this->addHtml($this->getRowWrapped());

		return $this;
	}

	/**
	 * @return TableHtml
	 */
	public function createRowWrapped(): TableHtml
	{
		return TableHtml::el("tr");
	}

	/**
	 * @return HeaderItem[]
	 */
	public function getItems(): array
	{
		return $this->rowWrapped->getChildren();
	}

	/**
	 * @param null $title
	 * @return Item
	 */
	public function createItem($title = NULL)
	{
		$item = new Item();
		$item->setText($title);
		$this->rowWrapped->addHtml($item);
		return $item;
	}

	/**
	 * @param Item $item
	 * @return $this
	 */
	public function addItem(Item $item)
	{
		$this->rowWrapped->addHtml($item);
		return $this;
	}

	/**
	 * @return TableHtml
	 */
	public function getRowWrapped(): TableHtml
	{
		return $this->rowWrapped;
	}


	/**
	 * @return TableHtml
	 */
	public function getTFooter(): TableHtml
	{
		return $this->tFooter;
	}
}
