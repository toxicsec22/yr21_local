<?php
        $path=$_SERVER['DOCUMENT_ROOT']; 
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	include_once('../backendphp/functions/editok.php');
        if (!allowedToOpen(7000,'1rtc')) { echo 'No permission'; exit;}
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	  
        
        $which=$_GET['w'];
        $txndate=$_REQUEST['Date'];
        $branch=$_REQUEST['BranchNo'];
        $txnid=intval($_REQUEST['TxnID']);

switch ($which){
    case 'distri':
	$txntype=$_REQUEST['txntype'];
        if (editOk('invty_2sale',$txnid,$link,$txntype)){
            
            $sql0='create temporary table prorated (
    IDNo smallint(6) not null,
    SaleNo varchar(100) not null,
    ProratedCharge double not null
    )';
    
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();
    
        $saleno=$_REQUEST['SaleNo'];
       
	$sqlcheckbh='select lpir.IDNo from attend_30latestpositionsinclresigned lpir JOIN attend_1defaultbranchassign dba ON lpir.IDNo=dba.IDNo where DefaultBranchAssignNo='.$branch.' AND PositionID=32';
	$stmtcheckbh=$link->query($sqlcheckbh);
		
	
        $sqlsub='Select s.*, s.UnitPrice*s.Qty as Amount from invty_2salesub s where TxnID='.$txnid;
		$stmt=$link->query($sqlsub);
		$resultsub=$stmt->fetchAll();
		foreach ($resultsub as $row){
			
			$itemcode=$row['ItemCode'];
			$amount=$row['Amount'];
			// echo $amount;
			include ('../acctg/calcproratedauditcharge.php');
		}
	
	
	$sqlcheckbh='select IDNo from attend_30latestpositionsinclresigned WHERE IDNo IN (SELECT IDNo FROM mandays) AND PositionID=32';
	$stmtcheckbh=$link->query($sqlcheckbh);
	$resultbh=$stmtcheckbh->fetch();
	
	if($stmtcheckbh->rowCount()==0){ //no branch head = based on attendance
		$sql='INSERT INTO `invty_2salesubauditdistri` (`TxnID`,`ChargeToIDNo`,`ChargeAmount`,`TimeStamp`,`EncodedByNo`)
        Select '.$txnid.' as TxnID, p.IDNo as ChargeToIDNo, round(Sum(ProratedCharge),2) as ChargeAmount, Now() as `TimeStamp`, '.$_SESSION['(ak0)'].' as `EncodedByNo` from prorated p group by p.IDNo';
	} else { //by percent
		//amount to distribute //no of branch personnel
		$sqlsub='Select SUM(ProratedCharge) as TotAmount,COUNT(DISTINCT(IDNo))-1 AS CountOfBP from prorated'; // -1 for head
		$stmt=$link->query($sqlsub);
		$resultsub=$stmt->fetch();
		
		//BH With 1 BP = 60 40
		//BH With 2 BP = 44 28 28
		//BH With 3 BP = 34 22 22 22
		//BH With 4 BP = 28 18 18 18 18
		//BH With 5 BP = 25 15 15 15 15 15
		
		if($resultsub['CountOfBP']==1){
			$bh=.6; $bp=.4;
		}
		if($resultsub['CountOfBP']==2){
			$bh=.44; $bp=.28;
		}
		if($resultsub['CountOfBP']==3){
			$bh=.34; $bp=.22;
		}
		if($resultsub['CountOfBP']==4){
			$bh=.28; $bp=.18;
		}
		if($resultsub['CountOfBP']==5){
			$bh=.25; $bp=.15;
		}
		
		$bhamt=$resultsub['TotAmount']*$bh;
		$bpamt=$resultsub['TotAmount']*$bp;
		
		$sql='INSERT INTO `invty_2salesubauditdistri` (`TxnID`,`ChargeToIDNo`,`ChargeAmount`,`TimeStamp`,`EncodedByNo`)
        Select '.$txnid.' as TxnID, p.IDNo as ChargeToIDNo, round((CASE
			WHEN p.IDNo='.$resultbh['IDNo'].' THEN '.$bhamt.'
			ELSE '.$bpamt.'
		END),2) as ChargeAmount, Now() as `TimeStamp`, '.$_SESSION['(ak0)'].' as `EncodedByNo` from prorated p group by p.IDNo';
		// echo $sql;
	}
	
	$msg='';
	} else {
		$sql='Select * from `invty_2sale` where TxnID='.$txnid;
		$msg='&closeddata=true';
	}
	
	
	$stmt=$link->prepare($sql);
	$stmt->execute();
	header("Location:addeditsale.php?txntype=3&TxnID=".$txnid.$msg);
	

    break;
case 'send':
    $sql0='SELECT TxnID FROM acctg_2salemain where BranchNo='.$branch.' and Date=\''.$txndate.'\' and Date>\''.$_SESSION['nb4A'].'\';'; 
    $stmt=$link->query($sql0);
    $resultacctg=$stmt->fetch();
    if ($stmt->rowCount()==0){
        header("Location:addeditsale.php?txntype=".$_REQUEST['txntype']."&TxnID=".$txnid.'&closeddata=true');
    } else {
    $acctgtxnid=$resultacctg['TxnID'];
    
    $sql='Select concat(m.SaleNo, " - ",e.Nickname) as Particulars, d.* from `invty_2salesubauditdistri` d join invty_2sale m on m.TxnID=d.TxnID left join `1employees` e on d.ChargeToIDNo=e.IDNo where m.TxnID='.$txnid;
    $stmt=$link->query($sql);
    $result=$stmt->fetchAll();
    
    foreach ($result as $row){
        $sql='INSERT INTO `acctg_2salesub` (`TxnID`, `Particulars`, `ClientNo`, `DebitAccountID`, `Amount`, `TimeStamp`, `EncodedByNo`) Select '.$acctgtxnid.' as TxnID, \''.$row['Particulars'].'\', '.$row['ChargeToIDNo'].' as ClientNo, 200 as DebitAccountID, '.$row['ChargeAmount'].' as Amount, Now() as `TimeStamp`, '.$_SESSION['(ak0)'].' as `EncodedByNo`';
        $stmt=$link->prepare($sql);
        $stmt->execute();
    }
    $sql='Update invty_2sale Set Posted=1, PostedByNo='.$_SESSION['(ak0)'].' where TxnID='.$txnid;
    $stmt=$link->prepare($sql);
    $stmt->execute();
    header("Location:addeditsale.php?txntype=".$_REQUEST['txntype']."&TxnID=".$txnid.'&senttoacctg=done');
    }
    break;

case 'add':
    $sql='INSERT INTO `invty_2salesubauditdistri` (`TxnID`,`ChargeToIDNo`,`ChargeAmount`,`TimeStamp`,`EncodedByNo`)
        Select '.$txnid.' as TxnID, '. $_POST['ChargeToIDNo'].' as ChargeToIDNo, 0 as ChargeAmount, Now() as `TimeStamp`, '.$_SESSION['(ak0)'].' as `EncodedByNo` ';
        $stmt=$link->prepare($sql);
        $stmt->execute();
    
    header("Location:addeditsale.php?txntype=".$_REQUEST['txntype']."&TxnID=".$txnid);
    
    break;
 }
  $link=null; $stmt=null;
?>