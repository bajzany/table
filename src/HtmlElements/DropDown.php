<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\HtmlElements;

use Bajzany\Table\TableHtml;
use Nette\Utils\Html;

class DropDown extends TableHtml implements HtmlElements
{

	/**
	 * @var TableHtml
	 */
	private $wrapped;

	/**
	 * @var TableHtml
	 */
	private $buttonWrapped;

	/**
	 * @var TableHtml
	 */
	private $ulWrapped;

	/**
	 * @var TableHtml[]
	 */
	private $items = [];

	/**
	 * @return DropDown
	 */
	public function createWrapped()
	{
		$this->setName('div');
		$this->addAttributes([
			'class' => 'btn-group',
		]);

		return $this;
	}

	/**
	 * @return Html
	 */
	public function createButtonWrapped()
	{
		$buttonTwo = TableHtml::el('button');
		$buttonTwo->setAttribute('data-toggle','dropdown');
		$buttonTwo->setAttribute('class',"btn btn-default dropdown-toggle");

		$buttonTwo->addHtml(TableHtml::el('span')->setAttribute('class','caret'));
		$buttonTwo->addHtml(TableHtml::el('span')->setAttribute('class','sr-only')->setText('Toggle Dropdown'));
		$wrapped = TableHtml::el()
			->addHtml($buttonTwo);

		return $wrapped;
	}

	/**
	 * @return Html
	 */
	public function createUlWrapped()
	{
		$ul = TableHtml::el('ul');
		$ul->setAttribute('class','dropdown-menu');
		$ul->setAttribute('style','margin-left: -120px;');
		$ul->setAttribute('role','menu');

		foreach ($this->getItems() as $item) {
			$ul->addHtml(TableHtml::el('li')->addHtml($item));
		}
		return $ul;
	}
	
	public function build()
	{
		$this->wrapped = $this->createWrapped();
		$this->buttonWrapped = $this->createButtonWrapped();
		$this->ulWrapped = $this->createUlWrapped();
		$this->addHtml($this->buttonWrapped);
		$this->addHtml($this->ulWrapped);
	}

	/**
	 * @return TableHtml
	 */
	public function getWrapped(): TableHtml
	{
		return $this->wrapped;
	}

	/**
	 * @return TableHtml
	 */
	public function getUlWrapped(): TableHtml
	{
		return $this->ulWrapped;
	}

	/**
	 * @return Html[]
	 */
	public function getItems(): array
	{
		return $this->items;
	}

	/**
	 * @param TableHtml $item
	 * @return $this
	 */
	public function addItem(TableHtml $item)
	{
		$this->items[] = $item;
		return $this;
	}

	/**
	 * @return TableHtml
	 */
	public function getButtonWrapped(): TableHtml
	{
		return $this->buttonWrapped;
	}

}
