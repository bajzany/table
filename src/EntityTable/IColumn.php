<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\EntityTable;

use Bajzany\Table\Table;
use Bajzany\Table\TableObjects\HeaderItem;
use Nette\Application\UI\Control;

/**
 * @method onBodyCreate()
 * @method onHeaderCreate()
 * @method onFooterCreate()
 */
interface IColumn
{

	/**
	 * @return string
	 */
	public function getKey(): string;

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
	 * @return array
	 */
	public function getUsedComponents(): array;

	/**
	 * @param string $name
	 */
	public function useComponent(string $name);

	/**
	 * @return bool
	 */
	public function isAllowRender(): bool;

	/**
	 * @param bool $render
	 * @return $this
	 */
	public function setAllowRender(bool $render);

	/**
	 * @param bool $bool
	 * @return $this
	 */
	public function setSearchable(bool $bool);

	/**
	 * @return bool
	 */
	public function isSearchable():bool;

	/**
	 * @return bool
	 */
	public function isSortable(): bool;

	/**
	 * @param bool $sortable
	 * @return $this
	 */
	public function setSortable(bool $sortable);

	/**
	 * @param HeaderItem $headerItem
	 * @param Control $control
	 * @return mixed
	 */
	public function renderSearchColumn(HeaderItem $headerItem, Control $control);

	/**
	 * @param Table $table
	 */
	public function buildSearchColumn(Table $table);

	/**
	 * @param string $name
	 * @param mixed $title
	 * @return $this
	 */
	public function addSearchSelectOption(string $name, $title);

	/**
	 * @param array $searchSelectOptions
	 * @return $this
	 */
	public function setSearchSelectOptions(array $searchSelectOptions);

	/**
	 * @param Table $table
	 */
	public function buildSortableColumn(Table $table);

	/**
	 * @param HeaderItem $headerItem
	 * @param Control $control
	 */
	public function renderSortableColumn(HeaderItem $headerItem, Control $control);

}
