# PHP Model
*PHP library for simple and complex database models.*

### About
This library was created to provide a flexible, but yet easy to use Model system, 
which can be used for new projects but also integrated into existing projects.
Every part of the library can be overwritten and replaced separately for custom logic.

In contrary to other model libraries, this library is not tied to any specific database
backend, instead every model can contain custom logic for accessing different databases, 
e.g. for a different cache or search backend. Therefore this library does not provide
any provisioning functionality.

**Note: This library is still in development and some features especially regarding more
driver functions will be added in the future.** 

### Installation
```
composer require aternos/model
```

## Basic usage

### Driver
The library includes some drivers, but you can also create your own drivers
and use them in your project or submit them to be added to the library.

Currently included drivers are:

* [Cache\Redis](src/Driver/Cache/Redis.php)
* [NoSQL\Cassandra](src/Driver/NoSQL/Cassandra.php)
* [Relational\Mysqli](src/Driver/Relational/Mysqli.php)
* [Search\Elasticsearch](src/Driver/Search/Elasticsearch.php)

*All of these drivers require additional extensions or packages, see "suggest" in [composer.json](composer.json).*

Most drivers will work out of the box with a local database set up without
password, but for most use cases you have to use different credentials. To
do that with the included drivers, you have to create a new driver class
extending the library driver and overwrite the protected credential properties 
(either in the class itself or in the constructor), e.g.:

```php
<?php

namespace MyModel;

class Mysqli extends \Aternos\Model\Driver\Relational\Mysqli 
{
    protected $user = 'username';
    protected $password = 'password';

    public function __construct()
    {
        $this->host = \Config::getHost();
    }
}
```

After that you have to register the class in the [DriverFactory](src/Driver/DriverFactory.php) 
(or create your own DriverFactory overwriting the $drivers property):

```php
<?php

\Aternos\Model\Driver\DriverFactory::getInstance()->registerDriver(
    \Aternos\Model\Driver\Relational\RelationalDriverInterface::class, 
    \MyModel\Mysqli::class
);
```

### Model
Now you can create a model class. All model classes have to follow the [ModelInterface](src/ModelInterface.php).
This library includes three different abstract model classes to make the model creation
easier:
 
* [BaseModel](src/BaseModel.php) - Implements the basic model logic and is not related to any Driver
* [SimpleModel](src/SimpleModel.php) - Minimal implementation for the NoSQL driver, mainly for demonstration purposes
* [GenericModel](src/GenericModel.php) - Optional implementation of all drivers and registry, by default only the relational driver is enabled

It's recommended to start with the [GenericModel](src/GenericModel.php) since it already implements
all drivers and you can enable whatever you need (e.g. caching, searching) for each model or for
all models (by using your own parent model for all your models).

This is an example implementation of a model using the [GenericModel](src/GenericModel.php) with a NoSQL database
as backend and caching:

```php
<?php

class User extends \Aternos\Model\GenericModel 
{
    // configure the generic model drivers
    // enable nosql driver
    protected static $nosql = true; 
    
    // cache the model for 60 seconds
    protected static $cache = 60;
    
    // disable default relational driver
    protected static $relational = false;
    
    // the name of your model (and table)
    public static function getName() : string
    {
        return "users";
    }
    
    // all public properties are database fields
    public $id;
    public $username;
    public $email;
}
```

If you want to implement your own driver logic in your model take a look at the [SimpleModel](src/SimpleModel.php), 
which should give you a good idea of the minimal requirements.

### Use your model
You can now use your model in your code:

```php
<?php

// create new user
$user = new User();
$user->username = "username";
$user->email = "mail@example.org";
$user->save();

// get a user by id
$user = User::get($id);
echo $user->username;

// you can force to skip the registry and the cache
$user = User::get($id, true);

// update a user
$user->email = "othermail@example.org";
$user->save();

// delete a user
$user->delete();
```

### Query
You can query the model using a [Query](src/Query/Query.php) object, e.g. [SelectQuery](src/Query/SelectQuery.php).
It allows different syntax possibilities such as simple array/string/int values directly passed to the constructor or
building all parameters as objects based on the [Query/...](src/Query) classes. All queries return a [QueryResult](src/Query/QueryResult.php)
object, which is iterable and countable.

#### Select
```php
<?php

// the following lines are all the same
User::select(["email" => "mail@example.org"]); // ::select() is only a helper function of GenericModel
User::query(new \Aternos\Model\Query\SelectQuery(["email" => "mail@example.org"]));
User::query((new \Aternos\Model\Query\SelectQuery())->where(["email" => "mail@example.org"]));
User::query(new \Aternos\Model\Query\SelectQuery(
    new \Aternos\Model\Query\WhereCondition("email", "mail@example.org")
));
User::query(new \Aternos\Model\Query\SelectQuery(
    new \Aternos\Model\Query\WhereGroup([
        new \Aternos\Model\Query\WhereCondition("email", "mail@example.org")
    ])
));

// use the result
$userQueryResult = User::select(["email" => "mail@example.org"]);

if (!$userQueryResult->wasSuccessful()) {
    echo "Query failed";
}

echo "Found " . count($userQueryResult) . " users";

foreach($userQueryResult as $user) {
    /** @var User $user */
    echo $user->username;
}

// another query example
User::select(
    ["field" => "value", "hello" => "world", "foo" => "bar"],
    ["field" => "ASC", "hello" => "DESC", "foo" => "ASC"],
    ["field", "hello", "foo"],
    [100, 10]
);
// can also be written as
User::query((new \Aternos\Model\Query\SelectQuery)
    ->where(["field" => "value", "hello" => "world", "foo" => "bar"])
    ->orderBy(["field" => "ASC", "hello" => "DESC", "foo" => "ASC"])
    ->fields(["field", "hello", "foo"])
    ->limit([100, 10])
); 

// a more complex query with nested where groups using the query parameter classes
User::query(new \Aternos\Model\Query\SelectQuery(
    new \Aternos\Model\Query\WhereGroup([
        new \Aternos\Model\Query\WhereCondition("field", "value", "<>"),
        new \Aternos\Model\Query\WhereGroup([
            new \Aternos\Model\Query\WhereCondition("hello", "world"),
            new \Aternos\Model\Query\WhereCondition("foo", "bar")
        ], \Aternos\Model\Query\WhereGroup:: OR)
    ]),
    [
        new \Aternos\Model\Query\OrderField("field", \Aternos\Model\Query\OrderField::DESCENDING),
        new \Aternos\Model\Query\OrderField("hello", \Aternos\Model\Query\OrderField::ASCENDING),
        new \Aternos\Model\Query\OrderField("foo", \Aternos\Model\Query\OrderField::DESCENDING)
    ],
    ["field", "hello", "foo"],
    new \Aternos\Model\Query\Limit(10, 100)
));
```

#### Update
```php
<?php

// update mail to "mail@example.org" where username is "username"
User::query(new \Aternos\Model\Query\UpdateQuery(["email" => "mail@example.org"], ["username" => "username"]));
User::query((new \Aternos\Model\Query\UpdateQuery())
    ->fields(["email" => "mail@example.org"])
    ->where(["username" => "username"]));
User::query(new \Aternos\Model\Query\UpdateQuery(
    new \Aternos\Model\Query\Field("email", "mail@example.org"),
    new \Aternos\Model\Query\WhereCondition("username", "username")
));
User::query(new \Aternos\Model\Query\UpdateQuery(
    [new \Aternos\Model\Query\Field("email", "mail@example.org")],
    new \Aternos\Model\Query\WhereGroup([
        new \Aternos\Model\Query\WhereCondition("username", "username")
    ])
));
```

#### Delete
```php
<?php

// delete where email is mail@example.org
User::query(new \Aternos\Model\Query\DeleteQuery(["email" => "mail@example.org"]));
User::query((new \Aternos\Model\Query\DeleteQuery())->where(["email" => "mail@example.org"]));
User::query(new \Aternos\Model\Query\DeleteQuery(
    new \Aternos\Model\Query\WhereCondition("email", "mail@example.org")
));
User::query(new \Aternos\Model\Query\DeleteQuery(
    new \Aternos\Model\Query\WhereGroup([
        new \Aternos\Model\Query\WhereCondition("email", "mail@example.org")
    ])
));
```

## Advanced usage
*More information about more advanced usage, such as writing your own drivers, driver factory or models
will be added in the future, in the meantime just take a look at the source code.*