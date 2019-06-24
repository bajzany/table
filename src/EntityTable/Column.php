<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\EntityTable;

use Nette\SmartObject;

/**
 * @method onBodyCreate()
 * @method onHeaderCreate()
 * @method onFooterCreate()
 */
class Column implements IColumn
{

	use SmartObject;

	const ON_HEADER_CREATE = "onHeaderCreate";
	const ON_BODY_CREATE = "onBodyCreate";
	const ON_FOOTER_CREATE = "onFooterCreate";

	/**
	 * @var callable[]
	 */
	public $onBodyCreate = [];

	/**
	 * @var callable[]
	 */
	public $onHeaderCreate = [];

	/**
	 * @var callable[]
	 */
	public $onFooterCreate = [];

	/**
	 * @var string
	 */
	private $key;

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
	 * @var array
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
	}

	/**
	 * @return string
	 */
	public function getKey(): string
	{
		return $this->key;
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
	 * @return array
	 */
	public function getUsedComponents(): array
	{
		return $this->components;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function useComponent(string $name)
	{
		$this->components[] = $name;
		return $this;
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
