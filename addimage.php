<?php include "header.php" ?>
<script type="text/javascript">
    function PreviewImage() {
        var oFReader = new FileReader();
        oFReader.readAsDataURL(document.getElementById("image").files[0]);

        oFReader.onload = function (oFREvent) {
            document.getElementById("uploadPreview").src = oFREvent.target.result;
        };
    };
</script>
<?php
function showImageUploadForm($tsn) {
        $taxon = new Taxon($tsn);
        echo <<<HTML
<h1>Add an image for $taxon->rank $taxon->longname</h1>
<a href="species.php?tsn=$taxon->tsn">Back</a>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="tsn" value="$taxon->tsn" />
    <div>
        <label for="image">Image of $taxon->longname</label>
        <input type="file" name="image" id="image" onchange="PreviewImage()" />
        <img class="thumbnail" id="uploadPreview" />
    </div>
    <div>
        <label for="alt">Describe the picture</label><br />
        <textarea type="text" name="alt" id="alt" rows=10 cols=80></textarea>
    </div>
    <button type="submit">Add Image</button>
</form>
HTML;
}
function check_if_exists($sha1) {
    global $db;
    $query = "select tsn, alt, location from images where sha1=:sha1";
    $stmt = $db->prepare($query);
    $stmt->bindParam('sha1', $sha1);
    $stmt->execute();
    $res = $stmt->fetch();
    return $res;
}
function upload_image($image, $tsn, $alt, $sha1) {
    global $db;
    $name = "images/{$taxon->rank}_{$taxon->longname}_{$image['name']}";
    move_uploaded_file($image['tmp_name'], $name);
    $query = "insert into images (tsn, alt, location, sha1) 
        values (:tsn, :alt, :location, :sha1);";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":tsn", $tsn);
    $stmt->bindParam(":alt", $alt);
    $stmt->bindParam(":location", $name);
    $stmt->bindParam(":sha1", $sha1);
    $success = $stmt->execute();
    return $success;
}
if(empty($_GET['tsn'])) {
    die("You must supply a valid tsn");
} else {
    if(empty($_FILES['image'])) {
        $tsn = $_GET['tsn'];
        showImageUploadForm($tsn);
    } else {
        $tsn = $_POST['tsn'];
        $taxon = new Taxon($tsn);
        if(empty($_POST['alt'])) $alt = "Picture of $taxon->rank $taxon->longname";
        else                     $alt = $_POST['alt']; 
        $image = $_FILES['image'];
        if(getimagesize($image['tmp_name']) <= 0) {
            die("Not an image file");
        } else {
            $sha1 = sha1_file($image['tmp_name']);
            $maybe_image = check_if_exists($sha1);
            if($maybe_image) {
                echo "<a href='species.php?tsn=$taxon->tsn'>Back</a>";
                echo "<h1>Image already exists</h1>";
                echo "<img class='thumbnail' src='{$maybe_image['location']}'
                    alt='{$maybe_image['alt']}'/>";
                echo "<div><em>{$maybe_image['alt']}</em></div>";
            } else {
                upload_image($image, $tsn, $alt, $sha1);
            }
        }
    }
}
?>
<?php include "footer.php" ?>
