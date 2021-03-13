<?php 

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

// echo '<br/><br/><form action="motorvehicleuploadorcrpic.php" method="POST" enctype="multipart/form-data">
				// <font size="small">'.$uploadtitle.' (Only *.jpg files allowed.) <input type="text" name="UploadID" size=4 autocomplete="off" list="'.$uploaddatalist.'"> 
                // &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="file" name="userfile" accept="image/jpg">  
				// <input type="submit" name="btnsubmit" value="Submit"> 
                // </font> </form>';

// if (isset($_POST['btnsubmit'])){
$DOWNLOAD_DIR="orcrpics";
$upload_id=$_POST['UploadID'];
$maxsize = 1048576;

if (isset($_FILES['userfile']['tmp_name'])) {
	
                $photo_filename=$_FILES['userfile']['name'];
				
				$ext = pathinfo($photo_filename, PATHINFO_EXTENSION);
				if( $ext !== 'jpg' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['userfile']['size'] >= $maxsize)){ echo 'Error! Invalid File Size (MAX 1MB).'; exit(); }
				
                $temp_pathinfo=pathinfo($photo_filename);
                $file_extension=$temp_pathinfo['extension'];
                $photo_stored_filename=$upload_id . $_POST['addlname']."." . $file_extension;
                $place_to_put=$DOWNLOAD_DIR . "/" . $photo_stored_filename;
                if (file_exists($place_to_put)) {
                    unlink($place_to_put);
                }
                 if (copy($_FILES['userfile']['tmp_name'],$place_to_put)) {
                 $good="Successfully_added_$photo_stored_filename";
				 
				  
				 $sql='UPDATE `'.$_POST['table'].'` SET '.$_POST['addlname'].'=1 WHERE TxnID='.$upload_id.';'; 
				 $stmt=$link->prepare($sql); $stmt->execute();
				 header("Location:".$_SERVER['HTTP_REFERER'])."?status=$good";
                 }
                 else {
                 $good="Error: " . $_FILES["userfile"]["error"];
                 }
            } else {
		$good="No file to upload.";
	    }
// }
?>