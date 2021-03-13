<?php
if ((!isset($uploadtitle)) AND (!isset($folder)) AND (!isset($uploaddatalist)) AND (!isset($directorypage)) AND (!isset($subfolder))){
	$uploadtitle=''; $folder=''; $uploaddatalist=''; $directorypage=''; $subfolder='';
}
echo '<br/><br/><form action="../backendphp/layout/genuploadpic.php" method="POST" enctype="multipart/form-data">
				<font size="small">'.$uploadtitle.' (Only *.jpg files allowed.) <input type="hidden" name="folder" value="'.$folder.'"><input type="hidden" name="subfolder" value="'.$subfolder.'"><input type="hidden" name="directorypage" value="'.$directorypage.'"><input type="text" name="UploadID" size=4 autocomplete="off" list="'.$uploaddatalist.'"> 
                &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <input type="file" name="userfile" accept="image/jpg">   
				<input type="submit" name="btnsubmit" value="Submit"> 
                </font> </form>';

if (isset($_POST['btnsubmit'])){
session_start();
$DOWNLOAD_DIR="../../".$_POST['folder']."/".$_POST['subfolder']."";
$upload_id=$_POST['UploadID'];
$maxsize = 1048576; //1MB

if (isset($_FILES['userfile']['tmp_name'])) {
	
                $photo_filename=$_FILES['userfile']['name'];
				
				$ext = pathinfo($photo_filename, PATHINFO_EXTENSION);
				if( $ext !== 'jpg' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['userfile']['size'] >= $maxsize)){ echo 'Error! Invalid File Size (1MB Max).'; exit(); }
				
                $temp_pathinfo=pathinfo($photo_filename);
                $file_extension=$temp_pathinfo['extension'];
                $photo_stored_filename=$upload_id . "." . $file_extension;
                $place_to_put=$DOWNLOAD_DIR . "/" . $photo_stored_filename;
                if (file_exists($place_to_put)) {
                    unlink($place_to_put);
                }
                 if (copy($_FILES['userfile']['tmp_name'],$place_to_put)) {
                 $good="Successfully_added_$photo_stored_filename";
				 
				 $_SESSION['TxnIDFromUpload']=$upload_id;
				 header("Location:".$_POST['directorypage']."&status=$good");
				 
                 }
                 else {
                 $good="Error: " . $_FILES["userfile"]["error"];
                 }
            } else {
		$good="No file to upload.";
	    }
}
?>