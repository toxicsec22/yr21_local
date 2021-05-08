<?php
 $path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
 if (!allowedToOpen(7030,'1rtc')) { echo 'No permission'; exit; }
 $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
 if (isset($_GET['w']) AND ($_GET['w']<>'Print')){
		include_once('../switchboard/contents.php');
		
 } else {
	 include_once($path.'/acrossyrs/dbinit/userinit.php');
 }

?>
<br><div id="section" style="display: block;">
<?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);
//Get the property ID
if (isset($_GET['PropID'])) { $PropID = $_GET['PropID']; } else { $PropID = 'N/A'; }


if (in_array($which,array('List','EditSpecifics','ReturnSpecifics')))
{
   echo comboBox($link,'SELECT *, CONCAT(PropertyDesc,IFNULL(CONCAT("SN ",SerialNo),"")) AS Property FROM `admin_1property`','Property','PropertyID','propertylist');
   echo comboBox($link,'SELECT IDNo, CONCAT(FirstName, " ", Surname) AS Name FROM `1employees` WHERE Resigned=0 ORDER BY Name','IDNo','Name','employeelist');
   include_once('../backendphp/layout/showencodedbybutton.php');
   $sql='SELECT pa.*, CONCAT(e.FirstName, " ", e.Surname) AS AssignedTo, Branch AS AssignedBranch, department AS Department, CONCAT(e1.Nickname, " ", e1.Surname) AS EncodedBy, CONCAT(e2.Nickname, " ", e2.Surname) AS ReturnEncodedBy FROM admin_1property p JOIN admin_2propertyassign AS pa ON p.PropID = pa.PropID LEFT JOIN `1_gamit`.`0idinfo` e ON e.IDNo=pa.AssignToIDNo LEFT JOIN `1branches` b ON b.BranchNo=pa.AssignBranchNo LEFT JOIN `1departments` d ON d.deptid=pa.DeptID LEFT JOIN `1_gamit`.`0idinfo` e1 ON e1.IDNo=pa.EncodedByNo LEFT JOIN `1_gamit`.`0idinfo` e2 ON e2.IDNo=pa.ReturnEncodedByNo WHERE pa.PropID='.$PropID;
  
   $columnnameslist=array('AssignDate','AssignedTo','Department','AssignedBranch','AssignRemarks','BookValueAsOfIssueDate','DateReturned','ReturnRemarks','BookValueAsOfReturnDate');
   
   if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp','ReturnEncodedBy','ReturnTS');}
} 

if (in_array($which,array('Add','Edit'))){
	// error_reporting(E_ALL);
	// ini_set('display_errors', 1);
    if(substr($_POST['AssignDate'],0,4)<>$currentyr){ echo 'Please enter the correct date.  This is '.$currentyr.' data.'; exit;}
	
	$sqli='SELECT IDNo FROM 1employees WHERE CONCAT(FirstName, " ", Surname) LIKE "'.addslashes($_POST['AssignedTo']).'" AND Resigned=0'; //echo $sqli;
   $stmti=$link->query($sqli); $resi=$stmti->fetch();
   $idno=$resi['IDNo'];
   
   // $idno=comboBoxValue($link,'`1employees`','CONCAT(FirstName, " ", Surname)',addslashes($_POST['AssignedTo']),'IDNo');
   
   $sql1='SELECT deptid,BranchNo FROM attend_30currentpositions WHERE IDNo='.$idno;
   $stmt=$link->query($sql1); $res=$stmt->fetch();
   $columnstoadd=array('AssignDate','AssignRemarks','BookValueAsOfIssueDate');
        }

switch ($which){
	case 'List':
                $stmt=$link->query('SELECT p.*, Branch AS PropertyOfBranch FROM admin_1property p JOIN `1branches` b ON b.BranchNo=p.PropOfBranchNo WHERE PropID='.$PropID); 
                $res=$stmt->fetch();
		$title='Property Assignment for '.$res['PropertyID'];
                $formdesc='</i><br><br><div style="background-color: white; margin-left: 30%; width: 30%; padding: 15px;">'
                        . '<p style="font-weight: bold; font-size: medium; color: darkblue;">'.$res['PropertyDesc'].'</p>'
                        . '<br><br>Serial No '.$res['SerialNo'].'<br>Owned By '.$res['PropertyOfBranch']
                        .'<br>Date of Purchase '.$res['DatePurchased'].'<br>Supplier Details '.$res['SupplierNameAddressTel'];
                $formdesc.='</div><br><br><a href="propertyassign.php?w=Upload">Upload Data</a><i>';
		$method='post';
		
		//Columns to Add
		$columnnames=array(
		array('field'=>'PropertyID', 'type'=>'text','size'=>10,'required'=>true,'list'=>'propertylist','value'=>$res['PropertyID']), 
		array('field'=>'AssignedTo', 'type'=>'text','size'=>15,'required'=>true,'list'=>'employeelist'),
		array('field'=>'AssignDate','type'=>'date','size'=>10,'required'=>true),
		array('field'=>'AssignRemarks','type'=>'text','size'=>20,'required'=>false),
		array('field'=>'BookValueAsOfIssueDate', 'caption'=>'Book Value as of Issue Date','type'=>'text','size'=>15,'required'=>true));
			
		$action='propertyassign.php?w=Add'; $fieldsinrow=5; $liststoshow=array();
		
		//Count admin_1property Number of Rows
		$nRowsProp = $link->query('select count(*) from admin_1property')->fetchColumn();
		
		//Condition to Display Input Form
		if ($nRowsProp==0)
		{
			echo '<h4>Please add property first.</h4>';
		}
		else
		{
			include('../backendphp/layout/inputmainform.php');
		}
		
		//Processes
                if (allowedToOpen(7031,'1rtc')) { $delprocess='propertyassign.php?w=Delete&TxnID=';}

		$title=''; $formdesc=''; $txnidname='TxnID';
		$columnnames=$columnnameslist;
		$editprocess='propertyassign.php?w=EditSpecifics&PropID='.$PropID.'&TxnID='; $editprocesslabel='Edit';
		$addlprocess2='propertyassign.php?w=Print&TxnID=';
	$addlprocesslabel2='Print';
                $addlprocess='propertyassign.php?w=ReturnSpecifics&PropID='.$PropID.'&TxnID='; $addlprocesslabel='Return';
		//Count admin_2propertyassign Number of Rows
		$nRows = $link->query('select count(*) from admin_2propertyassign')->fetchColumn();
		
		//Condition to Display Table
		if ($nRows==0)
		{
			echo '<h4>No Data</h4>';
		}
		else
		{
			include('../backendphp/layout/displayastable.php');
		}
	break;
	
	//Start of Case Add
	case 'Add':
		if (allowedToOpen(7030,'1rtc')){
			if(''.date('Y-m-d').''>''.$currentyr.'-12-20'.''){
				echo 'Data should be encoded next year.'; exit();
			}
                require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                $propid=comboBoxValue($link,'admin_1property','PropertyID',addslashes($_POST['PropertyID']),'PropID');
                $sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='INSERT INTO `admin_2propertyassign` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' PropID='.$propid.', AssignToIDNo='.$idno.', DeptID='.$res['deptid'].',AssignBranchNo='.$res['BranchNo'].', TimeStamp=Now()'; 
                $link->query($sql);
		}		
		header("Location:propertyassign.php?w=List&PropID=".$propid);
	break; //End Case Add
	
	case 'Upload':
		if(''.date('Y-m-d').''>''.$currentyr.'-12-20'.''){
            echo 'Data should be encoded next year.'; exit();
        }
        $title='Upload Property Assignment';
        $colnames=array('PropID','AssignToIDNo','DeptID','AssignBranchNo','AssignDate','AssignRemarks','BookValueAsOfIssueDate','EncodedByNo');
        $requiredcol=array('PropID (NOT PropertyID)','AssignToIDNo','DeptID','AssignBranchNo','AssignDate','BookValueAsOfIssueDate','EncodedByNo');
        $required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
        $specific_instruct='Make sure that the DeptID corresponds with AssignBranchNo.  These information must be true for the employee <u>at the time of assignment</u>'
                .'<br>PropID is <u>different</u> from PropertyID.  This is the integer that pertains to the specific record.'
                . '<br><br><i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname='admin_2propertyassign'; $firstcolumnname='PropID';
        $DOWNLOAD_DIR="../../uploads/"; ;
        include('../backendphp/layout/uploaddata.php');
        if(($row-1)>0){ echo '<a href="propertyassign.php?w=List" target="_blank">Lookup Newly Imported Data</a>';}
        
        break;
	//Start Case Delete
	case 'Delete':
		if(''.date('Y-m-d').''>''.$currentyr.'-12-20'.''){
            echo 'Data should be encoded next year.'; exit();
        }
        if (!allowedToOpen(7031,'1rtc')) { echo 'No permission'; exit; }
		$sql='DELETE FROM `admin_2propertyassign` WHERE TxnID='.$_GET['TxnID'];
		$stmt=$link->prepare($sql); $stmt->execute();
		   
		header("Location:".$_SERVER['HTTP_REFERER']);
	break; //End Case Delete
    
	//Start Case EditSpecifics
	case 'EditSpecifics':
		if(''.date('Y-m-d').''>''.$currentyr.'-12-20'.''){
            echo 'Data should be encoded next year.'; exit();
        }
        if (!allowedToOpen(7032,'1rtc')) { echo 'No permission'; exit; }
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']); $PropID=$_REQUEST['PropID'];
		$sql=$sql.' AND TxnID='.$txnid;
		$columnstoedit=array_diff($columnnameslist,array('Department','AssignedBranch','ReturnRemarks','BookValueAsOfReturnDate'));	
                $columnnames=$columnnameslist;
		$columnswithlists=array('AssignedTo');
		$listsname=array('AssignedTo'=>'employeelist');
		$editprocess='propertyassign.php?w=Edit&PropID='.$PropID.'&TxnID='.$txnid; 
		include('../backendphp/layout/editspecificsforlists.php');
	break; //End Case EditSpecifics
	
	//Start Case Edit
    case 'Edit':
		 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                 $PropID=$_REQUEST['PropID'];
		 if (allowedToOpen(7030,'1rtc')){ //editable if within the day
		$sql='';
		foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		$sql='UPDATE `admin_2propertyassign` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' AssignToIDNo='.$idno.', DeptID='.$res['deptid'].',AssignBranchNo='.$res['BranchNo'].', TimeStamp=Now() WHERE TxnID='.$_GET['TxnID'].' AND '.(allowedToOpen(7032,'1rtc')?'1=1':'Date(`TimeStamp`)=CURDATE()');
		$stmt=$link->prepare($sql);
		$stmt->execute();
		 }

		header("Location:propertyassign.php?w=List&PropID=".$PropID);
	break; //End Case Edit
        case 'ReturnSpecifics':
		$title='Return Specifics';
		$txnid=intval($_GET['TxnID']); 
		$sql=$sql.' AND TxnID='.$txnid;
		$columnstoedit=array('DateReturned','ReturnRemarks','BookValueAsOfReturnDate');	
                $columnnames=$columnnameslist;
		$editprocess='propertyassign.php?w=Return&PropID='.$PropID.'&TxnID='.$txnid; 
		include('../backendphp/layout/editspecificsforlists.php');
	break; 
    
	case 'Return':
		if(''.date('Y-m-d').''>''.$currentyr.'-12-20'.''){
            echo 'Data should be encoded next year.'; exit();
        }
		 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                 if (allowedToOpen(7030,'1rtc')){ //editable if within the day
                 $columnstoedit=array('DateReturned','ReturnRemarks','BookValueAsOfReturnDate');	
                 $sql='';
		foreach ($columnstoedit as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
		$sql='UPDATE `admin_2propertyassign` SET ReturnEncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' ReturnTS=Now() WHERE TxnID='.$_GET['TxnID'].' AND '.(allowedToOpen(7032,'1rtc')?'1=1':'Date(`ReturnTS`)=CURDATE()');
                $stmt=$link->prepare($sql);
		$stmt->execute();
		 }

		header("Location:propertyassign.php?w=List&PropID=".$PropID);
	break;

case 'Print':
			$stmt=$link->query('SELECT p.*,AssignDate,DateReturned,BookValueAsOfIssueDate, Branch AS PropertyOfBranch,CONCAT(id.FirstName, " ",id.Surname) AS CurrentlyAssignedTo FROM admin_1property p JOIN `1branches` b ON b.BranchNo=p.PropOfBranchNo JOIN admin_2propertyassign pa ON p.PropID=pa.PropID LEFT JOIN `1_gamit`.`0idinfo` id ON id.IDNo=pa.AssignToIDNo WHERE pa.TxnID='.intval($_GET['TxnID']).''); 
                $res=$stmt->fetch();
				
				$title='Company Property Acknowledgement of Receipt';
				echo '<title>'.$title.'</title>';
				echo $title.'<br><br>';
				$space = str_repeat("&nbsp;",10);
				echo '<p>'.$space.'I understand that the Company equipment issued to me is to be used for Company business only and belongs to the Company. It is expected that reasonable care be taken when operating company property so as to be able to return it in good operating condition, including all accessories such as chargers, batteries, etc.</p>';
				echo '<p>'.$space.'The use of the unit is monitored by the Head Office. Installation of unnecessary application is not allowed. Below are the list of authorized apps for cell phone and laptops:</p>';
				
				echo '<div align="center"><table><tr><td>Cell phone:</td><td>Viber, Chrome & Waze</td></tr><tr><td valign="top">Laptop:</td><td>Viber, Chrome, Mozilla Thunderbird & Open/Libre Office,
		Team viewer & Dropbox (as needed)</td></tr></table></div>';
		
		
		echo '<p>'.$space.'I also understand and acknowledge that the value of any lost, broken or unreturned item may be deducted from my paycheck, including my final pay. I further understand and acknowledge that if I incur additional charges, such as over-usage of mobile data, there will be a deduction for my outright paycheck or I need to pay for those charges. I authorize the company to deduct my salary as necessary.</p>';
		
		echo '<p>'.$space.'Finally, I also understand that if I do not comply with these provisions, I may be subject to disciplinary action up to and including termination.</p>';
		echo '<p>'.$space.'By initialing below, I acknowledge that I have received the following company property:</p>';
		
		echo '<br><div align="center"><table style="border-collapse: collapse;" border="1px solid"><tr><th>Initial</th><th>Item</th><th>Current Value</th><th>Date Issued</th><th>Date Returned</th></tr><tr><td></td><td><table><tr><td>Asset ID: </td><td>'.$res["PropertyID"].'</td><tr><tr><td>SerialNo: </td><td>'.$res["SerialNo"].'</td><tr><tr><td>Description: </td><td>'.$res["PropertyDesc"].'</td><tr></table></td><td>P'.number_format($res["BookValueAsOfIssueDate"],2).'</td><td>'.$res["AssignDate"].'</td><td>'.$res["DateReturned"].'</td></tr></table></div>';
			
			echo '<br><br><br>';
			echo '<table width="100%"><tr><td></td><td></td><td></td></tr>
				<tr><td>____________</td><td><u>'.strtoupper($res['CurrentlyAssignedTo']).'</u></td><td>____________</td></tr>
				<tr><td>Signature</td><td>Printed Name</td><td>Date</td></tr>
				<tr><td style="padding-bottom:60px"></td><td></td><td></td></tr>
				<tr><td>Issued & Recorded by:</td><td>____________</td><td>____________</td></tr>
				<tr><td></td><td>Printed Name</td><td>Date</td></tr>
				</table>';
			break;


}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body></html>