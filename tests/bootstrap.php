<?php

error_reporting(E_ALL);

if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
    date_default_timezone_set(@date_default_timezone_get());
}

require_once __DIR__ . '/TestSuite.php';
require_once __DIR__ . '/../src/Arrays.php';