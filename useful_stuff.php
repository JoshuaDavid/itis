<?php
global $db;
$db = new PDO('sqlite:ITIS.sqlite');
function show_result($result, $row_limit = 100) {
    if(count($result) == 0) return;
    echo '<table>';
    $i = 0;
    foreach($result as $row) {
        echo '<thead>';
        echo '<tr>';
        if($i == 0) {
            foreach($row as $col => $val) {
                if(!is_numeric($col)) {
                    echo '<th>';
                    echo $col;
                    echo '</th>';
                }
            }
        }
        $i++;
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        echo '<tr>';
        foreach($row as $col => $val) {
            if(!is_numeric($col)) {
                echo '<td>';
                echo $val;
                echo '</td>';
            }
        }
        echo '</tr>';
        echo '</tbody>';
    }
    echo '</table>';
}

function getLongName($tsn) {
    global $db;
    $query = "select completename from longnames where tsn=:tsn";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':tsn', $tsn);
    $stmt->execute();
    $result = $stmt->fetchAll();
    if(count($result)) {
        return $result[0]["completename"];
    } else {
        die("No taxon found with tsn=$tsn");
    }
}
?>
<link rel="stylesheet" href="style.css" />
