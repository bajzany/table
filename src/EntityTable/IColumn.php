<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\EntityTable;

use Bajzany\Table\Events\Listener;
use Nette\Application\UI\Control;

interface IColumn
{

	/**
	 * @return string
	 */
	public function getKey(): string;

	/**
	 * @return Listener
	 */
	public function getListener(): Listener;

	/**
	 * @param callable $callable
	 * @return $this
	 */
	public function onHeaderCreate(callable $callable);

	/**
	 * @param callable $callable
	 * @return $this
	 */
	public function onBodyCreate(callable $callable);

	/**
	 * @param callable $callable
	 * @return $this
	 */
	public function onFooterCreate(callable $callable);

	/**
	 * @return string
	 */
	public function getLabel(): ?string;

	/**
	 * @param string $label
	 * @return $this
	 */
	public function setLabel($label);

	/**
	 * @return string
	 */
	public function getPattern(): ?string;

	/**
	 * @param string $pattern
	 * @return $this
	 */
	public function setPattern($pattern);

	/**
	 * @return callable[][]
	 */
	public function getFilters(): array;

	/**
	 * @param callable $filter
	 * @param array $config
	 * @return $this
	 */
	public function addFilter(callable $filter, array $config = []);

	/**
	 * @return string|null
	 */
	public function getFooter(): ?string;

	/**
	 * @param string|null $footer
	 * @return $this
	 */
	public function setFooter($footer);

	/**
	 * @return Control[]
	 */
	public function getComponents(): array;

	/**
	 * @param Control $control
	 */
	public function addComponent(Control $control);

}
