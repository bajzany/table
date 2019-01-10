<?php
/**
 * Author: Radek Zíka
 * Email: radek.zika@dipcom.cz
 * Created: 09.01.2019
 */

namespace Bajzany\Table;

use Bajzany\Paginator\QueryPaginator;
use Bajzany\Table\EntityTable\Column;
use Bajzany\Table\Exceptions\TableException;
use Bajzany\Table\TableObjects\Item;
use Doctrine\Common\Persistence\ObjectRepository;
use Kdyby\Doctrine\EntityManager;
use Kdyby\Doctrine\QueryBuilder;
use Nette\ComponentModel\IContainer;

class EntityTable extends Table
{

	const PATTERN_REGEX = "~{{\s([a-zA-Z_0-9]+)\s}}~";

	/**
	 * @var string
	 */
	private $entityClass;

	/**
	 * @var array
	 */
	private $entities = [];

	/**
	 * @var Column[]
	 */
	private $columns = [];

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
	 * @param string $entityClass
	 * @param EntityManager $entityManager
	 */
	public function __construct(string $entityClass, EntityManager $entityManager)
	{
		parent::__construct();
		$this->entityClass = $entityClass;
		$this->entityManager = $entityManager;
		$this->entityRepository = $this->getEntityManager()->getRepository($this->getEntityClass());
		$this->queryBuilder = $this->entityRepository->createQueryBuilder('e');

		$this->paginator = new QueryPaginator();

	}

	/**
	 * @throws TableException
	 */
	public function execute()
	{
		if (!$this->isBuild()) {
			throw TableException::notBuild(get_class($this));
		}

		$this->emitPreRender();

		$this->createHeader();
		$this->createBody();
		$this->createFooter();

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

		$this->queryBuilder->whereCriteria($this->getWhere());
		foreach ($this->getSort() as $by => $sort) {
			$this->queryBuilder->addOrderBy($by, $sort);
		}

		$query = $this->queryBuilder->getQuery();
		$paginator = $this->getPaginator();

		if ($paginator instanceof QueryPaginator) {
			$paginator->setQuery($query);
			$query = $paginator->getQuery();
			// IT'S BECAUSE ATTACH FUNCTION IN TABLECONTROL
			$container->getComponent(TableControl::PAGINATOR_NAME);
		}

		$this->entities = $query->getResult();

		return parent::build($container);
	}

	private function createHeader()
	{
		$header = $this->getTableWrapped()->getHeader();

		foreach ($this->getColumns() as $column) {
			$item = $header->createItem();
			$item->setText($column->getLabel());

			if (!empty($column->getHeaderItemCallable())) {
				call_user_func_array($column->getHeaderItemCallable(), [$item]);
			}

		}
	}

	private function createBody()
	{
		$body = $this->getTableWrapped()->getBody();
		foreach ($this->entities as $entity) {
			$row = $body->createRow();
			$origin = $this->getEntityManager()->getUnitOfWork()->getOriginalEntityData($entity);
			foreach ($this->getColumns() as $column) {
				$item = $row->createItem();
				$this->usePattern($column, $origin, $item);

				if (!empty($column->getBodyItemCallable())) {
					call_user_func_array($column->getBodyItemCallable(), [$item, $entity]);
				}
			}
		}
	}

	/**
	 * @param Column $column
	 * @param array $origin
	 * @param Item $item
	 * @throws TableException
	 */
	private function usePattern(Column $column, array $origin, Item $item)
	{
		if (!empty($column->getPattern())) {
			$labelItem = $column->getPattern();
			$templateKeys = $this->getPatternsKeys($column->getPattern());
			foreach ($templateKeys as $i => $key) {
				if (!array_key_exists($key["name"], $origin)) {
					throw TableException::doesNotExistField($key["name"], $this->getEntityClass());
				}

				$value = $origin[$key["name"]];
				$templateKeys[$i]["translate"] = $value;
			}

			foreach ($templateKeys as $key) {
				$labelItem = str_replace($key["search"], $key["translate"], $labelItem);
			}
			$item->setText($labelItem);
		}
	}

	private function createFooter()
	{
		$footer = $this->getTableWrapped()->getFooter();
		foreach ($this->getColumns() as $column) {
			$item = $footer->createItem();
			$item->setText($column->getFooter());

			if (!empty($column->getFooterItemCallable())) {
				call_user_func_array($column->getFooterItemCallable(), [$item]);
			}
		}
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
	 * @return Column[]
	 */
	public function getColumns(): array
	{
		return $this->columns;
	}

	/**
	 * @return Column
	 */
	public function createColumn()
	{
		$column = new Column();
		$this->columns[] = $column;
		return $column;
	}

	/**
	 * @param Column $column
	 * @return $this
	 */
	public function addColumn(Column $column)
	{
		$this->columns[] = $column;
		return $this;
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

}
