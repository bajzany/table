<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\EntityTable;

use Nette\Application\UI\Control;

class Column
{

	/**
	 * @var string
	 */
	private $label;

	/**
	 * @var string|null
	 */
	private $footer;

	/**
	 * @var string|null
	 */
	private $pattern;

	/**
	 * @var callable|null
	 */
	private $bodyItemCallable;

	/**
	 * @var callable|null
	 */
	private $headerItemCallable;

	/**
	 * @var callable|null
	 */
	private $footerItemCallable;

	/**
	 * @var Control[]
	 */
	private $components = [];

	/**
	 * @var array
	 */
	private $filters = [];

	/**
	 * @return string
	 */
	public function getLabel(): ?string
	{
		return $this->label;
	}

	/**
	 * @param string $label
	 * @return $this
	 */
	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPattern(): ?string
	{
		return $this->pattern;
	}

	/**
	 * @param string $pattern
	 * @return $this
	 */
	public function setPattern($pattern)
	{
		$this->pattern = $pattern;
		return $this;
	}

	/**
	 * @return callable[][]
	 */
	public function getFilters(): array
	{
		return $this->filters;
	}

	/**
	 * @param callable $filter
	 * @param array $config
	 * @return $this
	 */
	public function addFilter(callable $filter, array $config = [])
	{
		$this->filters[] = [
			"callable" => $filter,
			"config" => $config,
		];
		return $this;
	}

	/**
	 * @return callable|null
	 */
	public function getBodyItemCallable(): ?callable
	{
		return $this->bodyItemCallable;
	}

	/**
	 * @param callable|null $bodyItemCallable
	 * @return $this
	 */
	public function setBodyItemCallable($bodyItemCallable)
	{
		$this->bodyItemCallable = $bodyItemCallable;
		return $this;
	}

	/**
	 * @return callable|null
	 */
	public function getHeaderItemCallable(): ?callable
	{
		return $this->headerItemCallable;
	}

	/**
	 * @param callable|null $headerItemCallable
	 * @return $this
	 */
	public function setHeaderItemCallable($headerItemCallable)
	{
		$this->headerItemCallable = $headerItemCallable;
		return $this;
	}

	/**
	 * @return callable|null
	 */
	public function getFooterItemCallable(): ?callable
	{
		return $this->footerItemCallable;
	}

	/**
	 * @param callable|null $footerItemCallable
	 * @return $this
	 */
	public function setFooterItemCallable($footerItemCallable)
	{
		$this->footerItemCallable = $footerItemCallable;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFooter(): ?string
	{
		return $this->footer;
	}

	/**
	 * @param string|null $footer
	 * @return $this
	 */
	public function setFooter($footer)
	{
		$this->footer = $footer;
		return $this;
	}

	/**
	 * @return Control[]
	 */
	public function getComponents(): array
	{
		return $this->components;
	}

	/**
	 * @param Control $control
	 */
	public function addComponent(Control $control)
	{
		$this->components[] = $control;
	}

}
