<?php 
/**
 * In this example we'll see how to fetch states.
 * We can use this information to find radio stations.
 */

use AdinanCenci\RadioBrowser\RadioBrowser;

//-----------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);

//-----------------------------

require '../vendor/autoload.php';

//-----------------------------

$browser    = new RadioBrowser();
$filter     = 'Texas'; // optional
$country    = '';      // optional
$orderBy    = 'name';  // optional
$reverse    = true;    // ( decrescent ) optional
$hideBroken = false;   // Optional
$offset     = 0;       // optional
$limit      = 50;      // optional
$data       = $browser->getStates($filter, $country, $orderBy, $reverse, $hideBroken);

echo '<pre>';
print_r($data);
echo '</pre>';
