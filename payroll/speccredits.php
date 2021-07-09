<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

if (!allowedToOpen(array(792,7921),'1rtc')) {   echo 'No permission'; exit;}
$showbranches=false;
include_once('../switchboard/contents.php');

$w=!isset($_GET['w'])?'Lookup':$_GET['w'];

if(in_array($w, array('addmain','add','deletemain','editmainspecifics','edit','delete'))){
require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
}

if(in_array($w, array('Lookup','addmain','add','editmainspecifics','editmain'))){
include_once $path.'/acrossyrs/commonfunctions/listoptions.php'; 
$listvia='SELECT * FROM payroll_0disbursevia ';
}

if(in_array($w, array('addmain','editmainspecifics'))){
$disburse=comboBoxValue($link,'payroll_0disbursevia','Disburse_Via',$_POST['Disburse_Via'],'DisburseVia');
}

if(in_array($w, array('Lookup','editmain'))){
echo comboBox($link,$listvia,'DisburseVia','Disburse_Via','disbursevia');
}

switch($w){
   case 'SpecCredits': 
	    if (!allowedToOpen(792,'1rtc')) {   echo 'No permission'; exit;}
		
            $liststoshow=array('employeeid');
            include_once "../generalinfo/lists.inc";
            renderlist('employeeid');

if (!isset($_GET['TxnID'])){ goto noform;} else {
	 include_once('../backendphp/layout/showencodedbybutton.php');
	 include_once('../backendphp/functions/editok.php');
	$sqlmain='Select ocm.TxnID,ocm.Remarks,DateofCredit,ocm.DisburseVia,Disburse_Via, Batch,Posted,CONCAT("P" ,(FORMAT(sum(Amount),2))) as Amount from payroll_30othercreditsmain ocm join payroll_30othercreditssub ocs on ocs.TxnID=ocm.TxnID JOIN payroll_0disbursevia dv ON dv.DisburseVia=ocm.DisburseVia where ocm.TxnID='.$_GET['TxnID'].'';
	 // echo $sqlmain; exit();
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
    $main='';
	$title='Add Special Credits';
	if ($result['Posted']==0) {
	$editmain='<td><a href=speccredits.php?w=editmain&TxnID='.$result['TxnID'].'&DateofCredit='.$result['DateofCredit'].'&Batch='.$result['Batch'].'>Edit</a>'.str_repeat('&nbsp',8).'<a href=speccredits.php?w=deletemain&action_token='.html_escape($_SESSION['action_token']).'&TxnID='.$result['TxnID'].' OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';}else{$editmain='';}
        $editsub=true;
		if (allowedToOpen(7921,'1rtc')) { 
        $editmain=$editmain.(($result['Posted']==1 and (in_array($result['DisburseVia'],array(1,3))))?'<td><a href="exportto'.strtolower($result['Disburse_Via']).'.php?w=SpecCredits&DateofCredit='.$result['DateofCredit'].'&Batch='.$result['Batch'].'&Amount='.$result['Amount'].'">Export To '.$result['Disburse_Via'].'</a></td>':'');}
        $columnnamesmain=array('DateofCredit','Disburse_Via','Batch','Remarks');
		$columnsub=array('IDNo','FullName','Amount','Remarks');
		
		if ($showenc==1) {array_push($columnsub,'TimeStamp','EncodedByNo');}
		if ($result['Posted']==1) {array_push($columnnamesmain,'Posted');}
		$colno=0;

	foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
	
	$sqlsub='Select ocs.*,TxnSubID,concat(FirstName, \' \', Surname) as FullName from payroll_30othercreditssub ocs LEFT JOIN `1employees` e on e.IDNo=ocs.IDNo where TxnID='.$_GET['TxnID'].' ';
	$coltototal='Amount';
	$totalprice=true;
	if ($result['Posted']==0) {
	$columnnames[]=array('field'=>'IDNo', 'type'=>'text','size'=>10, 'required'=>true, 'list'=>'employeeid');
	$columnnames[]=array('field'=>'Amount', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0);
	$columnnames[]=array('field'=>'Remarks', 'type'=>'text','size'=>10, 'required'=>true);}else{$editsub=false; $columnnames=array();}
        
	$action='speccredits.php?w=add&TxnID='.$_GET['TxnID'].'';
	$method='POST';
	$editprocess='speccredits.php?w=editcreditdate&TxnID='.$_GET['TxnID'].'&TxnSubId=';
	$delprocess='speccredits.php?w=delete&TxnSubId=';
	$txnsubid='TxnSubID';
	 $columnstoedit=array();
	 $editok=$editsub; $editprocesslabel='Edit';
	 
	$postvalue='1';
    $table='payroll_30othercreditsmain';
	$txnid=$_GET['TxnID'];
	$txntype='SpecCredits';
	$withsub=true;include('../backendphp/layout/inputsubform.php');
   
}
            break;	
			
 case 'Lookup':
		if (!isset($_POST['submit'])){
	       $dateofcredit=date('Y-m-d',time());
	    } 
		if(!isset($_GET['TxnID']) and !isset($_POST['lookup'])){
		?>
<form action='speccredits.php?w=addmain' method='POST' enctype='multipart/form-data'>
        Date of Credit<input type='date' name='DateofCredit'  value='<?php echo $dateofcredit; ?>'autocomplete='off' size=8>&nbsp &nbsp &nbsp
        Disburse Via<input type='text' name='Disburse_Via' value='UBP' autocomplete='off' size=5 list='disbursevia'>&nbsp &nbsp &nbsp
	Batch<input type='text' name='Batch' value='01' autocomplete='off' size=3>&nbsp &nbsp &nbsp
	Remarks<input type='text' name='Remarks' size="10" autocomplete='off' size=3>&nbsp &nbsp &nbsp
	<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>" /> 
        <input type='submit' name='submit' value='Submit'>
	 </form>
<?php
		}
		
		$dateofcredit='DateofCredit';
		$title='Special Credits';
		$width='50%';
		$sql='Select ocm.TxnID,DateofCredit, Disburse_Via, Batch,ocm.Remarks, FORMAT(sum(Amount),2) as Amount, COUNT(TxnSubId) AS NoofRecipients from payroll_30othercreditsmain ocm left join payroll_30othercreditssub ocs on ocs.TxnID=ocm.TxnID JOIN payroll_0disbursevia dv ON dv.DisburseVia=ocm.DisburseVia Group By ocm.TxnID ORDER BY DateofCredit DESC ';
		// echo $sql;exit();
		$columnnames=array('DateofCredit','Disburse_Via','Batch','Remarks','NoofRecipients','Amount');
		$editprocess='speccredits.php?w=SpecCredits&TxnID=';
		$editprocesslabel='Lookup';
		include('../backendphp/layout/displayastable.php');
	break;
			
 case 'addmain':
	if (!allowedToOpen(792,'1rtc')) { echo 'No permission'; exit; }
	$sqlinsert='INSERT INTO `payroll_30othercreditsmain` SET ';
        $sql='';
        $columnstoadd=array('DateofCredit');
       
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' DisburseVia='.$disburse.', Batch='.$_POST['Batch'].',Remarks=\''.$_POST['Remarks'].'\''; 
	// echo $sql; exit();
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='SELECT LAST_INSERT_ID() AS TxnID from payroll_30othercreditsmain;';
		$stmt=$link->query($sql); $result=$stmt->fetch();
	
	header("Location:speccredits.php?w=SpecCredits&TxnID=".$result['TxnID']);
        break;
      
        
        
    case 'add':
        
		$title='add';
		$txnid=intval($_GET['TxnID']);
		$sql1='INSERT INTO `payroll_30othercreditssub` SET TxnID='.$txnid.', IDNo='.$_POST['IDNo'].', Amount='.$_POST['Amount'].', Remarks=\''.$_POST['Remarks'].'\', EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=now() ';
       // echo $sql1; exit();
		$stmt=$link->prepare($sql1);
		$stmt->execute();
		header("Location:speccredits.php?w=SpecCredits&TxnID=".$txnid);
		
	break;
	
	case 'deletemain':
		$title='Edit Main';
		$txnid=intval($_GET['TxnID']);
		
		$sql='delete from payroll_30othercreditssub where TxnID='.$txnid; 
		$stmt=$link->prepare($sql);
		$stmt->execute();
		
		$sql='delete from payroll_30othercreditsmain where Posted=0 AND TxnID='.$txnid; 
		$stmt=$link->prepare($sql);
		$stmt->execute();
		
		header('Location:speccredits.php?w=Lookup');
	break;
	
	case 'editmain':
            
		$title='Edit Main';
		$txnid=intval($_GET['TxnID']);
		$sql='select Remarks,DateofCredit, Disburse_Via, Batch from payroll_30othercreditsmain ocm JOIN payroll_0disbursevia dv ON dv.DisburseVia=ocm.DisburseVia where TxnID='.$txnid; 
		// echo $sql; exit();
		$columnstoedit=array('DateofCredit','Disburse_Via','Batch','Remarks');
		$columnswithlists=array('Disburse_Via');
                $listsname=array('Disburse_Via'=>'disbursevia');
		$columnnames=$columnstoedit;
		$editprocess='speccredits.php?w=editmainspecifics&TxnID='.$txnid.'';
		
		include('../backendphp/layout/editspecificsforlists.php');
	break;
	
	case 'editmainspecifics':
            $txnid = intval($_GET['TxnID']);      
			$sql='UPDATE `payroll_30othercreditsmain` ocs SET Remarks=\''.$_POST['Remarks'].'\',DateofCredit=\''.$_POST['DateofCredit'].'\', DisburseVia='.$disburse.', Batch='.$_POST['Batch'].' WHERE Posted=0 AND TxnID='.$txnid;
			// echo $sql; exit();
			$stmt=$link->prepare($sql);
			$stmt->execute();
			
			header("Location:speccredits.php?w=SpecCredits&TxnID=".$txnid."&DateofCredit=".$_POST['DateofCredit']."&Batch=".$_POST['Batch']);
		
	break;	
		
		
	break;
	
	case 'editcreditdate':
        $liststoshow=array('employeeid');
		include_once "../generalinfo/lists.inc";
		renderlist('employeeid');
		$title='Edit Specifics';
		$txnsubid=intval($_GET['TxnSubId']);
		$sql='select IDNo,Amount,Remarks from payroll_30othercreditssub where TxnSubId='.$txnsubid; 
		// echo $sql;exit();
		$columnstoedit=array('IDNo','Amount','Remarks');
		$columnswithlists=array('IDNo','Amount','Remarks');
		$listsname=array('IDNo'=>'employeeid');
		$columnnames=$columnswithlists;
		
		$editprocess="speccredits.php?w=edit&TxnID=".$_GET['TxnID']."&TxnSubId=".$txnsubid."";
		
		include('../backendphp/layout/editspecificsforlists.php');
		
	break;
	
	case 'edit':
            $columnstoadd=array('IDNo','Amount','Remarks');  
			$sql='';
			foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
            $txnsubid = intval($_GET['TxnSubId']);      
			$sql='UPDATE `payroll_30othercreditssub` ocs SET '.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=now() WHERE TxnSubId='.$txnsubid;
			$stmt=$link->prepare($sql);
			$stmt->execute();
			header("Location:speccredits.php?w=SpecCredits&TxnID=".$_GET['TxnID']);
		
	break;
	
	case 'delete':          
             $txnsubid = intval($_GET['TxnSubId']);  
	
			$sql='DELETE FROM `payroll_30othercreditssub` where TxnSubId='.$txnsubid;
			$stmt=$link->prepare($sql);
			$stmt->execute();			 
			header('Location: '.$_SERVER['HTTP_REFERER'].'');
		
	break;
    
}
noform: