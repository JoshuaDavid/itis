<?php
include_once('useful_stuff.php');

if(!empty($_GET['table'])) {
    $tablename = $_GET['table'];
    show_table($tablename);
} else {
    show_table_selector();
}

function show_table($tablename) {
    global $db;
    echo '<h2><a href="?">Back</a></h2>';
    $tablename = addslashes($tablename);
    $query = "select * from $tablename limit 1000;";
    $result = $db->query($query);
    show_result($result);
}

function show_table_selector() {
    global $db;
    echo '<h2>Tables</h2>';
    $query = "select name from sqlite_master where type='table';";
    $result = $db->query($query);
    if(count($result)) {
        echo '<ul>';
        foreach($result as $row) {
            $name = $row['name'];
            echo '<li>';
            echo "<a href='?table=$name'>$name</a>";
            echo '</li>';
        }
        echo '</ul>';
    }
}

