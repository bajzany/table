<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\EntityTable;

use Bajzany\Table\Events\Listener;
use Nette\Application\UI\Control;

class Column implements IColumn
{
	const ON_HEADER_CREATE = "onHeaderCreate";
	const ON_BODY_CREATE = "onBodyCreate";
	const ON_FOOTER_CREATE = "onFooterCreate";

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var Listener
	 */
	private $listener;

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
	 * @var Control[]
	 */
	private $components = [];

	/**
	 * @var array
	 */
	private $filters = [];

	/**
	 * @var bool
	 */
	private $allowRender = TRUE;

	/**
	 * @param string $key
	 */
	public function __construct(string $key)
	{
		$this->key = $key;
		$this->listener = new Listener();
	}

	/**
	 * @return string
	 */
	public function getKey(): string
	{
		return $this->key;
	}

	/**
	 * @return Listener
	 */
	public function getListener(): Listener
	{
		return $this->listener;
	}

	/**
	 * @param callable $callable
	 * @return $this
	 */
	public function onHeaderCreate(callable $callable)
	{
		$this->listener->create(self::ON_HEADER_CREATE, $callable);
		return $this;
	}

	/**
	 * @param callable $callable
	 * @return $this
	 */
	public function onBodyCreate(callable $callable)
	{
		$this->listener->create(self::ON_BODY_CREATE, $callable);
		return $this;
	}

	/**
	 * @param callable $callable
	 * @return $this
	 */
	public function onFooterCreate(callable $callable)
	{
		$this->listener->create(self::ON_FOOTER_CREATE, $callable);
		return $this;
	}

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

	/**
	 * @return bool
	 */
	public function isAllowRender(): bool
	{
		return $this->allowRender;
	}

	/**
	 * @param bool $allowRender
	 * @return $this
	 */
	public function setAllowRender(bool $allowRender)
	{
		$this->allowRender = $allowRender;
		return $this;
	}

}
