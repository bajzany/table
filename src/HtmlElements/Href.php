<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\HtmlElements;

use Bajzany\Table\TableHtml;

class Href extends TableHtml implements HtmlElements
{
	/**
	 * @var string|null
	 */
	private $label;

	/**
	 * @var string|null
	 */
	private $link;

	/**
	 * @var string|null
	 */
	private $class;

	public function __construct($label = NULL, $link = NULL, $class = NULL)
	{
		$this->label = $label;
		$this->link = $link;
		$this->class = $class;

		$this->setName('a');
		$this->addAttributes([
			'href' => $this->link,
			'class' => $this->class,
		]);
		$this->setText($this->label);
	}
	
	public function build()
	{
		$this->addAttributes([
			'href' => $this->getLink(),
			'class' => $this->getClass(),
		]);
		$this->setText($this->getLabel());
	}

	/**
	 * @return string|null
	 */
	public function getLabel(): ?string
	{
		return $this->label;
	}

	/**
	 * @param string|null $label
	 * @return $this
	 */
	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getLink(): ?string
	{
		return $this->link;
	}

	/**
	 * @param string|null $link
	 * @return $this
	 */
	public function setLink($link)
	{
		$this->link = $link;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getClass(): ?string
	{
		return $this->class;
	}

	/**
	 * @param string|null $class
	 * @return $this
	 */
	public function setClass($class)
	{
		$this->class = $class;
		return $this;
	}

}
