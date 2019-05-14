<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 09.01.2019
 */

namespace Bajzany\Table;

use Bajzany\Paginator\QueryPaginator;
use Bajzany\Table\EntityTable\Column;
use Bajzany\Table\EntityTable\IColumn;
use Bajzany\Table\EntityTable\ISearchColumn;
use Bajzany\Table\Events\Listener;
use Bajzany\Table\Events\TableEvents;
use Bajzany\Table\Exceptions\TableException;
use Bajzany\Table\TableObjects\Footer;
use Bajzany\Table\TableObjects\Item;
use Doctrine\Common\Persistence\ObjectRepository;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\QueryBuilder;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;
use Nette\Utils\Html;

class EntityTable extends Table
{

	const PATTERN_REGEX = "~{{\s([a-zA-Z_0-9]+)\s}}~";
	const EVENT_ON_BUILD_QUERY = 'EVENT_ON_BUILD_QUERY';

	/**
	 * @var string
	 */
	private $entityClass;

	/**
	 * @var array
	 */
	private $entities = [];

	/**
	 * @var EntityManager
	 */
	protected $entityManager;

	/**
	 * @var ObjectRepository
	 */
	protected $entityRepository;

	/**
	 * @var array
	 */
	protected $where = [];

	/**
	 * @var array
	 */
	protected $sort = [];

	/**
	 * @var QueryBuilder
	 */
	private $queryBuilder;

	/**
	 * @var TableEvents
	 */
	private $tableEvents;

	/** @var Listener  */
	private $tableListener;

	/**
	 * @var array
	 */
	protected $registerComponents = [];

	/**
	 * @param string $entityClass
	 * @param EntityManager $entityManager
	 * @param TableEvents $tableEvents
	 */
	public function __construct(string $entityClass, EntityManager $entityManager, TableEvents $tableEvents)
	{
		parent::__construct();
		$this->entityClass = $entityClass;
		$this->entityManager = $entityManager;
		$this->entityRepository = $this->getEntityManager()->getRepository($this->getEntityClass());
		$this->queryBuilder = $this->entityRepository->createQueryBuilder('e');

		$this->paginator = new QueryPaginator();

		/** TABLE SUBSCRIBER REGISTRATION EVENTS */
		$this->tableEvents = $tableEvents;
		$this->tableListener = new Listener();
		$group = $this->tableEvents->getEntityGroup($this->entityClass);
		if (!empty($group)) {
			foreach ($group->getEvents() as $event) {
				foreach ($event->getSubscribedEvents() as $eventType => $subscribedEvent) {
					$this->tableListener->create($eventType, [$event, $subscribedEvent]);
				}
			}
		}
	}

	/**
	 * @throws TableException
	 */
	public function execute(TableControl $control)
	{
		if (!$this->isBuild()) {
			throw TableException::notBuild(get_class($this));
		}

		$this->emitPreRender();

		$this->createHeader();
		$this->createBody();
		$this->createFooter();

		if (empty($this->getTableWrapped()->getFooter()->getItems())) {
			foreach ($this->getTableWrapped()->getChildren() as $key => $child) {
				if ($child instanceof Footer) {
					$this->getTableWrapped()->removeChild($key);
				}
			}
		}

		if (empty($this->getTableWrapped()->getCaption()->getChildren())) {
			foreach ($this->getTableWrapped()->getChildren() as $key => $child) {
				if ($child instanceof TableHtml && $child->getName() == 'caption') {
					$this->getTableWrapped()->removeChild($key);
				}
			}
		}

		$this->getTableWrapped()->render();

		$this->emitPostRender();
	}

	/**
	 * @param IContainer $container
	 * @return ITable
	 */
	public function build(IContainer $container): ITable
	{
		if ($this->isBuild()) {
			return $this;
		}

		$this->control = $container;

		$this->buildQuery($container);
		return parent::build($container);
	}

	/**
	 * @param IContainer $container
	 */
	private function buildQuery(IContainer $container)
	{
		/**
		 * SEARCH COLUMN BUILD AND EXECUTE EVENTS
		 */
		foreach ($this->getColumns() as $column) {
			if (!$column instanceof ISearchColumn) {
				continue;
			}

			$column->build($this);
		}

		$this->queryBuilder->whereCriteria($this->getWhere());
		foreach ($this->getSort() as $by => $sort) {
			$this->queryBuilder->addOrderBy($by, $sort);
		}

		$this->tableListener->emit(self::EVENT_ON_BUILD_QUERY, $this);

		$query = $this->queryBuilder->getQuery();
		$paginator = $this->getPaginator();

		if ($paginator instanceof QueryPaginator) {
			$paginator->setQuery($query);
			$query = $paginator->getQuery();
			// IT'S BECAUSE ATTACH FUNCTION IN TABLECONTROL
			$container->getComponent(TableControl::PAGINATOR_NAME);
		}

		$this->entities = $query->getResult();
	}

	private function createHeader()
	{
		$header = $this->getTableWrapped()->getHeader();

		foreach ($this->getColumns() as $column) {
			if (!$column->isAllowRender()) {
				continue;
			}
			$item = $header->createItem();
			if ($column instanceof ISearchColumn) {
				$column->render($item);
			} else {
				$item->setHtml($column->getLabel());
			}

			$listener = $column->getListener();
			$listener->emit(Column::ON_HEADER_CREATE, $item, $column);
		}
	}

	private function createBody()
	{
		$body = $this->getTableWrapped()->getBody();
		foreach ($this->entities as $entity) {
			$row = $body->createRow();

			$origin = $this->getEntityManager()->getUnitOfWork()->getOriginalEntityData($entity);
			if (empty($origin)) {
				continue;
			}
			$identifiers = $this->getEntityManager()->getUnitOfWork()->getEntityIdentifier($entity);
			$origin = array_merge($origin, $identifiers);
			foreach ($this->getColumns() as $column) {
				if (!$column->isAllowRender()) {
					continue;
				}
				$item = $row->createItem();
				$this->usePattern($column, $origin, $item);
				$listener = $column->getListener();
				$listener->emit(Column::ON_BODY_CREATE, $item, $entity);
			}
		}
	}

	private function createFooter()
	{
		$footer = $this->getTableWrapped()->getFooter();
		foreach ($this->getColumns() as $column) {
			if (!$column->getFooter()) {
				continue;
			}

			if (!$column->isAllowRender()) {
				continue;
			}
			$item = $footer->createItem();
			$item->setHtml($column->getFooter());

			$listener = $column->getListener();
			$listener->emit(Column::ON_FOOTER_CREATE, $item);
		}
	}

	/**
	 * @param IColumn $column
	 * @param array $origin
	 * @param Item $item
	 * @throws TableException
	 */
	private function usePattern(IColumn $column, array $origin, Item $item)
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
	private function applyFilters(IColumn $column, $value)
	{
		foreach ($column->getFilters() as $filter) {
			$value = call_user_func_array($filter["callable"], array_merge([$value, $column], $filter["config"]));
		}
		return $value;
	}

	/**
	 * @return EntityManager
	 */
	public function getEntityManager(): EntityManager
	{
		return $this->entityManager;
	}

	/**
	 * @return string
	 */
	public function getEntityClass(): string
	{
		return $this->entityClass;
	}

	/**
	 * @param string $entityClass
	 * @return $this
	 */
	public function setEntityClass($entityClass)
	{
		$this->entityClass = $entityClass;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getEntities(): array
	{
		return $this->entities;
	}



	/**
	 * @return ObjectRepository
	 */
	public function getEntityRepository(): ObjectRepository
	{
		return $this->entityRepository;
	}

	/**
	 * @param ObjectRepository $entityRepository
	 * @return $this
	 */
	public function setEntityRepository($entityRepository)
	{
		$this->entityRepository = $entityRepository;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getWhere(): array
	{
		return $this->where;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return $this
	 */
	public function addWhere(string $key, string $value)
	{
		$this->where[$key] = $value;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getSort(): array
	{
		return $this->sort;
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return $this
	 */
	public function addSort(string $key, string $value)
	{
		$this->sort[$key] = $value;
		return $this;
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
	 * @return QueryBuilder
	 */
	public function getQueryBuilder(): QueryBuilder
	{
		return $this->queryBuilder;
	}

	/**
	 * @param QueryBuilder $queryBuilder
	 */
	public function setQueryBuilder(QueryBuilder $queryBuilder)
	{
		$this->queryBuilder = $queryBuilder;
	}

	/**
	 * @param IContainer $control
	 * @param string $name
	 * @return string
	 */
	public function getComponentName(IContainer $control, string $name = '')
	{
		if ($control instanceof Presenter) {
			return $name;
		}

		if (empty($name)) {
			$controlName = $control->getName();
		} else {
			$controlName = $control->getName() . '-' . $name;
		}

		return $this->getComponentName($control->getParent(), $controlName);
	}

	/**
	 * @param string $name
	 * @param string $className
	 */
	public function addRegisterComponent(string $name, string $className)
	{
		$this->registerComponents[$name] = $className;
	}

	/**
	 * @return array
	 */
	public function getRegisterComponents(): array
	{
		return $this->registerComponents;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function getRegisterComponentByName(string $name): ?string
	{
		if (array_key_exists($name, $this->registerComponents)) {
			return $this->registerComponents[$name];
		}
		return NULL;
	}

	/**
	 * @param string $name
	 * @param int $entityId
	 * @return string
	 * @throws TableException
	 */
	public function getComponentHtml(string $name, int $entityId)
	{
		$component = $this->getControl()->getComponent($name . '_' . $entityId, FALSE);
		if ($component && method_exists($component, 'render')) {
			ob_start();
			$component->render();
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}

		return '';
	}

}
