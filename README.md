CakeDataGridder
===============

A CakePHP plugin to easy create DataGrids in CakePHP with functionalities as: pagination, sorting, searching etc.

## Requirements

The master branch has the following requirements:

* CakePHP 2.2.0 or greater.
* PHP 5.3.0 or greater.
* jQuery 1.9 or greater

## Installation

* Clone/Copy the files in this directory into `app/Plugin/DataGridder`
* Ensure the plugin is loaded in `app/Config/bootstrap.php` by calling `CakePlugin::load('DataGridder');`
* Include the helper in your `AppController.php`:
	* `public $helpers = array('DataGridder.DataGrid');`

## Documentation

In your view you can create a simple DataGrid by doing something like this:

```php
$this->DataGrid->addColumn('Id', 'User.id');
$this->DataGrid->addColumn('Username', 'User.username', array('sort' => true));

//Actions column to add actions to the row
$this->DataGrid->addColumn('Actions', null, array('type' => 'actions'));

//Add delete action to the actions column
$this->DataGrid->addAction('Delete', array('action' => 'delete'), array('User.id'));

echo $this->DataGrid->generate($users);
```

This code will generate a DataGrid with 3 columns for all the users we pass. The DataGrid Helper uses the Set::extract format to query the array data. So instead of `User.id` you can also use `/User/id`.

When you are using multiple DataGrids on one page you can reset the DataGrid settings:

```php
$this->DataGrid->reset();
```

### Tree
It is also possible to give a threaded result from `$this->Model->find('threaded')` to the DataGrid. The Helper recognizes the array structure and gives you extra functionalities, like expanding and collapsing.

### Columns
Adding a column can be done with several options, to add extra functionality to the column:

```php
$this->DataGrid->addColumn($label, $valuePath, $options);
```

#### String column
The String column is the default type of column. It retrieves the value of the array and places it inside the column. Optionally any URLs or e-mail addresses can be autolinked by enabling the `autoLink` column option.

```php
$options = array(
	'type'		=> 'string',
	'autoLink'	=> false
);
```

#### Image column
The Image column creates an Image tag of the retrieved value. When you have the BradCrumb/CakeImageCropResize plugin inside your project. You have extra functionality to resize and crop the image. For this type of column.

```php
$this->DataGrid->addColumn('Image', 'User.image', array(
	'type'		=> 'image',
	'resize'	=> array(
		'width'		=> 80,
		'height'	=> 80,
		'autocrop'	=> true,
		'crop'		=> true
	)
));
```

#### Switcher column
A switcher column switches a field between 2 states: enabled and disabled.

```php
$this->DataGrid->addColumn('Active', 'User.active', array(
	'type'				=> 'switcher',
	'options' => array(
		'url'				=> array(				//The url where the switch is triggered
			'action' => 'active'
		),
		'trailingParams'	=> array('User.id'),	//Parameters to add to the url
		'icon'				=> 'active' 			//Add the default Icon class and the active class
	)
));
```

#### Datetime column
A datetime column formats a datetime or a timestamp to a specified format and optionally a locale.
The format can be defined according to http://www.php.net/manual/en/function.date.php

```php
$this->DataGrid->addColumn(
	'Modified',
	'User.modified',
	array(
		'sort' => true,
		'type' => 'datetime',
		'format' => '%A %e %B %Y',
		'locale' => 'nl_NL.UTF8', // Optional
	)
);
```

#### Column with a user defined callback function
One or more examples to show the possibilities

```php
$this->DataGrid->addColumn(
	'Example A',
	array('User.first_name'),
	array(
		'type' => 'user_defined',
		'callback' => function ($value) {
			return strtoupper($value);
		}
	)
);
```
```php
$this->DataGrid->addColumn(
	'Example B',
	'User.last_name',
	array(
		'type' => 'user_defined',
		'callback' => function ($value) {
			return strtoupper($value);
		}
	)
);
```
```php
$this->DataGrid->addColumn(
	'Example C',
	null, // Do not supply any columns, so the complete record will be available as a parameter in the callback function
	array(
		'type' => 'user_defined',
		'callback' => function ($data) {
			return implode(' ', array_filter(array($data['User']['first_name'], $data['User']['last_name'])));
		}
	)
);
```
```php
$this->DataGrid->addColumn(
	'Example D',
	array('User.first_name','User.last_name'),
	array(
		'type' => 'user_defined',
		'callback' => function ($data) {
			return implode(' ', array_filter(array($data['User.first_name'], $data['User.last_name'])));
		}
	)
);
```

#### Actions column
The actions column is the container for the actions you can add with `addAction`.

```php
$this->DataGrid->addColumn('Actions', null, array(
	'type' => 'actions'
));

/**
 * @param $label
 * @param $url
 * @param $trailingParams
 * @param $options
 */
$this->DataGrid->addAction('Delete', array(
	'action' => 'delete'
), array(
	'User.id'
), $options);
```

By setting the `type` option for an action to 'image' and the `image` option to the URL to an image file, it is possible to create image links for actions:

```php
$this->DataGrid->addAction('Delete', array(
	'action' => 'delete'
), array(
	'User.id'
), array(`type` => `image`, `image` => $this->Html->url('/img/image.jpg')));
```

By default a link will be generated (`type` = 'link').

#### Conditional column
With the Conditional column it is possible to show a value according to 1 or more conditions.

```php
$this->DataGrid->addColumn('Active', 'User.active', array(
	'type'			=> 'conditional',
	'conditions'	=> array('User.active' => '1'),	//Check if User.active == 1
	'true'			=> 'Active',					//If true then print "Active"
	'false'			=> 'Inactive'					//If false then print "Active"
));
```

A more advanced example:

```php
$this->DataGrid->addColumn('Active', 'User.active', array(
	'type'			=> 'conditional',
	'conditions'	=> array('User.usergroup' => '1'),	//Check if User.usergroup == 1
	'true'			=> array(							//If true
		'type'	=> 'string',							//We want a string value
		'url'	=> '...',								//Whit a link
		'rawData' => 'Set active state',				//And instead of the value we want the text "Set active state"
	),
	'false'			=> '-'								//If false then print "-"
));
```

You can see dat you can create conditions with as result a new column. So it is also possible to nest conditions. Simply use type `conditional` again, with `true` or `false`.

#### Formatted column
The Formatted column type displays one or more values in a specific string format using sprintf() formatting.

The simplest form just uses one value:

```php
$this->DataGrid->addColumn('Name', 'User.last_name', array(
	'type'			=> 'formatted',
	'formatString'	=> 'My name is %s.'
));
```

We can also display an alternative value. The following example will display the user's account balance, while still using the last name for other functionality such as sorting:

```php
$this->DataGrid->addColumn('Account Balance', 'User.last_name', array(
	'type'			=> 'formatted',
	'formatString'	=> 'Account balance: $ %.2f',
	'valuePath'		=> 'User.account_balance'
));
```

Multiple values can be displayed by using multiple substitution patterns and supplying the value paths as an array:

```php
$this->DataGrid->addColumn('Name', 'User.last_name', array(
	'type'			=> 'formatted',
	'formatString'	=> 'My full name is %s %s, but we sort on last name.',
	'valuePath'		=> array('User.first_name', 'User.last_name')
));
```

It is possible to wrap the different formatted elements in HTML span tags for styling purposes by enabling the `span` option:

```php
$this->DataGrid->addColumn('Last Name', 'User.last_name', array(
	'type'			=> 'formatted',
	'formatString'	=> 'My last name is %s.',
	'span'			=> true
));
```

If we are called John Doe, this will result in the following output:

```html
My last name is <span class="User_last_name">Doe</span>.
```

The class names can be customized by explicitly providing them in the `span` option as a string (1 value) or as an array (multiple values):

```php
$this->DataGrid->addColumn('Name', 'User.last_name', array(
	'type'			=> 'formatted',
	'formatString'	=> 'My full name is %s %s, but we sort on last name.',
	'valuePath'		=> array('User.first_name', 'User.last_name'),
	'span'			=> array('first_name', 'last_name')
));
```

If we are called John Doe, this will result in the following output:

```html
My full name is <span class="first_name">John</span> <span class="last_name">Doe</span>, but we sort on last name.
```

The `span` option is set to `false` by default.

#### Add multiple columns in one call
It is also possible to add multiple columns in one method call. The `addColumns` method can be used.

```php
$this->DataGrid->addColumns(
	array(
		//Column 1
		array(
			'label' => 'Id',
			'valuePath' => 'User.id',
			'options' => array('sort' => true)
		),

		//Column 2
		array(
			'label' => 'Username',
			'valuePath' => 'User.username',
			'options' => array('sort' => true)
		)
	)
);
```

### Default DataGrid settings
It is possible to set default settings for the DataGrid.

```php
$this->DataGrid->defaults(array(
	'ajax'			=> true,				//Do we use AJAX for pagination, sorting and switching
	'update'		=> '#content',			//Conainer to update when we do an AJAX request
	'column'		=> array(				//Default settings for columns
		'sort'				=> false,		//Sorting on or off
		'type'				=> 'string',	//Type of the column
		'htmlAttributes'	=> false,		//Other HTML attributes
		'header'			=> false,		//Header settings
		'iconClass'			=> 'icon',		//Icon class
		'indentOnThread'	=> false,		//Indent on threaded data
		'indentSize'		=> 2,			//Indent size for nested grids
		'rawData'			=> false		//Place this data one on one inside the field instead of searching for data
		'escape'			=> false		//HTML escape retrieved data
		'autoLink'			=> false		//Automatically create hyperlinks for URLs and e-mail addresses
	),
	'grid'			=> array(				//Default grid settings
		'class' => 'data_grid'				//Class for datagrid
	),
	'pagination'	=> array(				//Default settings for pagination
		'numbers' => array()				//Default settings for numbers
	),
	'filter'		=> array(				//Default settings for filters
		'submit' => array()					//Settings for submit
	),
	'action' => array(						//Default settings for actions
		'options' => array(
			'type' => 'link'				//Type of action link: can be 'link' or 'image'
		)
	),
));
```

### Filters
It is possible to add a search filter to filter the datagrid. The API is the same as the $this->Form->input() method.

```php
$this->DataGrid->addFilter($fieldName, $options);
```

The filter submits to the same URL. You have to implement the filter yourself in the Controller. When AJAX is enabled, the submit will be done threw AJAX.

### Component
The plugin also provides a Component which extends the PaginatorComponent, but with some extras.

* Saves pagination options to the Session so the state of the grid is saved
* Saves filter options of the pagination to the Session so also filtering is saved
