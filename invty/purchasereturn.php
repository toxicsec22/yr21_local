<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if ($_GET['w']=='PurchaseReturn' OR $_GET['w']=='PRList'){$showbranches=true;}else{
$showbranches=false; }
if ($_GET['w']!='PrintPRprocess'){
include_once('../switchboard/contents.php');}
        // require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	include_once('../backendphp/functions/editok.php');
	include_once '../generalinfo/lists.inc'; 
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    $method='POST';

$which=(!isset($_GET['w'])?'PurchaseReturn':$_GET['w']);
if ($_GET['w']!='PrintPRprocess'){
}
if (!allowedToOpen(6961,'1rtc')) { echo 'No permission'; exit();}

switch ($which){     
case 'PurchaseReturn': 
if (!allowedToOpen(695,'1rtc')) { echo 'No permission'; exit();}
include_once('../backendphp/layout/linkstyle.php');
	echo '</br>';
	?>
	<div>
	<font size=4 face='sans-serif'>
	<a id="link" href='purchasereturn.php?w=PRPendingList'>Pending Decisions</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PurchaseReturn'>Add New Pr</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRList'>LookUp</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRDecision'>PR Decision </a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PrintPR'>Print PR</a><?php echo str_repeat('&nbsp',5); ?>
	</font></div><br>
	<?php
        $title='Add Purchase Return';
        $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'SupplierNo','type'=>'text','size'=>10,'required'=>true,'list'=>'suppliers')
                           );
        $liststoshow=array('suppliers');
        // $txntype=8;
		
		$action='purchasereturn.php?w=PRaddmain';
    include('../backendphp/layout/inputmainform.php');
          break;
		  
case 'PRaddmain':

        if (!allowedToOpen(696,'1rtc')) { echo 'No permission'; exit;}
	        //to check if editable
	if(($_POST['Date'])<$_SESSION['nb4']  or date('Y', strtotime($_POST['Date']))<>$currentyr){
		header('Location:/'.$url_folder.'/forms/errormsg.php?err=Closed'); 	 break; 
	}
	
	//To get Store Used Number
	include_once '../backendphp/functions/getnumber.php';
	
	$suppno=getValue($link,'1suppliers','SupplierName',addslashes($_POST['SupplierNo']),'SupplierNo');
	$txnnoprefix=date('y').'pr-'.str_pad($suppno,3,'0',STR_PAD_LEFT).'-';
	$txnno=getAutoTxnNo($txnnoprefix,9,'PRNo','invty_2pr',$link);
	$sql='INSERT INTO `invty_2pr` SET `Date`=\''.$_POST['Date'].'\', PRNo=\''.$txnno.'\', SupplierNo=\''.$suppno.'\', BranchNo=\''.$_SESSION['bnum'].'\',  EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now()';
	// echo $sql; break;
	$stmt=$link->prepare($sql);
	$stmt->execute();
	
	$sql='Select TxnID, PRNo from `invty_2pr` where BranchNo=\''.$_SESSION['bnum'].'\' and PRNo=\''.$txnno.'\'';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
	header("Location:purchasereturn.php?w=addeditpr&TxnID=".$result['TxnID']);
        break;		
		
case 'addeditpr':
$allowed=array(6922,6923);
if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit();}
include_once('../backendphp/layout/linkstyle.php');
	echo '</br>';
	?>
	<div>
	<font size=4 face='sans-serif'>
	<a id="link" href='purchasereturn.php?w=PRPendingList'>Pending Decisions</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PurchaseReturn'>Add New Pr</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRList'>LookUp</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRDecision'>PR Decision </a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PrintPR'>Print PR</a><?php echo str_repeat('&nbsp',5); ?>
	</font></div><br>
	<?php


include_once('../backendphp/layout/showencodedbybutton.php');
$method='POST';
$txnid=intval($_REQUEST['TxnID']);

 //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="EDDBFF";
        $rcolor[1]="FFFFFF";

 $title='Add/Edit Purchase Return<br><i>Qty must be NEGATIVE</i>';
 
  $sqlmain='select p.*,PRNo, s.SupplierName, b.Branch as RequestingBranch, CompanyName, e.Nickname as EncodedBy,e1.Nickname as PostedBy,e2.Nickname as CheckedBy from invty_2pr p 
        left join `1suppliers` as s on s.SupplierNo=p.SupplierNo
        left join `1companies` as co on co.CompanyNo=p.RCompany
        join `1branches` as b on p.BranchNo=b.BranchNo 
		
		left join `1employees` as e on p.EncodedByNo=e.IDNo
		left join `1employees` as e1 on p.PostedByNo=e1.IDNo
		left join `1employees` as e2 on p.CheckedByNo=e2.IDNo


		where TxnID='.$txnid
        .' union select p.*,PRNo, b.Branch as SupplierName, b.Branch as RequestingBranch,  CompanyName, e.Nickname as EncodedBy,e1.Nickname as PostedBy,e2.Nickname as CheckedBy from invty_2pr p 
        join `1branches` b on b.BranchNo=p.BranchNo

		left join `1employees` as e on p.EncodedByNo=e.IDNo
		left join `1employees` as e1 on p.PostedByNo=e1.IDNo
		left join `1employees` as e2 on p.CheckedByNo=e2.IDNo
		
		left join `1companies` as co on co.CompanyNo=p.RCompany  where  p.BranchNo='.$_SESSION['bnum'].' and TxnID='.$txnid;
    // echo $sqlmain;exit();
    $stmt=$link->query($sqlmain);
    $result=$stmt->fetch();
//added	Check/Reset button

	if (allowedToOpen(6963,'1rtc')) { 
		if($result['Posted']==1){
		$formdesc='</i><form method="POST" action="purchasereturn.php?w=addeditpr&TxnID='.$txnid.'"><input type="hidden" name="CheckValue" value="'.(($result['Checked']==1)?'0':'1').'"><input type="submit" name="Checker" value="Checked/Reset"></form>';
		}
	}
	if(isset($_POST['Checker'])){
		$sqlch='Update invty_2pr set Checked=\''.$_POST['CheckValue'].'\',CheckedByNo=\''.$_SESSION['(ak0)'].'\',CheckedTS=Now() where TxnID='.$_GET['TxnID'].' and Posted=1 and `Date`>\''.$_SESSION['nb4A'].'\'';
		// echo $sqlch; exit();
		$stmtch=$link->prepare($sqlch); $stmtch->execute();
		header('Location:purchasereturn.php?w=addeditpr&TxnID='.$txnid.'');
	}
	
    if($result['Checked']==1){
		$nopost=true;
		
	}
//	
    $main='';
	$sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'Category, ItemDesc');
	// $listcondition=$result['ForPONo'];
	if ($result['Posted']==0) {
	$editmain='<td>'.((allowedToOpen(6924,'1rtc'))?'<a href="purchasereturn.php?w=PReditmain&TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'">Edit</a>'.str_repeat('&nbsp',8).'<a href=purchasereturn.php?w=PRdeletemain&TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].' OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'</td>');}if ($result['Posted']==1) {$editmain='';}
        $editsub=(allowedToOpen(6924,'1rtc'))?true:false;
        
       
        $columnnamesmain=array('Date','SupplierName','PRNo','Remarks','RequestingBranch','CompanyName');
        $columnsub=array('ItemCode','Category','ItemDesc','Qty','Unit','SerialNo','ItemRemarks','DefectiveOrGoodOrForCheckUp','Decision','DecisionRefNo','TransferRecptNo');
		
		if ($showenc==1) { array_push($columnnamesmain,'TimeStamp','EncodedBy','PostedBy','CheckedBy'); array_push($columnsub,'TimeStamp','EncodedBy','DecisionTS','DecisionEncByNo');}
		if ($result['Posted']==1) {array_push($columnnamesmain,'Posted','Checked');}
		// if ($result['Posted']==0) {array_push($columnnamesmain,'Posted');}
		$colno=0;
		foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
    }
	
	$showall=isset($_GET['showall']);
    $main='<table><tr>'.$main.$editmain.'<tr></table>';
	
	$sqlsub='Select ps.*,(UnitCost*Qty*-1) as Total, CASE WHEN DecisionNo=1 THEN "Credit Memo" WHEN DecisionNo=2 THEN "Rejected" WHEN DecisionNo=3 THEN "Replaced" ELSE "" END AS Decision, c.Category, i.ItemDesc, i.Unit,If(Defective=1,"Defective",If(Defective=2,"ForCheckUp","Good Item")) as DefectiveOrGoodOrForCheckUp,  e1.Nickname as EncodedBy,e1.Nickname as DecisionEncByNo from invty_2prsub ps  join invty_1items i on i.ItemCode=ps.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e1 on ps.EncodedByNo=e1.IDNo left join `1employees` as e2 on ps.DecisionEncByNo=e2.IDNo join invty_2pr m on m.TxnID=ps.TxnID  where m.TxnID='.$txnid.' Order By '.$sortfield;
	
	// echo $sqlsub; exit();
	
	$fieldsinrow='3';
   $columnnames=array();   
   if ($result['Posted']==1) { $editsub=false;}
   $columnnames[]=array('field'=>'ItemCode', 'type'=>'text','size'=>1,'required'=>true,'autofocus'=>true);
   $columnnames[]=array('field'=>'TransferRecptNo', 'type'=>'text','size'=>6,'required'=>true,'autofocus'=>true);
   $columnnames[]=array('field'=>'Qty', 'type'=>'text','size'=>1, 'required'=>true, 'value'=>0);
   $columnnames[]=array('field'=>'SerialNo', 'type'=>'text','size'=>10, 'required'=>false);
   $columnnames[]=array('field'=>'UnitCost', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0);
   $columnnames[]=array('field'=>'ItemRemarks', 'type'=>'text','size'=>10,'required'=>false,'autofocus'=>true);
                  $columnnames[]=array('field'=>'Defective', 'caption'=>'DEFECTIVE ', 'type'=>'radio','size'=>10, 'value'=>1,'required'=>FALSE);
                  $columnnames[]=array('field'=>'Defective', 'caption'=>'GOOD ITEM ', 'type'=>'radio','size'=>10, 'value'=>0,'required'=>FALSE);
				  $columnnames[]=array('field'=>'Defective', 'caption'=>'ForCheckUp ', 'type'=>'radio','size'=>10, 'value'=>2,'required'=>FALSE);
				  
				  $columnnames[]=array('field'=>'TxnID', 'type'=>'hidden', 'size'=>0,'value'=>$txnid);
				    

    
        
    $action='purchasereturn.php?w=PRaddsub&TxnID='.$txnid;
      
	$post='1';
    $table='invty_2pr';
	$liststoshow=array();
	$withsub=true;
	if ($result['Posted']==0) {
	$editprocess='purchasereturn.php?w=PReditsub&TxnID='.$txnid.'&TxnSubId='; $editprocesslabel='Enter';  
    $delprocess='purchasereturn.php?w=PRdeletesub&TxnID='.$txnid.'&TxnSubId=';}else{$editprocess='';$editprocesslabel='';}
    $txnsubid='TxnSubId'; 
	// $sum='Total Decision: ';
	$coltototal='Total';
	$totalprice=true;
    $left='90%'; $leftmargin='91%'; $right='9%';
	$editok=true;
	if ($editok and (allowedToOpen(69251,'1rtc'))){$columnstoedit[]='UnitCost';$columnsub[]='UnitCost';$columnsub[]='Total';};
	$columnstoedit=array('ItemCode','Qty','SerialNo','ItemRemarks','UnitCost','TransferRecptNo');
	if ($result['Posted']==1) {$editok=false;$columnstoedit=array('');}
	
	
	
	
	
	
	include('../backendphp/layout/inputsubform.php');


break;


case 'PReditmain':
		if (!allowedToOpen(6924,'1rtc')){ echo 'No permission'; exit;}
		echo comboBox($link,'SELECT  CompanyName,CompanyNo FROM 1companies where CompanyNo','CompanyName','CompanyNo','CompanyList');
		$title='Edit Main';
		$txnid=intval($_GET['TxnID']);
		$sql='select Date,PRNo,SupplierNo,Remarks,CompanyName from invty_2pr p left join `1companies` as co on co.CompanyNo=p.RCompany where TxnID='.$txnid; 
		// echo $sql; exit();
		$columnstoedit=array('Date','PRNo','SupplierNo','Remarks','CompanyName');
		$columnswithlists=array('Date','PRNo','SupplierNo','Remarks','CompanyName');
		$listsname=array('CompanyName'=>'CompanyList');
		$columnnames=$columnswithlists;
		$editprocess='purchasereturn.php?w=PReditprocessmain&TxnID='.$txnid.'';
		
		include('../backendphp/layout/editspecificsforlists.php');
		break;

case 'PRaddsub':
    if (!allowedToOpen(6924,'1rtc')){ echo 'No permission'; exit;}
	$txnid=intval($_REQUEST['TxnID']);
	$postqty=$_POST['Qty'];
	if($postqty>=0){
				echo 'Qty must be negative value.'; exit();
			}
	
		$sqlcost='`invty_52latestcost` c where c.ItemCode=\''.$_POST['ItemCode'].'\'';
		$addlfield=',`Defective`';
		$addlfieldvalue=','.$_POST['Defective'].'';
		$sql='INSERT INTO `invty_2prsub` (`TxnID`, `ItemCode`, `Qty`, `SerialNo`,`ItemRemarks`,`TransferRecptNo`,`TimeStamp`, `EncodedByNo`, `UnitCost`'.$addlfield.')
Select '.$txnid.' as `TxnID`, \''.$_POST['ItemCode'].'\' as `ItemCode`, \''.$_POST['Qty'].'\' as `Qty`,  \''.$_POST['SerialNo'].'\' as `SerialNo`,\''.$_POST['ItemRemarks'].'\' as `ItemRemarks`,  \''.$_POST['TransferRecptNo'].'\' as `TransferRecptNo`, Now() as `TimeStamp`, \''.$_SESSION['(ak0)'].'\' as `EncodedByNo`, c.UnitCost'.$addlfieldvalue.' from '.$sqlcost;
	
	// echo $sql;break;	
        $stmt=$link->prepare($sql);
	$stmt->execute();
	
            $sql1='SELECT LAST_INSERT_ID() AS TxnSubId from invty_2prsub;';
			
		$stmt1=$link->query($sql1); $result1=$stmt1->fetch();
       
		
	header("Location:purchasereturn.php?w=addeditpr&TxnID=".$txnid."&TxnSubId=".$result1['TxnSubId']);
	
	break;
	
case 'PRdeletemain':          
             // $txnsubid = intval($_GET['TxnSubId']);  
			$txnid=intval($_REQUEST['TxnID']);
			$sql='DELETE FROM `invty_2pr` where Date>"'.$_SESSION['nb4A'].'" AND TxnID='.$txnid;
			// echo $sql; exit();
			$stmt=$link->prepare($sql);
			  include_once 'trailinvty.php'; recordtrail($txnid,'invty_2pr',$link,1);
			$stmt->execute();			 
			 header("Location:purchasereturn.php?w=PurchaseReturn");
		
	break;	
	
	case 'PReditprocessmain':          
             $txnid = intval($_GET['TxnID']);      
			$sql='UPDATE `invty_2pr` p SET RCompany=\''.$_POST['CompanyName'].'\',Remarks=\''.$_POST['Remarks'].'\',Date=\''.$_POST['Date'].'\',PRNo=\''.$_POST['PRNo'].'\' ,SupplierNo=\''.$_POST['SupplierNo'].'\',p.EncodedByNo=\''.$_SESSION['(ak0)'].'\', p.TimeStamp=Now() where Date>"'.$_SESSION['nb4A'].'" AND TxnID='.$txnid;
			// echo $sql; exit();
			$stmt=$link->prepare($sql);
			include_once 'trailinvty.php'; recordtrail($txnid,'invty_2pr',$link,0);
			$stmt->execute();
			
			header("Location:purchasereturn.php?w=addeditpr&TxnID=".$txnid."");
	break;	

case 'PReditsub':
	// if (!allowedToOpen(695,'1rtc')) { echo 'No permission'; exit;}
	
	$txnid=intval($_REQUEST['TxnID']);
	$txnsubid=$_REQUEST['TxnSubId'];
	
			if (!allowedToOpen(6924,'1rtc')){ echo 'No permission'; exit;} else { $columnstoedit=array('ItemCode','SerialNo','TransferRecptNo','ItemRemarks');}
		
		if (allowedToOpen(69241,'1rtc')){
			array_push($columnstoedit,'UnitCost');
		}
	$sqlupdate='UPDATE `invty_2prsub` as ps join `invty_2pr` as p on p.TxnID=ps.TxnID SET Qty='.$_POST['Qty'].', ';
	$sql=''; 
		
	foreach ($columnstoedit as $field) {
		$sql=$sql.' ' . $field. '=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' ps.EncodedByNo=\''.$_SESSION['(ak0)'].'\', ps.TimeStamp=Now() where TxnSubId='.$txnsubid . ' and p.Posted=0 and p.`Date`>\''.$_SESSION['nb4'].'\''; 
	 // echo $sql;exit();
        include_once 'trailinvty.php'; recordtrail($txnsubid,'invty_2prsub',$link,0);
	$stmt=$link->prepare($sql); $stmt->execute();
        header("Location:purchasereturn.php?w=addeditpr&TxnSubId=".$_GET['TxnSubId']."&TxnID=".$txnid);
	break;	
	
	
	case 'PRdeletesub':       
	if (!allowedToOpen(6924,'1rtc')){ echo 'No permission'; exit;}	
             $txnsubid = intval($_GET['TxnSubId']);  
			$txnid=intval($_REQUEST['TxnID']);
			$sql='DELETE FROM `invty_2prsub` where TxnSubId='.$txnsubid;
			// echo $sql; exit();
			$stmt=$link->prepare($sql);
			include_once 'trailinvty.php'; recordtrail($txnsubid,'invty_2prsub',$link,1);
			$stmt->execute();			 
			
			 header("Location:purchasereturn.php?w=addeditpr&TxnSubId=".$_GET['TxnSubId']."&TxnID=".$txnid);
		
	break;
	
	case 'PRPendingList':
	
	 include_once('../backendphp/layout/linkstyle.php');
	echo '</br>';
	?>
	<div>
	<font size=4 face='sans-serif'>
	<a id="link" href='purchasereturn.php?w=PRPendingList'>Pending Decisions</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PurchaseReturn'>Add New Pr</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRList'>LookUp</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRDecision'>PR Decision </a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PrintPR'>Print PR</a><?php echo str_repeat('&nbsp',5); ?>
	</font></div><br>
	<?php
		$title='Pending Decisions';
		$sql='SELECT (select sum(DecisionNo=0) from invty_2prsub where TxnID=ps.TxnID) as PendingQty,SupplierName,PRNo,Branch from invty_2prsub ps join invty_2pr p on p.TxnID=ps.TxnID join `1suppliers` s on s.SupplierNo=p.SupplierNo join 1branches b on b.BranchNo=p.BranchNo where DecisionNo=0 and p.Posted=1 group by ps.TxnID ';
		
		
		$columnnames=array('SupplierName','PRNo','Branch','PendingQty');
		$width='45%';
		 include('../backendphp/layout/displayastable.php');
        break;
		
		
		case 'PRDecision':
	
	include_once('../backendphp/layout/linkstyle.php');
	echo '</br>';
	?>
	<div>
	<font size=4 face='sans-serif'>
	<a id="link" href='purchasereturn.php?w=PRPendingList'>Pending Decisions</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PurchaseReturn'>Add New Pr</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRList'>LookUp</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRDecision'>PR Decision </a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PrintPR'>Print PR</a><?php echo str_repeat('&nbsp',5); ?>
	</font></div><br>
	<?php
	if (isset($_REQUEST['SupplierNo']) and !empty($_REQUEST['SupplierNo'])){
		$_SESSION['SupplierNo']=$_REQUEST['SupplierNo']; 
        }
//        } else { 
//            $_SESSION['SupplierNo']= !isset($_SESSION['SupplierNo'])?'':$_SESSION['SupplierNo'];
//        }
		
        if (isset($_REQUEST['ShowAll'])) { 
            unset($_SESSION['SupplierNo']); 
            $suppliercondition='';		
            $suppliername='ALL';
        } elseif (!isset($_SESSION['SupplierNo']) or !isset($_POST['SupplierNo'])){
			unset($_SESSION['SupplierNo']); 
			$suppliercondition=' DecisionNo=0 AND';
			$suppliername='';
		}
            else {        
		$suppliercondition=' p.SupplierNo='.$_SESSION['SupplierNo'].' AND ';		
                $suppliername=comboBoxValue ($link,'1suppliers','SupplierNo',$_SESSION['SupplierNo'],'SupplierName');
        }
		
		
		$title='Purchase Return Decision - '. $suppliername;
		$formdesc= '</BR><form method="POST" action="purchasereturn.php?w=PRDecision">
				<p style="font-size:80%;">
				Supplier: <input type="text" name="SupplierNo" list="Supplier""/>
				<input type="submit" name="btnSubmit" value="Filter"/> &nbsp; <input type="submit" name="ShowAll" value="Show All"/></p>
				'.comboBox($link, 'SELECT SupplierName,s.SupplierNo FROM 1suppliers s JOIN invty_2mrr m ON m.SupplierNo=s.SupplierNo WHERE Inactive=0 and InvtySupplier=1 GROUP BY s.SupplierNo','SupplierName', 'SupplierNo', 'Supplier').'
				</form></div></BR></BR>';
                $formdesc.='</i><div style="background-color: #e6e6e6;
                        width: 1100px;
                        border: 2px solid grey;
                        padding: 25px;
                        margin: 25px;">
                        Decision Options: '
                        . '<ol>'
                        . '<li><b>Credit Memo</b> - The value of the item will be deducted from payment to the supplier. This is entered in Acctg like a negative MRR.</li>'
                        . '<li><b>Rejected</b> - Supplier will not accept responsibility for the defective item.  Item must be marked with 000 to differentiate from other defective items. Item must be returned to the client directly, if they choose to take it back. </li>'
                        . '<li><b>Replaced</b> - Supplier will replace the defective item with a good one.  Once encoded as replaced, item activity will show a new item entered as GOOD.</li>'
                        . '</ol></br>
						<b>TransferRecptNo</b> - From transfer receipts of branches when they returned to warehouse.
						</br>
						<b>DecisionRefNo</b> - Document reference from the supplier, such as credit memo number, etc.
						</div>'
                        . '<i>';
                
		$txnidname='TxnSubId';
		if(allowedToOpen(6962,'1rtc')){
		$tdform=true;
                $tdforminput='<input type="text" name="DecisionRefNo" placeholder="Reference -- blank if none">'
                        . '<input list="decisions" name="Decision" placeholder="Decision" required> <datalist id="decisions"><option value="Credit Memo"><option value="Rejected"><option value="Replaced"></datalist>';
		}
		$editprocess1='purchasereturn.php?w=update&TxnSubId='; $editprocesslabel1='Submit'; 
		$sql1='SELECT SupplierName,p.Remarks,Date,PRNo from invty_2pr p join `1suppliers` s on s.SupplierNo=p.SupplierNo join invty_2prsub ps on ps.TxnID=p.TxnID join invty_1items i on i.ItemCode=ps.ItemCode join invty_1category c on c.CatNo=i.CatNo where '.$suppliercondition.' ps.Posted=0 and p.Posted=1   group by PRNo order by Date Desc';
		$sql2='Select ItemRemarks,SerialNo,If(Defective=1,"Defective",If(Defective=2,"ForCheckUp","Good Item")) as DefectiveOrGoodOrForCheckUp,ps.TxnSubId,CASE WHEN DecisionNo=1 THEN "Credit Memo" WHEN DecisionNo=2 THEN "Rejected" WHEN DecisionNo=3 THEN "Replaced" ELSE "" END AS Decision,c.Category,i.ItemDesc as Description,i.Unit,ps.Qty,ps.ItemCode,DecisionRefNo,TransferRecptNo from invty_2prsub ps join `invty_1items` i on i.ItemCode=ps.ItemCode join `invty_1category` c on c.CatNo=i.CatNo join invty_2pr p on p.TxnID=ps.TxnID ';                $secondcondition=' AND ps.Posted=0 and p.Posted=1 ';
		// echo $sql1;
		// echo '</br>';
		// echo $sql2;
		$groupby='PRNo';
		$orderby=' ORDER By PRNo';
	
		$nocount=true;
		$columnnames1=array('Date','PRNo','Remarks','SupplierName');
        $columnnames2=array('ItemCode','Category','Description','Qty','SerialNo','ItemRemarks','DefectiveOrGoodOrForCheckUp','DecisionRefNo','Decision','TransferRecptNo');
		
        include('../backendphp/layout/displayastablewithsub.php');
		if (isset($_POST['SupplierNo'])){
		$title='Posted';
		$formdesc='';
		unset($tdform);
		if(allowedToOpen(6921,'1rtc')){
		$tdform1=true;
		}
		$editprocess2='purchasereturn.php?w=updates&TxnSubId='; $editprocesslabel2='Unpost'; 
		$sql1='SELECT SupplierName,p.Remarks,Date,PRNo from invty_2pr p join `1suppliers` s on s.SupplierNo=p.SupplierNo join invty_2prsub ps on ps.TxnID=p.TxnID join invty_1items i on i.ItemCode=ps.ItemCode join invty_1category c on c.CatNo=i.CatNo where '.$suppliercondition.'  ps.Posted=1 group by PRNo order by Date Desc';
                $secondcondition=' AND ps.Posted=1 ';
	// echo $sql1; exit();
		include('../backendphp/layout/displayastablewithsub.php');}
        break;
		
	case 'update':
		
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnSubId']);
                switch ($_POST['Decision']){ case 'Credit Memo': $decisionno=1; break; case 'Rejected': $decisionno=2; break; case 'Replaced': $decisionno=3; break; default: $decisionno=0;}
                $DecisionRefNo=!empty($_POST['DecisionRefNo'])?', DecisionRefNo=\''.addslashes($_POST['DecisionRefNo']).'\'':'';
				
		$sql='Update `invty_2prsub` ps join invty_2pr p SET DecisionNo=\''.$decisionno.'\''.$DecisionRefNo.', DecisionTS=Now(), ps.Posted=1,DecisionEncByNo='.$_SESSION['(ak0)'].' where ps.TxnSubId='.$txnid.' AND p.Posted=1';
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		include_once 'trailinvty.php'; recordtrail($txnid,'invty_2prsub',$link,0);
		$stmt->execute();
		header('Location:purchasereturn.php?w=PRDecision');
		
		break;
		case 'updates':
		
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnSubId']);
		$sql='Update `invty_2prsub` ps join invty_2pr p SET DecisionNo=null,DecisionRefNo=null,ps.Posted=0  where ps.TxnSubId='.$txnid.' and p.TimeStamp>'.$_SESSION['nb4'].'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		
		$stmt->execute();
		header('Location:purchasereturn.php?w=PRDecision');
		
		break;
		
		case 'PRList':
		include_once('../backendphp/layout/linkstyle.php');
	echo '</br>';
	?>
	<div>
	<font size=4 face='sans-serif'>
	<a id="link" href='purchasereturn.php?w=PRPendingList'>Pending Decisions</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PurchaseReturn'>Add New Pr</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRList'>LookUp</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRDecision'>PR Decision </a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PrintPR'>Print PR</a><?php echo str_repeat('&nbsp',5); ?>
	</font></div><br>
	<?php
		echo '</br>';
		$title='Purchase Return List';
		echo '<form method="post" style="display:inline;" action="purchasereturn.php?w=PRList&perday=1">
		Choose Date: <input type="date" name="Date" value="'.date('Y-m-d').'">
		<input type="submit" name="lookup" value="lookup">
		</form>&nbsp;&nbsp;
		';
		echo '<form method="post" style="display:inline;" action="purchasereturn.php?w=PRList&perday=0">
		Choose Month (1 - 12): <input type="text" name="Month" value="'.date('m').'">
		<input type="submit" name="lookup" value="lookup">
		</form></br>
		';
		if(!isset($_POST['lookup'])){$datecondition='Date=curdate()';}
		if(isset($_POST['lookup'])){
		$perday=$_GET['perday'];
		if ($perday==1){
		$datecondition=' Date=\''.$_POST['Date'].'\' ';
		} else {
		$datecondition=' Month(p.Date)=\''.$_POST['Month'].'\' ';
		}
		}
	
	
		$sql='select p.TxnID,Date,SupplierName,PRNo,p.Posted from invty_2pr p join 1suppliers s on s.SupplierNo=p.SupplierNo where '.$datecondition.' and p.BranchNo=\''.$_SESSION['bnum'].'\' ';
		// echo $sql; exit();
		$columnnames=array('Date','SupplierName','PRNo','Posted');
		$editprocess='purchasereturn.php?w=addeditpr&TxnID='; $editprocesslabel='Edit';
		$width='40%';
		
		
		
		include('../backendphp/layout/displayastable.php');
		
		break;
		
		
		case 'PrintPR':
		?>
		<title>Print PR</title>
		<?php
		if (!allowedToOpen(763,'1rtc')){   echo 'No permission'; exit;}
		$title='Print PR';
		include_once('../backendphp/layout/linkstyle.php');
	echo '</br>';
	?>
	<div>
	<font size=4 face='sans-serif'>
	<a id="link" href='purchasereturn.php?w=PRPendingList'>Pending Decisions</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PurchaseReturn'>Add New Pr</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRList'>LookUp</a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PRDecision'>PR Decision </a><?php echo str_repeat('&nbsp',5); ?>
	<a id="link" href='purchasereturn.php?w=PrintPR'>Print PR</a><?php echo str_repeat('&nbsp',5); ?>
	</font></div><br>
	<form action="purchasereturn.php?w=PrintPRprocess" method='POST'>
	From PR Number:  <input type=text size=20 name='PRFrom' autocomplete='off'><br>
	To PR Number:  <input type=text size=20 name='PRTo' autocomplete='off'><br>
	<input type=submit name=submit value='Print preview'>
	</form>
	<?php
		
		break;
		
		
	CASE 'PrintPRprocess': 
	?>
	<title>Print PR</title>
	<?php
	include ('../backendphp/layout/standardprintsettings.php');
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link; 
	$sqlmain='select p.*,CompanyName, s.SupplierName,CONCAT (Nickname,\' \',SurName) as CheckedBy from invty_2pr p
			join `1suppliers` as s on p.SupplierNo=s.SupplierNo left join `1companies` co on co.CompanyNo=p.RCompany left join 1employees e on e.IDNo=p.CheckedByNo where  p.PRNo>=\''.addslashes($_POST['PRFrom']).'\' and p.PRNo<=\''.addslashes($_POST['PRTo']).'\' and Checked=1';
	echo '<center><a href="javascript:window.print()">PR from '.addslashes($_POST['PRFrom']).' to '.addslashes($_POST['PRTo']).'</a></center><br>';
		$stmt=$link->query($sqlmain);
		$result=$stmt->fetchAll();
	foreach ($result as $mainrow){
	$main='<div class="keeptog"><font face="arial" size="3"><table width="100%" class="maintable">
	<tr>
	<td>PR No. '.$mainrow['PRNo'].'</td>
	<td>Supplier:  '.$mainrow['SupplierName'].'</td>
	<td>CompanyName: '.$mainrow['CompanyName'].'</td>
	<td>Date Received:  '.$mainrow['Date'].'</td>
	</tr></table></font>';
	$sqlsub='Select ItemRemarks,SerialNo,If(Defective=1,"Defective",If(Defective=2,"ForCheckUp","Good Item")) as DefectiveOrGoodOrForCheckUp,ps.ItemCode, ps.UnitCost,ps.Qty,concat(c.Category,\' \', i.ItemDesc) as Description, i.Unit, (ps.UnitCost*ps.Qty*-1) as Amount from invty_2prsub ps join invty_1items i on i.ItemCode=ps.ItemCode join invty_1category c on c.CatNo=i.CatNo join invty_2pr p on p.TxnID=ps.TxnID where p.PRNo=\''.($mainrow['PRNo']).'\' Order by Category'; //txntype=6 and 
		$stmt=$link->query($sqlsub);
		$resultsub=$stmt->fetchAll();
		$sub='<table width="100%" class="subtable"><tr>
	<td width=5%>ItemCode</td>
	<td width=65%>Description</td>
	<td width=5%>Qty</td>
	<td width=5%>SerialNo</td>
	<td width=5%>ItemRemarks</td>
	<td width=5%>DefectiveOrGoodOrForCheckUp</td>
	<td width=5%>Unit</td>
	<td width=5%>UnitCost</td>
	<td width=5%>Amount</td></tr>';
	foreach ($resultsub as $row){    
	$sub=$sub.'<tr>
	<td width=5%>'.$row['ItemCode'].'</td>
	<td width=65%>'.$row['Description'].'</td>
	<td width=5%>'.$row['Qty'].'</td>
	<td width=5%>'.$row['SerialNo'].'</td>
	<td width=5%>'.$row['ItemRemarks'].'</td>
	<td width=5%>'.$row['DefectiveOrGoodOrForCheckUp'].'</td>
	<td width=5%>'.$row['Unit'].'</td>
	<td width=5%>'.$row['UnitCost'].'</td>
	<td width=5%>'.$row['Amount'].'</td></tr>';
	$sqlsum='Select count(ItemCode) as LineItems, sum(ps.UnitCost*ps.Qty*-1) as Total from invty_2prsub ps join invty_2pr p on p.TxnID=ps.TxnID where p.PRNo=\''.($mainrow['PRNo']).'\''; //removed txntype=6 and 

    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $total='<div style="float:right">Line Items: '.$result['LineItems'].str_repeat('&nbsp',20).'Total:  '.number_format($result['Total'],2).'</div>';
	}
	echo $main.$sub.'</table><br>'.$total.'</div><br><hr>';
	echo '</br>';
	echo '<p align="right">____________</p>';
	echo '<p align="right">Checked By:&nbsp; '.$mainrow['CheckedBy'].''.str_repeat('&nbsp',7).'Received by</p>';
	}

    
      break;
		  
	
		  
}
         
    

    ?>
