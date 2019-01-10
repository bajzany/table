<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table\TableObjects;

use Bajzany\Table\TableHtml;

class Item extends TableHtml
{

	/**
	 * @var TableHtml
	 */
	private $content;

	/**
	 * @var TableHtml
	 */
	private $wrapped;

	public function __construct()
	{
		$this->content = $this->createContent();
		$this->wrapped = $this->createWrapped();

		$this->getWrapped()->addHtml($this->getContent());
	}

	/**
	 * @return TableHtml
	 */
	public function createContent(): TableHtml
	{
		return TableHtml::el();
	}

	/**
	 * @return TableHtml
	 */
	public function createWrapped(): TableHtml
	{
		$this->setName("td");
		return $this;
	}

	/**
	 * @return TableHtml
	 */
	public function getContent(): TableHtml
	{
		return $this->content;
	}

	/**
	 * @return TableHtml
	 */
	public function getWrapped(): TableHtml
	{
		return $this->wrapped;
	}

}
