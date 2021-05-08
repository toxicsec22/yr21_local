<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(58221,'1rtc')) { echo 'No permission'; exit();}
include_once $path.'/acrossyrs/dbinit/userinit.php';
$showbranches=false;
include_once('../switchboard/contents.php');
include_once ('../generalinfo/trailgeninfo.php');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

include_once('../backendphp/layout/linkstyle.php');
echo'</br>';
	echo'<a id="link" href="blotterassign.php">Blotter Assignments</a> '.str_repeat('&nbsp',5).'';

	echo'<a id="link" href="blotterassign.php?w=Unreceived">Unreceived Blotter</a> '.str_repeat('&nbsp',5).'';
		
	echo'<a id="link" href="blotterassign.php?w=Unfinished">Unfinished Blotter after 7 Days</a> '.str_repeat('&nbsp',5).'';
	if(allowedToOpen(58230,'1rtc')){
		echo'<a id="link" href="blotterassign.php?w=ResetBlotter">Reset Blotter Assign</a> '.str_repeat('&nbsp',5).'';	
	}
echo'</br>';

	$which=(!isset($_GET['w'])?'lists':$_GET['w']);
	
		if (in_array($which,array('lists','EditSpecifics'))){
			
			if (isset($_POST['btnFilter'])){
						
						if ($_POST['InvoiceType']=="All"){
						$typeidsql = '';
						$ti = 'All';
						}else {
						$typeidsql = ' AND a.InvoiceType='.$_POST['InvoiceType'] ;
						$ti = comboBoxValue($link,'`invty_0txntype`','txntypeid',$_POST['InvoiceType'],'txndesc');
						}
						// <----->
						if ($_POST['Branch']=="All"){
						$branchsql = '';
						$br = 'All';
						}else {
						$branchsql = ' AND a.BranchNo='.$_POST['Branch'] ;
						$br = comboBoxValue($link,'`1branches`','BranchNo',$_POST['Branch'],'Branch');
						}
						// <----->
						if ($_POST['AssignedTo']=="All"){
						$assigneesql = '';
						$ae = 'All';
						}else {
						$assigneesql = ' AND a.AssignedTo='.$_POST['AssignedTo'] ;
						$ae = comboBoxValue($link,'`attend_30currentpositions`','IDNo',$_POST['AssignedTo'],'FullName');
						}
						// <----->		
						if ($_POST['InvoiceNo']==""){
						$seriessql = '';
						}else {
						$seriessql = ' AND '.$_POST['InvoiceNo'].' BETWEEN SeriesFrom AND SeriesTo' ;
						// echo $seriessql;exit();
						}
						
						// <----->	

						
				$condi = ' WHERE DateofInvoices>="'.$_POST['sDate'].'" AND DateofInvoices<="'.$_POST['eDate'].'" '.$branchsql.'  '.$assigneesql.' '.$typeidsql.' '.$seriessql.' ';
				
						// echo $condi; exit();
						echo  '<br><h3>From: '.$_POST['sDate']; echo ', To: '.$_POST['eDate'];   echo ', InvoiceNo: '.$_POST['InvoiceNo']; echo ', Branch: '.$br. ', InvoiceType: '.$ti. ', AssignedTo: '.$ae.  '</h3>'; 
						}
						
						else {
						if (isset($_GET['BranchNo'])){
							$condi = ' WHERE b.BranchNo='.intval($_GET['BranchNo']).'';
						} else {
							$condi = ' ';
						}
						}
						
						
						
			
		echo comboBox($link,'SELECT BranchNo, Branch FROM 1branches WHERE BranchNo>=0 AND Pseudobranch<>1 AND Active<>0 ORDER BY Branch','BranchNo','Branch','branchlist');
		echo comboBox($link,'SELECT e.IDNo, CONCAT(Nickname," ",SurName) AS FullName FROM 1employees e JOIN attend_30currentpositions lpir ON e.IDNo=lpir.IDNo JOIN attend_0positions p ON lpir.PositionID=p.PositionID WHERE p.deptid IN (20,60)','IDNo','FullName','AssignToNolist');
		echo comboBox($link,'SELECT txntypeid, txndesc FROM invty_0txntype WHERE txntypeid IN (1,2,29,30,5,4,7)','txntypeid','txndesc','Typelist');
		$sql='SELECT a.*,b.Branch,txndesc AS InvoiceType,CONCAT(cp.Nickname," ",cp.SurName) AS AssignedTo,CONCAT (e.Nickname," ",e.SurName) AS AssignedBy,IF(Finished<>0,"Yes","") AS Finished,IF(Received<>0,"Yes","") AS Received FROM acctg_4blotterassign a JOIN 1employees e ON a.AssignedBy=e.IDNo JOIN 1branches b ON b.BranchNo=a.BranchNo LEFT JOIN 1employees cp ON cp.IDNo=a.AssignedTo JOIN invty_0txntype i ON i.txntypeid=a.InvoiceType'.$condi.' ';
		
		// echo $sql;
		$columnnameslist=array('Branch','DateofInvoices','InvoiceType', 'SeriesFrom','SeriesTo','RemarksofAssignor','AssignedTo','Received','ReceivedTS','Finished','AssignedBy','Timestamp','Remarks'); 
		$columnstoadd=array('Branch','DateofInvoices','InvoiceType', 'SeriesFrom','SeriesTo','RemarksofAssignor','AssignedTo','Finished'); 
		$columnstoedit=array('Remarks');	
		}
		if (in_array($which,array('addlists','editlists'))){
		$branch=companyandbranchValue($link,'1branches','Branch',addslashes($_POST['Branch']),'BranchNo');
		// $fnid=comboBoxValue($link,'attend_30currentpositions','FullName',addslashes($_POST['AssignedTo']),'IDNo');
		$fnid=comboBoxValue($link,'1employees','CONCAT(Nickname," ",SurName)',addslashes($_POST['AssignedTo']),'IDNo');
		$tdid=comboBoxValue($link,'invty_0txntype','txndesc',addslashes($_POST['InvoiceType']),'txntypeid');
		$columnstoadd=array('DateofInvoices', 'SeriesFrom','SeriesTo','RemarksofAssignor'); 
		}
		
	
	switch ($which){
		
		case'Unreceived':
		$title='Unreceived Blotter';
		$sql='select Branch,DateofInvoices,SeriesFrom,SeriesTo,RemarksofAssignor,txndesc AS InvoiceType,CONCAT(Nickname," ",SurName) AS AssignedTo from acctg_4blotterassign ba left join 1employees e on e.IDNo=ba.AssignedTo
		left join 1branches b on b.BranchNo=ba.BranchNo left join invty_0txntype tt on tt.txntypeid=ba.InvoiceType		
		where Received=0';
		$columnnames=array('Branch','DateofInvoices','InvoiceType','SeriesFrom','SeriesTo','RemarksofAssignor','AssignedTo');
		include('../backendphp/layout/displayastablenosort.php');
		
		break;
		
		case'Unfinished':
		$title='Unfinished Blotter after 7 Days';
		$sql='select ReceivedTS,Branch,DateofInvoices,SeriesFrom,SeriesTo,RemarksofAssignor,txndesc AS InvoiceType,CONCAT(Nickname," ",SurName) AS AssignedTo from acctg_4blotterassign ba left join 1employees e on e.IDNo=ba.AssignedTo
		left join 1branches b on b.BranchNo=ba.BranchNo left join invty_0txntype tt on tt.txntypeid=ba.InvoiceType		
		where Received=1 and date(ReceivedTS) <= curdate()-interval 7 day';
		// echo $sql; exit();
		$columnnames=array('Branch','DateofInvoices','InvoiceType','SeriesFrom','SeriesTo','RemarksofAssignor','AssignedTo','ReceivedTS');
		include('../backendphp/layout/displayastablenosort.php');
		break;
		
		
		case 'lists':
		$title='Blotter Assignment';
				$method='post';
				
					include_once('../backendphp/layout/clickontabletoedithead.php');
					
					$sql1='SELECT txntypeid,txndesc FROM invty_0txntype WHERE `txntypeid` IN (1,2,29,30,5,4,7) ORDER BY txndesc;';
					$stmt = $link->query($sql1);	
					$choosetypeid=' Invoice Type: <select name="InvoiceType"><option value="All">Invoice Type</option>';
					while($row= $stmt->fetch()) {
					$choosetypeid.='<option value="'.$row['txntypeid'].'">'.$row['txndesc'].'</option>';
					}
					$choosetypeid.='</select>';
					// <----->
					$sql1='SELECT BranchNo, Branch FROM 1branches WHERE Active=1 AND BranchNo>=0 ORDER BY Branch';
					$stmt = $link->query($sql1);	
					$choosebranch=' Branch: <select name="Branch"><option value="All">Branch</option>';
					while($row= $stmt->fetch()) {
					$choosebranch.='<option value="'.$row['BranchNo'].'">'.$row['Branch'].'</option>';
					}
					$choosebranch.='</select>';
					// <----->
					// $sql1='SELECT IDNo, FullName FROM attend_30currentpositions WHERE deptid=20';
					$sql1='SELECT DISTINCT(AssignedTo) AS IDNo, CONCAT(Nickname," ",SurName) AS FullName FROM acctg_4blotterassign ba JOIN 1employees e ON ba.AssignedTo=e.IDNo';
					
					$stmt = $link->query($sql1);
					$chooseassignee=' AssignedTo: <select name="AssignedTo"><option value="All">AssignedTo</option>';
					while($row= $stmt->fetch()) {
					$chooseassignee.='<option value="'.$row['IDNo'].'">'.$row['FullName'].'</option>';
					}
					$chooseassignee.='</select>';
					// <----->			
					
					
					echo '<table>'; echo'<td style=" border: 1px solid black;">';
					// echo '<BR>'; echo '<BR>';
					echo '<h3>For Searching</h3>'; 
					// echo '<BR>'; echo '<BR>';
					
					echo '<form method="POST" action="blotterassign.php?w=lists">
					Date From: <input type="date" name="sDate" value="'.date('Y-m-d').'"/>
					Date To: <input type="date" name="eDate" value="'.date('Y-m-d').'">
					Invoice: <input type="text" name="InvoiceNo"/>';
					echo $choosetypeid;
					echo $choosebranch;
					echo '<BR>'; echo '<BR>';
					echo $chooseassignee;

					echo ' <input type="submit" name="btnFilter" value="Filter">';
					echo '</form>'; 
					echo '</table>';

					$title='';
				// $title='For Assigning'; $formdesc=''; 
				// echo '<br>';
				if (allowedToOpen(58223,'1rtc')) { 
				// echo '<table>'; echo'<td style=" border: 1px solid black;">';
				$columnnames=array(
				array('field'=>'Branch','type'=>'text','list'=>'branchlist','size'=>15,'required'=>true),
				array('field'=>'DateofInvoices','type'=>'date','value'=>date('Y-m-d'),'size'=>15,'required'=>true),
				array('field'=>'InvoiceType','type'=>'text','list'=>'Typelist','size'=>15,'required'=>true),
				array('field'=>'SeriesFrom','type'=>'text','size'=>15,'required'=>true),
                array('field'=>'SeriesTo','type'=>'text','size'=>15,'required'=>true),
				array('field'=>'RemarksofAssignor','type'=>'text','size'=>15,'required'=>false),
				array('field'=>'AssignedTo','type'=>'text','list'=>'AssignToNolist','size'=>20,'required'=>true),
				);
				
				$action='blotterassign.php?w=addlists'; //$fieldsinrow=5; $liststoshow=array();
				
				// include('../backendphp/layout/inputmainform.php');
				$modaltitle='Assign Blotter'; $buttonval='Assign Blotter'; 
				include('../backendphp/layout/inputmainformmodal.php');
				} 
				// echo '</table>';
				 if (allowedToOpen(58222,'1rtc')) {  
				$addlprocess='blotterassign.php?w=EditSpecifics&TxnID='; $addlprocesslabel='Edit';
				$delprocess='blotterassign.php?w=deletelists&TxnID='; $delprocesslabel='Delete'; 
				 }
				if (allowedToOpen(58221,'1rtc')) {  
				$addlprocess2='blotterassign.php?w=Accept&TxnID='; $addlprocess2label='Accept';
				$editprocess='blotterassign.php?w=Finished&TxnID='; $editprocesslabel='Finished';
				}
						
				$columnnames=$columnnameslist;   
				$title=''; $formdesc=''; $txnidname='TxnID';
				
				$cronlysql='';
				if (allowedToOpen(58231,'1rtc')) { 
					$cronlysql=' AND InvoiceType IN (2,30)';
				}
				if (allowedToOpen(58232,'1rtc')) { 
					$cronlysql=' AssignedTo IN (select IDNo from attend_30currentpositions WHERE deptid=(SELECT deptid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].'))';
				}
						
				$sql=$sql.(allowedToOpen(58223,'1rtc')?((!isset($_POST['btnFilter']))?' WHERE DateofInvoices=CURDATE() '.$cronlysql.' ORDER BY SeriesFrom ASC':''):' '.((isset($_POST['btnFilter']))?' AND':' WHERE  Finished=0 AND').' '.((allowedToOpen(58232,'1rtc'))?'':'AssignedTo='.$_SESSION['(ak0)'].'').' '.$cronlysql.' ');
				// echo $sql;
				include('../backendphp/layout/displayastableeditcellswithsorting.php');
				
				//disabling input
				// $disablefield=true; $triggercolumn='AssignedTo';
				// $txtshouldbe=$_SESSION['(ak0)'];
    break;
		case 'addlists':
	if (allowedToOpen(58223,'1rtc')){
		 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='INSERT INTO `acctg_4blotterassign` SET BranchNo='.$branch.', InvoiceType='.$tdid.', AssignedTo='.$fnid.',  AssignedBy='.$_SESSION['(ak0)'].', '.$sql.'  Timestamp=Now()';
          // echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location: blotterassign.php');
	}
	break;
	
	case 'EditSpecifics':
        if (allowedToOpen(58222,'1rtc')) {
		$title='Edit Specifics';
		$txnid=intval($_GET['TxnID']);
		$sql=$sql.' WHERE a.TxnID='.$txnid; 
		// echo $sql; exit();
		$columnstoedit=$columnstoadd;
			
		$columnswithlists=array('Branch','DateofInvoices','InvoiceType', 'SeriesFrom','SeriesTo','Remarks','AssignedTo');
		$listsname=array('Branch'=>'branchlist','AssignedTo'=>'AssignToNolist','InvoiceType'=>'Typelist');
		
		$columnnames=$columnswithlists;
		
		$editprocess='blotterassign.php?w=editlists&TxnID='.$txnid;
		
		include('../backendphp/layout/editspecificsforlists.php');
	} 
	break;
	
	
	case 'editlists':
		
		if (allowedToOpen(58222,'1rtc')){
		
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnID']);
                // $table='acctg_4blotterassign';
		// recordtrail($txnid,$table,$link,0);
		$sql='';
                $columnstoadd[]='Finished';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
		$sql='Update `acctg_4blotterassign` SET BranchNo='.$branch.', InvoiceType='.$tdid.', AssignedTo='.$fnid.',  AssignedBy='.$_SESSION['(ak0)'].', '.$sql.'  Timestamp=Now() WHERE Received=0 AND `DateofInvoices`>"'.$_SESSION['nb4A'].'" AND TxnID='.$txnid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		// header("Location:blotterassign.php?w=lists");
		 header("Location:".$_SERVER['HTTP_REFERER']);
		} 
    break;
	case 'deletelists':
		if (allowedToOpen(58222,'1rtc')){
			require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
                        
                        $txnid = intval($_GET['TxnID']);
                        $table='acctg_4blotterassign';
                        recordtrail($txnid,$table,$link,1);
                        
			$sql='DELETE FROM `acctg_4blotterassign` WHERE Received=0 AND `DateofInvoices`>"'.$_SESSION['nb4A'].'" AND TxnID='.$txnid;
			$stmt=$link->prepare($sql);
			$stmt->execute();
			header("Location:blotterassign.php?w=lists");
		} 
    break;
	case 'Accept':
		if (allowedToOpen(58221,'1rtc')){
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnID']);
		$sql='Update `acctg_4blotterassign` SET   Received=1, ReceivedTS=Now() WHERE AssignedTo='.$_SESSION['(ak0)'].' AND Received=0 AND TxnID='.$txnid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:blotterassign.php?w=lists");
		}
    break;
	case 'Finished':
		if (allowedToOpen(58221,'1rtc')){
		$columnstoadd=array('Remarks');
		
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnID']);
		$sql='';
		foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\' '; }
		$sql='Update `acctg_4blotterassign` SET   Finished=1, '.$sql.'  WHERE AssignedTo='.$_SESSION['(ak0)'].' AND Finished=0 AND Received=1 AND TxnID='.$txnid;
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header("Location:blotterassign.php?w=lists");
		}
    break;
	
	case 'ResetBlotter':

	if (!allowedToOpen(58230,'1rtc')) {   echo 'No permission'; exit;}           
			  
			$title='Reset Blotter Assign';
			
			echo comboBox($link,'SELECT BranchNo,Branch FROM 1branches WHERE Active=1 AND BranchNo>=0 ORDER BY Branch;','BranchNo','Branch','branches');
			$sql1='SELECT txntypeid,txndesc FROM invty_0txntype WHERE `txntypeid` IN (1,2,29,30,5,4,7) ORDER BY txndesc;';
			$stmt = $link->query($sql1);	
			$choosetypeid=' Invoice Type: <select name="InvoiceType" required><option value="">-- Select --</option>';
			while($row= $stmt->fetch()) {
			$choosetypeid.='<option value="'.$row['txntypeid'].'">'.$row['txndesc'].'</option>';
			}
			$choosetypeid.='</select>';
					
			echo '<title>'.$title.'</title>';
			echo '<br><h3>'.$title.'</h3><br>';
			echo '<form action="blotterassign.php?w=Reset" method="POST">';
			echo '<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">';
			echo 'Branch: <input type="text" name="BranchNo" list="branches" autocomplete="off" size=10 required>';
			echo ' Date of Invoice: <input type="date" name="DateofInvoice" value="'.date('Y-m-d').'" required>';
			echo $choosetypeid;
			echo ' <input type="submit" value="Reset" name="btnReset" OnClick="return confirm(\'Are you sure you want to reset?\');">';
			echo '</form>';
			 
		  
	break;
	
	case 'Reset':
        if (!allowedToOpen(58230,'1rtc')) {   echo 'No permission'; exit;}
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	   $branchno=companyandbranchValue($link,'1branches','Branch',addslashes($_POST['BranchNo']),'BranchNo');
	   // print_r($_POST); exit();
	    $sqlcheck='SELECT TxnID FROM `acctg_4blotterassign` WHERE BranchNo='.$branchno.' AND DateofInvoices="'.$_POST['DateofInvoice'].'" AND InvoiceType='.$_POST['InvoiceType'].'';
		// echo $sqlcheck; exit();
		$stmtcheck=$link->query($sqlcheck); $rescheck=$stmtcheck->fetch();
		if($stmtcheck->rowCount()>0){
			
			$sql='UPDATE `acctg_4blotterassign` SET Received=0,ReceivedTS=NULL,Finished=0,Remarks="" WHERE TxnID='.$rescheck['TxnID'].' AND `DateofInvoices`>"'.$_SESSION['nb4A'].'"';
			$stmt=$link->prepare($sql); $stmt->execute();
			
		} else {
			echo '<br>No Records'; exit();
		}
		echo '<br><h4 style="color:green;">Done.</h4>';
        break;

	}
	
		
		
	