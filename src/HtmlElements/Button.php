<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\HtmlElements;

use Bajzany\Table\TableHtml;

class Button extends TableHtml implements HtmlElements
{
	/**
	 * @var string|null
	 */
	private $label = 'Action';

	/**
	 * @var string|null
	 */
	private $class = 'btn btn-default';

	/**
	 * @var string
	 */
	private $type = 'button';

	public function __construct($label = NULL, $class = NULL, $type = NULL)
	{
		$this->label = $label ? $label : $this->getLabel();
		$this->class = $class ? $class : $this->getClass();
		$this->type = $type ? $type : $this->getType();

		$this->setName('button');
		$this->addAttributes([
			'class' => $this->class,
			'type' => $this->type,
		]);
		$this->setText($this->label);
	}
	
	public function build()
	{
		$this->addAttributes([
			'class' => $this->getClass(),
			'type' => $this->getType(),
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
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return $this
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

}
