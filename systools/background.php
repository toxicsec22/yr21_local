<html>
    <title>Background Pic</title>
    <body>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(669,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
$showbranches=false; include_once('../switchboard/contents.php');
 
$title='Background';

$which=!isset($_REQUEST['w'])?'showall':($_REQUEST['w']);

switch ($which){
case 'showall':
 

if(isset($_GET['result'])){
	$formdesc='</i><h2 align="center"><font color="green">'.$_GET['result'].'</font></h2><i>';
} 
$sql='Select *,(CASE WHEN BGOpenFor = 1 THEN "All" WHEN BGOpenFor = 2 THEN "Office" ELSE "Branch/Warehouse" END) AS `OpenFor?` from `gen_info_00indexbg`';

$columnnames=array('StartDate','EndDate','OpenFor?','Filename','clickhere');
$editprocess='background.php?w=Edit&edit=2&TxnID=';
$editprocesslabel='Edit';
$addlprocess='/testindex.php?ID=';
$addlprocesslabel='Preview';
?><br><br>
<div style="border:1px solid black;padding:8px;background-color:white;">
<form method="post" action="background.php?w=Add">
    Start Date <input type=date name="StartDate" size=5 value="<?php echo date('Y-m-d');?>"> &nbsp End Date <input type=date name="EndDate" size=5  value="<?php echo date('Y-m-d');?>"> OpenFor? <select name="BGOpenFor"><option value="1">1 - All</option><option value="2">2 - Office</option><option value="3">3 - Branch/Warehouse</option></select> <br><br>
    Filename <input type=text name="Filename" size=10 autocomplete=off > "Click Here" Filename(s) (<i>Leave blank if no data</i>) <input type=text name="clickhereFilename" size=25 autocomplete=off placeholder="Filenamech1,Filenamech2">
    <input type=submit name=submit value=Add>
</form><br><hr><br>

<form method="post" style="display: inline" action="background.php?w=PrUploadPic" enctype="multipart/form-data">
    Upload Picture (<b>Main Background</b>) .jpg only <input type="file" name="userfile" accept="image/jpg">
    <input type=submit name=submit value="Submit"><br><br>
</form><hr>
<br>
<form action="background.php?w=PrUploadPicMultiple" method="post" enctype="multipart/form-data">
    Upload Picture (<b>"Click Here"</b>) (960h x 720v [px] - Max:300KB) .jpg only 
    <input type="file" name="files[]" multiple >
    <input type="submit" name="submit" value="Submit">  &nbsp; &nbsp; <a href="background.php?w=ImgLists">View All "Click Here" Images</a>
</form>

</div>
<?php
include('../backendphp/layout/displayastable.php');
break;
case 'Add':
    
    $columnnames=array('StartDate','EndDate','BGOpenFor','Filename');
    $sql='';
    foreach ($columnnames as $col){
        $sql=$sql.(!is_null($_POST[$col])?'`'.$col.'`=\''.addslashes($_POST[$col]).'\', ':'');
    }
    $sql='insert into `gen_info_00indexbg` SET '.$sql.' `clickhere`="'.$_POST['clickhere'].'"';
  
    $stmt=$link->prepare($sql);
    $stmt->execute();
    header('Location:background.php');
    break;
case 'Edit':
    $txnid=$_REQUEST['TxnID'];
    $sql='Select * from `gen_info_00indexbg` where TxnID='.$txnid;
  
    $columnnames=array('StartDate','EndDate','BGOpenFor','Filename','clickhere');
  
	$columnstoedit=array('StartDate','EndDate','BGOpenFor','Filename','clickhere');
    $method='POST';
    $columnslist=array(); $processlabelblank=''; $processblank='';
$action='background.php?w=PrEdit&TxnID='.$txnid;
include('../backendphp/layout/rendersubform.php');
    break;
case 'PrEdit':
    $txnid=intval($_REQUEST['TxnID']);
   
    $columnstoedit=array('StartDate','EndDate','BGOpenFor','Filename');
    $sql='';
    foreach ($columnstoedit as $col){
        $sql=$sql.(!is_null($_POST[$col])?'`'.$col.'`=\''.addslashes($_POST[$col]).'\', ':'');
    }
    $sql='Update `gen_info_00indexbg` SET '.$sql.' `clickhere`="'.$_POST['clickhere'].'" where TxnID='.$txnid;
    
    $stmt=$link->prepare($sql);
    $stmt->execute();
    header('Location:background.php');
    break;
	
case 'ImgLists':

$title = 'Images List';
echo '<title>'.$title.'</title>';
echo '<h3><a href="background.php">Edit Background</a>  &nbsp; &nbsp;  '.$title.'</h3><br/>';
echo '<table border="1px solid black" style="border-collapse:collapse;">
<tr>';
		

		$files = glob("../generalinfo/clickherepics/*");
		$cnttr=1;
		foreach ($files as $filename) {
			if ($cnttr % 4 == 1){
					echo '</tr><tr align="left"><td style="padding:5px;">';
				} else {
						
					echo '<td style="padding:5px;">';
				}
				echo 'Filename: '.str_replace('./generalinfo/clickherepics/','',$filename).'<br/>';
				echo 'DateUploaded: '.str_replace('./generalinfo/clickherepics/','',date ("F d Y.", filemtime($filename))).'<br/>';
				echo '<a href="'.$filename.'"><img width="100px" height="100px" src="'.$filename.'"/></a><br/>
			</td>';
			$cnttr++;
			
		}
		?>
</table>
<?php
break;
	
	
case 'PrUploadPic':
    $uploaddir="../generalinfo/bgpics/";
    $sourcefile=$_FILES['userfile']['tmp_name'];
	$maxsize = 307200; //MAX Size 300KB
	
if (is_uploaded_file($sourcefile)) {
	
                $Filename=$_FILES['userfile']['name'];
                $destination=$uploaddir . basename($_FILES['userfile']['name']);
                
                $uploadOk = 1;
                $imageFileType = pathinfo($destination,PATHINFO_EXTENSION);
				
				$ext = pathinfo($Filename, PATHINFO_EXTENSION);
				if( $ext !== 'jpg' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['userfile']['size'] > $maxsize)){ echo 'Error! Invalid File Size (MAX 300KB).'; exit(); }
				
            // Check if image file is a actual image or fake image
            
                $check = getimagesize($sourcefile);
                if($check !== false) {
                    echo "File is an image - " . $check["mime"] . ".";
                    $uploadOk = 1;
                } else {
                    echo "File is not an image.";
                    $uploadOk = 0;
                }
                
                if (file_exists($destination)) {
                    unlink($destination);
                }
                 if (copy($sourcefile,$destination) and $uploadOk==1) {
                 $good='Successfully_added_'.$Filename;
                 }
                 else {
                 $good=$Filename."_NOT_UPLOADED_Error:_" . $_FILES["userfile"]["error"]; //.error_reporting(E_ALL | E_STRICT); //
                 }
            }
    header('Location:background.php?result='.$good);
    break;
	
	case 'PrUploadPicMultiple':
	
	extract($_POST);
$error=array();
$extension=array("jpeg","jpg","png","gif");
$uploaddir="../generalinfo/clickherepics/";
 
	$maxsize = 307200; //MAX Size 300KB
	

foreach($_FILES["files"]["tmp_name"] as $key=>$tmp_name) {
	
	
				$Filename=$_FILES['files']['name'][$key];
                $destination=$uploaddir . basename($_FILES['files']["name"][$key]);
                $sourcefile=$_FILES['files']['tmp_name'][$key];
				
				
                $uploadOk = 1;
                $imageFileType = pathinfo($destination,PATHINFO_EXTENSION);
				
				$ext = pathinfo($Filename, PATHINFO_EXTENSION);
				if( $ext !== 'jpg' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['files']['size'][$key] > $maxsize)){ echo 'Error! Invalid File Size (MAX 300KB).'; exit(); }
				
            // Check if image file is a actual image or fake image
            
                $check = getimagesize($sourcefile);
                if($check !== false) {
                    echo "File is an image - " . $check["mime"] . ".";
                    $uploadOk = 1;
                } else {
                    echo "File is not an image.";
                    $uploadOk = 0;
                }
                
                if (file_exists($destination)) {
                    unlink($destination);
                }
                 if (copy($sourcefile,$destination) and $uploadOk==1) {
                 $good='Successfully_added_'.$Filename;
                 }
                 else {
                 $good=$Filename."_NOT_UPLOADED_Error:_" . $_FILES["files"]["error"]; //.error_reporting(E_ALL | E_STRICT); //
                 }
}
 
 

    header('Location:background.php?result='.$good);
	
	break;
	
	

}
  $link=null; $stmt=null;
?></body>    
</html>