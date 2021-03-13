<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$DOWNLOAD_DIR="permitpics";
$upload_id=$_POST['UploadID'];
$maxsize = 1048576;

if (allowedToOpen(8292,'1rtc')) {
	if (isset($_FILES['userfile']['tmp_name'])) {
	
                $photo_filename=$_FILES['userfile']['name'];
				
				$ext = pathinfo($photo_filename, PATHINFO_EXTENSION);
				if( $ext !== 'jpg' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['userfile']['size'] >= $maxsize)){ echo 'Error! Invalid File Size (MAX 1MB).'; exit(); }
				
                $temp_pathinfo=pathinfo($photo_filename);
                $file_extension=$temp_pathinfo['extension'];
                $photo_stored_filename=$upload_id .'.'. $file_extension;
                $place_to_put=$DOWNLOAD_DIR . "/" . $photo_stored_filename;
                if (file_exists($place_to_put)) {
                    unlink($place_to_put);
                }
                 if (copy($_FILES['userfile']['tmp_name'],$place_to_put)) {
                 $good="Successfully_added_$photo_stored_filename";
				 
				 
				 header("Location:".$_SERVER['HTTP_REFERER'])."?status=$good";
                 }
                 else {
                 $good="Error: " . $_FILES["userfile"]["error"];
                 }
            } else {
		$good="No file to upload.";
	    }
}
?>