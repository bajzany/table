## Table

Nette Table for baseUsing or DoctrineEntity

Required:
- php: ^7.2
- [nette/di](https://packagist.org/packages/nette/di)
- [nette/application](https://packagist.org/packages/nette/application)
- [nette/bootstrap](https://packagist.org/packages/nette/bootstrap)
- [latte/latte](https://packagist.org/packages/latte/latte)
- [nette/utils](https://packagist.org/packages/nette/utils)
- [chomenko/app-webloader](https://packagist.org/packages/chomenko/app-webloader)
- [kdyby/events](https://packagist.org/packages/kdyby/events)
- [kdyby/doctrine](https://packagist.org/packages/kdyby/doctrine)
- [bajzany/paginator](https://packagist.org/packages/bajzany/paginator)


#### Instalation

- Composer instalation

		composer require bajzany/table dev-master


- Register into Nette Application

		extensions:
    		BajzanyTable: Bajzany\Table\DI\TableExtensions


- Now register into Presenter

		/**
		 * @var TableFactory @inject
		 */
		public $tableFactory;
		

#### BaseTable

It's very simple Html table object. For creating this component use:

	public function createComponentTable()
	{
		$table = $this->tableFactory->createTable();
		$wrapped = $table->getTableWrapped();

		$wrapped->setAttribute('class','table table-striped');
		$caption = $wrapped->getCaption();
		$caption->setText('BASE TABLE');

		$header = $wrapped->getHeader();
		$header->createItem('head1');
		$header->createItem('head2');
		$header->createItem('head3');

		$body = $wrapped->getBody();
		$row = $body->createRow();
		$item = $row->createItem('1');
		$item = $row->createItem('2');
		$item = $row->createItem('3');

		$row = $body->createRow();
		$item = $row->createItem('1');
		$item = $row->createItem('2');
		$item = $row->createItem('3');

		$footer = $wrapped->getFooter();
		$item = $footer->createItem('footer1');
		$item = $footer->createItem('footer2');
		$item = $footer->createItem('footer3');


		return $this->tableFactory->createComponentTable($table);
	}
	
TableWrapped have header, body and footer. Each of this section have children as html item

Render in .latte:

	<div class="box-body">
		{control table}
	</div>
	
![Table](src/Doc/image1.PNG?raw=true)


#### EntityTable

EntityTable is used for work with entity.

	public function createComponentTableEntity()
	{
		// Register table for entity User
		$table = $this->tableFactory->createEntityTable(User::class);
		
		// Paginator section
		$paginator = $table->getPaginator();
		$item = $paginator->getPaginatorWrapped()->createItem();
		$item->getContent()->addHtml(Html::el('')->setText('-1 Léňa'));
		$table->getPaginator()->setPageSize(2);
		
		// Table Wrapped section
		$wrapped = $table->getTableWrapped();
		$caption = $wrapped->getCaption();
		$caption->setText('ENTITY TABLE');

		// TableColum Section
		$column = $table->createColumn();
		$column->setLabel('Email');
		$column->setPattern('Email uzivatele {{ email }}');
		$column->setFooter('Email');
		$column->setBodyItemCallable(function (Item $item, User $entity){
			$item->getParent()->setAttribute('style','background-color:#d42d2d');
		});
		$column->setHeaderItemCallable(function (HeaderItem $item){
			$item->getParent()->setAttribute('style','background-color:#9e0000;color:#FFFFFF;');
		});
		$column->setFooterItemCallable(function (Item $item){
			$item->setAttribute('style','background-color:#9e0000;color:#FFFFFF;');
		});

		$column = $table->createColumn();
		$column->setLabel('Aktivni ucet');
		$column->setBodyItemCallable(function (Item $item, User $entity){
			$item->setText($entity->isActive() ? 'ANO' : 'NE');
		});
		$column->setFooterItemCallable(function (Item $item){
			$item->setAttribute('style','background-color:#9e9000;color:#FFFFFF;');
		});
		$column->setFooter('Aktivni ucet');

		$column = $table->createColumn();
		$column->setLabel('Avatar');
		$column->setBodyItemCallable(function (Item $item, User $entity){
			$image = new Image();
			$image->setClass('img-responsive');
			$image->setTooltip('AVATAR');
			$image->setImage('data:image/png;base64, '.$entity->getAvatarBase64());

			$item->setHtml($image);
		});
		$column->setFooterItemCallable(function (Item $item){
			$item->setAttribute('style','background-color:#1e9000;color:#FFFFFF;');
		});
		$column->setFooter('Avatar');


		$column = $table->createColumn();
		$column->setLabel('Actions');
		$column->setBodyItemCallable(function (Item $item, User $entity){
			$dropdown = new DropDown();

			$button = $dropdown->getButton();
			$button->setLabel('AKCE');
			$button->setClass('btn btn-success');

			$href = new Href('Link','/','link');

			$dropdown->addItem($href);

			$href = new Href('Link2','/','link');
			$dropdown->addItem($href);
			$item->setHtml($dropdown);
		});
		$column->setFooter('Actions');


		return $this->tableFactory->createComponentTable($table);
	}
	
- Register Table Section:
	- For register entityTable you must first set entity class into $this->tableFactory->createEntityTable(User::class);

- Paginator section 
	- Entity table contain bajzany/paginator, for more detail click on this [link](https://github.com/bajzany/paginator)
	- You can change paginator pageSize, add another items into list pagination.
	
- TableWrapped section
 	- TableWrapped is same as BaseTable TableWrapped

- TableColumn section
	- Function $table->createColumn() create new col for table in every row 
	- Column have this settings:
		- setLabel(string)
			 - Label is for th tag in table, it's only name col
		- setPattern(string)
			- Pattern example( User email {{ email }} ) This show you in field column "User email (specific email from entity user)"
		- setFooter(string)
			- Footer is same as Label, it's only differently position
		- setHeaderItemCallable(function(HeaderItem){})
			- This header function get you in anonymous function HeaderItem. You can edit it. Its nette Html object 
		- setBodyItemCallable(function(Item, Entity){})
			- This body function get you in anonymous function Item. You can edit it. Its nette Html object 
			- Get you so Entity for reading properity and use it for edit Body Item.
		- setFooterItemCallable(function(Item){})
			- This footer function get you in anonymous function Item. You can edit it. Its nette Html object 
		
For rendering table use .latte

	<div class="box-body">
		{control tableEntity}
	</div>
	
	{*Paginator section*}
	<div class="box-footer clearfix">
		{control tableEntity:paginator}
	</div>
