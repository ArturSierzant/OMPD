<?php 
/**
 * In this example we'll see how to find stations from a specific country.
 */

use AdinanCenci\RadioBrowser\RadioBrowser;

//-----------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);

//-----------------------------

require '../vendor/autoload.php';

//-----------------------------

$browser    = new RadioBrowser();
$country    = 'Brazil';     // optional
$orderBy    = 'clickcount'; // optional
$reverse    = true;         // ( decrescent ) optional
$hideBroken = false;        // optional
$offset     = 0;            // optional
$limit      = 10;           // optional
$data       = $browser->getStationsByCountry($country, $orderBy, $reverse, $hideBroken, $offset, $limit);

echo '<pre>';
print_r($data);
echo '</pre>';
