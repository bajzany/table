<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 09.01.2019
 */

namespace Bajzany\Table;

use Bajzany\Paginator\IPaginationControl;
use Bajzany\Paginator\IPaginator;
use Bajzany\Paginator\Paginator;
use Bajzany\Table\ColumnDriver\ColumnDriver;
use Bajzany\Table\EntityTable\Column;
use Bajzany\Table\EntityTable\IColumn;
use Bajzany\Table\Exceptions\TableException;
use Bajzany\Table\TableObjects\Item;
use Bajzany\Table\TableObjects\TableWrapped;
use Doctrine\Common\Collections\Criteria;
use Nette\Application\UI\Component;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Localization\ITranslator;
use Nette\Utils\Html;

/**
 * @method onBuild(Table $table)
 * @method onPreRender(Table $table)
 * @method onPostRender(Table $table)
 */
abstract class Table extends Control
{

	const PATTERN_REGEX = "~{{\s([a-zA-Z_0-9]+)\s}}~";
	const SESSION_EXPIRATION = '10 minutes';
	const TABLE_PREFIX = 'TABLE_';

	use TableUtils;

	/**
	 * @var TableWrapped
	 */
	protected $tableWrapped;

	/**
	 * @var callable[]
	 */
	public $onBuild = [];

	/**
	 * @var callable[]
	 */
	public $onPreRender = [];

	/**
	 * @var callable[]
	 */
	public $onPostRender = [];

	/**
	 * @var bool
	 */
	protected $build = FALSE;

	/**
	 * @var Paginator
	 */
	protected $paginator;

	/**
	 * @var ColumnDriver
	 */
	protected $columnDriver;

	/**
	 * @var IColumn[]
	 */
	private $columns = [];

	/**
	 * @var RowsCollection|null
	 */
	protected $rowsCollection;

	/**
	 * @var IPaginationControl
	 */
	protected $paginationControl;

	/**
	 * @var bool
	 */
	private $rendered = FALSE;

	/**
	 * @var ITranslator|null
	 */
	protected $translator;

	/**
	 * @var Filters
	 */
	protected $filter;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var Session
	 */
	private $session;

	private $criteria;

	/**
	 * @var string|null
	 */
	private $snippetName = NULL;

	public function __construct()
	{
		$this->tableWrapped = new TableWrapped($this);
		$this->paginator = new Paginator();
		$this->columnDriver = new ColumnDriver();
		$this->filter = new Filters($this);
		$this->criteria = new Criteria();
		parent::__construct();
	}

	/**
	 * @param Session $session
	 */
	public function injectSession(Session $session)
	{
		$this->session = $session;
	}

	/**
	 * @param Config $config
	 */
	public function injectConfig(Config $config)
	{
		$this->config = $config;
		$this->translator = $this->config->getTranslator();
	}

	/**
	 * @param IPaginationControl $paginationControl
	 */
	public function injectPaginator(IPaginationControl $paginationControl)
	{
		$this->paginationControl = $paginationControl;
	}

	/**
	 * @param ITranslator $translator
	 */
	public function setTranslator(?ITranslator $translator)
	{
		$this->translator = $translator;
		if ($this->getParent()) {
			$this->template->setTranslator($translator);
		}
	}

	/**
	 * @return Translator|null
	 */
	public function getTranslator(): ?ITranslator
	{
		return $this->translator;
	}

	/**
	 * @param mixed $presenter
	 */
	public function attached($presenter)
	{
		parent::attached($presenter);
		if ($this->presenter->isAjax()) {
			$this->presenter->redrawControl($this->snippetName);
		}
		if ($this->translator) {
			$this->template->setTranslator($this->translator);
		}

		$this->session = $this->session->getSection(self::TABLE_PREFIX . $this->getComponentName($this));
		$this->session->setExpiration(self::SESSION_EXPIRATION);
		$this->build();
	}

	protected function build()
	{
		$rowsCollection = new RowsCollection();
		$this->create($rowsCollection);
		$this->rowsCollection = $rowsCollection;
		foreach ($this->getColumns() as $column) {
			if ($column->isSortable()) {
				$column->buildSortableColumn($this);
			}
			if ($column->isSearchable()) {
				$column->buildSearchColumn($this);
			}
		}
		$this->paginator->setCount($rowsCollection->count());
		$this->onBuild($this);
	}

	/**
	 * @param RowsCollection $rowsCollection
	 * @return RowsCollection
	 */
	abstract protected function create(RowsCollection $rowsCollection);

	public function render()
	{
		$this->onPreRender($this);
		if (!$this->rendered) {
			$this->createHeader();
			$this->createBody();
			$this->createFooter();
		}

		$this->getTableWrapped()->render();
		$this->onPostRender($this);
		$this->rendered = TRUE;
	}

	public function renderPaginator()
	{
		$paginatorComponent = $this->getComponent("paginator");
		$paginatorComponent->render();
	}

	/**
	 * @return \Bajzany\Paginator\PaginationControl
	 * @throws TableException
	 */
	public function createComponentPaginator()
	{
		$paginator = $this->getPaginator();
		if (empty($paginator)) {
			throw TableException::paginatorIsNotSet(get_class($this));
		}
		return $this->paginationControl->create($paginator);
	}

	/**
	 * @param string $message
	 * @param mixed $count
	 * @return mixed
	 */
	protected function translate($message, $count = NULL)
	{
		if ($this->translator && !empty($message)) {
			return call_user_func_array([$this->translator, "translate"], func_get_args());
		}
		return $message;
	}

	protected function createHeader()
	{
		$header = $this->getTableWrapped()->getHeader();
		foreach ($this->getColumns() as $column) {
			if (!$column->isAllowRender()) {
				continue;
			}
			$item = $header->createItem();

			if ($column->isSortable()) {
				$column->renderSortableColumn($item, $this);
			}
			if ($column->isSearchable()) {
				$column->renderSearchColumn($item, $this);
			} else {
				if (!empty($column->getLabel())) {
					$item->addHtml($this->translate($column->getLabel()));
				}
			}
			$column->onHeaderCreate($item, $column);
		}
	}

	/**
	 * @param bool $cutByPaginator
	 * @throws TableException
	 * @throws \ReflectionException
	 */
	protected function createBody(bool $cutByPaginator = TRUE)
	{
		$body = $this->getTableWrapped()->getBody();
		$list = $this->rowsCollection;
		$list = $list->matching($this->criteria);
		if ($cutByPaginator) {
			$from = ($this->paginator->getPageSize() * $this->paginator->getCurrentPage()) - $this->paginator->getPageSize();
			$list->slice($from, $this->paginator->getPageSize());
		}

		foreach ($list as $identifier => $data) {
			$origin = $this->getData($data);
			if (!$origin) {
				continue;
			}
			$row = $body->createRow();
			foreach ($this->getColumns() as $column) {
				if (!$column->isAllowRender()) {
					continue;
				}
				$item = $row->createItem();
				$this->usePattern($column, $origin, $item);
				$this->useComponents($item, $column, $identifier);
				$column->onBodyCreate($item, $data, $column);
			}
		}
	}

	/**
	 * @param mixed $data
	 * @return mixed
	 * @throws \ReflectionException
	 */
	protected function getData($data)
	{
		if (is_object($data)) {
			$origin = [];
			$ref = new \ReflectionClass($data);
			foreach ($ref->getProperties() as $property) {
				$property->setAccessible(TRUE);
				$origin[$property->getName()] = $property->getValue($data);
			}
			foreach ($ref->getMethods() as $method) {
				if (substr($method->getName(), 0, 2) == "__") {
					continue;
				}
				if (count($method->getParameters()) > 0) {
					continue;
				}
				if (!$method->isPublic()) {
					continue;
				}
				$method->setAccessible(TRUE);
				$origin[$method->getName()] = function () use ($method, $data) {
					return $method->invoke($data);
				};
			}
			return $origin;
		}
		return $data;
	}

	protected function createFooter()
	{
		$footer = $this->getTableWrapped()->getFooter();
		foreach ($this->getColumns() as $identifier => $column) {
			if (!$column->getFooter()) {
				continue;
			}
			if (!$column->isAllowRender()) {
				continue;
			}
			$item = $footer->createItem();
			$item->setHtml($this->translate($column->getFooter()));
			$column->onFooterCreate($item, $column);
		}
	}

	/**
	 * @param TableHtml $item
	 * @param Column $column
	 * @param string|integer $identifier
	 */
	protected function useComponents(TableHtml $item, Column $column, $identifier)
	{
		foreach ($column->getUsedComponents() as $name) {
			$component = $this->getComponent($name . "_" . $identifier);
			$item->addHtml($component);
		}
	}

	public function getComponent($name, $throw = TRUE, $args = [])
	{
		$exp = explode("_", $name);
		if (count($exp) == 2 && !isset($this->components[$name])) {

			$componentName = $exp[0];
			$identifier = $exp[1];
			$nextComponentName = NULL;

			if (strpos($identifier, '-') !== false) {
				$ex = explode("-", $identifier);
				$identifier = $ex[0];
				$nextComponentName = $ex[1];
			}

			$ucName = ucfirst($componentName);
			$method = 'createComponent' . $ucName;
			$data = $this->rowsCollection->get($identifier);

			if ($ucName !== $componentName && method_exists($this, $method) && (new \ReflectionMethod($this, $method))->getName() === $method) {
				/** @var Component $component */
				$component = $this->$method($componentName, $data);
				if ($nextComponentName) {
					$name = str_replace('-'.$nextComponentName, '', $name);
					$this->addComponent($component, $name);
					$nextComponent = $component->getComponent($nextComponentName);
					return $nextComponent;
				}

				$this->addComponent($component, $name);
				return $component;
			}
		}

		$component = parent::getComponent($name, $throw);
		return $component;
	}

	/**
	 * @return TableWrapped
	 */
	public function getTableWrapped(): TableWrapped
	{
		return $this->tableWrapped;
	}

	/**
	 * @return IPaginator
	 */
	public function getPaginator(): ?IPaginator
	{
		return $this->paginator;
	}

	/**
	 * @return ColumnDriver
	 */
	public function getColumnDriver(): ColumnDriver
	{
		return $this->columnDriver;
	}

	/**
	 * @return IColumn[]
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}

	/**
	 * @param string $key
	 * @return IColumn|null
	 */
	public function getColumn(string $key): ?IColumn
	{
		if ($this->issetColumnKey($key)) {
			return $this->columns[$key];
		}
		return NULL;
	}

	/**
	 * @param string $key
	 */
	public function removeColumn(string $key): void
	{
		if ($this->issetColumnKey($key)) {
			unset($this->columns[$key]);
			$this->columnDriver->removeAvailableColumn($key);
		}
	}

	/**
	 * @param string $key
	 * @return Column
	 * @throws TableException
	 */
	public function createColumn(string $key)
	{
		if ($this->issetColumnKey($key)) {
			throw TableException::columnKeyExist($key);
		}

		$column = new Column($key);
		$this->columns[$key] = $column;
		$this->columnDriver->addAvailableColumn($key, $column);
		return $column;
	}

	/**
	 * @param IColumn $column
	 * @return $this
	 * @throws TableException
	 */
	public function addColumn(IColumn $column)
	{
		if ($this->issetColumnKey($column->getKey())) {
			throw TableException::columnKeyExist($column->getKey());
		}
		$this->columns[$column->getKey()] = $column;
		$this->columnDriver->addAvailableColumn($column->getKey(), $column);
		return $this;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function issetColumnKey(string $key): bool
	{
		if (array_key_exists($key, $this->columns)) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * @param IColumn $column
	 * @param array $origin
	 * @param Item $item
	 * @throws TableException
	 */
	protected function usePattern(IColumn $column, array $origin, Item $item)
	{
		if (!empty($column->getPattern())) {
			$labelItem = $column->getPattern();
			$templateKeys = $this->getPatternsKeys($column->getPattern());
			foreach ($templateKeys as $i => $key) {
				if (!array_key_exists($key["name"], $origin)) {
					throw TableException::doesNotExistField($key["name"], $this->getEntityClass());
				}

				$value = $origin[$key["name"]];
				$value = $this->applyFilters($column, $value);

				$templateKeys[$i]["translate"] = $value;
			}

			foreach ($templateKeys as $key) {
				$translate = $key['translate'];

				if ($translate instanceof \Closure) {
					$translate = call_user_func($translate);
				}

				if (!$translate instanceof Html) {
					$translate = htmlspecialchars($translate);
				}
				$labelItem = str_replace($key["search"], $translate, $labelItem);
			}
			$item->setHtml($labelItem);
		}
	}

	/**
	 * @param IColumn $column
	 * @param mixed $value
	 * @return mixed
	 */
	protected function applyFilters(IColumn $column, $value)
	{
		foreach ($column->getFilters() as $filter) {
			$value = call_user_func_array($filter["callable"], array_merge([$value, $column], $filter["config"]));
		}
		return $value;
	}

	/**
	 * @param string $template
	 * @return array
	 */
	protected function getPatternsKeys(string $template): array
	{
		$keys = [];
		preg_match_all(self::PATTERN_REGEX, $template, $matches);
		foreach ($matches[0] as $index => $value) {
			$name = $matches[1][$index];
			$keys[$name] = [
				"search" => $value,
				"name" => $name,
				"translate" => "",
			];
		}
		return $keys;
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
	 * @return SessionSection
	 */
	public function getSession(): SessionSection
	{
		return $this->session;
	}

	/**
	 * @return RowsCollection|null
	 */
	public function getRowsCollection(): ?RowsCollection
	{
		return $this->rowsCollection;
	}

	/**
	 * @return Criteria
	 */
	public function getCriteria(): Criteria
	{
		return $this->criteria;
	}

	/**
	 * @param string|null $snippetName
	 * @return $this
	 */
	public function setSnippetName(?string $snippetName)
	{
		$this->snippetName = $snippetName;
		return $this;
	}

	public function enableAjaxPaginator()
	{
		$this->getPaginator()->getPaginatorWrapped()->setLinkClass('ajax');
	}

}
