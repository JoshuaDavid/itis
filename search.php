<?php include "header.php" ?>
<?php
?>

<form method="get">
    <label for="searchterm">Enter Search Terms</label>
    <input type="text" name="searchterm" id="searchterm" />
    <button type="submit">Search</button>
</form>
<?php
if(empty($_GET['searchterm'])) {
} else {
    global $db;
    $term = $_GET['searchterm'];
    $term = addslashes($term);
    $query = "select tsn from longnames
        where completename like '%$term%'
        order by LENGTH(completename);";
    $result = $db->query($query);
    echo '<table>';
    foreach($result as $row) {
        $taxon = new Taxon($row['tsn']);
        echo '<div>' . $taxon->getLink() . '</div>';
    } 
    echo '</table>';
}
?>
<?php include "footer.php" ?>
