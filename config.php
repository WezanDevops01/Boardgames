<?php
// HTTP
define('HTTP_SERVER', 'https://www.gamma.boardgamesnmore.com/');

// HTTPS
define('HTTPS_SERVER', 'https://www.gamma.boardgamesnmore.com/');

// Catalog URLs (IMPORTANT for emails)
define('HTTP_CATALOG', 'https://www.gamma.boardgamesnmore.com/');
define('HTTPS_CATALOG', 'https://www.gamma.boardgamesnmore.com/');


// DIR
define('DIR_APPLICATION', '/home/boardgamenite/gamma.boardgamesnmore.com/catalog/');
define('DIR_SYSTEM', '/home/boardgamenite/gamma.boardgamesnmore.com/system/');
define('DIR_IMAGE', '/home/boardgamenite/gamma.boardgamesnmore.com/image/');
define('DIR_STORAGE', '/home/boardgamenite/storage_gamma_2025/');
//define('DIR_STORAGE', '/home/boardgamenite/public/system/storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'boardgamenite_gamma');
define('DB_PASSWORD', 'Yl.E3xXMW7(,kW^K');
define('DB_DATABASE', 'boardgamenite_gamma_2025');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//define('DB_USERNAME', 'boardgam_boardgamenite_dev');
//define('DB_PASSWORD', '9=SSUy{{aIaR');
//define('DB_DATABASE', 'boardgam_boardgamenite_dev');