<?php
if(session_id()==''){
		session_start();
	}
$path=$_SERVER['DOCUMENT_ROOT'];


$showbranches=false;
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$which=(!isset($_GET['w'])?'List':$_GET['w']);
//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';
// do not include people under investigation:
$investigate='0,1011,1833,1117,1065,1990,1821,1376,1249,2346,1432,1803,1517,2331,1028,1184,1192,1149';
if ($which=='Names'){ goto nopicture;}

?>

<br><div id="section" style="display: block;">
<head>
<script type="text/javascript">
var imgList = [];

function createImgList() {
	// create an array of images to apply the border effect
	var imgs = document.body.getElementsByTagName("h1");
	for (var i=0; i < imgs.length; i++) {
		if (imgs[i].className == "border0") {
			imgList.push(imgs[i]);
		}
	}
	borderchange(1);
}

function borderchange(n) {
	for (var i=0; i < imgList.length; i++) {
		imgList[i].className = "border" + n;
	}
	setTimeout(function () {borderchange(Math.abs(n-1));},700);
}

window.onload = createImgList;
</script>

<style>
body {
 background-image: url("bg.jpg");
 background-repeat: no-repeat;
  background-size: 100%;
  position: relative;
  margin-left: auto;
  margin-right: auto;
}
.border1 {
	border: 2px dotted red; color:green;
}
.border0 {
	border: 2px dotted green; color:red;
}
</style>
</head>
<?php

  $sqlwinners='SELECT r.*,
  (CASE
  WHEN Major=0 THEN "Minor"
  WHEN Major=1 THEN "Major"
  WHEN Major=2 THEN "Females only"
  WHEN Major=3 THEN "Males only"
  WHEN Major=4 THEN "Office only"
  ELSE "Office, NCR, Cavite only (no freight)"
	END)
  AS Major, CONCAT(e.Nickname, " ", e.Surname) AS EncodedBy, FullName AS Winner, IF(deptid in (10,2,3,4),Branch,department) AS `Branch/Department`, Position FROM events_1rafflewinners r LEFT JOIN `attend_30currentpositions` cp ON cp.IDNo=r.WinnerIDNo JOIN `1employees` e ON e.IDNo=r.EncodedByNo';
   
   $columnnameslistwinners=array('Winner','Position','Branch/Department', 'Prize');
  

 
if(isset($_SESSION['(ak0)'])){
	include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
	include_once('../switchboard/contents.php');
} else {
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
	goto notloggedin;
}


include_once('../backendphp/layout/linkstyle.php');
if (allowedToOpen(array(40041,40042,40043),'1rtc')){
    echo '<br><br>Employees above 30 days with us are included.<br><br>';
echo '<div>';
    echo '<a id=\'link\' href="raffle.php?w=List">Raffle Winners</a> ';
    if (allowedToOpen(40041,'1rtc')){ echo '<a id=\'link\' href="raffle.php?w=UploadPrizes">Upload Prizes</a> ';}
    if (allowedToOpen(40042,'1rtc')){ echo '<a id=\'link\' href="raffle.php?w=PickWinners&f=0">Choose Random Winners - Minor</a> ';}
    if (allowedToOpen(40042,'1rtc')){ echo '<a id=\'link\' href="raffle.php?w=PickWinners&f=1">Choose Random Winners - Major</a> ';}
    if (allowedToOpen(40042,'1rtc')){ echo '<a id=\'link\' href="raffle.php?w=PickWinners&f=2">Choose Random Winners - Female</a> ';}
    if (allowedToOpen(40042,'1rtc')){ echo '<a id=\'link\' href="raffle.php?w=PickWinners&f=3">Choose Random Winners - Male</a> ';}
    if (allowedToOpen(40042,'1rtc')){ echo '<a id=\'link\' href="raffle.php?w=PickWinners&f=5">Choose Random Winners - Office, NCR, Cavite</a> ';}
    if (allowedToOpen(40042,'1rtc')){ echo '<a id=\'link\' href="raffle.php?w=PickWinners&f=4">Choose Random Winners - Office Only</a> ';}
    if (allowedToOpen(40043,'1rtc')){ echo '<a id=\'link\' href="raffle.php?w=Names">Print Raffle Names</a> ';}
echo '</div><br/>';
}



if (in_array($which,array('List','EditSpecifics'))){
	include_once('../backendphp/layout/showencodedbybutton.php');
	echo comboBox($link,'SELECT e.IDNo, CONCAT(Nickname, " ", Surname) AS Name FROM `1employees` e WHERE Resigned=0 AND DATEDIFF(CURDATE(),DateHired)>30 AND IDNo NOT IN (SELECT WinnerIDNo FROM events_1rafflewinners) AND IDNo NOT IN ('.$investigate.') ORDER BY Name','IDNo','Name','winnerlist');
        echo comboBox($link,'SELECT TxnID,CONCAT(IF(Major=1,"Major Prize: ",""),Prize) AS Prizes FROM events_1rafflewinners WHERE WinnerIDNo=0','TxnID','Prizes','prizes');
	
         $sql=$sqlwinners;       
  
   
   $columnnameslist=$columnnameslistwinners;
  
   $columnstoadd=array('Prize');
   if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
  
}

if (in_array($which,array('Add','Edit'))){
	$idno=comboBoxValue($link,'`1employees`','CONCAT(Nickname, " ", Surname)',addslashes($_POST['Winner']),'IDNo');
        $prize=comboBoxValue($link,'events_1rafflewinners','CONCAT(IF(Major=1,"Major Prize: ",""),Prize)',addslashes($_POST['Prize']),'TxnID');
   $columnstoadd=array('Prize');
}


nopicture: 
if($which=='Names'){
	include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
}   
switch ($which)
{
	
	//Start of Case List
	case 'List':

		$title='Current Year Raffle Winners'; 
       
                                
                if (allowedToOpen(40041,'1rtc')){ 
                    
                    $formdesc='Add New Raffle Item';
                    echo comboBox($link,'SELECT 0 AS PrizeTypeNo,"Minor" AS PrizeType UNION SELECT 1,"Major" UNION SELECT 2,"Females only" UNION SELECT 3,"Males only" UNION SELECT 4,"Office only" UNION SELECT 5,"Office, NCR, Cavite only (no freight)"  ','PrizeType','PrizeTypeNo','prizetype');
                    echo comboBox($link,'SELECT TxnID,CONCAT(IF(Major=1,"Major Prize: ",""),Prize) AS Prizes FROM events_1rafflewinners ','TxnID','Prizes','allprizes');
                    
                    $method='post';
				$columnnames=array(
				array('field'=>'Prize','type'=>'text','size'=>65,'required'=>true, 'list'=>'allprizes'),
                array('field'=>'Major','caption'=>'Prize Type','type'=>'text','size'=>2,'required'=>true, 'list'=>'prizetype'));
				
							
		$action='raffle.php?w=AddPrize'; $fieldsinrow=3; $liststoshow=array();
		
		include('../backendphp/layout/inputmainform.php');
                    
                }                 
		
		
		$formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;
		$width='80%';
if (allowedToOpen(40041,'1rtc')){ 		
		array_push($columnnames,'Major');
		array_unshift($columnnames,'WinnerIDNo');
		$width='100%';
}
		
		
		include('../backendphp/layout/displayastable.php'); 
	break; 
        
    
        
        case 'Edit': //encode winner
		if (allowedToOpen(603,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			
			$sql='UPDATE `events_1rafflewinners` SET WinnerIDNo='.$idno.', EncodedByNo='.$_SESSION['(ak0)'].', `TimeStamp`=Now() WHERE WinnerIDNo=0 AND TxnID='.$prize;
				// echo $sql;
				$stmt=$link->prepare($sql); $stmt->execute();
				header('Location: raffle.php');
		
		}
	break; 
	
        case 'UploadPrizes':
            if (allowedToOpen(40041,'1rtc')){
             $title='Upload Prizes';
			$colnames=array('Prize','Major','EncodedByNo');
			$requiredcol=array('Prize','EncodedByNo');
			$required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
			$allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
			$specific_instruct='Prize description must be unique. <br><br>'
                                .'Under the field `Major`, choose one of the following: <br><br>'
                                .'0 - Minor prize <br>1 - Major prize <br> 2 - for Females only <br> 3 - for Males only '
                                . '<br> 4 - for Office employees only <br> 5 -Office, NCR, Cavite'
					. '<br><br><i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
			$tblname='events_1rafflewinners'; $firstcolumnname='Prize';
			$DOWNLOAD_DIR="../../uploads/"; 
			include('../backendphp/layout/uploaddata.php');
			if(($row-1)>0){ echo '<a href="raffle.php?w=List" target="_blank">Lookup Newly Imported Data</a>';}
			} else {
			echo 'No permission'; exit;
		}
            break;
            
            case 'AddPrize':
            if (allowedToOpen(40041,'1rtc')){
                require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$columnstoadd=array('Prize','Major');
			$sql='';
			foreach ($columnstoadd as $field) { $sql.=' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
			$sql='INSERT INTO `events_1rafflewinners` SET '.$sql
                                .' EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';				
				$stmt=$link->prepare($sql); $stmt->execute();
				header('Location: raffle.php');
            }
                else {
			echo 'No permission'; exit;
		}
            
            break;
        
        case 'PickWinners':
            if (allowedToOpen(40042,'1rtc')){
             $title='Pick Raffle Winners';
             //$formdesc='Minor prizes only';
             switch($_REQUEST['f']){
                 case 1:
                    $empfilter=' AND DATEDIFF(CURDATE(),DateHired)>180 '; //6 months
                    break;
                 case 2: //females
                     $empfilter=' AND Gender=0 ';
                     break;
                 case 3: //males
                     $empfilter=' AND Gender=1 ';
                     break;
                 case 4: //office
                     $empfilter=' AND IDNo IN (SELECT IDNo FROM attend_30currentpositions WHERE deptid NOT IN (10,2,3,4)) ';
                     break;
                 case 5: //NCR, Cavite, Office
                     $empfilter=' AND IDNo IN (SELECT IDNo FROM attend_30currentpositions cp JOIN 1branches b ON b.BranchNo=cp.BranchNo WHERE deptid NOT IN (10,2,3,4) OR AreaNo IN (0,1,2) OR (AreaNo=3 AND b.BranchNo NOT IN (45,54,55,61,56))) ';
                     break;
                 default: // all employees
                     $empfilter='';
                     break;
             }
             $sql0='SELECT * FROM events_1rafflewinners WHERE WinnerIDNo=0 AND Major='.$_REQUEST['f'];
             $stmt=$link->query($sql0); $res=$stmt->fetchAll();
             
             foreach ($res as $prize){
                 
                 $sql1='SELECT IDNo FROM `1employees` WHERE Resigned=0 AND DATEDIFF(CURDATE(),DateHired)>30 AND IDNo>1002 AND IDNo NOT IN (SELECT WinnerIDNo FROM events_1rafflewinners) AND IDNo NOT IN ('.$investigate.') '.$empfilter.' ORDER BY RAND( ) LIMIT 1;';
				//  echo $sql1;
                 $stmt=$link->query($sql1); $winner=$stmt->fetch();
                 $sql='UPDATE `events_1rafflewinners` SET WinnerIDNo='.$winner['IDNo'].', EncodedByNo='.$_SESSION['(ak0)'].', `TimeStamp`=Now() WHERE '.($_REQUEST['f']==1?'Major=1':'Major<>1').' AND WinnerIDNo=0 AND TxnID='.$prize['TxnID'];
               //  if ($_SESSION['(ak0)']==1002){   echo $sql; exit;}
            $stmt=$link->prepare($sql); $stmt->execute();
             }
             header('Location: raffle.php');
                    } else {
			echo 'No permission'; exit;
		}
            break;
            
        case 'Names':
            if (allowedToOpen(40042,'1rtc') or true){
                include_once($path.'/acrossyrs/dbinit/userinit.php');
                $link=!isset($link)?connect_db(date('Y').'_1rtc',0):$link;
             ?>
            <title>Print Names for Raffle</title>
             <style>
            body {
             margin: 10px;
            }
            table td { height: 100px; width: 250px; padding: 10px;}
            </style>
             <?php

            $sql='SELECT CONCAT(IDNo,MID(FullName,LOCATE(" - ",FullName)),"<br>(",IF(deptid IN (10,2,3,4),Branch,Department),")") AS Name FROM `attend_30currentpositions` WHERE IDNo>1002 AND IDNo NOT IN (SELECT WinnerIDNo FROM events_1rafflewinners) AND IDNo NOT IN ('.$investigate.');'; //echo $sql; exit();
            $stmt=$link->query($sql); $res=$stmt->fetchAll(); $fieldsinrow=4;
            $names=''; $counter=0;
            foreach($res as $name){
                $counter++;
                $names.=(($counter-1)%$fieldsinrow==0?'<tr>':'').'<td>'.$name['Name'].'</td>'.($counter%$fieldsinrow==0?'</tr>':'');
            }
            echo '<table>'.$names.'</table>';
            } else {
			echo 'No permission'; exit;
		}
            break;

}
$link=null; $stmt=null; $link=null;
exit();

notloggedin:
$sql=$sqlwinners;
$hidecontents=true;
$columnnames=$columnnameslistwinners;
$dbtouse=$link; $title='Christmas Raffle Winners';
echo '<title>'.$title.'</title>';
echo '<h1 class="border0" style="text-align:center;background-color:white;">'.$title.'</h1>';
$title=''; $width='80%';
include('../backendphp/layout/displayastable.php');

$link=null; $stmt=null; $link=null;
  
?>
</div> <!-- end section -->
