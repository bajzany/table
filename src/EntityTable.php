<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 09.01.2019
 */

namespace Bajzany\Table;

use Bajzany\Paginator\IPaginationControl;
use Bajzany\Paginator\QueryPaginator;
use Bajzany\Table\EntityTable\ISearchColumn;
use Doctrine\Common\Persistence\ObjectRepository;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\QueryBuilder;

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
		$this->buildQuery();
		$this->onBuild($this);
	}

	private function buildQuery()
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

		$this->onBuildQuery($this, $this->queryBuilder);

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
		$header = $this->getTableWrapped()->getHeader();
		foreach ($this->getColumns() as $column) {
			if (!$column->isAllowRender()) {
				continue;
			}
			$item = $header->createItem();
			if ($column instanceof ISearchColumn) {
				$column->render($item);
			} else {
				$item->setHtml($this->translate($column->getLabel()));
			}
			$column->onHeaderCreate($item, $column);
		}
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
