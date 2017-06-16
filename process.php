<?php
require 'bigcommerce.php';
define('BIGCOMMERCE_TOKEN', 'dif8e5acwt9nylnk42wz5jr3ine14bj');
define('BIGCOMMERCE_CLIENT', 'duuwyg05lrp0j4ci47lcclpzqc4wyeq');
define('BIGCOMMERCE_STORE', '3byfqcws4f');
define('BIGCOMMERCE_VERSION', 'v2');

$bigcommerce = new BigCommerce(BIGCOMMERCE_TOKEN, BIGCOMMERCE_CLIENT, BIGCOMMERCE_STORE, BIGCOMMERCE_VERSION);

$bigcommerce->makeRequest('orders/100', 'GET', '');

//$bigcommerce->getFeed("feed2.csv");

?>
