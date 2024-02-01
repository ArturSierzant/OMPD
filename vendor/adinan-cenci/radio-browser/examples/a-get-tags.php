<?php 
/**
 * In this example we'll see how to fetch tags.
 * Tags are used to describe radio stations, we can use tags to find them.
 */

use AdinanCenci\RadioBrowser\RadioBrowser;

//-----------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);

//-----------------------------

require '../vendor/autoload.php';

//-----------------------------

$browser    = new RadioBrowser();
$filter     = 'metal';        // optional
$orderBy    = 'stationcount'; // optional
$reverse    = true;           // ( decrescent ) optional
$data       = $browser->getTags($filter, $orderBy, $reverse);

echo '<pre>';
print_r($data);
echo '</pre>';
