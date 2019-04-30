# smart-dto
Simple trait for creating \"smart\" data-transfer-objects in PHP

## Installation
```
composer require intraworlds/smart-dto
```

## Usage
```php
<?php

use IW\SmartDTO;

final class UserDTO
{
  use SmartDTO;
  
  // public properties are best way, they are accessible directly
  public $id;
  public $username;
  
  // private properties with leadind underscore are also accessible 
  // by default if is not defined explicit setter
  private $_config;
  
  public function setConfig($config): void {
    if (is_string($config)) {
      $this->_config = json_decode($config, true);  // loading from DB
    } else {
      $this->_config = $config;
    }
  }
}

$user = new UserDTO;
$user->id = 123;
$user->username = 'joe.doe';
$user->config = '{"foo":"bar"}';

// undefined properties will fail
$user->surname = 'doe';
echo $user->surname; // throws an exception

// you can extract an array from DTO
print_r($user);
// > Array(id => 123, username => joe.doe, config => Array(foo => bar))

// you can hydrate object from an array
$user->hydrate(['id' => 456, 'config => ['foo' => 'baz']]);

// the MAIN usage will be probably when loading from DB
$stmt = $pdo->query('SELECT * FROM user', \PDO::FETCH_CLASS, UserDTO::class);
$user = $stmt->fetch();

```

## License
MIT
