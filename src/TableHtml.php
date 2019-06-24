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
use Nette\Application\UI\Control;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Html;

class TableHtml extends Html
{

	/**
	 * @var TableHtml|null
	 */
	private $parent;

	/**
	 * Returns all children.
	 * @param $key
	 */
	public function removeChild($key)
	{
		 unset($this->children[$key]);
	}

	/**
	 * @param null $indent
	 * @return string
	 */
	public function render($indent = NULL)
	{
		$s = $this->startTag();

		if (!$this->isEmpty()) {
			// add content
			if ($indent !== NULL) {
				$indent++;
			}
			foreach ($this->getChildren() as $child) {
				if ($child instanceof self || $child instanceof Control) {
					ob_start();
					$content = $child->render($indent);
					$output = ob_get_contents();
					if (empty($content) && !empty($output)) {
						$content = $output;
					}
					ob_end_clean();
					$s .= $content;
				} else {
					$s .= $child;
				}
			}

			// add end tag
			$s .= $this->endTag();
		}

		if ($indent !== NULL) {
			return "\n" . str_repeat("\t", $indent - 1) . $s . "\n" . str_repeat("\t", max(0, $indent - 2));
		}
		return $s;
	}

	/**
	 * @param string|Html $child
	 * @return $this|Html|void
	 */
	public function addHtml($child)
	{
		if ($child instanceof IComponent) {
			$this->children[$child->getName()] = $child;
			return;
		}

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
	public function getTableWrapped()
	{
		$parent = $this->getLastParent($this);

		if (!$parent instanceof TableWrapped) {
			throw TableException::notAttached();
		}
		return $parent;
	}

	/**
	 * @return ITable
	 * @throws TableException
	 */
	public function getTable()
	{
		return $this->getTableWrapped()->getTable();
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
