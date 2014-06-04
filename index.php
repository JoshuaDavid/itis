<?php
include_once('useful_stuff.php');

$query = 'select * from longnames limit 100';
$result = $db->query($query);
show_result($result);

