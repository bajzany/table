<?php

/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 */

namespace Bajzany\Table;

class TableComponent
{

	/** @var string */
	private $componentInterface;

	/**
	 * @var array
	 */
	private $componentProperties = [];

	/**
	 * @param string $componentInterface
	 */
	public function __construct(string $componentInterface)
	{
		$this->componentInterface = $componentInterface;
	}

	/**
	 * @return string
	 */
	public function getComponentInterface(): string
	{
		return $this->componentInterface;
	}

	/**
	 * @param $identification
	 * @return array
	 */
	public function getComponentPropertiesByIdentification($identification): array
	{
		if (array_key_exists($identification, $this->componentProperties)) {
			return $this->componentProperties[$identification];
		}

		return [];
	}

	/**
	 * @return array
	 */
	public function getComponentProperties(): array
	{
		return $this->componentProperties;
	}

	/**
	 * @param $identification
	 * @param array $args
	 * @return $this
	 */
	public function addComponentProperties($identification, array $args)
	{
		$this->componentProperties[$identification] = $args;
		return $this;
	}

}
