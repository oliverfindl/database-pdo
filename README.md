# database-pdo

Simple [PHP](https://secure.php.net/) database class based on [PDO](https://secure.php.net/manual/en/book.pdo.php) class. This class adds 2 new methods - for ping and bulk insert, which are useful for web scrapers. All [PDO](https://secure.php.net/manual/en/book.pdo.php) functionality is preserved.

---

## Install

`git clone https://github.com/oliverfindl/database-pdo.git`

## Usage

* Class initialization is same as for [PDO](https://secure.php.net/manual/en/class.pdo.php) class
* Ping method doesn't require any argument
* Insert method has 3 arguments:
	* Table (required), into which will be data inserted
	* Data (required), which has to be array of associative arrays
	* Mode (optional):
		* \OliverFindl\Database::INSERT_DEFAULT:  
		`INSERT INTO table (...) VALUES (...), (...), ... ;`
		* \OliverFindl\Database::INSERT_IGNORE:  
		`INSERT IGNORE INTO table (...) VALUES (...), (...), ... ;`
		* \OliverFindl\Database::INSERT_UPDATE:  
		`INSERT INTO table (...) VALUES (...), (...), ... ON DUPLICATE KEY UPDATE column = VALUES(column), ... ;`

## Example

```php
require_once("./vendor/database-pdo/database.class.php");
// use \OliverFindl\Database; // optional, if you want omit namespace in code

try {
	$db = new \OliverFindl\Database(/* ... */); // same arguments as PDO
	$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
	$db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
} catch(\Exception $e) {
	exit($e->getMessage());
}

// ...

try {
	$db->ping();
} catch(\Exception $e) {
	exit($e->getMessage());
}

// ...

try {
	$res = $db->insert("table", $data, \OliverFindl\Database::INSERT_UPDATE);
	if($res) $id = $db->lastInsertId();
} catch(\Exception $e) {
	exit($e->getMessage());
}
```

## Requirements

* [PHP 7](https://secure.php.net/manual/en/install.php)
* [PDO extension](https://secure.php.net/manual/en/pdo.setup.php)

## Notes

This class is compliant with selected [PDO::ERR_MODE](http://php.net/manual/en/pdo.setattribute.php).

---

## License

[MIT](http://opensource.org/licenses/MIT)
