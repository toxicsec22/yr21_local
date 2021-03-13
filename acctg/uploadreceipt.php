<?php 

$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

$directory=$_POST['directory'];
if(isset($_POST['newdir']) and isset($_POST['UploadID'])){
	$DOWNLOAD_DIR=$_POST['newdir'];
	$maxsize = 308576;
	$size='(MAX 300KB)';
}else{
$DOWNLOAD_DIR="../../acrossyrs/unpaidarinv";
$maxsize = 1048576;
$size='(MAX 1MB)';
}
//added
if(!isset($_POST['UploadID'])){
$upload_id=$_FILES['userfile']['name'];
$DOWNLOAD_DIR=$_POST['newdir'];
$maxsize = 1048576;
$size='(MAX 1MB)';	
}else{
$upload_id=$_POST['UploadID'];	
}
//
if (isset($_FILES['userfile']['tmp_name'])) {
	
                $photo_filename=$_FILES['userfile']['name'];
				
				$ext = pathinfo($photo_filename, PATHINFO_EXTENSION);
				
				
				if(!isset($_POST['UploadID'])){
				if( $ext !== 'jpg' and $ext !== 'pdf' and $ext !== 'docx' ) { echo 'Error! Invalid File Type.'; exit(); }
				}else{
				if( $ext !== 'jpg' ) { echo 'Error! Invalid File Type.'; exit(); }
				}

				if(($_FILES['userfile']['size'] >= $maxsize)){ echo 'Error! Invalid File Size '.$size.'.'; exit(); }
				
                $temp_pathinfo=pathinfo($photo_filename);
                $file_extension=$temp_pathinfo['extension'];
                $photo_stored_filename=$upload_id .(isset($_POST['UploadID'])?".".$file_extension:'');
                $place_to_put=$DOWNLOAD_DIR . "/" . $photo_stored_filename;
                if (file_exists($place_to_put)) {
                    unlink($place_to_put);
                }
                 if (copy($_FILES['userfile']['tmp_name'],$place_to_put)) {
                 $good="Successfully_added_$photo_stored_filename";
				 
				 $_SESSION['TxnIDFromUpload']=$upload_id;
				 header("Location:".$directory."&status=$good");
                 }
                 else {
                 $good="Error: " . $_FILES["userfile"]["error"];
                 }
            } else {
		$good="No file to upload.";
	    }
// }
?>