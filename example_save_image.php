<?php
include 'img2db_pdo.class.php';
$save_image = new Img2Db();
$id = $save_image->save($_FILES);


?>