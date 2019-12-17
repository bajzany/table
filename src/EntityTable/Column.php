<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 04.01.2019
 */

namespace Bajzany\Table\EntityTable;

use Bajzany\Table\EntityTable;
use Bajzany\Table\Table;
use Bajzany\Table\TableHtml;
use Bajzany\Table\TableObjects\HeaderItem;
use Doctrine\Common\Collections\Criteria;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\SmartObject;
use Nette\Utils\Html;

/**
 * @method onBodyCreate()
 * @method onHeaderCreate()
 * @method onFooterCreate()
 */
class Column implements IColumn
{

	use SmartObject;

	const SEARCH_NAME_PREFIX = 'searchTable_';
	const SORTING_NAME_PREFIX = 'sortingTable_';
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
	 * @var callable[]
	 */
	public $onSearchAction = [];

	/**
	 * @var callable[]
	 */
	public $onSortingAction = [];

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
	 * @var bool
	 */
	private $searchable = FALSE;

	/**
	 * @var bool
	 */
	private $sortable = FALSE;

	/**
	 * @var string|null
	 */
	private $selectedSearchValue;

	/**
	 * @var string|null
	 */
	private $selectedSortValue;

	/**
	 * @var array
	 */
	private $searchSelectOptions = [];

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

	/**
	 * @return bool
	 */
	public function isSearchable(): bool
	{
		return $this->searchable;
	}

	/**
	 * @param bool $searchable
	 * @return $this
	 */
	public function setSearchable(bool $searchable)
	{
		$this->searchable = $searchable;
		return $this;
	}

	/**
	 * @param Table $table
	 * @return bool
	 */
	private function isSearching(Table $table)
	{
		$search = FALSE;

		foreach ($table->getParameters() as $parameterName => $value) {
			if (strpos($parameterName, self::SEARCH_NAME_PREFIX) !== FALSE) {
				return TRUE;
			}
		}
		return $search;
	}

	private function applySessionSearchParams(Table $table)
	{
		$this->removeSearch($table);
		foreach ($table->getParameters() as $parameterName => $value) {
			$table->getSession()->searchFields[$parameterName] = $value;
		}
	}

	private function removeSearch(Table $table)
	{
		$table->getSession()->searchFields = [];
	}

	/**
	 * @internal
	 *
	 * @param Table $table
	 */
	public function buildSearchColumn(Table $table)
	{
		if ($this->isSearching($table)) {
			$this->applySessionSearchParams($table);
		}
		$this->setSelectedSearchValue($this->getDefaultSearchValue($table));

		if (!empty($this->onSearchAction)) {
			$this->onSearchAction($table, $this->getSelectedSearchValue());
		} else {
			if ($table instanceof EntityTable) {
				$metadata = $table->getEntityManager()->getClassMetadata($table->getEntityClass());
				$uniq = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 12);
				if (in_array($this->getKey(), $metadata->getFieldNames())) {
					$table->getQueryBuilder()->andWhere("e.{$this->getKey()} LIKE :{$uniq}")
						->setParameter($uniq, '%' . $this->getSelectedSearchValue() . '%');
				}
			} else {
				foreach ($table->getRowsCollection()->toArray() as $key => $values) {
					if (array_key_exists($this->getKey(), $values)) {
						if ($this->getSelectedSearchValue() === '') {
							continue;
						}
						if (strpos($values[$this->getKey()], $this->getSelectedSearchValue()) === FALSE) {
							continue;
						}
						$table->getRowsCollection()->remove($key);
					}
				}
			}

		}
	}

	/**
	 * @param Table $table
	 * @return string|null
	 */
	public function getDefaultSearchValue(Table $table)
	{
		if (!is_array($table->getSession()->searchFields)) {
			$table->getSession()->searchFields = [];
		}

		if (array_key_exists($this->getSearchInputName(), $table->getSession()->searchFields)) {
			return $table->getSession()->searchFields[$this->getSearchInputName()];
		}
		return NULL;
	}

	/**
	 * @param HeaderItem $headerItem
	 * @param Control $control
	 * @return mixed|void
	 * @throws \Nette\Application\UI\InvalidLinkException
	 */
	public function renderSearchColumn(HeaderItem $headerItem, Control $control)
	{
		$inputName = $this->getSearchInputName();
		$defaultValue = $this->getSelectedSearchValue();
		$componentName = $this->getComponentName($control);

		if (!empty($this->getSearchSelectOptions())) {

			$titleDescription = Html::el('label', [
				'class' => 'searchTitle',
			]);
			if ($control instanceof Table) {
				$titleDescription->setHtml($control->getTranslator()->translate($this->getLabel()));
			} else {
				$titleDescription->setText($this->getLabel());
			}

			$def = $defaultValue;
			if ($def == NULL) {
				$def = '';
			}

			$select = TableHtml::el('select', [
				'name' => $inputName,
				'class' => 'searchTable floating-select',
				'data-url' => $control->link('this'),
				'data-control' => $componentName,
				'onclick' => "this.setAttribute('value', this.value);",
				'value' => $def !== '' ? $def : ''
			]);

			$defaultOption = TableHtml::el('option', ['value' => '']);

			$select->addHtml($defaultOption);

			foreach ($this->getSearchSelectOptions() as $name => $title) {
				$opt = TableHtml::el('option', ['value' => $name]);
				if ($defaultValue !== NULL && $defaultValue !== '' && $name == $defaultValue) {
					$opt->setAttribute('selected', true);
				}

				$opt->setText($title);
				$select->addHtml($opt);
			}

			$inputContainer = Html::el('div', [
				'class' => 'floating-form',
			]);

			$inputWrapped = Html::el('div', [
				'class' => 'floating-label',
			]);

			$inputContainer->addHtml($inputWrapped);


			$inputWrapped->addHtml($select);
			$inputWrapped->addHtml("<span class=\"highlight\"></span>");
			$inputWrapped->addHtml($titleDescription);

			$headerItem->addHtml($inputContainer);
		} else {

			$titleDescription = Html::el('label');
			if ($control instanceof Table) {
				$titleDescription->setHtml($control->getTranslator()->translate($this->getLabel()));
			} else {
				$titleDescription->setText($this->getLabel());
			}

			$field = TableHtml::el('input', [
				'name' => $inputName,
				'placeholder' => ' ',
				'class' => 'searchTable floating-input',
				'data-url' => $control->link('this'),
				'data-control' => $componentName,
				'value' => $defaultValue
			]);

			$inputContainer = Html::el('div', [
				'class' => 'floating-form',
			]);

			$inputWrapped = Html::el('div', [
				'class' => 'floating-label',
			]);

			$inputContainer->addHtml($inputWrapped);

			$inputWrapped->addHtml($field);
			$inputWrapped->addHtml("<span class=\"highlight\"></span>");
			$inputWrapped->addHtml($titleDescription);

			$headerItem->addHtml($inputContainer);
		}
	}

	/**
	 * @return string
	 */
	public function getSoringInputName()
	{
		return self::SORTING_NAME_PREFIX . $this->getKey();
	}

	/**
	 * @param Table $table
	 * @return string|null
	 */
	public function getDefaultSortingValue(Table $table)
	{
		if (!is_array($table->getSession()->sortingFields)) {
			$table->getSession()->sortingFields = [];
		}

		if (array_key_exists($this->getSoringInputName(), $table->getSession()->sortingFields)) {
			return $table->getSession()->sortingFields[$this->getSoringInputName()];
		}
		return NULL;
	}

	/**
	 * @return string|null
	 */
	public function getSelectedSortValue(): ?string
	{
		return $this->selectedSortValue;
	}

	/**
	 * @param string|null $selectedSortValue
	 * @return $this
	 */
	public function setSelectedSortValue($selectedSortValue)
	{
		$this->selectedSortValue = $selectedSortValue;
		return $this;
	}

	/**
	 * @param Table $table
	 * @return bool
	 */
	private function isSorting(Table $table)
	{
		$search = FALSE;

		foreach ($table->getParameters() as $parameterName => $value) {
			if (strpos($parameterName, self::SORTING_NAME_PREFIX) !== FALSE) {
				return TRUE;
			}
		}
		return $search;
	}

	private function applySessionSortingParams(Table $table)
	{
		$this->removeSorting($table);
		foreach ($table->getParameters() as $parameterName => $value) {
			$table->getSession()->sortingFields[$parameterName] = $value;
		}
	}

	private function removeSorting(Table $table)
	{
		$table->getSession()->sortingFields = [];
	}

	/**
	 * @param Table $table
	 */
	public function buildSortableColumn(Table $table)
	{
		if ($this->isSorting($table)) {
			$this->applySessionSortingParams($table);
		}
		$this->setSelectedSortValue($this->getDefaultSortingValue($table));
		if (empty($this->getSelectedSortValue())) {
			return;
		}

		if (!empty($this->onSortingAction)) {
			$this->onSortingAction($table, $this->getSelectedSortValue());
		} else {
			if ($table instanceof EntityTable) {
				$metadata = $table->getEntityManager()->getClassMetadata($table->getEntityClass());
				if (in_array($this->getKey(), $metadata->getFieldNames())) {
					$table->getQueryBuilder()->addOrderBy("e.{$this->getKey()}", $this->getSelectedSortValue());
				}
			} else {

				$table->getCriteria()->orderBy([$this->getKey() => $this->getSelectedSortValue()]);

//
//				barDump($this->getKey());
//				barDump($this->getSelectedSortValue());
//
//				barDump($table->getRowsCollection());



//				$criteria = new Criteria(NULL, ['sorting' => Criteria::ASC]);
//				$children = $table->getRowsCollection()->matching($criteria);
//
			}
		}

	}

	/**
	 * @param HeaderItem $headerItem
	 * @param Control $control
	 * @throws \Nette\Application\UI\InvalidLinkException
	 */
	public function renderSortableColumn(HeaderItem $headerItem, Control $control)
	{

		$headerItem->setAttribute('class', 'withSortable');

		$inputName = $this->getSoringInputName();
		$defaultValue = $this->getSelectedSortValue();
		$componentName = $this->getComponentName($control);

		$mode = $defaultValue;
		if ($defaultValue === NULL) {
			$mode = 'undefined';
		}

		$modeClass = $mode === 'undefined' ? 'fa-sort' : ($mode === 'ASC' ? 'fa-sort-up' : 'fa-sort-down');
		$i = Html::el('i', [
			'data-url' => $control->link('this'),
			'data-control' => $componentName,
			'data-name' => $inputName,
			'data-mode' => $mode,
			'class' => "table-sortable fas {$modeClass}"
		]);

		$headerItem->addHtml($i);
	}

	/**
	 * @param IComponent $component
	 * @param string $name
	 * @return string
	 */
	private function getComponentName($component, $name = "")
	{
		if ($component instanceof Presenter || !$component) {
			return $name;
		}
		$name = $component->getName() . ((!empty($name) ? "-" : "") . $name);
		return $this->getComponentName($component->getParent(), $name);
	}

	/**
	 * @return string
	 */
	public function getSearchInputName()
	{
		return self::SEARCH_NAME_PREFIX . $this->getKey();
	}

	/**
	 * @return string|null
	 */
	public function getSelectedSearchValue(): ?string
	{
		return $this->selectedSearchValue;
	}

	/**
	 * @param string|null $selectedSearchValue
	 * @return $this
	 */
	public function setSelectedSearchValue($selectedSearchValue)
	{
		$this->selectedSearchValue = $selectedSearchValue;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getSearchSelectOptions(): array
	{
		return $this->searchSelectOptions;
	}

	/**
	 * @param string $name
	 * @param mixed $title
	 * @return $this
	 */
	public function addSearchSelectOption(string $name, $title)
	{
		$this->searchSelectOptions[$name] = $title;
		return $this;
	}

	/**
	 * @param array $searchSelectOptions
	 * @return $this
	 */
	public function setSearchSelectOptions(array $searchSelectOptions)
	{
		$this->searchSelectOptions = $searchSelectOptions;
		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSortable(): bool
	{
		return $this->sortable;
	}

	/**
	 * @param bool $sortable
	 * @return $this
	 */
	public function setSortable(bool $sortable)
	{
		$this->sortable = $sortable;
		return $this;
	}

}
