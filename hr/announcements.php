<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
if (!allowedToOpen(7309,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');



include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=!isset($_REQUEST['w'])?'List':($_REQUEST['w']);


if (in_array($which,array('List','EditSpecifics'))){
   $sql='SELECT a.*,(CASE WHEN OpenFor = 1 THEN "All" WHEN OpenFor = 2 THEN "Office" ELSE "Branch/Warehouse" END) AS `OpenFor?`,DATEDIFF(DateTo,DateFrom) AS NoOfDays,IF(`Show?`=1,"Yes","No") AS `Showed?`,AnnounceID AS TxnID, CONCAT(e.Nickname," ",e.Surname) AS EncodedBy FROM hr_1announcements a 
JOIN 1employees e ON e.IDNo=a.EncodedByNo ';
$columnnames=array('TxnID','DateFrom','DateTo','NoOfDays','Msg','OpenFor?','Showed?','Filename','EncodedBy','TimeStamp');
   $columnstoadd=array('DateFrom','DateTo','Msg','OpenFor','Filename');
}


switch ($which){
case 'List':
include_once $path.'/acrossyrs/commonfunctions/popupwindow.php';
$title='Announcements';
$formdesc='</i><br><br>
   <div>
   <div style="float:left;">
   <form action="announcements.php?w=Add" method="POST" enctype="multipart/form-data" style="display: inline">
        DateFrom <input type="date" name="datefrom" value='.(date("Y-m-d")).' size=5>
        DateTo <input type="date" name="dateto" value='.(date("Y-m-d")).' size=5>
        Msg <input id="annceValue" type="text" name="msg" required size=40><br><br>
        OpenFor? <select name="OpenFor"><option value="1">1 - All</option><option value="2">2 - Office</option><option value="3">3 - Branch/Warehouse</option></select> Filename/s (<i>Leave blank if no data</i>) <input type="text" name="Filename" value="" size=25 placeholder="Filenamech1,Filenamech2">
        <input type="submit" name="submit" value="Add Announcement">
   </form><br><br><hr><br><form action="announcements.php?w=PrUploadPicMultiple" method="post" enctype="multipart/form-data">
    Upload Picture (<b>"Click Link"</b>) (960h x 720v [px] - Max:300KB) .jpg only 
    <input type="file" name="files[]" multiple >
    <input type="submit" name="submit" value="Add Image">  &nbsp; &nbsp; <a href="announcements.php?w=ImgLists">View All "Click Here" Images</a>
</form><br>
   <input id="preview" type="submit" value="Announcement Preview" onClick="preview()">
   </div>
   <div style="margin-left:80%;">
	<b>Allowed Tags</b>
	<ul>
		<li>&lt;a href="LINK HERE"&gt; - hyperlink.</li>
		<li>&lt;br&gt; - new line.</li>
		<li>&lt;i&gt;&lt;/i&gt; - italic.</li>
		<li>&lt;b&gt;&lt;/b&gt; - bold.</li>
		<li>&lt;u&gt;&lt;/u&gt; - underline.</li>
		<li>&lt;sub&gt;&lt;/sub&gt; - subscript.</li>
		<li>&lt;sup&gt;&lt;/sup&gt; - superscript.</li>
		<li>&lt;font color="red"&gt;&lt;/font&gt; - font color.</li>
	</ul>
	</div>
   </div>';    
    
$formdesc.='<br><div style="background-color:white;width:30%;padding:4px;">* If hyperlink with image<br> &nbsp; <b>clickhere</b>.php?annce=<b>TxnID</b> (if single image)<br>&nbsp; <b>clickheremultiple</b>.php?annce=<b>TxnID</b> (if multiple image)</div><br>';


$width="100%";
$editprocess='announcements.php?w=SetorUnset&TxnID=';
$editprocesslabel='Set/Unset';

$addlprocess='announcements.php?w=EditSpecifics&TxnID=';
$addlprocesslabel='Edit';
$delprocess='announcements.php?w=Delete&TxnID=';
     include('../backendphp/layout/displayastable.php');
	 
     break;
	 
	 
case 'ImgLists':

$title = 'Images List';
echo '<title>'.$title.'</title>';
echo '<h3>'.$title.'</h3><br/>';
echo '<table border="1px solid black" style="border-collapse:collapse;">
<tr>';
		

		$files = glob("../generalinfo/anncepics/*");
		$cnttr=1;
		foreach ($files as $filename) {
			if ($cnttr % 4 == 1){
					echo '</tr><tr align="left"><td style="padding:5px;">';
				} else {
						
					echo '<td style="padding:5px;">';
				}
				echo 'Filename: '.str_replace('./generalinfo/anncepics/','',$filename).'<br/>';
				echo 'DateUploaded: '.str_replace('./generalinfo/anncepics/','',date ("F d Y.", filemtime($filename))).'<br/>';
				echo '<a href="'.$filename.'"><img width="100px" height="100px" src="'.$filename.'"/></a><br/>
			</td>';
			$cnttr++;
			
		}
		?>
</table>
<?php
break;	


	case 'PrUploadPicMultiple':
	
	extract($_POST);
$error=array();
$extension=array("jpeg","jpg","png","gif");
$uploaddir="../generalinfo/anncepics/";
 
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
 
    header('Location:announcements.php?result='.$good);
	
	break;
	 
	 case 'EditSpecifics':
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']);

		$sql=$sql.' WHERE AnnounceID='.$txnid;
		$columnstoedit=$columnstoadd;
		$editprocess='announcements.php?w=Edit&TxnID='.$txnid;		
		include('../backendphp/layout/editspecificsforlists.php');
	break;
	
	
	case 'Edit':
	
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$columnstoadd=array('DateFrom','DateTo','Msg','OpenFor','Filename');

		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		
		$sql='UPDATE `hr_1announcements` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE AnnounceID='.intval($_GET['TxnID']);
		$stmt=$link->prepare($sql); $stmt->execute();
		
		header("Location:announcements.php");
    break;
     
	case 'Add':
	
	$dateto = strtotime($_POST['dateto']);
	$datefrom = strtotime($_POST['datefrom']);
	$datediff = $dateto - $datefrom;

	$cntdate=round($datediff / (60 * 60 * 24));
	
	if (allowedToOpen(7310,'1rtc')){
		goto longer7days;	
	}
	
	if($cntdate>7){
			echo 'Error: '.$cntdate.' day(s).<br>';
			echo 'Maximum days allowed: 7 days';
			echo '<br><a href="announcements.php">Go back</a>';
			exit();
	}
	longer7days:
	
	$sql='INSERT INTO `hr_1announcements` (`DateFrom`,`DateTo`,`Msg`,`OpenFor`,`Filename`,`EncodedByNo`,`TimeStamp`)
	VALUES ("'.$_POST['datefrom'].'","'.$_POST['dateto'].'",\''.$_POST['msg'].'\',\''.$_POST['OpenFor'].'\',\''.$_POST['Filename'].'\', '.$_SESSION['(ak0)'].',NOW())';
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
    header('Location:announcements.php');
    break;
	
	
	case 'Delete':
		$sql='DELETE FROM `hr_1announcements` WHERE AnnounceID='.$_REQUEST['TxnID'].'';
		$stmt=$link->prepare($sql);
		$stmt->execute();
    header('Location:announcements.php');
    break;
	
	
	case 'SetorUnset':
		$sql='UPDATE `hr_1announcements` SET `Show?`=IF(`Show?`=0,1,0) WHERE AnnounceID='.$_REQUEST['TxnID'].'';
		echo $sql;
		$stmt=$link->prepare($sql);
		$stmt->execute();
    header('Location:announcements.php');
    break;
	
	
}
  $link=null;  $stmt=null;
?>