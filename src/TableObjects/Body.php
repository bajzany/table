<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table\TableObjects;

use Bajzany\Table\TableHtml;

class Body extends TableHtml
{

	/**
	 * @var TableHtml
	 */
	private $tBody;

	public function __construct()
	{
		$this->tBody = $this->createTbody();
	}

	/**
	 * @return TableHtml
	 */
	public function createTbody(): TableHtml
	{
		$this->setName('tbody');
		return $this;
	}

	/**
	 * @return BodyRow
	 */
	public function createRow()
	{
		$row = new BodyRow();
		$this->addHtml($row);
		return $row;
	}

	/**
	 * @param BodyRow $row
	 * @return $this
	 */
	public function addRow(BodyRow $row)
	{
		$this->addHtml($row);
		return $this;
	}

	/**
	 * @return BodyRow[]
	 */
	public function getRows(): array
	{
		return $this->getChildren();
	}

	/**
	 * @return TableHtml
	 */
	public function getTbody(): TableHtml
	{
		return $this->tBody;
	}

}

