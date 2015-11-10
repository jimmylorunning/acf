<?php

// Path to your craft/ folder
$craftPath = '../craft';

// Setup environment-friendly configs
switch ($_SERVER['SERVER_NAME']) {

	case 'atlantacyclingfestival.com' :
		define('CRAFT_ENVIRONMENT', 'live');
		break;

	case '162.243.84.5' :
		define('CRAFT_ENVIRONMENT', 'staging');
		break;

	case 'localhost' :
		define('CRAFT_ENVIRONMENT', 'dev');
		break;

	default :
		define('CRAFT_ENVIRONMENT', 'local');
		break;
}

// Do not edit below this line
$path = rtrim($craftPath, '/').'/app/index.php';

if (!is_file($path))
{
	exit('Could not find your craft/ folder. Please ensure that <strong><code>$craftPath</code></strong> is set correctly in '.__FILE__);
}

require_once $path;
