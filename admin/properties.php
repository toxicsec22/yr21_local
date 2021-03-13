<?php
 $path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
 if (!allowedToOpen(7030,'1rtc')) { echo 'No permission'; exit; }
 $showbranches=false;

include_once('../switchboard/contents.php');
	
 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$which=(!isset($_GET['w'])?'List':$_GET['w']);

if (in_array($which,array('List','EditSpecifics'))){
   echo comboBox($link,'SELECT BranchNo, Branch FROM `1branches` ORDER BY Branch','BranchNo','Branch','branchlist');
   include_once('../backendphp/layout/showencodedbybutton.php');
   
   $sql='SELECT p.*,IF(p.Active=1,"Yes","No") AS Active, Branch AS PropertyOfBranch, PropID AS TxnID, CONCAT(Nickname, " ",Surname) AS EncodedBy, FORMAT(PurchaseAmount,2) AS PurchaseAmount FROM `admin_1property` p LEFT JOIN `1branches` b ON b.BranchNo=p.PropOfBranchNo '
           . 'LEFT JOIN `1employees` e ON e.IDNo=p.EncodedByNo ';
	
   $columnnameslist=array('PropertyID','PropertyDesc', 'SerialNo', 'DatePurchased','PurchaseAmount', 'PropertyOfBranch','SupplierNameAddressTel','Active');
   if ($showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
} 

if (in_array($which,array('List','Unassigned'))){
	$formdesc='</i><br><a href="properties.php?w=Upload">Upload Data</a> &nbsp; &nbsp; '
                    . '<a href="properties.php?w=AssignList" target=_blank>List of Assignments</a> &nbsp; &nbsp; '
                    . '<a href="properties.php?w=Unassigned" target=_blank>Unassigned Properties</a><i>';
}
if (in_array($which,array('Add','Edit'))){
    // if((substr($_POST['DatePurchased'],0,4)<>$currentyr) AND (!allowedToOpen(7031,'1rtc'))){ echo 'Please enter the correct date.  This is '.$currentyr.' data.'; exit; }
   $branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['PropertyOfBranch']),'BranchNo');
   $columnstoadd=array('PropertyID', 'PropertyDesc', 'SerialNo', 'DatePurchased','SupplierNameAddressTel');
        }

switch ($which)
{
	case 'List':
            $title='List of Properties'; 
			
			// $formdesc='</i><br><a href="properties.php?w=Upload">Upload Data</a> &nbsp; &nbsp; '
                    // . '<a href="properties.php?w=AssignList" target=_blank>List of Assignments</a><i>';
            $columnnames=array(
                    array('field'=>'PropertyID','caption'=>'Property ID','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'PropertyDesc', 'caption'=>'Description', 'type'=>'text','size'=>50, 'required'=>true),
                    array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false),
                    array('field'=>'DatePurchased', 'type'=>'date','size'=>10,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'PurchaseAmount','type'=>'text','size'=>5, 'required'=>true),
                    array('field'=>'PropertyOfBranch', 'caption'=>'Property Of Branch ', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>$_SESSION['@brn'], 'list'=>'branchlist'),
                    array('field'=>'SupplierNameAddressTel', 'type'=>'text','size'=>50, 'required'=>true)
                    );
    
            $action='properties.php?w=Add'; $fieldsinrow=4; $liststoshow=array();
			
      $sqlexec='SELECT (SELECT COUNT(PropID) FROM admin_1property) AS TotalNoOfProperties, (SELECT COUNT(PropID) FROM admin_1property WHERE Active=1) AS TotalNoOfActive, (SELECT COUNT(PropID) FROM admin_1property WHERE Active=0) AS TotalNoOfInActive,(SELECT  COUNT(p.PropID) FROM admin_2propertyassign a 
                JOIN (SELECT PropID,  MAX(AssignDate) AS MaxAssignDate FROM admin_2propertyassign  GROUP BY PropID ) b ON a.AssignDate=b.MaxAssignDate AND a.PropID=b.PropID 
                JOIN `1employees` e ON e.IDNo=a.EncodedByNo JOIN `1_gamit`.`0idinfo` id ON id.IDNo=a.AssignToIDNo
                JOIN `1departments` d ON d.deptID=a.DeptID JOIN `1branches` b ON b.BranchNO=a.AssignBranchNo
                JOIN admin_1property p ON p.PropID=a.PropID WHERE (DateReturned IS NOT NULL AND DateReturned<>"" AND DateReturned<>"0000-00-00") AND p.Active=1) + (SELECT COUNT(PropID) FROM admin_1property WHERE Active=1 AND PropID NOT IN (SELECT PropID FROM admin_2propertyassign)) AS UnassignedProperties;';
				$stmtexec=$link->query($sqlexec); $result=$stmtexec->fetch();
				echo '<br><br><div style="padding:10px;width:25%;background-color:white">Total No. of Properties: '.$result['TotalNoOfProperties'];
				echo '<br>Total No. of Active: '.$result['TotalNoOfActive'];
				echo '<br>Total No. of Inactive: '.$result['TotalNoOfInActive'];
				echo '<br>Total No. of Unassigned Properties: '.$result['UnassignedProperties'];
				echo '</div>';
	 include('../backendphp/layout/inputmainform.php');
         // if (allowedToOpen(7031,'1rtc')) { $delprocess='properties.php?w=Delete&PropID=';$editprocess='properties.php?w=EditSpecifics&PropID='; $editprocesslabel='Edit';}
         if (allowedToOpen(7032,'1rtc')) { $editprocess='properties.php?w=EditSpecifics&PropID='; $editprocesslabel='Edit'; 
		$addlprocess2='properties.php?w=ActiveInactive&PropID='; $addlprocesslabel2='Active/Inactive'; }
		 if (allowedToOpen(7031,'1rtc')) {$delprocess='properties.php?w=Delete&PropID=';}
	
      //Processes
	
	$addlprocess='propertyassign.php?w=List&PropID=';
	$addlprocesslabel='Assign To';
	
	
	$title=''; $formdesc=''; $txnid='PropID';
	$columnnames=$columnnameslist;       
	$sql.='ORDER BY PropertyOfBranch';
	include('../backendphp/layout/displayastable.php');       
	break; //End of Case List
    
    case 'Add':            
	if (allowedToOpen(7030,'1rtc')){
        if(''.date('Y-m-d').''>''.$currentyr.'-12-20'.''){
            echo 'Data should be encoded next year.'; exit();
        }
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        
        $sql='INSERT INTO `admin_1property` SET PurchaseAmount="'.(!is_numeric($_POST['PurchaseAmount'])?str_replace(',', '',$_POST['PurchaseAmount']):$_POST['PurchaseAmount']).'",EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' PropOfBranchNo='.$branchno.', TimeStamp=Now()'; 
		// echo $sql; exit();
        $link->query($sql);
        }
        header("Location:properties.php");
            break;
        
    case 'Upload':
        if(''.date('Y-m-d').''>''.$currentyr.'-12-20'.''){
            echo 'Data should be encoded next year.'; exit();
        }
        $title='Upload Office Property List';
        $colnames=array('PropertyID','PropertyDesc','SerialNo','DatePurchased','PurchaseAmount','SupplierNameAddressTel','PropOfBranchNo','EncodedByNo');
        $requiredcol=array('PropertyID','PropertyDesc','DatePurchased','PurchaseAmount','SupplierNameAddressTel','PropOfBranchNo','EncodedByNo');
        $required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
        $specific_instruct='PropertyID must be unique. Make sure there is no duplicate in the data.'
                . '<br><br><i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname='admin_1property'; $firstcolumnname='PropertyID';
        $DOWNLOAD_DIR="../../uploads/"; ;
        include('../backendphp/layout/uploaddata.php');
        if(($row-1)>0){ echo '<a href="properties.php?w=List" target="_blank">Lookup Newly Imported Data</a>';}
        
        break;
        
	//Start Of Case Delete
    case 'Delete':
        if(''.date('Y-m-d').''>''.$currentyr.'-12-20'.''){
            echo 'Data should be encoded next year.'; exit();
        }
	//access
         if (allowedToOpen(7031,'1rtc')){
         require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `admin_1property` WHERE PropID='.$_GET['PropID'];
                $link->query($sql);
         }
        header("Location:properties.php");
        break; //End of Case Delete
	
	//Start Of Case EditSpecifics
    case 'EditSpecifics':
        if(''.date('Y-m-d').''>''.$currentyr.'-12-20'.''){
            echo 'Data should be encoded next year.'; exit();
        }
        $title='Edit Specifics';
	$txnid=$_GET['PropID'];

	//Condition For Edit Specifics
	$sql.=' WHERE PropID='.$txnid; 
	// $columnstoedit=array_diff($columnnameslist,array('PropertyOfBranch','EncodedBy','TimeStamp'));	
	$columnstoedit=array_diff($columnnameslist,array('EncodedBy','TimeStamp','PropID','Active'));	
	// $columnnames=$columnnameslist;
	$columnnames=$columnstoedit;
	$columnswithlists=array('PropertyOfBranch');
	//Input List
        $listsname=array('PropertyOfBranch'=>'branchlist');
	$editprocess='properties.php?w=Edit&PropID='.$txnid;
        include('../backendphp/layout/editspecificsforlists.php');
	break;
	//End of Case EditSpecifics

	//Start Of Case Edit
    case 'Edit':
        if (allowedToOpen(7032,'1rtc')){
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        
	$sql='';
	foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
	
	$sql='UPDATE `admin_1property` SET PurchaseAmount='.(!is_numeric($_POST['PurchaseAmount'])?str_replace(',', '',$_POST['PurchaseAmount']):$_POST['PurchaseAmount']).',PropOfBranchNo='.$branchno.',EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE PropID='.$_GET['PropID'];
	$link->query($sql);
        }
    header("Location:properties.php");
    break;
    //End Of Case Edit
    
        case 'AssignList':
            $title='List of Assignments'; $txnidfield='PropID';
            $columnnames=array('Department','CurrentlyAssignedTo','PropertyID','PropertyDesc','AssignDate','AssignRemarks','BookValueAsOfIssueDate','Active','EncodedBy','TimeStamp');
            $sql='SELECT a.*,IF(p.Active=1,"Yes","No") AS Active,PropertyID,PropertyDesc,CONCAT(id.FirstName, " ",id.Surname) AS CurrentlyAssignedTo, IF(a.DeptID=10,Branch,Department) AS Department, 
                CONCAT(id2.Nickname, " ",id2.Surname) AS EncodedBy, FORMAT(BookValueAsOfIssueDate,0) AS BookValueAsOfIssueDate FROM admin_2propertyassign a 
                JOIN (SELECT PropID,  MAX(AssignDate) AS MaxAssignDate FROM admin_2propertyassign  GROUP BY PropID ) b ON a.AssignDate=b.MaxAssignDate AND a.PropID=b.PropID 
                LEFT JOIN `1_gamit`.`0idinfo` id2 ON id2.IDNo=a.EncodedByNo JOIN `1_gamit`.`0idinfo` id ON id.IDNo=a.AssignToIDNo
                LEFT JOIN `1departments` d ON d.deptID=a.DeptID JOIN `1branches` b ON b.BranchNO=a.AssignBranchNo
                JOIN admin_1property p ON p.PropID=a.PropID';
            include('../backendphp/layout/displayastable.php'); 
            break;
			
		case 'Unassigned':
		$title='Unassigned Properties'; $txnidfield='PropID';
		$addlprocess='propertyassign.php?w=List&PropID=';
		$addlprocesslabel='Assign To';
	
					$formdesc.='<br>';
            $columnnames=array('PropertyID','PropertyDesc');
            $sql='SELECT  p.PropID AS TxnID,PropertyID,PropertyDesc FROM admin_2propertyassign a 
                JOIN (SELECT PropID,  MAX(AssignDate) AS MaxAssignDate FROM admin_2propertyassign  GROUP BY PropID ) b ON a.AssignDate=b.MaxAssignDate AND a.PropID=b.PropID 
                JOIN `1employees` e ON e.IDNo=a.EncodedByNo JOIN `1_gamit`.`0idinfo` id ON id.IDNo=a.AssignToIDNo
                JOIN `1departments` d ON d.deptID=a.DeptID JOIN `1branches` b ON b.BranchNO=a.AssignBranchNo
                JOIN admin_1property p ON p.PropID=a.PropID WHERE (DateReturned IS NOT NULL AND DateReturned<>"" AND DateReturned<>"0000-00-00") AND p.Active=1 UNION ALL SELECT PropID AS TxnID,PropertyID,PropertyDesc FROM admin_1property WHERE Active=1 AND PropID NOT IN (SELECT PropID FROM admin_2propertyassign);';
				$width='50%';
            include('../backendphp/layout/displayastablenosort.php'); 
		break;
		
		case 'ActiveInactive':
		$sql='UPDATE `admin_1property` SET Active=IF(Active=1,0,1) WHERE PropID='.$_GET['PropID'];
		$link->query($sql);
		header("Location:properties.php");
		break;
}
  
?>