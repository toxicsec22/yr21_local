<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(58224,58225,58226,58227,58228),'1rtc')) { echo 'No permission'; exit();}
include_once $path.'/acrossyrs/dbinit/userinit.php';
$showbranches=false;
include_once('../switchboard/contents.php');
include_once ('../generalinfo/trailgeninfo.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');
echo comboBox($link,'SELECT BEID, Explanation FROM `acctg_0blotterbranchexplanation`','BEID','Explanation','explanationlist');

if (allowedToOpen(58229,'1rtc')) {
	echo '<br><a id="link" href="stmtblotterfindings.php" target="_blank">Blotter Findings Statements</a>';
}
if (allowedToOpen(58225,'1rtc')) {
	echo ' <a id="link" href="blotterfindings.php?w=Cancelled" target="_blank">Cancelled Invoices per Month</a>';
}
if (allowedToOpen(58235,'1rtc')) {
echo ' <a id="link" href="blotterfindings.php">Blotter Findings</a>';	
echo ' <a id="link" href="blotterfindings.php?w=BranchExplanationsList">Branch Explanations List</a>';
}
echo'</br></br>';
?>
	
    <?php
	
	$which=(!isset($_GET['w'])?'lists':$_GET['w']);
	
		if (in_array($which,array('lists','EditSpecifics'))){
			$cnccondi='';
			$txnos='(1,2,3,4,5,7,10,30,32,33)';
				//c&c
				if (allowedToOpen(array(58231,58232),'1rtc')) {
					$txnos='(2,30)';
					$cnccondi=' AND s.txntype IN (2)';
				}
			
			$sqlm='SELECT ssbf.TxnSubID,MissingTxnID AS Missing,ssbf.TxnID,ssbf.AckByOps,ssbf.Remarks,ssbf.FixedByAcctg,s.txntype,txndesc AS TxnType,Branch,s.TxnID AS InvTxnID,Date,(SELECT COUNT(DISTINCT(Nickname)) FROM 1employees eb LEFT JOIN acctg_4blotterassign ba ON eb.IDNo=ba.AssignedTo WHERE ba.BranchNo=s.BranchNo AND ba.InvoiceType=s.txntype AND s.SaleNo BETWEEN SeriesFrom AND SeriesTo) AS AssignedToCnt,IF((SELECT AssignedToCnt)>1,"Possible Duplication",(SELECT DISTINCT(Nickname) FROM 1employees eb LEFT JOIN acctg_4blotterassign ba ON eb.IDNo=ba.AssignedTo WHERE ba.BranchNo=s.BranchNo AND ba.InvoiceType=s.txntype AND s.SaleNo BETWEEN SeriesFrom AND SeriesTo)) AS AssignedTo,SaleNo AS InvoiceNo,Findings,CONCAT(e.Nickname," ",e.SurName) AS InvoiceEncBy,e2.Nickname AS AknowledgedBy,e3.Nickname AS FixedBy,Explanation,bfs.FID FROM acctg_8blotterfindings ssbf left join acctg_0blotterbranchexplanation bexp on bexp.BEID=ssbf.BEID JOIN invty_2sale s ON ssbf.TxnID=s.TxnID AND TxnTypeNo=0 '.$cnccondi.' JOIN acctg_0blotterfindingsstmt bfs ON ssbf.FID=bfs.FID LEFT JOIN 1employees e ON s.EncodedByNo=e.IDNo LEFT JOIN 1employees e2 ON ssbf.AckByNo=e2.IDNo LEFT JOIN 1employees e3 ON ssbf.FixedByNo=e3.IDNo JOIN invty_0txntype tt ON s.txntype=tt.txntypeid JOIN 1branches b ON s.BranchNo=b.BranchNo ';
			
			//collection receipt
			$sqlm3=' UNION SELECT ssbf.TxnSubID,MissingTxnID AS Missing,ssbf.TxnID,ssbf.AckByOps,ssbf.Remarks,ssbf.FixedByAcctg,s.Type,"Collection Receipt" AS TxnType,Branch,s.TxnID AS InvTxnID,Date,(SELECT COUNT(DISTINCT(Nickname)) FROM 1employees eb LEFT JOIN acctg_4blotterassign ba ON eb.IDNo=ba.AssignedTo WHERE ba.BranchNo=s.BranchSeriesNo AND ba.InvoiceType=30 AND s.CollectNo BETWEEN SeriesFrom AND SeriesTo) AS AssignedToCnt,IF((SELECT AssignedToCnt)>1,"Possible Duplication",(SELECT DISTINCT(Nickname) FROM 1employees eb LEFT JOIN acctg_4blotterassign ba ON eb.IDNo=ba.AssignedTo WHERE ba.BranchNo=s.BranchSeriesNo AND ba.InvoiceType=30 AND s.CollectNo BETWEEN SeriesFrom AND SeriesTo)) AS AssignedTo,CollectNo AS InvoiceNo,Findings,CONCAT(e.Nickname," ",e.SurName) AS InvoiceEncBy,e2.Nickname AS AknowledgedBy,e3.Nickname AS FixedBy,Explanation,bfs.FID FROM acctg_8blotterfindings ssbf left join acctg_0blotterbranchexplanation bexp on bexp.BEID=ssbf.BEID JOIN acctg_2collectmain s ON ssbf.TxnID=s.TxnID AND TxnTypeNo=30 JOIN acctg_0blotterfindingsstmt bfs ON ssbf.FID=bfs.FID LEFT JOIN 1employees e ON s.EncodedByNo=e.IDNo LEFT JOIN 1employees e2 ON ssbf.AckByNo=e2.IDNo LEFT JOIN 1employees e3 ON ssbf.FixedByNo=e3.IDNo JOIN 1branches b ON s.BranchSeriesNo=b.BranchNo ';
			
			if (!allowedToOpen(array(58231,58232),'1rtc')){
			//Transfer IN
			$sqlm4=' UNION SELECT ssbf.TxnSubID,MissingTxnID AS Missing,ssbf.TxnID,ssbf.AckByOps,ssbf.Remarks,ssbf.FixedByAcctg,s.txntype,"Transfer IN" AS TxnType,Branch,s.TxnID AS InvTxnID,DateIN,(SELECT COUNT(DISTINCT(Nickname)) FROM 1employees eb LEFT JOIN acctg_4blotterassign ba ON eb.IDNo=ba.AssignedTo WHERE ba.BranchNo=s.ToBranchNo AND ba.InvoiceType=7 AND s.TransferNo BETWEEN SeriesFrom AND SeriesTo) AS AssignedToCnt,IF((SELECT AssignedToCnt)>1,"Possible Duplication",(SELECT DISTINCT(Nickname) FROM 1employees eb LEFT JOIN acctg_4blotterassign ba ON eb.IDNo=ba.AssignedTo WHERE ba.BranchNo=s.ToBranchNo AND ba.InvoiceType=7 AND s.TransferNo BETWEEN SeriesFrom AND SeriesTo)) AS AssignedTo,TransferNo AS InvoiceNo,Findings,CONCAT(e.Nickname," ",e.SurName) AS InvoiceEncBy,e2.Nickname AS AknowledgedBy,e3.Nickname AS FixedBy,Explanation,bfs.FID FROM acctg_8blotterfindings ssbf left join acctg_0blotterbranchexplanation bexp on bexp.BEID=ssbf.BEID JOIN invty_2transfer s ON ssbf.TxnID=s.TxnID AND TxnTypeNo=7 JOIN acctg_0blotterfindingsstmt bfs ON ssbf.FID=bfs.FID LEFT JOIN 1employees e ON s.FROMEncodedByNo=e.IDNo LEFT JOIN 1employees e2 ON ssbf.AckByNo=e2.IDNo LEFT JOIN 1employees e3 ON ssbf.FixedByNo=e3.IDNo JOIN 1branches b ON s.ToBranchNo=b.BranchNo ';
			
			//Transfer OUT
			$sqlm5=' UNION SELECT ssbf.TxnSubID,MissingTxnID AS Missing,ssbf.TxnID,ssbf.AckByOps,ssbf.Remarks,ssbf.FixedByAcctg,s.txntype,"Transfer OUT" AS TxnType,Branch,s.TxnID AS InvTxnID,DateIN,(SELECT COUNT(DISTINCT(Nickname)) FROM 1employees eb LEFT JOIN acctg_4blotterassign ba ON eb.IDNo=ba.AssignedTo WHERE ba.BranchNo=s.BranchNo AND ba.InvoiceType=4 AND s.TransferNo BETWEEN SeriesFrom AND SeriesTo) AS AssignedToCnt,IF((SELECT AssignedToCnt)>1,"Possible Duplication",(SELECT DISTINCT(Nickname) FROM 1employees eb LEFT JOIN acctg_4blotterassign ba ON eb.IDNo=ba.AssignedTo WHERE ba.BranchNo=s.BranchNo AND ba.InvoiceType=4 AND s.TransferNo BETWEEN SeriesFrom AND SeriesTo)) AS AssignedTo,TransferNo AS InvoiceNo,Findings,CONCAT(e.Nickname," ",e.SurName) AS InvoiceEncBy,e2.Nickname AS AknowledgedBy,e3.Nickname AS FixedBy,Explanation,bfs.FID FROM acctg_8blotterfindings ssbf left join acctg_0blotterbranchexplanation bexp on bexp.BEID=ssbf.BEID JOIN invty_2transfer s ON ssbf.TxnID=s.TxnID AND TxnTypeNo=4 JOIN acctg_0blotterfindingsstmt bfs ON ssbf.FID=bfs.FID LEFT JOIN 1employees e ON s.TOEncodedByNo=e.IDNo LEFT JOIN 1employees e2 ON ssbf.AckByNo=e2.IDNo LEFT JOIN 1employees e3 ON ssbf.FixedByNo=e3.IDNo JOIN 1branches b ON s.BranchNo=b.BranchNo ';
			
			}
			//missing txn
			 $sqlm2=' UNION SELECT ssbf.TxnSubID,RIGHT(LEFT(MissingTxnID,11),10) AS Missing,"",ssbf.AckByOps,ssbf.Remarks,ssbf.FixedByAcctg,s.txntype,txndesc AS TxnType,Branch,"" AS InvTxnID,Date,(SELECT COUNT(DISTINCT(Nickname)) FROM 1employees eb LEFT JOIN acctg_4blotterassign ba ON eb.IDNo=ba.AssignedTo WHERE ba.BranchNo=s.BranchNo AND ba.InvoiceType=s.txntype AND s.InvNo BETWEEN SeriesFrom AND SeriesTo) AS AssignedToCnt,IF((SELECT AssignedToCnt)>1,"Possible Duplication",(SELECT DISTINCT(Nickname) FROM 1employees eb LEFT JOIN acctg_4blotterassign ba ON eb.IDNo=ba.AssignedTo WHERE ba.BranchNo=s.BranchNo AND ba.InvoiceType=s.txntype AND s.InvNo BETWEEN SeriesFrom AND SeriesTo)) AS AssignedTo,InvNo AS InvoiceNo,Findings,"" AS InvoiceEncBy,e2.Nickname AS AknowledgedBy,e3.Nickname AS FixedBy,Explanation,bfs.FID FROM acctg_8blotterfindings ssbf left join acctg_0blotterbranchexplanation bexp on bexp.BEID=ssbf.BEID JOIN noTxnID s ON ssbf.TxnSubID=s.TxnSubID JOIN acctg_0blotterfindingsstmt bfs ON ssbf.FID=bfs.FID LEFT JOIN 1employees e2 ON ssbf.AckByNo=e2.IDNo LEFT JOIN 1employees e3 ON ssbf.FixedByNo=e3.IDNo JOIN invty_0txntype tt ON s.txntype=tt.txntypeid JOIN 1branches b ON s.BranchNo=b.BranchNo ';
			 
			echo comboBox($link,'SELECT * FROM `acctg_0blotterfindingsstmt` ORDER BY `FID`;','FID','Findings','findingslist');
			echo comboBox($link,'SELECT BranchNo,Branch FROM `1branches` WHERE Active=1 AND BranchNo>0 AND PseudoBranch=0 ORDER BY `Branch`;','BranchNo','Branch','branchlist');
			
			
				
			echo comboBox($link,'SELECT txntypeid,txndesc FROM `invty_0txntype` WHERE txntypeid IN '.$txnos.' ORDER BY `txndesc`;','txntypeid','txndesc','txntypelist');
			
			$columnstoadd=array('InvoiceNo','Findings','Branch','TxnType','Missing');
			
			
			
				 $sql0='CREATE TEMPORARY TABLE `noTxnID` (
				   `TxnSubID` int(11) NULL,
				   `BranchNo` SMALLINT(6) NULL,
				   `txntype` TINYINT(4) NULL,
				   `Date` Date NULL,
				   `InvNo` VARCHAR(6) NULL
				 )';
				$stmt0=$link->prepare($sql0); $stmt0->execute();
				 
				function get_string_between($string, $start, $end){
					$string = ' ' . $string;
					$ini = strpos($string, $start);
					if ($ini == 0) return '';
					$ini += strlen($start);
					$len = strpos($string, $end, $ini) - $ini;
					return substr($string, $ini, $len);
				}
				
				$sqlallnotxn='SELECT TxnSubID,MissingTxnID FROM acctg_8blotterfindings WHERE TxnID IS NULL OR TxnID=0;';
				$stmtallnotxn=$link->query($sqlallnotxn); $resallnotxn=$stmtallnotxn->fetchAll();
				foreach ($resallnotxn AS $resall){
					//<> Date
					$mdate = get_string_between($resall['MissingTxnID'], '<', '>');
					//$$ invno
					$minvno = get_string_between($resall['MissingTxnID'], '$', '$');
					//$$ txntype
					$mtxntype = get_string_between($resall['MissingTxnID'], '*', '*');
					//!! branch
					$mbranch = get_string_between($resall['MissingTxnID'], '!', '!');
					
					$sqlinsertn='INSERT INTO noTxnID SET TxnSubID='.$resall['TxnSubID'].',InvNo="'.$minvno.'",Date="'.$mdate.'",txntype='.$mtxntype.',BranchNo='.$mbranch.'';
					// echo $sqlinsertn;
					$stmtinsertn=$link->prepare($sqlinsertn); $stmtinsertn->execute();
					
				}
		}
		
		if (in_array($which,array('addlists','editlists'))){
			
			// print_r($_POST);
			
			// exit();
			// if()
			$fid=comboBoxValue($link,'acctg_0blotterfindingsstmt','Findings',addslashes($_POST['Findings']),'FID');
		
			$branchno=companyandbranchValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
			$ttid=comboBoxValue($link,'invty_0txntype','txndesc',addslashes($_POST['TxnType']),'txntypeid');
			$missingtxn='';
			
			//acctg_8blotterfindings
			// if($_POST['Missing']==''){
			if($_POST['TxnType']=='Collection Receipt'){ //collection receipt
				$sql='SELECT TxnID FROM acctg_2collectmain WHERE CollectNo="'.addslashes($_POST['InvoiceNo']).'" AND BranchSeriesNo='.$branchno.'';
				$txntypeno=30; //txntypeno
			} else if($_POST['TxnType']=='Transfer IN'){ //transfer in, TO branch
				$sql='SELECT TxnID FROM invty_2transfer WHERE TransferNo="'.addslashes($_POST['InvoiceNo']).'" AND ToBranchNo='.$branchno.'';
				$txntypeno=7;
			}  else if($_POST['TxnType']=='Transfer OUT'){ //transfer out, branchno
				$sql='SELECT TxnID FROM invty_2transfer WHERE TransferNo="'.addslashes($_POST['InvoiceNo']).'" AND BranchNo='.$branchno.'';
				$txntypeno=4;
			} else {
				$sql='SELECT TxnID FROM invty_2sale WHERE SaleNo="'.addslashes($_POST['InvoiceNo']).'" AND BranchNo='.$branchno.' AND txntype='.$ttid.'';
				$txntypeno=0;
			}
				// echo $sql; exit();
				$stmt=$link->query($sql); $res=$stmt->fetch();
				if($stmt->rowCount()==1){
					$invtxnid=$res['TxnID'];
				} else {
					// print_r($_POST); exit();
					if($_POST['Missing']<>'' AND $fid==0){
					//Date,InvNo,txntype,Branch
					$missingtxn='<'.$_POST['Missing'].'>$'.addslashes($_POST['InvoiceNo']).'$*'.$ttid.'*!'.$branchno.'!';
					$invtxnid='';
					} else {
						echo '<br><br><font color="red">Invalid Invoice Number.</font>'; 
						exit();
					}
				}
			
		} 
		
	
	switch ($which){
		case'BranchExplanationsList':
			$title='Branch Explanations List';
			$formdesc='</i></br><form method="post" action="blotterfindings.php?w=AddExplanation">
			BEID: <input type="text" name="BEID" size="3">
			Explanation: <input type="text" name="Explanation" size="50">
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">			
			<input type="submit" name="submit">
				</form></br>';
			$sql='select * from acctg_0blotterbranchexplanation';
			$txnidname='BEID';
			$delprocess='blotterfindings.php?w=DeleteExplanation&BEID=';
			$editprocess='blotterfindings.php?w=EditExplanation&BEID=';
			$editprocesslabel='Edit';
			$columnnames=array('BEID','Explanation');
			include '../backendphp/layout/displayastablenosort.php';
		
		break;
		
	case'EditExplanation':
	$sql='select * from acctg_0blotterbranchexplanation where BEID='.$_GET['BEID'].'';
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo'<form method="post" action="blotterfindings.php?w=EditExplanationProcess&BEID='.$_GET['BEID'].'">
			BEID: <input type="text" name="BEID" value="'.$result['BEID'].'" size="3">
			Explanation: <input type="text" name="Explanation" value="'.$result['Explanation'].'" size="50">
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">			
			<input type="submit" name="submit" value="Edit">
		</form>';

	break;	
	
	case'EditExplanationProcess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='update acctg_0blotterbranchexplanation set BEID=\''.$_POST['BEID'].'\', Explanation=\''.$_POST['Explanation'].'\' where BEID=\''.$_GET['BEID'].'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:blotterfindings.php?w=BranchExplanationsList');
	break;
		
	case'AddExplanation':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='insert into acctg_0blotterbranchexplanation set BEID=\''.$_POST['BEID'].'\', Explanation=\''.$_POST['Explanation'].'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:blotterfindings.php?w=BranchExplanationsList');
	break;
	case'DeleteExplanation':
		$beid=intval($_GET['BEID']);
		$sql='delete from acctg_0blotterbranchexplanation where BEID=\''.$beid.'\'';
	// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:blotterfindings.php?w=BranchExplanationsList');
	break;
		
		case 'lists':
		$title='Blotter Findings';
echo '<title>'.$title.'</title>';

				if (allowedToOpen(array(58231,58232,58225),'1rtc')) { 
				
				$columnnames=array(
				array('field'=>'InvoiceNo','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'Findings','type'=>'text','size'=>25,'list'=>'findingslist','required'=>true),
				array('field'=>'Branch','type'=>'text','size'=>15,'list'=>'branchlist','required'=>true),
				array('field'=>'TxnType','type'=>'text','size'=>15,'list'=>'txntypelist','required'=>true),
				array('field'=>'Missing','caption'=>'Add date if missing','type'=>'date','size'=>15)
				);
				
				$action='blotterfindings.php?w=addlists'; $fieldsinrow=6; $liststoshow=array();
				
				include('../backendphp/layout/inputmainform.php');
				echo '<br><hr><br><br>';
				} 
				
				$specsql=''; $whtosql='';
				if (allowedToOpen(58226,'1rtc')) {
					$sqlf='select GROUP_CONCAT(BranchNo) AS HandledBranches from attend_1branchgroups WHERE OpsSpecialist='.$_SESSION['(ak0)'].';';
					$stmtf=$link->query($sqlf); $resf=$stmtf->fetch();
					
					$specsql=' AND b.BranchNo IN ('.$resf['HandledBranches'].')';
					$whtosql=' AND s.ToBranchNo IN ('.$resf['HandledBranches'].')';
				}
				//warehouses
				if (allowedToOpen(58233,'1rtc')) {
					$specsql=' AND b.BranchNo = '.$_SESSION['bnum'].'';
					$whtosql=' AND s.ToBranchNo = '.$_SESSION['bnum'].'';
				}
				
				$sql1='SELECT BranchNo, Branch FROM 1branches b WHERE Active=1 AND BranchNo>=0 '.$specsql.' ORDER BY Branch';
				$stmt = $link->query($sql1);	
				$choosebranch=' Branch: <select name="Branch"><option value="All">All</option>';
				while($row= $stmt->fetch()) {
				$choosebranch.='<option value="'.$row['BranchNo'].'">'.$row['Branch'].'</option>';
				}
				$choosebranch.='</select>';
				
				
				
				
				
				
				$sql1='SELECT txntypeid, txndesc FROM invty_0txntype WHERE txntypeid IN '.$txnos.' ORDER BY txndesc';
				$stmt = $link->query($sql1);	
				$choosetxn=' TxnType: <select name="TxnType"><option value="All">All</option>';
				while($row= $stmt->fetch()) {
				$choosetxn.='<option value="'.$row['txntypeid'].'">'.$row['txndesc'].'</option>';
				}
				$choosetxn.='</select>';
				
				
				$monthno=isset($_POST['MonthNo'])?$_POST['MonthNo']:date('m');
				if (!allowedToOpen(58234,'1rtc')) {
				echo '<form action="#" method="POST">'.$choosebranch.' '.$choosetxn.' MonthNo: <input type="number" min=1 max=12 name="MonthNo" value="'.$monthno.'"> <input type="submit" name="btnFilter" value="Filter"></form><br>';
				}
				$sql=$sqlm.' ';
				$sqlcon=''; $sqlcon4=''; $sqlcon5=''; $sqlcon3='';
				if (allowedToOpen(58234,'1rtc')) { 
				$othercond='AND b.BranchNo=\''.$_SESSION['bnum'].'\'';
				}else{
				$othercond='';	
				}
				if(isset($_POST['MonthNo'])){
					$sqlcon.=' WHERE MONTH(s.Date)='.$monthno.' '.$othercond.' ';
					$sqlcon4.=' WHERE MONTH(s.DateIN)='.$monthno.' '.$othercond.' ';
					$sqlcon5.=' WHERE MONTH(s.DateOUT)='.$monthno.' '.$othercond.' ';
					
				} else {
					$sqlcon.=' WHERE 1=1 '.$othercond.'';
					$sqlcon4.=' WHERE 1=1 '.$othercond.'';
					$sqlcon5.=' WHERE 1=1 '.$othercond.'';
					$sqlcon3.=' WHERE 1=1 '.$othercond.'';
				}
				$sqlcon.=$specsql; 
				$sqlcon3.=$specsql; 
				$sqlcon4.=$whtosql;
				// echo $sqlcon4;
				$sqlcon5.=$specsql; 
				if(isset($_POST['btnFilter'])){
					if($_POST['Branch']=='All'){
						$bsql='';
						$bsql3='';
						$bsql4='';
						$bsql5='';
					} else {
						$bsql=' AND s.BranchNo='.$_POST['Branch'].'';
						$bsql3=' AND s.BranchSeriesNo='.$_POST['Branch'].'';
						$bsql4=' AND s.ToBranchNo='.$_POST['Branch'].'';
						$bsql5=' AND s.BranchNo='.$_POST['Branch'].'';
					}
					$sqlcon.=' '.$bsql.'';
					$sqlcon3.=' '.$bsql3.'';
					$sqlcon4.=' '.$bsql4.'';
					$sqlcon5.=' '.$bsql5.'';
					
					
					if($_POST['TxnType']=='All'){
						$tsql=''; $tsql3=''; $tsql4=''; $tsql5='';
					} else {
						$tsql=' AND s.txntype='.$_POST['TxnType'].'';
						$tsql3=' AND TxnTypeNo='.$_POST['TxnType'].'';
						$tsql4=' AND TxnTypeNo='.$_POST['TxnType'].'';
						$tsql5=' AND TxnTypeNo='.$_POST['TxnType'].'';
					}
					$sqlcon.=' '.$tsql.'';
					$sqlcon3.=' '.$tsql3.'';
					$sqlcon4.=' '.$tsql4.'';
					$sqlcon5.=' '.$tsql5.'';
					
					
					
				} else {
					$sqlcon.=' AND FixedByAcctg=0 ';
					$sqlcon3.=' AND FixedByAcctg=0 ';
					$sqlcon4.=' AND FixedByAcctg=0 ';
					$sqlcon5.=' AND FixedByAcctg=0 ';
				}
				
				$sql.=$sqlcon;
				
				
				// echo $sqlcon.'<br>';
				
				if(!allowedToOpen(array(58231,58232),'1rtc')){
					$sql.=$sqlm3.$sqlcon3.$sqlm4.$sqlcon4.$sqlm5.$sqlcon5.$sqlm2.$sqlcon;
				} else {
					$sql.=$sqlm3.$sqlcon3.$sqlm2.$sqlcon;
				}
				 
				// $sql.=$sqlm3.$sqlcon.$sqlm4.$sqlcon.$sqlm2.$sqlcon; 
				
				// echo $sql; exit();
				
				$sql.=' ORDER BY FixedByAcctg,AckByOps DESC,Branch';
				 $stmt1=$link->query($sql); $res1=$stmt1->fetchAll();
				 
				 // echo $sql; exit();
				 $colorcount=0;
				$rcolor[0]=(!isset($_REQUEST['print'])?(isset($alternatecolor)?$alternatecolor:"FFFFCC"):"FFFFFF");
				$rcolor[1]="FFFFFF";
		
				 echo '<table style="background-color:white;border:1px solid blue;border-collapse:collapse;width:100%;">';
				 
				 echo '<tr><th>Branch</th><th>TxnType</th><th>Date</th><th>InvoiceNo</th><th>InvoiceEncBy</th><th>AssignedTo</th><th>Findings</th><th>BranchExplanation</th><th>AknowledgedByOps/WH</th><th>AknowledgedBy</th><th>Remarks</th><th>FixedByAcctg</th><th>FixedBy</th><th></th></tr>';
				 $pad='style="padding:5px;"';
				 $imgedit='<img src="../generalinfo/icons/edit.png" alt="Edit" height="20px;">';
					$imgdel='<img src="../generalinfo/icons/delete.png" alt="Edit" height="20px;">';
				 foreach($res1 AS $res){
					 echo '<tr bgcolor="'. $rcolor[$colorcount%2].'">';
					 $colorcount++;
					 
					 echo '<td '.$pad.'>'.$res['Branch'].'</td>';
					 echo '<td '.$pad.'>'.$res['TxnType'].'</td>';
					 echo '<td '.$pad.'>'.$res['Date'].'</td>';
					 
					 //2021 function so that we can use it sa acctg sched and acctg data
					 echo '<td '.$pad.'>'.($res['TxnID']<>''?($res['TxnType']=="Collection Receipt"?'<a href="../acctg/addeditclientside.php?w=Collect&TxnID='.$res['TxnID'].'">':(($res['TxnType']=="Transfer IN" OR $res['TxnType']=="Transfer OUT")?'<a href="../invty/addedittxfr.php?w=Transfers&TxnID='.$res['TxnID'].'&txntype='.$res['txntype'].'">':'<a href="../invty/addeditsale.php?TxnID='.$res['TxnID'].'&txntype='.$res['txntype'].'">')).$res['InvoiceNo'].'</a>':$res['InvoiceNo']).'</td>';
					 
					 
					 
					 echo '<td '.$pad.'>'.$res['InvoiceEncBy'].'</td>';
					 echo '<td '.$pad.'>'.$res['AssignedTo'].'</td>';
					 echo '<td '.($res['TxnID']==''?'style="background-color:red;color:white;"':'').'>'.$res['Findings'].'</td>';
					 
				if (allowedToOpen(58234,'1rtc')) { 
				if($res['AckByOps']==0 and $res['FID']!=2){
				$button='<form method="post" action="blotterfindings.php?w=UpdateBranchExplanation&TxnSubID='.$res['TxnSubID'].'">
				<input type="text" name="Explanation" value="'.$res['Explanation'].'" list="explanationlist" size="30">
				<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"></br>	
				<input type="submit" name="submit">
				</form>';
				}else{
				$button=''.$res['Explanation'].'';	
				}
				}else{
				$button=''.$res['Explanation'].'';	
				}
				echo'<td >'.$button.'</td>';
					 
					 echo '<td '.$pad.' align="center">'.($res['AckByOps']==0?((allowedToOpen(array(58226,58233),'1rtc'))?'<form action="blotterfindings.php?TxnSubID='.$res['TxnSubID'].'&w=Ack&action_token='.$_SESSION['action_token'].'" method="POST"><input type="submit" value="Click to Acknowledge" style="background-color:green;color:white;" OnClick="return confirm(\'Are you sure you want to ACKNOWLEDGE?\');"></form>':''):'Yes').'</td>';
					 echo '<td '.$pad.'>'.$res['AknowledgedBy'].'</td>';
					 echo '<td '.$pad.'>'.$res['Remarks'].'</td>';
					 echo '<td '.$pad.' align="center">'.(($res['AckByOps']==1 AND $res['FixedByAcctg']==0)?((allowedToOpen(array(58227,58231,58232),'1rtc'))?'<form action="blotterfindings.php?TxnSubID='.$res['TxnSubID'].'&w=FixedUnres&action_token='.$_SESSION['action_token'].'" method="POST"><input type="text" name="Remarks" value="'.$res['Remarks'].'"><br><input type="submit" value="Unresolved" name="Unresolved" style="background-color:red;color:white;"> <input type="submit" value="Mark as Fixed" name="Fixed" style="background-color:blue;color:white;"></form>':''):($res['AckByOps']==1?'Yes':'')).'</td>';
					 echo '<td '.$pad.'>'.($res['FixedByAcctg']==1?$res['FixedBy']:'').'</td>';
					 echo '<td width="85px" '.$pad.'>'.((allowedToOpen(58225,'1rtc'))?'<a href="blotterfindings.php?TxnSubID='.$res['TxnSubID'].'&w=EditSpecifics">'.$imgedit.'</a> <a href="blotterfindings.php?TxnSubID='.$res['TxnSubID'].'&w=deletelists&action_token='.$_SESSION['action_token'].'" OnClick="return confirm(\'Are you sure you want to DELETE?\');">'.$imgdel.'</a>':'').' '.((allowedToOpen(58228,'1rtc'))?'<a href="blotterfindings.php?TxnSubID='.$res['TxnSubID'].'&w=Reset&action_token='.$_SESSION['action_token'].'" OnClick="return confirm(\'Are you sure you want to RESET?\');">Reset</a>':'').'</td>';
					 echo '</tr>';
				 }
				 echo '</table>';
				 // echo $sqlcon;
				 
				 $sqlct='CREATE TEMPORARY TABLE TBranch AS SELECT TxnSubID,REVERSE(SUBSTRING_INDEX(REVERSE(SUBSTRING_INDEX(`MissingTxnID`,"!",2)),"!",1)) AS BranchNo FROM acctg_8blotterfindings WHERE TxnID IS NULL OR TxnID=0';
				 $stmtct=$link->prepare($sqlct); $stmtct->execute();
				 
				 
				 $sqlcon2='WHERE 1=1 '.$specsql;
				 
				 // echo $cnccondi.'1'; exit();
				 $sql='select COUNT(Findings) AS NoOfOccurences,Findings FROM acctg_8blotterfindings ssbf JOIN acctg_0blotterfindingsstmt bfs ON ssbf.FID=bfs.FID JOIN invty_2sale s ON s.TxnID=ssbf.TxnID JOIN 1branches b ON s.BranchNo=b.BranchNo '.$sqlcon.$cnccondi.' AND TxnTypeNo=0 GROUP BY ssbf.FID UNION select COUNT(Findings) AS NoOfOccurences,Findings FROM acctg_8blotterfindings ssbf JOIN acctg_0blotterfindingsstmt bfs ON ssbf.FID=bfs.FID JOIN acctg_2collectmain s ON s.TxnID=ssbf.TxnID JOIN 1branches b ON s.BranchSeriesNo=b.BranchNo '.$sqlcon3.' GROUP BY ssbf.FID '.((!allowedToOpen(array(58231,58232),'1rtc'))?'UNION select COUNT(Findings) AS NoOfOccurences,Findings FROM acctg_8blotterfindings ssbf JOIN acctg_0blotterfindingsstmt bfs ON ssbf.FID=bfs.FID JOIN invty_2transfer s ON s.TxnID=ssbf.TxnID JOIN 1branches b ON s.ToBranchNo=b.BranchNo '.$sqlcon4.' GROUP BY ssbf.FID UNION select COUNT(Findings) AS NoOfOccurences,Findings FROM acctg_8blotterfindings ssbf JOIN acctg_0blotterfindingsstmt bfs ON ssbf.FID=bfs.FID JOIN invty_2transfer s ON s.TxnID=ssbf.TxnID JOIN 1branches b ON s.BranchNo=b.BranchNo '.$sqlcon5.' GROUP BY ssbf.FID':'').' UNION SELECT COUNT(s.TxnSubID) AS CountT,Findings FROM acctg_8blotterfindings s JOIN acctg_0blotterfindingsstmt bfs ON s.FID=bfs.FID JOIN TBranch b ON s.TxnSubID=b.TxnSubID '.$sqlcon2.' AND MONTH(RIGHT(LEFT(MissingTxnID,11),10))='.$monthno.' AND (TxnID IS NULL OR TxnID=0) HAVING CountT>0;';
				 
				 
				 // echo '<br>'.$sql;
				 $title=''; $formdesc='';
				  $columnnames=array('NoOfOccurences','Findings'); 
        $hidecount=true;
      include_once('../backendphp/layout/displayastablenosort.php');  
				 
				 // echo '<b>'.$colorcount . '</b> record'.($colorcount>1?'s':'').'';
				
    break;
	
	case'UpdateBranchExplanation':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$txnsubid=intval($_GET['TxnSubID']);
	$beid=comboBoxValue($link, 'acctg_0blotterbranchexplanation', 'Explanation', $_REQUEST['Explanation'], 'BEID');	
	$sql='UPDATE acctg_8blotterfindings set BEID=\''.$beid.'\' WHERE TxnSubID=\''.$txnsubid.'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:blotterfindings.php');
	break;
	
		case 'addlists':
	if (allowedToOpen(array(58231,58232,58225),'1rtc')){
		 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='INSERT INTO `acctg_8blotterfindings` SET MissingTxnID="'.$missingtxn.'",TxnTypeNo='.$txntypeno.',TxnID="'.$invtxnid.'",FID='.$fid.', EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now()';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: blotterfindings.php');
	}
	break;
	
	case 'EditSpecifics':
        if (allowedToOpen(array(58231,58232,58225),'1rtc')) {
		$title='Edit Specifics';
		$txnsubid=intval($_GET['TxnSubID']);
		
		//no transfer out
		// $sql=$sqlm .' WHERE ssbf.TxnSubID='.$txnsubid.$sqlm3.' WHERE ssbf.TxnSubID='.$txnsubid.$sqlm4.' WHERE ssbf.TxnSubID='.$txnsubid.$sqlm5.' WHERE ssbf.TxnSubID='.$txnsubid.$sqlm2.' WHERE ssbf.TxnSubID='.$txnsubid;
		$sqlcheckTxn='SELECT TxnTypeNo FROM acctg_8blotterfindings WHERE TxnSubID='.intval($_GET['TxnSubID']);
		$stmtchecktxn=$link->query($sqlcheckTxn); $reschecktxn=$stmtchecktxn->fetch();
		if($reschecktxn['TxnTypeNo']==30){ //CR
			$sql=str_replace("UNION ","",$sqlm3) .' WHERE ssbf.TxnSubID='.$txnsubid;
		} else if($reschecktxn['TxnTypeNo']==7){ //Transfer In
			$sql=str_replace("UNION ","",$sqlm4) .' WHERE ssbf.TxnSubID='.$txnsubid;
		} else if ($reschecktxn['TxnTypeNo']==4){ //Transfer OUT
			$sql=str_replace("UNION ","",$sqlm5) .' WHERE ssbf.TxnSubID='.$txnsubid;
		} else {
			$sql=$sqlm .' WHERE ssbf.TxnSubID='.$txnsubid.$sqlm2.' WHERE ssbf.TxnSubID='.$txnsubid;
		}
		
		
		
		// echo $sql;
		$columnstoedit=$columnstoadd;
			
		$columnswithlists=array('Findings','Branch','TxnType');
		$listsname=array('Findings'=>'findingslist','Branch'=>'branchlist','TxnType'=>'txntypelist');
		
		$columnnames=$columnstoadd;
		
		$editprocess='blotterfindings.php?w=editlists&TxnSubID='.$txnsubid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	} 
	break;
	
	
	case 'editlists':
		
		if (allowedToOpen(array(58231,58232,58225),'1rtc')){
		
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnsubid = intval($_GET['TxnSubID']);
                
		
		$sql='Update `acctg_8blotterfindings` SET MissingTxnID="'.$missingtxn.'",TxnID="'.$invtxnid.'",FID='.$fid.', EncodedByNo='.$_SESSION['(ak0)'].', Timestamp=Now() WHERE AckByOps=0 AND TxnSubID='.$txnsubid;
		
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:blotterfindings.php?w=lists");
		} 
    break;
	case 'deletelists':
		if (allowedToOpen(array(58231,58232,58225),'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                $txnsubid = intval($_GET['TxnSubID']);        
                       
			$sql='DELETE FROM `acctg_8blotterfindings` WHERE AckByOps=0 '.((!allowedToOpen(58228,'1rtc'))?'AND EncodedByNo='.$_SESSION['(ak0)'].'':'').' AND TxnSubID='.$txnsubid;
			$stmt=$link->prepare($sql);
			$stmt->execute();
			header("Location:blotterfindings.php?w=lists");
		} 
    break;
	
	
	case 'Ack':
		if (allowedToOpen(array(58226,58233),'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnsubid = intval($_GET['TxnSubID']);
		
		//checker
			$sqlchecker='select * from acctg_8blotterfindings WHERE AckByOps=0 AND (BEID<>0 OR FID=2) AND TxnSubID='.$txnsubid.'';
			$stmtchecker=$link->query($sqlchecker);
		
			if($stmtchecker->rowCount()==0){
				echo'You cannot Acknowledge without Branch Explanation.'; exit();
			}
		//
		
		$sql='Update `acctg_8blotterfindings` SET AckByOps=1, AckTS=Now(),AckByNo='.$_SESSION['(ak0)'].' WHERE AckByOps=0 AND (BEID<>0 OR FID=2) AND TxnSubID='.$txnsubid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql);		
		$stmt->execute();
		header("Location:blotterfindings.php?w=lists");
		}
    break;
	
	case 'FixedUnres':
		if (allowedToOpen(array(58227,58231,58232),'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnsubid = intval($_GET['TxnSubID']);
		// print_r($_POST); exit();
		$sql='Update `acctg_8blotterfindings` SET Remarks="'.$_POST['Remarks'].'",FixedByAcctg='.(isset($_POST['Fixed'])?1:0).', FixedTS=Now(),FixedByNo='.$_SESSION['(ak0)'].' WHERE AckByOps=1 AND FixedByAcctg=0 AND TxnSubID='.$txnsubid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:blotterfindings.php?w=lists");
		}
    break;
	
	case 'Reset':
		if (allowedToOpen(58228,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnsubid = intval($_GET['TxnSubID']);
		
		$sql='Update `acctg_8blotterfindings` SET AckByOps=0,AckByNo=NULL,AckTS=NULL,Remarks=NULL,FixedByAcctg=0, FixedTS=NULL,FixedByNo=NULL WHERE TxnSubID='.$txnsubid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:blotterfindings.php?w=lists");
		}
    break;
	
	case 'Cancelled':
		include_once $path.'/acrossyrs/commonfunctions/monthName.php';
		$title='Cancelled Invoices per Month';
		$sql='CREATE TEMPORARY TABLE cancelled AS SELECT m.BranchNo, Branch, MONTH(Date) AS CancelledMonth, COUNT(TxnID) AS Cancelled, FORMAT(COUNT(TxnID)/(SELECT COUNT(TxnID) FROM invty_2sale sm WHERE sm.BranchNo=m.BranchNo AND MONTH(sm.Date)=MONTH(m.Date))*100,2) AS Percent  FROM invty_2sale m JOIN 1branches b ON b.BranchNo=m.BranchNo WHERE ClientNo=10001 GROUP BY MONTH(Date), m.BranchNo ORDER BY Branch';
		$stmt=$link->prepare($sql);
		$stmt->execute();
		$columnnames=array('Branch');
		$sql=''; $months=array(1,2,3,4,5,6,7,8,9,10,11,12); $formatcond='';
		foreach ($months as $month){
			$monthname=monthName($month); 
			$columnnames[]=$monthname.'_Cancelled';
			$columnnames[]=$monthname.'_Percent';
			$sql.=' SUM(CASE WHEN CancelledMonth='.$month.' THEN IFNULL(Cancelled,0) END) AS `'.$monthname.'_Cancelled`, SUM(CASE WHEN CancelledMonth='.$month.' THEN Percent END) AS `'.$monthname.'_PercentNoFormat`,';
			$formatcond.= ', IF(`'.$monthname.'_PercentNoFormat`>=4,CONCAT("<font style=\"background-color: coral;\">",`'.$monthname.'_PercentNoFormat`,"</font>"),`'.$monthname.'_PercentNoFormat`) AS `'.$monthname.'_Percent`';
		}

		$sql='CREATE TEMPORARY TABLE sumcancelled AS SELECT '.$sql.' Branch FROM cancelled GROUP BY BranchNo ORDER BY Branch';
		$stmt=$link->prepare($sql);
		$stmt->execute();
		$sql='SELECT *'.$formatcond.' FROM sumcancelled;';
		
		include '../backendphp/layout/displayastablenosort.php';

	}
	
		
?>	
	