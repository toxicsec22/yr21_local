<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(array(4006,4011),'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

include_once('../backendphp/layout/linkstyle.php');
echo'</br>';
 if (allowedToOpen(4006,'1rtc')) {
	echo'<a id="link" href="motorcyclepromo.php?w=lists">Arangkada Motorcycle Promo</a> '.str_repeat('&nbsp',5).'';
}

	echo'<a id="link" href="motorcyclepromo.php?w=summaryreport&s=2">Suspicious Branch</a> '.str_repeat('&nbsp',5).'';

	echo'<a id="link" href="motorcyclepromo.php?w=summaryreport&s=3">Innocent</a> '.str_repeat('&nbsp',5).'';
		
	echo'<a id="link" href="motorcyclepromo.php?w=summaryreport&s=4">Guilty</a> '.str_repeat('&nbsp',5).'';	

echo'</br>';

$which=!isset($_GET['w'])?'lists':$_GET['w'];
if (in_array($which,array('lists','validate','listsall','listsperdate'))){
	
	$sqlt='create temporary table `'.$currentyr.'txn` as 
	select s.TxnID,Date,ClientNo,SaleNo,BranchNo,PaymentType as paytype,s.EncodedByNo,ifnull(s.TimeStamp,\'0000-00-00\') as TimeStamp,sum(Qty*UnitPrice) as RecordedAmount from '.$lastyr.'_1rtc.invty_2sale s join '.$lastyr.'_1rtc.invty_2salesub ss on ss.TxnID=s.TxnID where month(Date) between \'03\' AND \'12\' Group By s.TxnID
	
	UNION ALL
	
	select s.TxnID,Date,ClientNo,SaleNo,BranchNo,PaymentType as paytype,s.EncodedByNo,ifnull(s.TimeStamp,\'0000-00-00\') as TimeStamp,sum(Qty*UnitPrice) as RecordedAmount from '.$currentyr.'_1rtc.invty_2sale s join '.$currentyr.'_1rtc.invty_2salesub ss on ss.TxnID=s.TxnID where month(Date) between \'01\' AND \'03\' Group By s.TxnID
	
	';
	// echo $sqlt; exit();
	$stmt=$link->prepare($sqlt); $stmt->execute();
	
}
if (in_array($which,array('lists','listsall','listsperdate'))){
	if($which=='lists'){
		$addlcondition=' WHERE Valid=0 OR DATE(mp.TimeStamp)=CURDATE()';
	} else if($which=='listsperdate'){
		$addlcondition=' WHERE LEFT(mp.TimeStamp,10)="'.(isset($_SESSION['DateValidated'])?$_SESSION['DateValidated']:date('Y-m-d')).'"';
	}else{
		$addlcondition='';
	}
	
	
	$sql='SELECT round(Amount) as Amount,mp.paytype,round(RecordedAmount) as OriginalAmount,mp.TxnID,CASE WHEN mp.paytype=1 then "Cash" WHEN mp.paytype=2 then "Charge" WHEN mp.paytype=32 then "GCash" WHEN mp.paytype=33 then "Paymaya" end as PaymentType,DateOfEntry,Date,
	case 
	when Valid=0 then "PENDING"
	when Valid=-1 then "INVALID"
	when Valid=1 then "VALID"
	when Valid=2 then "SUSPICIOUS"
	when Valid=3 then "INNOCENT"
	when Valid=4 then "GUILTY"
	end as Valid
	
	,ClientName,Branch,mp.SaleNo as InvoiceNo,mp.BranchNo,CONCAT(e.Nickname,\' \',e.SurName) as EncodedBy,mp.TimeStamp as TimeStamp,CONCAT(e1.Nickname,\' \',e1.SurName) as SaleEncodedBy,t.TimeStamp as SaleTimeStamp FROM `'.$currentyr.'txn` t right join events_1motorcyclepromo mp on TRIM(LEADING "0" FROM mp.SaleNo)=TRIM(LEADING "0" FROM t.SaleNo) AND mp.BranchNo=t.BranchNo AND mp.paytype=t.paytype left join 1branches b on mp.BranchNo=b.BranchNo left join events_1tempclients c on mp.ClientNo=c.ClientNo left join 1employees e on e.IDNo=mp.EncodedByNo left join 1employees e1 on e1.IDNo=t.EncodedByNo '.$addlcondition.' Order By Date,ClientName';
	// echo $sql; exit();
	
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
}
if (in_array($which,array('lists','listsall','listsperdate','summaryreport'))){
?>
		<style>
			#table {
			  border-collapse: collapse;
			  font-size:9pt;
			  width: auto;
			  background-color:#cccccc;
			}

			#table td, #table th {
			  border: 1px solid black;
			  padding: 2px;
			}
			#table tr:nth-child(even){background-color:white;}
		</style>
	
	<?php
}
switch ($which){
	case'lists':
	if (!allowedToOpen(4006,'1rtc')) { echo 'No permission'; exit; }
	echo'<title>Arangkada sa Bagong Dekada</title>';
	
		echo'</br></br><div style="background-color:#f2f2f2; width:460px; border: 1px solid black; padding:5px;" >
		<b>INSTRUCTION:</b></br>
		If row is color yellow, the AmountOfEntry is not equal to RecordedAmount.<br>
		If row is color red, The invoice doesn\'t exists in our database.</br>
			 </div></br>';
			 
			  if(!isset($_POST['show'])){$_POST['showvalue']=0;}
			 //show encodedby
			  echo '<form style="display:inline;" method="post" action="motorcyclepromo.php?w=lists">
			  <input type="hidden" name="showvalue" '.(($_POST['showvalue']==0)?'value="1"':'value="0"').'>
			  <input type="submit" name="show" '.(($_POST['showvalue']==1)?'value="Hide EncodedBy and TimeStamp"':'value="Show EncodedBy and TimeStamp"').'></form>&nbsp;&nbsp;';
			  
			  //Validation
			  echo '<form style="display:inline;" method="post" action="motorcyclepromo.php?w=validate">
			  <input type="submit" name="validate" value="Validate?" OnClick="return confirm(\'Are you sure you want to Validate?\');">
			  </form>&nbsp;&nbsp;';
			  
			   //export
			  echo '<a href="motorcyclepromo.php?w=export">link to export data</a>';

			 //h3
			echo'<h3>Arangkada sa Bagong Dekada</h3>';

			//import
			 echo'</br> <table style="border:1px solid black;  padding: 3px; font-size:9pt;">
			 <tr><td><h3>Import</h3></br>
			<form method="post" action="motorcyclepromo.php?w=import" enctype="multipart/form-data">		
			<input type="file" name="userfile" required>
			<input type="submit" name="import" value="Import" OnClick="return confirm(\'Are you sure you want to Import?\');">
			</form></td></tr></table>';
			echo '<br><a href="motorcyclepromo.php?w=listsall" target="_blank"><b>Show all lists</b></a> &nbsp; &nbsp; &nbsp; <a href="motorcyclepromo.php?w=listsperdate" target="_blank"><b>Show per date</b></a>';
			if(isset($_GET['Message'])){
				echo $_GET['Message'];
			}
	$btn=1; 
	case 'listsall':
	if (!allowedToOpen(4006,'1rtc')) { echo 'No permission'; exit; }
	if($which=='listsall'){
		$title='All Entries';
		echo '<title>'.$title.'</title>';
		echo '<br><br><h3>'.$title.'</h3>';
		$_POST['showvalue']=0;
		$btn=0;
	}
	case 'listsperdate':
	if (!allowedToOpen(4006,'1rtc')) { echo 'No permission'; exit; }
	if($which=='listsperdate'){
		$title='All Entries Per Date';
		echo '<title>'.$title.'</title>';
		echo '<br><br><h3>'.$title.'</h3>';
		$_POST['showvalue']=0;
		$btn=1;
		echo '<form action="motorcyclepromo.php?w=SetDateSession" method="POST">DateValidated <input type="date" name="DateValidated" value="'.(isset($_SESSION['DateValidated'])?$_SESSION['DateValidated']:date('Y-m-d')).'"> <input type="submit" name="btnSubmit" value="Lookup"></form>';
	}
	
	echo'</br><table id="table"">
 <tr><th>DateOfEntry</th><th>DateOfInvoice</th><th>Branch</th><th>ClientName</th><th>InvoiceNo</th><th>Matched</th><th>AmountOfEntry</th><th>RecordedAmount</th><th>PaymentType</th><th>Valid?</th> '.(($_POST['showvalue']==1)?'<th>EncodedBy</th><th>TimeStamp</th>':'').'</tr>';
				// $c=0;
		foreach($result as $res){
			if($res['Amount']==$res['OriginalAmount']){
			echo'<tr><td>'.$res['DateOfEntry'].'</td><td>'.$res['Date'].'</td><td>'.$res['Branch'].'</td><td>'.$res['ClientName'].'</td><td>'.$res['InvoiceNo'].'</td><td><a href="motorcyclepromo.php?w=Matched&SaleNo='.$res['InvoiceNo'].'&PaymentType='.$res['paytype'].'" target="_blank"><b>View</b></a></td>
			<td>'.$res['Amount'].'</td><td>'.$res['OriginalAmount'].'</td><td>'.$res['PaymentType'].'</td><td>'.$res['Valid'].'</td> '.(($_POST['showvalue']==1)?'<td>'.$res['EncodedBy'].'</td><td>'.$res['TimeStamp'].'</td>':'').'
			
			'.(($res['Valid']=='VALID' AND $btn==1)?'<td><a OnClick="return confirm(\'Are you sure you want to invalidate?\');" href="motorcyclepromo.php?w=invalidate&action_token='.$_SESSION['action_token'].'&TxnID='.$res['TxnID'].'&c=1">Invalidate</a></td>':'').'
			
			'.(($res['Valid']=='INVALID' AND $btn==1)?'<td><a OnClick="return confirm(\'Are you sure you want to validate?\');" href="motorcyclepromo.php?w=invalidate&action_token='.$_SESSION['action_token'].'&TxnID='.$res['TxnID'].'">Validate</a></td>':'').'
			
			'.(($res['Valid']=='INVALID')?'<td><a OnClick="return confirm(\'Are you sure you want to delete?\');" href="motorcyclepromo.php?w=deleteinvalid&action_token='.$_SESSION['action_token'].'&TxnID='.$res['TxnID'].'">Delete</a></td>':'').'
			
			</tr>';	
			}elseif(empty($res['Date'])){
				echo'<tr><td td bgcolor="red">'.$res['DateOfEntry'].'</td><td bgcolor="red">'.$res['Date'].'</td><td bgcolor="red">'.$res['Branch'].'</td><td bgcolor="red">'.$res['ClientName'].'</td><td bgcolor="red">'.$res['InvoiceNo'].'</td><td><a href="motorcyclepromo.php?w=Matched&SaleNo='.$res['InvoiceNo'].'&PaymentType='.$res['paytype'].'" target="_blank"><b>View</b></a></td>
			<td bgcolor="red">'.$res['Amount'].'</td><td bgcolor="red">'.$res['OriginalAmount'].'</td><td bgcolor="red">'.$res['PaymentType'].'</td><td bgcolor="red">'.$res['Valid'].'</td> '.(($_POST['showvalue']==1)?'<td bgcolor="red">'.$res['EncodedBy'].'</td><td bgcolor="red">'.$res['TimeStamp'].'</td>':'').'
			
			'.(($res['Valid']=='INVALID')?'<td><a OnClick="return confirm(\'Are you sure you want to delete?\');" href="motorcyclepromo.php?w=deleteinvalid&action_token='.$_SESSION['action_token'].'&TxnID='.$res['TxnID'].'">Delete</a></td>':'').'
			
			</tr>';		

			
			}else{
			echo'<tr><td td bgcolor="yellow">'.$res['DateOfEntry'].'</td><td bgcolor="yellow">'.$res['Date'].'</td><td bgcolor="yellow">'.$res['Branch'].'</td><td bgcolor="yellow">'.$res['ClientName'].'</td><td bgcolor="yellow">'.$res['InvoiceNo'].'</td><td><a href="motorcyclepromo.php?w=Matched&SaleNo='.$res['InvoiceNo'].'&PaymentType='.$res['paytype'].'" target="_blank"><b>View</b></a></td>
			<td bgcolor="yellow">'.$res['Amount'].'</td><td bgcolor="yellow">'.$res['OriginalAmount'].'</td><td bgcolor="yellow">'.$res['PaymentType'].'</td><td bgcolor="yellow">'.$res['Valid'].'</td> '.(($_POST['showvalue']==1)?'<td bgcolor="yellow">'.$res['EncodedBy'].'</td><td bgcolor="yellow">'.$res['TimeStamp'].'</td>':'').'
			
			'.((($res['Valid']=='VALID' or $res['Valid']=='SUSPICIOUS' or $res['Valid']=='INNOCENT') AND $btn==1)?'<td><a OnClick="return confirm(\'Are you sure you want to invalidate?\');" href="motorcyclepromo.php?w=invalidate&action_token='.$_SESSION['action_token'].'&TxnID='.$res['TxnID'].'&c=1">Invalidate</a></td>':'').'
			
			'.(($res['Valid']=='INVALID' AND $btn==1)?'<td><a OnClick="return confirm(\'Are you sure you want to validate?\');" href="motorcyclepromo.php?w=invalidate&action_token='.$_SESSION['action_token'].'&TxnID='.$res['TxnID'].'">Validate</a></td>':'').'
			
			'.(($res['Valid']=='INVALID')?'<td><a OnClick="return confirm(\'Are you sure you want to delete?\');" href="motorcyclepromo.php?w=deleteinvalid&action_token='.$_SESSION['action_token'].'&TxnID='.$res['TxnID'].'">Delete</a></td>':'').'
			
			</tr>';	
			}
				// if($res['Amount']!=$res['OriginalAmount']){
					// $c++;
				// }
		}
		echo '</table>';
		// echo '<b>'.$c.'</b>'; 
	break;
	
	case'deleteinvalid':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnID']);
		$sql='delete from `events_1motorcyclepromo` WHERE TxnID='.$txnid.' and Valid=\'-1\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:motorcyclepromo.php?w=lists');
	break;
	
	case'invalidate':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$txnid = intval($_GET['TxnID']);
		if(isset($_GET['c'])){
			$valid='Valid=-1';
		}else{
			$valid='Valid=1';
		}
		$sql='Update `events_1motorcyclepromo` SET '.$valid.',EncodedByNo='.$_SESSION['(ak0)'].' ,TimeStamp=Now() WHERE TxnID='.$txnid.'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql);
		$stmt->execute();
		header('Location:motorcyclepromo.php?w=lists');
	break;
	
	case'export':
		$title='Export Data';
				echo '<title>'.$title.'</title>';
				echo '<br><h3>'.$title.'</h3>';
				echo'<form method="post" action="motorcyclepromo.php?w=export">
				DateValidated <input type="date" name="DateValidated" value="'.(isset($_SESSION['DateValidated'])?$_SESSION['DateValidated']:date('Y-m-d')).'"> <input type="submit" name="Export" value="Export Data" OnClick="return confirm(\'Are you sure you want to Export?\');">
				</form>';
				
				//Export Function
				function exportassql($link,$tablearray, $sqlarray){
					$output='';
					foreach($tablearray as $table)
				 {
					$statement = $link->prepare($sqlarray[$table]);
				  $statement->execute();
				  $total_row = $statement->rowCount();

				  for($count=0; $count<$total_row; $count++)
				  {
				   $single_result = $statement->fetch(PDO::FETCH_ASSOC);
				   $table_column_array = array_keys($single_result);
				   $table_value_array = array_values($single_result);
				   $output .= "\nINSERT INTO $table (";
				   $output .= "" . implode(", ", $table_column_array) . ") VALUES (";
				   $output .= "\"" . implode("\",\"", $table_value_array) . "\");\n";
				  }
				 }
				  
				 $tablename=implode($tablearray,"_");
				  $file_name = date('Y-m-d').'_'.$tablename.'.sql';
				  
				  ob_get_clean();
					header('Content-Type: application/octet-stream');
					header("Content-Transfer-Encoding: Binary");
					header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($output, '8bit'): strlen($output)) );
					header("Content-disposition: attachment; filename=\"".$file_name."\""); 
					echo $output; exit;
				}
				//End of Export Function
				if(isset($_POST['Export'])){
					// $sql='SELECT TxnID,Valid from events_1motorcyclepromo Where Valid in (1,-1) AND DATE(TimeStamp)=CURDATE() ';
					$sql='SELECT TxnID,
					case when Valid=1 then "1"
						when Valid=-1 then "-1"
						when Valid=2 then "1"
						when Valid=3 then "1"
						when Valid=4 then "1"
					end as
					Valid

					from events_1motorcyclepromo Where Valid <>0 AND DATE(TimeStamp)="'.$_POST['DateValidated'].'" ';
					// echo $sql; exit();
					$tablearray=array('2validentries');
					$sqlarray=array('2validentries'=>$sql);
					exportassql($link,$tablearray,$sqlarray);	
				}	
	
	break;
	
	case'validate':
if (!allowedToOpen(4006,'1rtc')) { echo 'No permission'; exit; }
//valid
		$sqlv='Update events_1motorcyclepromo Set Valid=1,EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() where TxnID in (select mp.TxnID from events_1motorcyclepromo mp join `'.$currentyr.'txn` t on TRIM(LEADING "0" FROM mp.SaleNo)=TRIM(LEADING "0" FROM t.SaleNo) AND mp.BranchNo=t.BranchNo AND mp.paytype=t.paytype where Valid=0) and Valid=0';
		// echo $sqlv; exit();
		$stmtv=$link->prepare($sqlv); $stmtv->execute();
//invalid		
		$sqlnv='Update events_1motorcyclepromo Set Valid=-1,EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() where TxnID not in (select mp.TxnID from events_1motorcyclepromo mp join `'.$currentyr.'txn` t on TRIM(LEADING "0" FROM mp.SaleNo)=TRIM(LEADING "0" FROM t.SaleNo) AND mp.BranchNo=t.BranchNo AND mp.paytype=t.paytype where Valid=0) and Valid=0';
		// echo $sqlnv; exit();
		$stmtnv=$link->prepare($sqlnv); $stmtnv->execute();
//suspicious
		$sqlv='Update events_1motorcyclepromo Set Valid=2,EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() where TxnID in (select mp.TxnID from events_1motorcyclepromo mp join `'.$currentyr.'txn` t on TRIM(LEADING "0" FROM mp.SaleNo)=TRIM(LEADING "0" FROM t.SaleNo) AND mp.BranchNo=t.BranchNo AND mp.paytype=t.paytype where Valid=1 and round(RecordedAmount)<>round(Amount) AND Date(mp.TimeStamp)=CURDATE()) and Valid=1';
		// echo $sqlv; exit();
		$stmtv=$link->prepare($sqlv); $stmtv->execute();

		header("Location:motorcyclepromo.php?w=lists");
	break;
	
	case 'SetDateSession':
		$_SESSION['DateValidated']=$_POST['DateValidated'];
		header("Location:motorcyclepromo.php?w=listsperdate");
	break;
	
	case 'summaryreport':
	if (allowedToOpen(4011,'1rtc')) {
		$th='<th>Verified</th>';
	}else{
		$th='';
	}
	
	if($_GET['s']==2){
		$title='Suspicious Branch';
	}elseif($_GET['s']==3){
		$title='Innocent';
	}elseif($_GET['s']==4){
		$title='Guilty';
	}
	$sql='select 
	(select TxnID from invty_2sale where BranchNo=mp.BranchNo and SaleNo=mp.SaleNo and PaymentType=mp.paytype ) as TxnIDValue,
	(select txntype from invty_2sale where BranchNo=mp.BranchNo and SaleNo=mp.SaleNo and PaymentType=mp.paytype ) as txntypevalue,
	
	
	mp.ClientNo,mp.BranchNo,mp.paytype,TxnID,DateOfEntry, Branch, ClientName,SaleNo as InvoiceNo, Amount as AmountOfEntry, CASE WHEN mp.paytype=1 then "Cash" WHEN mp.paytype=2 then "Charge" WHEN mp.paytype=32 then "GCash" WHEN mp.paytype=33 then "Paymaya" end as PaymentType from events_1motorcyclepromo mp
	left join 1branches b on b.BranchNo=mp.BranchNo
	left join events_1tempclients c on mp.ClientNo=c.ClientNo
	where Valid=\''.$_GET['s'].'\'';
	// echo $sql; exit();
	$stmt=$link->query($sql); $result=$stmt->fetchAll();
	echo'<title>'.$title.'</title></br><h3>'.$title.'</h3><table id="table"">
 <tr><th>DateOfEntry</th><th>Branch</th><th>ClientName</th><th>InvoiceNo</th><th>AmountOfEntry</th><th>PaymentType</th>'.$th.'<th>Lookup</th><th>Lookup Receipt</th></tr>';
				$c=0;
		foreach($result as $res){
		$bgcolor='';
		
			if (allowedToOpen(4011,'1rtc')) {
				$td='<td>
					<a OnClick="return confirm(\'Are you sure?\');" href="motorcyclepromo.php?w=Verified&s=4&action_token='.$_SESSION['action_token'].'&BranchNo='.$res['BranchNo'].'&paytype='.$res['paytype'].'&SaleNo='.$res['InvoiceNo'].'"><b>Guilty?</b></a> '.str_repeat('&nbsp;',5).'
					
					<a OnClick="return confirm(\'Are you sure?\');" href="motorcyclepromo.php?w=Verified&s=3&action_token='.$_SESSION['action_token'].'&BranchNo='.$res['BranchNo'].'&paytype='.$res['paytype'].'&SaleNo='.$res['InvoiceNo'].'"><b>Innocent?</b></a>
					
					</td>';
			}else{
				$td='';
			}
			
			echo'<tr><td '.$bgcolor.'>'.$res['DateOfEntry'].'</td><td '.$bgcolor.'>'.$res['Branch'].'</td><td '.$bgcolor.'>'.$res['ClientName'].'</td><td '.$bgcolor.'>'.$res['InvoiceNo'].'</td>
			<td '.$bgcolor.'>'.$res['AmountOfEntry'].'</td><td '.$bgcolor.'>'.$res['PaymentType'].'</td>'.$td.'<td>
			<a target="_blank" href="../invty/addeditsale.php?TxnID='.$res['TxnIDValue'].'&txntype='.$res['txntypevalue'].'">Lookup</a></td>
			<td>
				<a target="_blank" href="https://joinusat1rotarytrading.com/arangkadapromo/imageView.php?TxnID='.$res['TxnID'].'">Lookup Receipt</a>
			</td></tr>';	
				$c++;
		}
		echo '</table>';
		echo '</br><b>Total: '.$c.'</b>'; 
	
	
	break;
	
	case'Verified':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='update events_1motorcyclepromo set Valid=\''.$_GET['s'].'\' where BranchNo=\''.$_GET['BranchNo'].'\' and paytype=\''.$_GET['paytype'].'\' and SaleNo=\''.$_GET['SaleNo'].'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:motorcyclepromo.php?w=summaryreport&s=".$_GET['s']."");
	break;
	
	
	case 'Matched':
	
	if($_GET['PaymentType']==1){
		$paymenttype='Cash';
	} else if ($_GET['PaymentType']==2){
		$paymenttype='Charge';
	} else if ($_GET['PaymentType']==32) {
		$paymenttype='GCash';
	} else if($_GET['PaymentType']==33) {
		$paymenttype='Paymaya';
	}
	$title='Branches with matched InvoiceNo and Payment Type';
	$formdesc='<br></i>InvoiceNo: <font color="blue"><u>'.$_GET['SaleNo'].'</u></font>, PaymentType: <font color="blue"><u>'.$paymenttype.'</u></font><i>';
	$sql='SELECT SaleNo AS InvoiceNo,`Date` AS DateOfInvoice,SUM(Qty*UnitPrice) as RecordedAmount,Branch,(CASE WHEN PaymentType=1 then "Cash" WHEN PaymentType=2 then "Charge" WHEN PaymentType=32 then "GCash" WHEN PaymentType=33 then "Paymaya" end) as PaymentType FROM invty_2sale s JOIN invty_2salesub ss ON s.TxnID=ss.TxnID JOIN 1branches b ON s.BranchNo=b.BranchNo WHERE TRIM(LEADING "0" FROM SaleNo)=TRIM(LEADING "0" FROM '.$_GET['SaleNo'].') AND PaymentType='.$_GET['PaymentType'].' GROUP BY s.BranchNo ORDER BY `Date` DESC';
	 $columnnames=array('DateOfInvoice','Branch','InvoiceNo','RecordedAmount');
	// echo $sql;
	include('../backendphp/layout/displayastablenosort.php'); 
	break;
	
	case 'import':
	
													$maxsize = 10048576; //max size
												function sqlImport($file)
												{
													
														
													global $link;
													$delimiter = ';';
													$file = fopen('sqlfile/'.$file.'.sql', 'r');
													$isFirstRow = true;
													$isMultiLineComment = false;
													$sql = '';

													while (!feof($file)) {

														$row = fgets($file);

														// remove BOM for utf-8 encoded file
														if ($isFirstRow) {
															$row = preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', $row);
															$isFirstRow = false;
														}

														// 1. ignore empty string and comment row
														if (trim($row) == '' || preg_match('/^\s*(#|--\s)/sUi', $row)) {
															continue;
														}

														// 2. clear comments
														$row = trim(clearSQL($row, $isMultiLineComment));

														// 3. parse delimiter row
														if (preg_match('/^DELIMITER\s+[^ ]+/sUi', $row)) {
															$delimiter = preg_replace('/^DELIMITER\s+([^ ]+)$/sUi', '$1', $row);
															continue;
														}

														// 4. separate sql queries by delimiter
														$offset = 0;
														while (strpos($row, $delimiter, $offset) !== false) {
															$delimiterOffset = strpos($row, $delimiter, $offset);
															if (isQuoted($delimiterOffset, $row)) {
																$offset = $delimiterOffset + strlen($delimiter);
															} else {
																$sql = trim($sql . ' ' . trim(substr($row, 0, $delimiterOffset)));
																// echo $sql; exit();
																try {
																	$stmt=$link->prepare($sql); $stmt->execute();
																}
																catch(Exception $e) {
																	echo ($e->getMessage());
																	exit();
																}
																
																$row = substr($row, $delimiterOffset + strlen($delimiter));
																$offset = 0;
																$sql = '';
															}
														}
														$sql = trim($sql . ' ' . $row);
													}
													
													if (strlen($sql) > 0) {
													   // query($row,$pdo);
													    try {
															$stmt=$link->prepare($sql); $stmt->execute();
															
														}
														catch(Exception $e) {
															echo ($e->getMessage());
															exit();
														}
													}

													fclose($file);
												}
												function clearSQL($sql, &$isMultiComment)
												{
													if ($isMultiComment) {
														if (preg_match('#\*/#sUi', $sql)) {
															$sql = preg_replace('#^.*\*/\s*#sUi', '', $sql);
															$isMultiComment = false;
														} else {
															$sql = '';
														}
														if(trim($sql) == ''){
															return $sql;
														}
													}

													$offset = 0;
													while (preg_match('{--\s|#|/\*[^!]}sUi', $sql, $matched, PREG_OFFSET_CAPTURE, $offset)) {
														list($comment, $foundOn) = $matched[0];
														if (isQuoted($foundOn, $sql)) {
															$offset = $foundOn + strlen($comment);
														} else {
															if (substr($comment, 0, 2) == '/*') {
																$closedOn = strpos($sql, '*/', $foundOn);
																if ($closedOn !== false) {
																	$sql = substr($sql, 0, $foundOn) . substr($sql, $closedOn + 2);
																} else {
																	$sql = substr($sql, 0, $foundOn);
																	$isMultiComment = true;
																}
															} else {
																$sql = substr($sql, 0, $foundOn);
																break;
															}
														}
													}
													return $sql;
												}
												function isQuoted($offset, $text)
												{
													if ($offset > strlen($text))
														$offset = strlen($text);

													$isQuoted = false;
													for ($i = 0; $i < $offset; $i++) {
														if ($text[$i] == "'")
															$isQuoted = !$isQuoted;
														if ($text[$i] == "\\" && $isQuoted)
															$i++;
													}
													return $isQuoted;
												}
												set_time_limit(0);
												header('Content-Type: text/html;charset=utf-8');
	
	if (isset($_POST['import'])){
$DOWNLOAD_DIR="sqlfile";
if (isset($_FILES['userfile']['tmp_name'])) {
                $photo_filename=$_FILES['userfile']['name'];
				$ext = pathinfo($photo_filename, PATHINFO_EXTENSION);
				if( $ext !== 'sql' ) { echo 'Error! Invalid File Type.'; exit(); }
				if(($_FILES['userfile']['size'] >= $maxsize)){ echo 'Error! Invalid File Size (MAX 10MB).'; exit(); }
                $temp_pathinfo=pathinfo($photo_filename);
                $file_extension=$temp_pathinfo['extension'];
                $photo_stored_filename=$photo_filename."." . $file_extension;
                $place_to_put=$DOWNLOAD_DIR . "/" . $photo_stored_filename;
                if (file_exists($place_to_put)) {
                    unlink($place_to_put);
                }
                 if (copy($_FILES['userfile']['tmp_name'],$place_to_put)) {
                 $good="Successfully_added_$photo_filename";
				 
				$sql='DELETE FROM events_1tempclients'; 
				$stmt=$link->prepare($sql); $stmt->execute();
				
				sqlImport($photo_filename);
				header("Location:motorcyclepromo.php?w=lists&Message=".$good."");
                }
                 else {
                 $good="Error: " . $_FILES["userfile"]["error"];
                 }
            } else {
		$good="No file to upload.";
	    }
echo $good;
	}
	break;
	
	
	
	
}

?>