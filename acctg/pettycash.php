<?php
 
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';


// check if allowed
$allowed=array(582,5821,5822,5823); $allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
$which=!isset($_GET['w'])?'EnterTxn':$_GET['w'];
    if($which<>'Print'){ $showbranches=TRUE; 
	
	
	
	include_once('../switchboard/contents.php');
	if(allowedToOpen(5824,'1rtc')){
		include_once('../backendphp/layout/linkstyle.php');
			echo '<br><div><a id=\'link\' href="pettycash.php?w=EnterTxn">Petty Cash</a> <a id=\'link\' href="pettycash.php?w=PerCustodian">Petty Cash Per Custodian</a> </div><br>';
		} 
		if($which<>'PerCustodian'){
			include_once('../backendphp/layout/showencodedbybutton.php');
		}
	} else {
		 include_once($path.'/acrossyrs/dbinit/userinit.php'); $link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
	}
include_once('../backendphp/layout/regulartablestyle.php');
?>
<style>
    table,td,tr {padding: 4px;}
</style>
</head>
<body>
<?php

 
include_once('../generalinfo/lists.inc');
include_once "../acctg/acctglists.inc";
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
//FromBudgetOf
if (in_array($which,array('EnterTxn'))){
echo comboBox($link,'SELECT EntityID,Entity FROM `acctg_1budgetentities` ORDER BY Entity;','EntityID','Entity','entities');
}
if (in_array($which,array('Encode','Edit'))){
	$frombudgetof=companyandbranchValue($link, 'acctg_1budgetentities', 'Entity', $_POST['FromBudgetOf'], 'EntityID');
}
if (in_array($which,array('PerCustodian','EditSpecificsPerCustodian'))){
	$sql='SELECT pc.*,FORMAT(PettyCashOnHand,0) AS PettyCashOnHand,b.Branch AS PCBranch,PCBranchNo AS TxnID,FullName AS Custodian FROM `acctg_1pettycashpercustodian` pc LEFT JOIN 1branches b ON pc.PCBranchNo=b.BranchNo LEFT JOIN attend_30currentpositions cp ON pc.PettyCashCustodian=cp.IDNo ';
}
//End of FromBudgetOf

$sql0='SELECT ppc.PCBranchNo as PCBranchNo FROM `acctg_1pettycashpercustodian` ppc WHERE PettyCashCustodian='.$_SESSION['(ak0)']; 
                $stmt0=$link->query($sql0); $res0=$stmt0->fetch();

if (!isset($_REQUEST['PCBranchNo'])) { $pcbranchno=empty($res0['PCBranchNo'])?1:$res0['PCBranchNo'];} else { $pcbranchno=$_REQUEST['PCBranchNo'];}
                
if($stmt0->rowCount()>0 and ($pcbranchno==$res0['PCBranchNo'])) { $mypc=TRUE;} else {$mypc=FALSE;}


$sql0='SELECT Branch FROM `1branches` WHERE BranchNo='.$pcbranchno;
// echo $sql0;
$stmt0=$link->query($sql0); $res0=$stmt0->fetch();
$title='Petty Cash of '.$res0['Branch'];
if(isset($_GET['PCBranchNo']) AND ($which=='EnterTxn')){
	 echo '<title>'.$title.'</title>';
} elseif (in_array($which,array('PerCustodian','EditSpecificsPerCustodian'))){
	$title='';
} else {
	echo '<title>Petty Cash</title>';
}

$action='pettycash.php?w=EnterTxn&PCBranchNo='.$pcbranchno;



if(in_array($which,array('EnterTxn','Print'))){
    $reconcond=($which=='Print' and allowedToOpen(5823,'1rtc'))?' AND pc.Reconciled=1 ':'';
    $sqlsub='SELECT `TxnID`,`PCVNo`,`Date`, `Payee`, b.Branch,  pc.`Particulars`, t.CompanyName, pc.TIN, ca.ShortAcctID AS `Expense_Account`, `Amount`,`Entity`,`Entity` AS `FromBudgetOf`,
	    e.Nickname as `EncodedBy`, pc.`TimeStamp`, IF(pc.Reconciled=1,"YES","") AS Reconciled FROM `acctg_4pettycash` pc
                 LEFT JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=pc.AccountID JOIN `1branches` b ON b.BranchNo=pc.BranchNo
		JOIN `1employees` as e on pc.EncodedByNo=e.IDNo
		LEFT JOIN `gen_info_1tinforexpenses` t on t.TIN=pc.TIN LEFT JOIN `acctg_1budgetentities` be on be.EntityID=pc.FromBudgetOf  '
                    . 'WHERE PCBranchNo='.$pcbranchno.$reconcond.' ORDER BY `PCVNo`;'; // echo $sqlsub;
    $sqlsum='SELECT IFNULL(SUM(CASE WHEN Reconciled=1 THEN Amount END),0) AS ReconciledAmt, IFNULL(SUM(Amount),0) as TotalUsed FROM `acctg_4pettycash` pc WHERE PCBranchNo='.$pcbranchno.$reconcond ;
            $stmtsum=$link->query($sqlsum); $resultsum=$stmtsum->fetch(); 
            
    $sqlcash='SELECT pcc.*, PettyCashOnHand  FROM `acctg_4pettycashcount` pcc JOIN acctg_1pettycashpercustodian pcp ON pcc.PCBranchNo=pcp.PCBranchNo WHERE pcc.PCBranchNo='.$pcbranchno;
		$stmt=$link->query($sqlcash); $resultcash=$stmt->fetch();
	    $pcf=(empty($resultcash['PettyCashOnHand'])?0:$resultcash['PettyCashOnHand']);
}

switch ($which){

	case 'EnterTxn': 
            if (allowedToOpen(5823,'1rtc')){
                ?>
                <script>
                function chooseCustodian() { document.getElementById("Custodian").submit();}
                </script>
                <?php                
            $sql0='SELECT ppc.*, Branch FROM `acctg_1pettycashpercustodian` ppc JOIN `1branches` b ON b.BranchNo=ppc.PCBranchNo;'; 
            $stmt0=$link->query($sql0); $res0=$stmt0->fetchAll();
            $pccustodian='';
            foreach ($res0 as $pc){
            $pccustodian.='<input type="radio" name="PCBranchNo" onclick="chooseCustodian(this);" value="'.$pc['PCBranchNo'].'" '.($pc['PettyCashCustodian']==$_SESSION['(ak0)']?'checked="checked"':'').' />'.$pc['Branch'].str_repeat('&nbsp;', 15);
            }
            $pccustodian='<div style="margin-left:100px;"><br><form id="Custodian" action="pettycash.php?PCBranchNo='.$pcbranchno.'" style="display:inline"><font style="size: large; font-weight: bold;">Show Petty Cash of:</font>'.str_repeat('&nbsp;', 10).$pccustodian.'</form>';
            echo $pccustodian;
             renderotherlist('accounts','');
            } //elseif (allowedToOpen(5821,'1rtc')) { $pcbranchno=$_SESSION['bnum'];} elseif ($mypc=TRUE){ $pcbranchno=$pcbranchno;} else { $pcbranchno=$_SESSION['bnum'];}
            echo '<br><br><form method="POST" action="pettycash.php?w=Print&PCBranchNo='.$pcbranchno.'&print=true" style="display: inline;" ><input type=submit value="Print reconciled list"></form></div>';
            echo comboBox($link,'SELECT BranchNo,Branch from `1branches` where Active=1 ORDER BY Branch','BranchNo','Branch','allbranches');
            if (allowedToOpen(5822,'1rtc') or $mypc==FALSE) { goto noencode;}
            //echo comboBox($link,'SELECT BranchNo,Branch from `1branches` where Active=1 ORDER BY Branch','Branch','BranchNo','allbranches');
		?>
                <br><br><h4><?php  echo $title; ?></h4><br><br>
                
			
		<form method='POST' action='pettycash.php?w=Encode' style='display: inline;' >
                    Date<input type='date' name='Date' size=5 value='<?php echo date('Y-m-d');?>' required=true> &nbsp &nbsp
			PCVNo<input type='text' name='PCVNo' size=5 required=true >  &nbsp &nbsp
			Payee<input type='text' name='Payee' size=10 list='payees'>  &nbsp &nbsp
			Branch<input type='text' name='Branch' size=10 required=true list='allbranches'> &nbsp &nbsp
			From Budget Of <input type='text' name='FromBudgetOf' required=true list='entities' size=8 value="<?php echo $_SESSION['@brn'];?>"><font color='red'>*</font>&nbsp &nbsp
			Particulars<input type='text' name='Particulars' size=20> </br>
			TIN (numbers only)<input type='text' name='TIN' size=10>&nbsp &nbsp
                        <?php if (!allowedToOpen(5821,'1rtc') and !allowedToOpen(5822,'1rtc')){ ?>
			Expense_Account<input type='text' name='AccountID' size=10 required=true list='accounts'> &nbsp &nbsp
                        <?php } ?>
			Amount<input type='text' name='Amount' size=10 required=true value=0> &nbsp &nbsp
                        <input type="hidden" name="PCBranchNo" value="<?php  echo $pcbranchno; ?>" >
			<input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>"> &nbsp &nbsp
			<input type='submit' size=10 name='submit' value='Enter'>&nbsp &nbsp &nbsp</form>
<?php
		
		           
            noencode:
                
            
            
	    if (allowedToOpen(5823,'1rtc')){
            $columnsub=array('FromBudgetOf','PCVNo','Date', 'Payee', 'Branch', 'Particulars', 'CompanyName','TIN', 'Expense_Account','Amount','Reconciled');
                if ($mypc==TRUE){ 
                $columnstoedit=array('FromBudgetOf','PCVNo','Date', 'Payee','Branch','Particulars', 'TIN', 'Expense_Account','Amount'); 
                $colwithlistsub=array('Branch','Expense_Account'); 
				$listssub=array('Branch'=>'allbranches','Expense_Account'=>'accounts');
                $addlprocess='pettycash.php?w=Reconcile&PCBranchNo='.$pcbranchno.'&TxnID='; $addlprocesslabel='Reconcile';}
                else { $columnstoedit=array('FromBudgetOf','Branch','Expense_Account'); 
                    $colwithlistsub=array('Branch','Expense_Account'); $listssub=array('Branch'=>'allbranches','Expense_Account'=>'accounts');
                    $addlprocess='pettycash.php?w=Reconcile&PCBranchNo='.$pcbranchno.'&TxnID='; $addlprocesslabel='Reconcile';}
            }
            else { 
                $columnsub=array('FromBudgetOf','PCVNo','Date', 'Payee', 'Branch', 'Particulars', 'CompanyName','TIN', 'Amount');
                $columnstoedit=allowedToOpen(5821,'1rtc')?array('FromBudgetOf','PCVNo','Date', 'Payee','Branch','Particulars', 'TIN', 'Amount'):array();
                $colwithlistsub=array('Branch'); $listssub=array('Branch'=>'allbranches'); $sendtovch='';
            }
            if ($showenc==1) { array_push($columnsub,'EncodedBy','TimeStamp'); } else { $columnsub=$columnsub; }
            $outside=true;
            if (!allowedToOpen(5822,'1rtc')){
            $editprocess='pettycash.php?w=Edit&PCBranchNo='.$pcbranchno.'&TxnID='; $editprocesslabel='Enter'; $editok=true;
            if($mypc==TRUE) {$delprocess='pettycash.php?w=Del&PCBranchNo='.$pcbranchno.'&TxnID='; }}
            $txnidname='TxnID'; $txnsubid='TxnID'; 
            $branchlist='allbranches';
			$entities='entities';
            include('../backendphp/layout/displayastableeditcellssub.php');
	    if (allowedToOpen(5822,'1rtc')) { goto noform;}
	    // COUNT OF BILLS
	    
	    echo '<br><br><font style="font-size: 20; padding-left: 200px;">PCF:  '.number_format($pcf,2,".",",").str_repeat('&nbsp',50).'Total cash used:  '.number_format($resultsum['TotalUsed'],2,".",",").(!allowedToOpen(5821,'1rtc')?str_repeat('&nbsp',10).'Reconciled: '.number_format($resultsum['ReconciledAmt'],2,".",",").str_repeat('&nbsp',20).'<a href="pettycash.php?w=Replenish">Send to CV</a>':'').'</font>';
            if($mypc==TRUE){ $action='pettycash.php?w=EditBill&PCBranchNo='.$pcbranchno;}
           // if ($_SESSION['(ak0)']==1002) { echo 'pettycash.php?w=EditBill&PCBranchNo='.$pcbranchno;}
	    include('../backendphp/layout/calcbillsforpettycash.php');
		break;
	
	case 'Encode':
		if (!allowedToOpen(5821,'1rtc') and !allowedToOpen(582,'1rtc')){ echo 'No permission'; exit;}
                require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		include_once('../backendphp/functions/getnumber.php'); 
                $acctid=(!allowedToOpen(5821,'1rtc')?getNumber('Account',$_POST['AccountID']):100); 
                $branch=getNumber('Branch',$_POST['Branch']); 
				
		if (isset($_POST['TIN']) AND !empty($_POST['TIN'])){$tin=' TIN=\''.str_replace("-","",$_POST['TIN']).'\', ';} else { $tin='';}
		$sql='';
		$columnstoadd=array('Date','PCVNo', 'Payee','Particulars','Amount','PCBranchNo');
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; } 
		$sql='INSERT INTO `acctg_4pettycash` SET AccountID='.$acctid.', BranchNo='.$branch.', '.$tin.$sql.' FromBudgetOf=\''.$frombudgetof.'\',TimeStamp=Now(), EncodedByNo=\''.$_SESSION['(ak0)'].'\''; 
		// echo $sql; EXIT();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:pettycash.php?w=EnterTxn&PCBranchNo='.$pcbranchno);
		break;
	
	case 'Edit':
                if (!allowedToOpen(5821,'1rtc') and !allowedToOpen(582,'1rtc')){ echo 'No permission'; exit;}
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		include_once('../backendphp/functions/getnumber.php');
                $acctid=(!allowedToOpen(5821,'1rtc')?getNumber('Account',$_POST['Expense_Account']):100); 
                $branch=  comboBoxValue($link, '`1branches`', 'Branch', $_POST['Branch'], 'BranchNo');
	//	$branch=getNumber('Branch',$_POST['Branch']); 
		if (isset($_POST['TIN']) AND !empty($_POST['TIN'])){$tin=' TIN=\''.str_replace("-","",$_POST['TIN']).'\', ';} else { $tin='';}
		$sql='';
                if($mypc==TRUE){ $columnstoadd=array('Date','PCVNo', 'Payee','Particulars','Amount');} else { $columnstoadd=array();} 
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; }
		$sql='UPDATE `acctg_4pettycash` SET AccountID='.$acctid.', BranchNo='.$branch.',FromBudgetOf=\''.$frombudgetof.'\', '.$tin.$sql.' TimeStamp=Now(), EncodedByNo=\''.$_SESSION['(ak0)'].'\' WHERE TxnID='.$_REQUEST['TxnID']; 
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:pettycash.php?w=EnterTxn&PCBranchNo='.$pcbranchno);
		break;
	
	case 'Del':
                if (!allowedToOpen(5821,'1rtc') and !allowedToOpen(582,'1rtc')){ echo 'No permission'; exit;}
                if( $mypc==TRUE){
                $sql='DELETE FROM `acctg_4pettycash` where TxnID='.$_REQUEST['TxnID']; $stmt=$link->prepare($sql); $stmt->execute();}
		header('Location:pettycash.php?w=EnterTxn&PCBranchNo='.$pcbranchno);
		break;
	    
	case 'EditBill':
                if (!allowedToOpen(5821,'1rtc') and !allowedToOpen(582,'1rtc')){ echo 'No permission'; exit;}
		$bills=array('1000','500','200','100','50','20','10','5','1','025','010','005');
		$sql=''; 
		foreach ($bills as $bill){ $sql=$sql.' `' . $bill. '`='.$_POST[$bill].', '; }
		$sql='UPDATE `acctg_4pettycashcount` SET '.$sql.'  EncodedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() WHERE PCBranchNo='.$pcbranchno; 
                //if($_SESSION['(ak0)']==1002) {echo $sql; }
		$stmt=$link->prepare($sql);$stmt->execute();
		header('Location:pettycash.php?w=EnterTxn&PCBranchNo='.$pcbranchno);
	    break;

	case 'Reconcile':
            if (!allowedToOpen(582,'1rtc')){ echo 'No permission'; exit;}
	    $txnid=intval($_REQUEST['TxnID']);
	    $sql='Select PCVNo, Reconciled from `acctg_4pettycash` where TxnID='.$txnid;
	    $stmt=$link->query($sql);$result=$stmt->fetch();
	    $reconcile=$result['Reconciled']==0?1:0;
		$sql='UPDATE `acctg_4pettycash` SET  Reconciled='.$reconcile.' where PCVNo='.$result['PCVNo'].' AND PCBranchNo='.$pcbranchno;
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:pettycash.php?w=EnterTxn&PCBranchNo='.$pcbranchno);
	    break;
	case 'Replenish':
			if (!allowedToOpen(582,'1rtc')){ echo 'No permission'; exit;}
			include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
    		$vchno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',strtotime($currentyr.'-01-01'))).',2)')+1;
			

		
            $checkno=0;
	   
           $sql0='CREATE TEMPORARY TABLE replenish AS '
                   . ' SELECT pc.*,CompanyNo, pcc.PettyCashCustodian AS PettyCashCustodian FROM `acctg_4pettycash` pc JOIN `1branches` b ON b.BranchNo=pc.BranchNo '
                   . ' JOIN `acctg_1pettycashpercustodian` pcc ON pc.PCBranchNo=pcc.PCBranchNo WHERE Reconciled<>0 ';
				   // echo $sql0; exit();
           $stmt=$link->prepare($sql0); $stmt->execute();
	   $sql0='SELECT CompanyNo, IF(PCBranchNo=40,'.$_SESSION['(ak0)'].',PettyCashCustodian) AS PettyCashCustodian FROM replenish GROUP BY CompanyNo;'; 
	   $stmt0=$link->query($sql0); $res0=$stmt0->fetchAll();
	   foreach ($res0 as $vchperco){
	    $vchno=$vchno+1; $checkno=$checkno+1;
	    $sqlinsert='Insert into acctg_2cvmain Set CVNo='.$vchno.', CheckNo="PCF'.str_pad($checkno,2,'0',STR_PAD_LEFT).'", Date=Now(), DateofCheck=Now(), PayeeNo='.$vchperco['PettyCashCustodian'].',
	    Payee=(SELECT concat(FirstName," ",SurName) FROM `1employees` WHERE IDNo='.$vchperco['PettyCashCustodian'].'), CreditAccountID=403, TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)'].', PostedByNo='.$_SESSION['(ak0)']; 
           // if($_SESSION['(ak0)']==1002) { echo $sqlinsert; break;}
	    $stmt=$link->prepare($sqlinsert); $stmt->execute();
	    
	   $sql='SELECT CVNo FROM acctg_2cvmain where CVNo='.$vchno;
	   $stmt=$link->query($sql); $result=$stmt->fetch();
	   $txnid=$result['CVNo'];
	   
	   $sql='SELECT * FROM replenish WHERE CompanyNo='.$vchperco['CompanyNo'].' ORDER BY PCVNo';
	   $stmt=$link->query($sql); $result=$stmt->fetchAll();
	   
	   foreach ($result as $sub){ //to add CV sub
	      $sqlinsert='Insert into acctg_2cvsub Set CVNo='.$txnid.', FromBudgetOf='.$sub['FromBudgetOf'].', Particulars=concat("'.str_pad($sub['PCVNo'],2,'0',STR_PAD_LEFT).': ","'.$sub['Payee'].' ","'
	      .addslashes($sub['Particulars']).'"," ",date_format(\''.$sub['Date'].'\',\'%Y-%m-%d\')), TIN=\''.$sub['TIN'].'\', DebitAccountID='.$sub['AccountID'].', Amount='
	      .$sub['Amount'].', BranchNo='.$sub['BranchNo'].', TimeStamp=Now(), EncodedByNo=\''
	      .$_SESSION['(ak0)'].'\';';
	      //echo $sqlinsert; 
	      $stmt=$link->prepare($sqlinsert);
	      $stmt->execute();
	   }      
	   
	   $sqldel='DELETE FROM `acctg_4pettycash` where Reconciled<>0 ';
	    $stmt=$link->prepare($sqldel); $stmt->execute();	   
	   }
	    
	   header("Location:txnsperday.php?perday=0&w=CV");	    
	   break;
        
        case 'Print':
            $title='<a href="javascript:window.print()">'.$title.'</a>';
			$sqlb='SELECT FullName,deptheadpositionid FROM attend_30currentpositions where IDNo='.$_SESSION['(ak0)'];
			$stmtb=$link->query($sqlb); $resultb=$stmtb->fetch();
			$user=$resultb['FullName'];
            $positionid=$resultb['deptheadpositionid'];
			
            include_once('../backendphp/functions/namefromposition.php'); //if ($_SESSION['(ak0)']==1002) { echo $resfrompos['Nickname'];}
            $sql=$sqlsub; $hidecontents=1;
            $columnnames=array('PCVNo','Date', 'Payee', 'Branch', 'Particulars', 'CompanyName','TIN', 'Amount','EncodedBy');
            include('../backendphp/layout/displayastablenosort.php');
            echo '<br><br><font style="font-size: 14; padding-left: 20px;">PCF:  '.number_format($pcf,2,".",",").'<div style="float:right; padding-right: 20px;">Total cash used:  '.number_format($resultsum['TotalUsed'],2,".",",").'</div>';
            echo '<br><br><div style="float:right; padding-right: 20px;">Approved By:</div><br>Printed by '.$user.'<br>on '.date('Y-m-d h:i:s').'<br><br><div style="float:right; padding-right: 20px;">______________________<br>'.$resfrompos['Nickname'].' '.$resfrompos['SurName'].'</div>';
            break;
			
			
	case 'PerCustodian':
	
	if (!allowedToOpen(5824,'1rtc')){ echo 'No Permission'; exit(); }
	$sql.=' ORDER BY b.Branch';

        $title='Petty Cash Per Custodian'; 
		$columnnames=array('PCBranch','PettyCashOnHand','Custodian');
		$editprocess='pettycash.php?w=EditSpecificsPerCustodian&PCBranchNo='; $editprocesslabel='Edit';
        include('../backendphp/layout/displayastablenosort.php');
	
	break;
	
	case 'EditSpecificsPerCustodian':
	if (!allowedToOpen(5824,'1rtc')){ echo 'No Permission'; exit(); }
	$title='Edit Specifics';
		$txnid=intval($_GET['PCBranchNo']);
                $columnnameslist=array('PCBranch', 'PettyCashOnHand', 'Custodian');
                $columnstoadd=array('PettyCashOnHand', 'Custodian');

		echo comboBox($link,'SELECT FullName,IDNo FROM `attend_30currentpositions` WHERE PositionID IN (50,53) OR deptid=20 ORDER BY dept;','IDNo','FullName','custodianlist');
		$sql=$sql.' WHERE PCBranchNo='.$txnid;
		
		$columnstoedit=$columnstoadd;
		
		$columnnames=$columnnameslist;
		
		$columnswithlists=array('Custodian');
		$listsname=array('Custodian'=>'custodianlist');
		
		$editprocess='pettycash.php?w=EditPerCustodian&PCBranchNo='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	
	break;
	
	case 'EditPerCustodian':
	if (!allowedToOpen(5824,'1rtc')){ echo 'No Permission'; exit(); }
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
			$IDNo=comboBoxValue($link,'attend_30currentpositions','FullName',addslashes($_POST['Custodian']),'IDNo');
			$sql='UPDATE `acctg_1pettycashpercustodian` SET PettyCashOnHand="'.str_replace(",","",$_POST['PettyCashOnHand']).'",`PettyCashCustodian` = "'.$IDNo.'" WHERE `acctg_1pettycashpercustodian`.`PCBranchNo` = '.intval($_GET['PCBranchNo']).';';
					
	$link->query($sql);
	
	header("Location:pettycash.php?w=PerCustodian");
	
	break;
}
noform:
      $link=null; $stmt=null;
?>