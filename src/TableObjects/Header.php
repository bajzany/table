<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table\TableObjects;

use Bajzany\Table\TableHtml;

class Header extends TableHtml
{

	/**
	 * @var TableHtml
	 */
	private $tHead;

	/**
	 * @var TableHtml
	 */
	private $rowWrapped;

	public function __construct()
	{
		$this->rowWrapped = $this->createRowWrapped();
		$this->tHead = $this->createThead();
	}

	/**
	 * @return TableHtml
	 */
	public function createThead(): TableHtml
	{
		$this->setName('thead');
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
	
	public function createItem($title = NULL)
	{
		$item = new HeaderItem();
		$item->setText($title);
		$this->rowWrapped->addHtml($item);
		return $item;
	}

	/**
	 * @param HeaderItem $item
	 * @return $this
	 */
	public function addItem(HeaderItem $item)
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
	public function getTHead(): TableHtml
	{
		return $this->tHead;
	}
}
