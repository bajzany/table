<?php

/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 */

namespace Bajzany\Table\EntityTable;

use Bajzany\Table\EntityTable;
use Bajzany\Table\Exceptions\TableException;
use Bajzany\Table\TableHtml;
use Bajzany\Table\TableObjects\HeaderItem;

class SearchSelectColumn extends SearchColumn implements IColumn, ISearchColumn
{

	/**
	 * @var array
	 */
	private $options = [];

	/**
	 * @internal
	 *
	 * @param EntityTable $entityTable
	 * @throws TableException
	 */
	public function build(EntityTable $entityTable)
	{
		parent::build($entityTable);

		if ($this->isBuild()) {
			throw TableException::searchColumnIsAlreadyBuild($this->getKey());
		}

		$listener = $this->getListener();
		$actions = $listener->getByType(SearchColumn::ON_SEARCH_ACTION);

		if (empty($actions)) {
			$metadata = $entityTable->getEntityManager()->getClassMetadata($entityTable->getEntityClass());
			if (in_array($this->getKey(),$metadata->getFieldNames())) {
				$entityTable->getQueryBuilder()->andWhere("e.{$this->getKey()} LIKE :value")
					->setParameter('value', '%'.$this->getSelectedValue().'%');
			}
		}

		$listener->emit(SearchColumn::ON_SEARCH_ACTION,$this);

		$this->build = TRUE;
	}

	/**
	 * @internal
	 *
	 * @param HeaderItem $item
	 * @throws TableException
	 * @throws \Nette\Application\UI\InvalidLinkException
	 */
	public function render(HeaderItem $item)
	{
		$inputName = $this->getInputName();
		$defaultValue = $this->getSelectedValue();
		$componentName = $this->getEntityTable()->getComponentName($this->getEntityTable()->getControl());
		$this->setSelectedValue($defaultValue);

		$select = TableHtml::el('select', [
			'name' => $inputName,
			'placeholder' => $this->getLabel(),
			'class' => 'form-control searchTable',
			'data-url' => $this->getEntityTable()->getControl()->link('this'),
			'data-control' =>$componentName
		]);

		$defaultOption = TableHtml::el('option', ['value' => '']);
		$defaultOption->setText($this->getKey());
		$select->addHtml($defaultOption);
		foreach ($this->getOptions() as $name => $title) {
			$opt = TableHtml::el('option', ['value' => $name]);

			if ($name == $defaultValue) {
				$opt->setAttribute('selected', true);
			}

			$opt->setText($title);
			$select->addHtml($opt);
		}

		$item->setHtml($select);
	}

	/**
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * @param array $options
	 * @return $this
	 */
	public function setOptions(array $options)
	{
		$this->options = $options;
		return $this;
	}

	/**
	 * @param string $name
	 * @param mixed $title
	 * @return $this
	 */
	public function addOption(string $name, $title)
	{
		$this->options[$name] = $title;
		return $this;
	}

}
