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

class SearchTextColumn extends SearchColumn implements IColumn, ISearchColumn
{

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

		$field = TableHtml::el('input',[
			'name' => $inputName,
			'placeholder' => $this->getLabel(),
			'class' => 'form-control searchTable',
			'data-url' => $this->getEntityTable()->getControl()->link('this'),
			'data-control' => $componentName,
			'value' => $defaultValue
		]);
		$item->setHtml($field);
	}
}
