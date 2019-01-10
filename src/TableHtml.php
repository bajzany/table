<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table;

use Bajzany\Table\Exceptions\TableException;
use Bajzany\Table\HtmlElements\HtmlElements;
use Bajzany\Table\TableObjects\TableWrapped;
use Nette\Utils\Html;

class TableHtml extends Html
{

	/**
	 * @var TableHtml|null
	 */
	private $parent;

	public function addHtml($child)
	{
		if ($child instanceof HtmlElements) {
			$child->build();
		}

		parent::addHtml($child);

		if ($child instanceof TableHtml) {
			$child->setParent($this);
		}

		return $this;

	}

	public function setHtml($html)
	{
		if ($html instanceof HtmlElements) {
			$html->build();
		}

		parent::setHtml($html);

		if ($html instanceof TableHtml) {
			$html->setParent($this);
		}

		return $this;
	}

	/**
	 * @return TableHtml
	 * @throws TableException
	 */
	public function getTable()
	{
		$parent = $this->getLastParent($this);

		if (!$parent instanceof TableWrapped) {
			throw TableException::notAttached();
		}
		return $parent;
	}

	/**
	 * @param TableHtml $tableHtml
	 * @return TableHtml
	 */
	private function getLastParent(TableHtml $tableHtml)
	{
		if ($tableHtml->getParent() === NULL) {
			return $tableHtml;
		}

		return $this->getLastParent($tableHtml->getParent());
	}

	/**
	 * @return TableHtml
	 */
	public function getParent(): ?TableHtml
	{
		return $this->parent;
	}

	/**
	 * @param TableHtml $parent
	 * @return $this
	 */
	public function setParent(TableHtml $parent)
	{
		$this->parent = $parent;
		return $this;
	}

}
