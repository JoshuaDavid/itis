<?php include "header.php" ?>
<?php

if(empty($_GET['tsn'])) die("You must supply a valid tsn");
$tsn = $_GET['tsn'];

echo '<pre>';
$taxon = new Taxon($tsn, true);
$s = "";
$s .= '<table>';
$s .=   '<tr>';
$s .=     "<td colspan=2><h1>" . $taxon->getLink() . "</h1></td>";
$s .=   '</tr>';
$s .=   '<tr>';
$s .=     '<th>Kingdom</th>';
$s .=     "<td>$taxon->kingdom;</td>";
$s .=   '</tr>';
$s .=   '<tr>';
$s .=     '<th>Taxonomic Parents</th>';
$s .=     '<td>';
foreach($taxon->getTaxonomicParents() as $parent) {
    $s .= "<div>" . $parent->getLink() . "</div>";
}
$s .=     '</td>';
$s .=   '</tr>';
$s .=   '<tr>';
$s .=     '<th>Taxonomic Children</th>';
$s .=     '<td>';
foreach($taxon->childtaxa as $child) {
    $s .= "<div>" . $child->getLink() . "</div>";
}
$s .=     '</td>';
$s .=   '</tr>';
$s .=   '<tr>';
$s .=     '<th>Vernacular</th>';
$s .=     '<td>';
$s .= "<div>" . $taxon->vernacular . "</div>";
$s .=     '</td>';
$s .=   '</tr>';
$s .=   '<tr>';
$s .=     '<th>Comments</th>';
$s .=     '<td>';
foreach($taxon->comments as $comment) {
    $s .= "<div>" . $comment . "</div>";
}
$s .=     '</td>';
$s .=   '</tr>';
$s .=   '<tr>';
$s .=     '<th>Search on Google</th>';
$s .=     "<td>";
$s .=       "<div><a href='//www.google.com/search?q=" .
    "$taxon->longname'>Standard Search</a></div>";
$s .=     "<div><a href='//www.google.com/search?tbm=isch&q=" .
    "$taxon->longname'>Image Search</a></div>";
$s .=     '</td>';
$s .=   '</tr>';
$s .=   '<tr>';
$s .=     '<th>Images</th>';
$s .=     '<td>';
foreach($taxon->images as $image) {
    $s .= "<div><img class='' src='{$image['location']}' 
        alt='{$image['alt']}'/><div><em>
        {$image['alt']}</em></div></div>";
}
$s .=        "<a href='addimage?tsn=$taxon->tsn'>Add an image</a>";
$s .=     '</td>';
$s .=   '</tr>';
echo $s;
echo '</pre>';
?>
<?php include "footer.php" ?>
