<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 22.12.2018
 */

namespace Bajzany\Table\TableObjects;

use Bajzany\Table\TableHtml;

class TableWrapped extends TableHtml
{
	/**
	 * @var Header
	 */
	private $header;

	/**
	 * @var Body
	 */
	private $body;

	/**
	 * @var Footer
	 */
	private $footer;

	/**
	 * @var TableHtml
	 */
	private $caption;

	public function __construct()
	{
		$this->header = new Header();
		$this->body = new Body();
		$this->footer = new Footer();
		$this->caption = $this->createCaption();
		$this->createTable();
	}

	public function render($indent = NULL)
	{
		echo parent::render($indent);
	}

	/**
	 * @return TableHtml
	 */
	private function createTable(): TableHtml
	{
		$this->setName('table');
		$this->setAttribute('class','table');
		$this->addHtml($this->getCaption());
		$this->addHtml($this->getHeader());
		$this->addHtml($this->getBody());
		$this->addHtml($this->getFooter());
		return $this;
	}

	/**
	 * @return TableHtml
	 */
	public function createCaption(): TableHtml
	{
		return TableHtml::el('caption');
	}

	/**
	 * @return Header
	 */
	public function getHeader(): Header
	{
		return $this->header;
	}

	/**
	 * @return Body
	 */
	public function getBody(): Body
	{
		return $this->body;
	}

	/**
	 * @return TableHtml
	 */
	public function getCaption(): TableHtml
	{
		return $this->caption;
	}

	/**
	 * @return Footer
	 */
	public function getFooter(): Footer
	{
		return $this->footer;
	}
}
