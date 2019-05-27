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

}
