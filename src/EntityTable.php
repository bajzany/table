<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 09.01.2019
 */

namespace Bajzany\Table;

use Bajzany\Paginator\IPaginationControl;
use Bajzany\Paginator\QueryPaginator;
use Bajzany\Table\Listener\TableEvents;
use Doctrine\Common\Persistence\ObjectRepository;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\QueryBuilder;
use Nette\Http\Session;

/**
 * @method onBuildQuery(EntityTable $entityTable)
 */
abstract class EntityTable extends Table
{

	/**
	 * @var callable[]
	 */
	public $onBuildQuery = [];

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
	 * @var array
	 */
	protected $registerComponents = [];

	/**
	 * @var TableEvents
	 */
	private $tableEvents;

	public function __construct(Session $session, TableEvents $tableEvents)
	{
		parent::__construct($session);
		$this->tableEvents = $tableEvents;
	}

	/**
	 * @param EntityManager $entityManager
	 */
	public function injectEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		$this->entityRepository = $this->getEntityManager()->getRepository($this->getEntityClass());
		$this->queryBuilder = $this->entityRepository->createQueryBuilder('e');
		$this->paginator = new QueryPaginator();
	}

	/**
	 * @return string
	 */
	abstract public function getEntityClass(): string;

	protected function build()
	{
		$rowsCollection = new RowsCollection();
		$this->create($rowsCollection);
		$this->rowsCollection = $rowsCollection;
		$this->getComponent("paginator");

		foreach ($this->getColumns() as $column) {
			if ($column->isSortable()) {
				$column->buildSortableColumn($this);
			}
			if ($column->isSearchable()) {
				$column->buildSearchColumn($this);
			}
		}

		$this->buildQuery();
		$this->onBuild($this);
	}

	private function buildQuery()
	{
		$this->queryBuilder->whereCriteria($this->getWhere());
		foreach ($this->getSort() as $by => $sort) {
			$this->queryBuilder->addOrderBy($by, $sort);
		}

		$this->onBuildQuery($this, $this->queryBuilder);
		$group = $this->tableEvents->getEntityGroup($this->getEntityClass());
		if (!empty($group)) {
			foreach ($group->getEvents() as $event) {
				$event->onBuildQuery($this);
			}
		}

		$query = $this->queryBuilder->getQuery();
		$paginator = $this->getPaginator();

		if ($paginator instanceof QueryPaginator) {
			$paginator->setQuery($query);
			$query = $paginator->getQuery();
		}

		foreach ($query->getResult() as $item) {
			$this->rowsCollection->add($item);
		}
	}

	protected function createHeader()
	{
		parent::createHeader();
	}

	/**
	 * @param bool $cutByPaginator
	 * @throws Exceptions\TableException
	 * @throws \ReflectionException
	 */
	protected function createBody(bool $cutByPaginator = TRUE)
	{
		parent::createBody(FALSE);
	}

	/**
	 * @param object $entity
	 * @return array|mixed|void
	 */
	protected function getData($entity)
	{
		$origin = $this->getEntityManager()->getUnitOfWork()->getOriginalEntityData($entity);
		if (empty($origin)) {
			return;
		}
		$identifiers = $this->getEntityManager()->getUnitOfWork()->getEntityIdentifier($entity);
		$origin = array_merge($origin, $identifiers);

		return $origin;
	}

	/**
	 * @return EntityManager
	 */
	public function getEntityManager(): EntityManager
	{
		return $this->entityManager;
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

}
