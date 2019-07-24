## Table

Nette Table for baseUsing or DoctrineEntity

Required:
- php: ^7.2
- [nette/di](https://packagist.org/packages/nette/di)
- [nette/application](https://packagist.org/packages/nette/application)
- [nette/bootstrap](https://packagist.org/packages/nette/bootstrap)
- [latte/latte](https://packagist.org/packages/latte/latte)
- [nette/utils](https://packagist.org/packages/nette/utils)
- [kdyby/events](https://packagist.org/packages/kdyby/events)
- [kdyby/doctrine](https://packagist.org/packages/kdyby/doctrine)
- [bajzany/paginator](https://packagist.org/packages/bajzany/paginator)
- [nettpack/stage](https://packagist.org/packages/nettpack/stage)


#### Instalation

- Composer instalation
````bash
composer require bajzany/table dev-master
````

- Register into Nette Application

````neon
extensions:
	BajzanyTable: Bajzany\Table\DI\TableExtensions
````
 	
- Set translator into table

````neon
BajzanyTable:
	translator: Chomenko\Translator\Translator
````

- Now create component table and his interface, for example:

	- ITestTable class
	````php
	<?php

    interface ITestTable
    {
    
    	/**
    	 * @return TestTable
    	 */
    	public function create(): TestTable;
    
    }

	````
	
	- Component have two options, BaseTable and EntityTable:
	
	BaseTable:
	- You must set data manualy ``$rowsCollection->add()``
	````php
	<?php
    
    use Bajzany\Table\RowsCollection;
    use Bajzany\Table\Table;

    class TestTable extends Table;
    {
    	
		/**
		 * @param RowsCollection $rowsCollection
		 * @throws \Bajzany\Table\Exceptions\TableException
		 */
		protected function create(RowsCollection $rowsCollection)
		{
			$rowsCollection->add(['failure' => '0', 'emailProbe' => 'a']);
			$rowsCollection->add(['failure' => '1', 'emailProbe' => 'd']);
		
			$this->getPaginator()->setPageSize(4);
		
			$this->createColumn("failure")
				->setSearchable(TRUE)
				->setLabel("Failure")
				->setPattern("{{ failure }}")
				->setSearchSelectOptions([
					'0' => 'Ne',
					'1' => 'Ano',
				])
				->addFilter([$this->filter, "boolLabel"], ["label-danger", "label-success"]);
		
			$this->createColumn("emailProbe")
				->setSearchable(TRUE)
				->setSortable(TRUE)
				->setLabel("Email")
				->setPattern("{{ emailProbe }}");
		}
    
    }
	````
	
	EntityTable: 
	- Data into entity table has been set with queryBuilder like ``getEntityClass()``
	````php
	<?php
    
    use Bajzany\Table\EntityTable;
    use Bajzany\Table\RowsCollection;
    
    class TestEntityTable extends EntityTable 
    {
    
    	/**
    	 * @return string
    	 */
    	public function getEntityClass(): string
    	{
    		return EntityClass::class;
    	}
    
    	public function searchActionFailure(EntityTable $table, $selectValue)
    	{
    		$table->getQueryBuilder()->andWhere("e.failure LIKE :failure")
    			->setParameter('failure', '%' . $selectValue . '%');
    	}
    
    	/**
    	 * @param RowsCollection $rowsCollection
    	 * @throws \Bajzany\Table\Exceptions\TableException
    	 */
    	protected function create(RowsCollection $rowsCollection)
    	{
    		$this->addSort("e.date", "DESC");
    		$this->getPaginator()->setPageSize(4);
    
    		$this->createColumn("failure")
    			->setSearchable(TRUE)
    			->setSortable(TRUE)
    			->setLabel("Failure")
    			->setPattern("{{ failure }}")
    			->addFilter([$this->filter, "boolLabel"], ["label-danger", "label-success"])
    			->setSearchSelectOptions([
    				'0' => 'Ne',
    				'1' => 'Ano',
    			])
    			->onSearchAction[] = [$this, 'searchActionFailure'];
    
    		$this->createColumn("emailProbe")
    			->setSearchable(TRUE)
    			->setLabel("Email")
    			->setPattern("{{ emailProbe }}");
    
    		$this->createColumn("statusMessage")
    			->setLabel("Status")
    			->setPattern("{{ statusMessage }}");
    
    		$this->createColumn("ip")
    			->setLabel("IP")
    			->setPattern("{{ ip }}");
    
    		$this->createColumn("date")
    			->setLabel("date")
    			->setSortable(TRUE)
    			->setPattern("{{ date }}")
    			->addFilter([$this->filter, "dateTime"]);
			$this->createColumn("date")
				->useComponent("customComponent")
				->onBodyCreate[] = [$this, "dateColumn"];
			$this->createColumn("option")
				->onBodyCreate[] = [$this, "optionColumn"];
		
			
    	}
   	
		/**
		 * @param string $name
    	 * @param EntityClass $entity
    	 */
    	public function createComponentCustomComponent($name, EntityClass $entity)
		{ 
    		return $this->customComponent->create();
    	}
   	
   	

    	public function optionColumn(Item $item, EntityClass $entity)
		{
		}
   	
    
    }
	````
- ``getEntityClass`` function:
	- This will be return class of entity

- In function create you have access to paginator ``$this->getPaginator()`` 
	- Entity table contain bajzany/paginator, for more detail click on this [link](https://github.com/bajzany/paginator)
	- You can change paginator pageSize, add another items into list pagination.
	
- createColumn function:
	- Function ``$this->createColumn('identificator')`` create new col for table in every row 
	- Column have this settings:
		- ``setLabel(string)``
			 - Label is for th tag in table, it's only name col
		- ``setPattern(string)``
			- Pattern example( User email {{ email }} ) This show you in field column "User email (specific email from entity user)"
		- ``setFooter(string)``
			- Footer is same as Label, it's only differently position
		- ``useComponent("customComponent")``
			- For rendering component into table
		- CallableFunctions: 
			- ``onBodyCreate[] = callable``
				- In Callable get this parameters: Item(HtmlObject), Entity
			- ``onHeaderCreate[] = callable``
				- In Callable get this parameters: Item(HtmlObject), Column
			- ``onFooterCreate[] = callable``
				- In Callable get this parameters: Item(HtmlObject), Column
			- ``onSearchAction[] = callable``
				- Must be set ``setSearchable(TRUE)``
				- For create select list ``setSearchSelectOptions([])``
					- for example ``['0' => 'No','1' => 'Yes']``
				- In Callable get this parameters: Table, InputValue
			- ``onSortingAction[] = callable``
				- Must be set ``setSortable(TRUE)``
				- In Callable get this parameters: Table, SortValue
			- Entity table have also callable ``onBuildQuery[]`` 
				- In Callable get this parameters: EntityTable, QuieryBuilder

- Now register into Presenter
	````php
	<?php
	
	/**
	 * @var ITestEntityTable @inject
	 */
	public $testTable;
	
	/**
	 * @param IGroupTable $groupTable
	 * @return GroupTable
	 */
	public function createComponentTestList(): GroupTable
	{
		$table = $testTable->create();
		return $table;
	}
	````
		
#### For rendering table use .latte

	<div class="box-body">
		{control testList}
	</div>
	
	{*Paginator section*}
	<div class="box-footer clearfix">
		{control testList:paginator}
	</div>

#### For register subscriber on table events

- Use @Tag annotation for register new listener on Table ``{TableExtensions::TAG_EVENT=EntityClass::class}`` - EntityClass::class is entity which you can listening
- ``onBuildQuery(EntityTable $entityTable)`` - this is event when will be call before render table, just change queryBuilder query for specific select entities
````php
<?php
namespace Bundles\User\Model;

use Bajzany\Table\EntityTable;
use Bajzany\Table\Listener\ITableSubscriber;
use Chomenko\AutoInstall\Config\Tag;
use Bajzany\Table\DI\TableExtensions;

/**
 * @Tag({TableExtensions::TAG_EVENT=EntityClass::class})
 */
class UserTableSubscriber implements ITableSubscriber
{

	public function onBuildQuery(EntityTable $entityTable)
	{
	}

}
````
