<?php 
/**
 * In this example we'll see how to fetch data formatted in XML.
 */

use AdinanCenci\RadioBrowser\RadioBrowserApi;

//-----------------------------

error_reporting(E_ALL);
ini_set('display_errors', 1);

//-----------------------------

require '../vendor/autoload.php';

//-----------------------------

$browser    = new RadioBrowserApi(false, 'xml');
$orderBy    = 'clickcount'; // optional
$reverse    = true;         // ( decrescent ) optional
$hideBroken = false;        // optional
$offset     = 0;            // optional
$limit      = 10;           // optional
$data       = $browser->getStations($orderBy, $reverse, $hideBroken, $offset, $limit);

header ("Content-Type:text/xml");
echo $data;