<?php 
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once('../switchboard/contents.php');
if (!allowedToOpen(64313,'1rtc')) { header("Location:".$_SERVER['HTTP_REFERER']);}
$DOWNLOAD_DIR="employeepics";
$person_id=$_POST['IDNum'];
$maxsize = 30720; //MAX Size 30KB
if (isset($_FILES['userfile']['tmp_name'])) {
                $photo_filename=$_FILES['userfile']['name'];
                $temp_pathinfo=pathinfo($photo_filename);
                $file_extension=$temp_pathinfo['extension'];
				
				$ext = pathinfo($photo_filename, PATHINFO_EXTENSION);
				if( $ext !== 'jpg' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['userfile']['size'] > $maxsize)){ echo 'Error! Invalid File Size (MAX 30KB).'; exit(); }
				
                $photo_stored_filename=$person_id . "." . $file_extension;
                $place_to_put=$DOWNLOAD_DIR . "/" . $photo_stored_filename;
                if (file_exists($place_to_put)) {
                    unlink($place_to_put);
                }
                 if (copy($_FILES['userfile']['tmp_name'],$place_to_put)) {
                 $good="Successfully_added_$photo_stored_filename";
                 }
                 else {
                 $good="Error: " . $_FILES["userfile"]["error"];
                 }
            } else {
		$good="No file to upload.";
	    }
//header("Location:companydirectory.php?status=$good");
header("Location:".$_SERVER['HTTP_REFERER'])."?status=$good";
                    ?>