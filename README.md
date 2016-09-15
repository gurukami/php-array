# Array Helper

[![Latest Stable Version](https://poser.pugx.org/gurukami/php-array/v/stable.png)](https://packagist.org/packages/gurukami/php-array)
[![Total Downloads](https://poser.pugx.org/gurukami/php-array/downloads.png)](https://packagist.org/packages/gurukami/php-array)
[![License](https://poser.pugx.org/gurukami/php-array/license.png)](https://packagist.org/packages/gurukami/php-array)
[![Build Status](https://travis-ci.org/Gurukami/php-array.svg?branch=master)](https://travis-ci.org/Gurukami/php-array)

Simple & secure helper to manipulate arrays in various ways, released under the MIT license.

Namespace Gurukami\Helpers

## Usage

**Exists** (Checks if the given key exists in the array by a string representation)

Arrays::exists($key, &$array)

```php
<?php

// Don't forget require 'autoload.php' composer

use \Gurukami\Helpers\Arrays;

$data = [
    'k0' => 'v0',
    'k1' => [
        'k1-1' => 'v1-1'
    ],
    'complex_[name]_!@#$&%*^' => 'complex',
    'k2' => 'string'
];

Arrays::exists('k0', $data); // returns: true
Arrays::exists('k9', $data); // returns: false

Arrays::exists('[k1][k1-1]', $data); // returns: true
Arrays::exists('[k1][k1-2]', $data); // returns: false

Arrays::exists('["complex_[name]_!@#$&%*^"]', $data); // returns: true

Arrays::exists('[k2][2]', $data); // returns: false
```

**Save** (Save element to the array by a string representation)

Arrays::save($key, &$array, $value)

```php
<?php

// Don't forget require 'autoload.php' composer

use \Gurukami\Helpers\Arrays;

$data = [
    'k2' => 'string'
];

Arrays::save('k0', $data, 'v0'); // returns: true, save as 'k0' => 'v0'
Arrays::save('[k1][k1-1]', $data, 'v1-1'); // returns: true, save as 'k1' => ['k1-1' => 'v1-1']
Arrays::save('[k2][2]', $data, 'p'); // returns: false, can't save value to string

// Broken key names
Arrays::save('k3[', $data, 'v3'); // returns: false, can't save, bad syntax
Arrays::save('["k4["]', $data, 'v4'); // returns: true, save as 'k4[' => 'v4'
Arrays::save('"k4["', $data, 'v4'); // returns: false, can't save, bad syntax

// Append
Arrays::save('k5', $data, []); // returns: true, create array 'k5' => []
Arrays::save('k5[]', $data, 'v5-0'); // returns: true, append value to exists array 'k5' => [ 'v5-0' ]
Arrays::save('k6[k6-1][]', $data, 'v6-1-0'); // returns: true, save as 'k6' => [ 'k6-1' => [ 'v6-1-0' ] ]
```

**Delete** (Delete element from the array by a string representation)

Arrays::delete($key, &$array)

```php
<?php

// Don't forget require 'autoload.php' composer

use \Gurukami\Helpers;

$data = [
    'k0' => 'v0',
    'k1' => [
        'k1-1' => 'v1-1'
    ],
    'complex_[name]_!@#$&%*^' => 'complex'
];

Arrays::delete('k0', $data); // returns: true, delete element from array
Arrays::delete('k9', $data); // returns: false

Arrays::delete('[k1][k1-1]', $data); // returns: true, delete element from array
Arrays::delete('[k1][k1-2]', $data); // returns: false, delete element from array

Arrays::delete('["complex_[name]_!@#$&%*^"]', $data); // returns: true, delete element from array
```

**Get** (Get element of the array by a string representation)

Arrays::get($key, &$array, $default = null, $ignoreString = true)

```php
<?php

// Don't forget require 'autoload.php' composer

use \Gurukami\Helpers;

$data = [
    'k0' => 'v0',
    'k1' => [
        'k1-1' => 'v1-1'
    ],
    'complex_[name]_!@#$&%*^' => 'complex',
    'k2' => 'string'
];

Arrays::get('k0', $data); // returns: 'v0'
Arrays::get('k9', $data, '0'); // returns: '0', key isn't exists in array

Arrays::get('[k1][k1-1]', $data); // returns: 'v1-1'
Arrays::get('[k1][k1-2]', $data, 'default'); // returns: 'default', key isn't exists in array

Arrays::get('["complex_[name]_!@#$&%*^"]', $data); // returns: 'complex'

Arrays::get('[k2][2]', $data); // returns: null, key isn't exists in array

// If you want get a symbol from string value, you may switch off option $ignoreString = false
Arrays::get('[k2][2]', $data, null, false); // returns: 'r'
Arrays::get('[k2][null]', $data, null, false); // returns: null, key isn't exists in array
```

## Behaviors

{method} - any method from class (exists,save,delete,get)

**Null (Empty)**

Search element with name **'null'**
```php
Arrays::{method}('null', ...) // or
Arrays::{method}('[null]', ...)
```

If you want find **'null'** as constants use empty **""** string
```php
Arrays::{method}('', ...) // or
Arrays::{method}('[""]', ...) // or
Arrays::{method}('[key][""]', ...)
```

**Warning!** You can get element with **'null'** constant only for one-dimensional array, if you need search in deeper use instructions above
```php
Arrays::{method}(null, ...)
```

**Boolean**

Search element with name **'true'**
```php
Arrays::{method}('true', ...) // or
Arrays::{method}('[true]', ...)
```

If you want find **'true'** as constants use integer **1** instead
```php
Arrays::{method}(1, ...) // or
Arrays::{method}('1', ...) // or
Arrays::{method}('[1]', ...) // or
Arrays::{method}('[key][1]', ...)
```

**Warning!** You can get element with **'true'** constant only for one-dimensional array, if you need search in deeper use instructions above
```php
Arrays::{method}(true, ...)
```

Search element with name **'false'**
```php
Arrays::{method}('false', ...) // or
Arrays::{method}('[false]', ...)
```

If you want find **'false'** as constants use integer **0** instead
```php
Arrays::{method}(0, ...) // or
Arrays::{method}('0', ...) // or
Arrays::{method}('[0]', ...) // or
Arrays::{method}('[key][0]', ...)
```

**Warning!** You can get element with **'false'** constant only for one-dimensional array, if you need search in deeper use instructions above
```php
Arrays::{method}(false, ...)
```

**Brackets in key name**

You must surround your key name with brackets if your key name includes it or use single **'** or double **"** quote
```php
 Arrays::{method}('key[]', ...) // Wrong for all methods except save, because [] is append instruction
 Arrays::{method}('[key[]]', ...) // Works fine
 Arrays::{method}('["key[]"]', ...) // Works fine
```

**Broken brackets in key name**

You can get element with broken brackets in key name use single **'** or double **"** quote
```php
Arrays::{method}('key[', ...) // Wrong
Arrays::{method}('key[subKey[]', ...) // Wrong
Arrays::{method}('["key["]', ...) // Works fine
Arrays::{method}('key["subKey["]', ...) // Works fine
```

**Broken quotes on boundary in key name**

If you use different quotes on boundary of key name, it will be recognized as a key name with quotes.
Also key name will be recognized as a key if use quote without brackets.
```php
Arrays::{method}('"key\'', ...) // recognized as "key'
Arrays::{method}('"key"', ...) // recognized as "key"
Arrays::{method}('["key"]', ...) // recognized as key
Arrays::{method}('[\'key\']', ...) // recognized as key
```

**Style representation**

You can use some styles like you want

```php
// Normal:
Arrays::{method}('[key][subKey][subSubKey][subSubSubKey]', ...)

// Camel:
Arrays::{method}('key[subKey]subSubKey[subSubSubKey]', ...)

// HTML:
Arrays::{method}('key[subKey][subSubKey][subSubSubKey]', ...)
```

## Requirements

- PHP 5.4 or greater

## Installation

    php composer.phar require "gurukami/php-array:*"

## License

The MIT license

Copyright (c) 2016 Gurukami, http://gurukami.com/
