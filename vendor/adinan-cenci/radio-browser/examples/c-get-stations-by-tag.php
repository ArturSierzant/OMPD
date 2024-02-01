<?php 
/**
 * In this example we'll see how to find stations described with a 
 * specific tag.
 */

use AdinanCenci\RadioBrowser\RadioBrowser;

//-----------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);

//-----------------------------

require '../vendor/autoload.php';

//-----------------------------

$browser    = new RadioBrowser();
$tag        = 'power metal'; // optional
$orderBy    = 'clickcount';  // optional
$reverse    = true;          // ( decrescent ) optional
$hideBroken = false;         // optional
$offset     = 0;             // optional
$limit      = 50;            // optional
$data       = $browser->getStationsByTag($tag, $orderBy, $reverse, $hideBroken, $offset, $limit);

echo '<pre>';
print_r($data);
echo '</pre>';
