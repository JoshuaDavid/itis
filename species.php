<?php
include_once('useful_stuff.php');
include_once('Taxon.php');

if(empty($_GET['tsn'])) die("You must supply a valid tsn");
$tsn = $_GET['tsn'];

echo '<pre>';
$taxon = new Taxon($tsn, true);
echo $taxon;
echo '</pre>';
