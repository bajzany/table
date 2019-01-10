<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\HtmlElements;

use Bajzany\Table\TableHtml;

class Image extends TableHtml implements HtmlElements
{
	/**
	 * @var string|null
	 */
	private $image;

	/**
	 * @var string|null
	 */
	private $class;

	/**
	 * @var string|null
	 */
	private $tooltip;

	public function __construct($image = NULL, $class = NULL, $tooltip = NULL)
	{
		$this->image = $image;
		$this->class = $class;
		$this->tooltip = $tooltip;

		$this->setName('img');
		$this->addAttributes([
			'src' => $this->image,
			'class' => $this->class,
			'title' => $this->tooltip,
		]);
	}
	
	public function build()
	{
		$this->addAttributes([
			'src' => $this->getImage(),
			'class' => $this->getClass(),
			'title' => $this->getTooltip(),
		]);
	}

	/**
	 * @return string|null
	 */
	public function getImage(): ?string
	{
		return $this->image;
	}

	/**
	 * @param string|null $image
	 * @return $this
	 */
	public function setImage($image)
	{
		$this->image = $image;
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

	/**
	 * @return string|null
	 */
	public function getTooltip(): ?string
	{
		return $this->tooltip;
	}

	/**
	 * @param string|null $tooltip
	 * @return $this
	 */
	public function setTooltip($tooltip)
	{
		$this->tooltip = $tooltip;
		return $this;
	}
}
