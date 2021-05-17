<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true; include_once('../switchboard/contents.php');
 

$method='POST';
$whichqry=$_GET['w'];

switch ($whichqry){

case 'Sale':
        
if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5971;}
        if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}        
    $title='Add Sale';
    $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'Branch', 'type'=>'text','size'=>10,'required'=>true,'list'=>'branchnames', 'value'=>$_SESSION['@brn']),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false));
    
    $action='praddmain.php?w=SaleMain';
    $liststoshow=array('branchnames');
     include('../backendphp/layout/inputmainform.php');
     break;

case 'Deposit':
   if (!allowedToOpen(514,'1rtc')) { echo 'No permission'; exit; }
        
      
    $title='Add Deposit';
    $bank=allowedToOpen(5141,'1rtc')?'allbanks':'banks';
    $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                       array('field'=>'DebitAccountID','caption'=>'Bank','type'=>'text','size'=>10,'required'=>true,'list'=>'banks'),
                       array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false));
    
    $action='praddmain.php?w=DepMain';
    $liststoshow=array($bank);  $fieldsinrow=5;
     include('../backendphp/layout/inputmainform.php');
     
     $sql='SELECT m.AccountID, m.ShortAcctID as Bank, m.AcctNo AS `Account Number`, m.AcctName AS `Account Name`, DepositCharge FROM `banktxns_1maintaining` as m 
WHERE m.OwnedByCompany='.$_SESSION['*cnum'].' OR AccountID=135
UNION SELECT m.AccountID, m.ShortAcctID, m.AcctNo, m.AcctName, DepositCharge FROM `banktxns_1maintaining` as m left JOIN `banktxns_branchdefaultbank` as d ON m.AccountID=d.BankAcctID WHERE (((d.BranchNo)='.$_SESSION['bnum'].')) ORDER BY Bank;';
     $columnnames=array('AccountID','Bank','Account Name','Account Number','DepositCharge');
     $hidecount=true; $title='Bank Accounts';
     echo '<br><br><br><style>table {    border-collapse: collapse; }
                td {  border: 1px solid black; } </style>';
     include('../backendphp/layout/displayastablenosort.php');
     
     break;
     

case 'Collect':
    if (!allowedToOpen(515,'1rtc')) { echo 'No permission'; exit; }        
    //COLLECTION RECEIPT
    $title='Add Collection Receipt';
	echo '<title>'.$title.'</title>';
	echo '<div align="center">';
	echo '<h2>'.$title.'</h2><br>';
    $radiosql='SELECT * FROM `acctg_1collecttype` WHERE CollectTypeID<>6  ORDER BY `OrderBy`';
	$stmt=$link->prepare($radiosql); $stmt->execute(); $res=$stmt->fetchAll();
	$radio='';
	$radionamefield='CollectTypeID'; $radiovaluefield='CollectTypeID'; $radiocaptionfield='CollectTypeDesc'; 
    $wid=1;
	
	foreach ($res as $radopt){
		$radio.='<input type="radio" id="watch-me'.$wid.'" name="'.$radionamefield.'" value="'.$radopt[$radiovaluefield].'">'.$radopt[$radiocaptionfield].'<br>';
		$wid++;
		$val[] = ($radopt[$radiocaptionfield]);
	}
	
    include $path.'/acrossyrs/commonfunctions/enablebasedonradio.php';
	$img='<img src="../acctg/acctglayout/samplecheck.jpg"><br><br>';
	$formaction="<br><br><form action='praddmain.php?w=CollectMain' method='POST'>
				<input type='hidden' name='action_token' value='".$_SESSION['action_token']."'>
				Date: <input type='date' name='Date' value='".date('Y-m-d')."' required><br>
				Collection Receipt Number: <input type='text' name='CollectNo' autocomplete='off' required><br>
				";
	//
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	echo comboBox($link,'SELECT Left(`ClientName`,20) as `ClientName`, ClientNo FROM `acctg_1clientsperbranch` WHERE `BranchNo`='.$_SESSION['bnum'].' and ClientNo=10004 ORDER BY ClientName','ClientNo','ClientName','clientsdatedcheck');			
	$clientsdatedcheck="Client: <input type='text' name='Client' list='clientsdatedcheck' required><br>";
	$dcrinput="Client Name:<input type='text' required name='Remarks[]' autocomplete='off'><br>
	Tin Number:<input type='text' required name='Remarks[]' pattern='[0-9]{12}' title='12 digits' autocomplete='off'><br>
	Address:<input type='text' required name='Remarks[]' autocomplete='off'><br>";
	//
	
	$clientsnodatedcheck="Client: <input type='text' name='Client' list='clients' required><br>";
	//clientsnodatedcheck1
	echo comboBox($link,'SELECT Left(`ClientName`,20) as `ClientName`, ClientNo FROM `acctg_1clientsperbranch` WHERE `BranchNo`='.$_SESSION['bnum'].' and ClientNo<>10004 ORDER BY ClientName','ClientNo','ClientName','clientsnodatedcheck');		
	$clientsnodatedcheck1="Client: <input type='text' name='Client' list='clientsnodatedcheck' required><br>";		
	//
	$rinput="Remarks: <input type='text' name='Remarks' autocomplete='off'><br>";
	$cinput="ReceivedBy: <input type='text' name='ReceivedBy' list='collectorlist' required><br>";
	$cbankinput="<input type='text' name='CheckBank' autocomplete='off' required><br>";
	$chknoinput="<input type='text' name='CheckNo' autocomplete='off' required><br>";
	$chkbrstninput="CheckBRSTN: <input type='text' name='CheckBRSTN' autocomplete='off' required><br>";
        $docinput="<input type='date' name='DateofCheck' value='".date('Y-m-d')."' required><br>";
	echo '<table style="text-align:left;"><tr><td>';
    echo "<br><br><form id='form-id'>
		".$radio."
		</form>";
	echo '</td></tr>';
	
	echo '<tr><td>';
	echo "<div id='show-me1' style='display:none'>
				".$formaction." ".$clientsnodatedcheck."
					<input type='hidden' name='Type' value='".$val[0]."'>
					".$rinput."
					".$cinput."
					<input type='submit' value='Add ".$val[0]."'>
				</form>
			</div>";
	echo '</td></tr>';
	echo '<tr><td>';
	echo "<div id='show-me2' style='display:none'>
				".$img.$formaction." ".$clientsnodatedcheck1."
				<input type='hidden' name='Type' value='".$val[1]."'>
				CheckBank: ".$cbankinput."
				CheckNo: ".$chknoinput."
				".$chkbrstninput."
				".$rinput."
				".$cinput."
                                Date of Check: ".$docinput."
				Account Number on Check: <input type='text' name='ClientCheckBankAccountNo' autocomplete='off' required></br>				
				<input type='submit' value='Add ".$val[1]."'>
			</form>
		</div>";
	echo '</td></tr>';
	echo '<tr><td>';
	echo "<div id='show-me4' style='display:none'>
				".$formaction." ".$clientsnodatedcheck."
				<input type='hidden' name='Type' value='".$val[3]."'>
				Approval Number: ".$chknoinput."
				".$rinput."
				".$cinput."
				<input type='submit' value='Add ".$val[3]."'>
			</form>
		</div>";
	echo '</td></tr>';
	echo '<tr><td>';
	echo "<div id='show-me5' style='display:none'>
				".$img.$formaction." ".$clientsnodatedcheck."
				<input type='hidden' name='Type' value='".$val[4]."'>
				CheckBank: ".$cbankinput."
				CheckNo: ".$chknoinput."
				".$chkbrstninput."
				".$rinput."
				".$cinput."
                                Date of Check: ".$docinput."
				Account Number on Check: <input type='text' name='ClientCheckBankAccountNo' autocomplete='off' required></br>				
				<input type='submit' value='Add ".$val[4]."'>
			</form>
		</div>";
	echo '</td></tr>';
	echo '<tr><td>';
	echo "<div id='show-me6' style='display:none'>
				".$formaction." ".$clientsnodatedcheck."
				<input type='hidden' name='Type' value='".$val[5]."'>
				<input type='hidden' name='CheckBank' value='Downpayment'>
				Canvass ID (number only): ".$chknoinput."
				".$rinput."
				".$cinput."
				<input type='submit' value='Add ".$val[5]."'>
			</form>
		</div>";
	echo '</td></tr>';
	echo '<tr><td>';
	echo "<div id='show-me3' style='display:none'>
				".$img.$formaction." ".$clientsdatedcheck."
				<input type='hidden' name='Type' value='".$val[2]."'>
				CheckBank: ".$cbankinput."
				CheckNo: ".$chknoinput."
				".$chkbrstninput."
				".$dcrinput."
				".$cinput."
                                Date of Check: ".$docinput."
				Account Number on Check: <input type='text' name='ClientCheckBankAccountNo' autocomplete='off' required></br>				
				<input type='submit' value='Add ".$val[2]."'>
			</form>
		</div>";
	echo '</td></tr>';
        
		echo '</table>';

	echo '</div>'; //center
	//clients
	$whichlist='clients';
	include_once "../generalinfo/lists.inc";
	renderlist($whichlist);
   
   //collectorlist
	 include_once('acctglists.inc');
	  $otherlist='collectorlist';
	  $listcondition='';
	 renderotherlist($otherlist,$listcondition);
    
	
     //END OF COLLECTION RECEIPT
     
     break;
     
case 'BouncedfromPR':        
        if (!allowedToOpen(5931,'1rtc')) { echo 'No permission'; exit; }
      //  echo 'OBSOLETE. Please use the new form.'; exit();
    $title='Add Bounced Check';
    $columnnames=array(
                    array('field'=>'Date', 'caption'=>'Date Bounced','type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'CreditAccountID','caption'=>'Bank','type'=>'text','size'=>10,'required'=>true,'list'=>'banks'),
                    array('field'=>'Client','type'=>'text','size'=>10,'required'=>true,'list'=>'clients'),
                       array('field'=>'CheckNo', 'type'=>'text','size'=>20, 'required'=>true),
                       array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false));
    
    $action='praddmain.php?w=BouncedMain';
    $liststoshow=array('allbanks','clients');
     include('../backendphp/layout/inputmainform.php');
     break;   

case 'Bounced':        
        if (!allowedToOpen(5931,'1rtc')) { echo 'No permission'; exit; }
    $title='Add Bounced Check';
   
    if(!isset($_GET['s']) and ($_GET['s']<>'SearchCR')) {
        $columnnames=array(
                    array('field'=>'Date', 'caption'=>'Date Bounced','type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'CreditAccountID','caption'=>'Bank','type'=>'text','size'=>10,'required'=>true,'list'=>'banks'),
                    array('field'=>'Client','type'=>'text','size'=>10,'required'=>true,'list'=>'clients'),
                       array('field'=>'CheckNo', 'type'=>'text','size'=>20, 'required'=>true),
                       array('field'=>'CR_LastYear?', 'type'=>'checkbox','size'=>20, 'required'=>false),
                       array('field'=>'Remarks', 'caption'=>'Remarks (unpaid invoices will automatically be added here)', 'type'=>'text','size'=>50, 'required'=>false));
        $action='addmain.php?w=Bounced&s=SearchCR';
        $liststoshow=array('allbanks','clients');
        include('../backendphp/layout/inputmainform.php');    
    }
    else {
		if(isset($_POST['CR_LastYear?'])){
			$fromlastyr='&FromLastYr='.$lastyr.'_1rtc.';
		} else {
			$fromlastyr='';
		}
        $editprocess='praddmain.php?w=BouncedfromCR'.$fromlastyr.'&action_token='.$_SESSION['action_token'].'&Bounced='.$_POST['Date'].(empty($_POST['Remarks'])?'':'&Remarks='.$_POST['Remarks']).'&CRID='.$_POST['CreditAccountID'].'&TxnID='; 
        $editprocesslabel='Record Bounced Check'; $txnidname='TxnID';
		if($fromlastyr==''){
        $sql='SELECT m.TxnID, CollectNo, BranchSeriesNo, ClientName, CheckBank, CheckNo, CheckBRSTN, DateofCheck, m.Remarks, ReceivedBy, FORMAT(SUM(s.Amount)-(SELECT IFNULL(SUM(Amount),0) FROM `acctg_2collectsubdeduct` csd WHERE csd.TxnID=m.TxnID),2) AS AmtofCheck, Branch FROM acctg_2collectmain m JOIN acctg_2collectsub s ON m.TxnID=s.TxnID JOIN 1clients c ON c.ClientNo=m.ClientNo LEFT JOIN acctg_2collectsubbounced sb ON sb.TxnID=m.TxnID
JOIN 1branches b ON b.BranchNo=m.BranchSeriesNo WHERE sb.TxnID IS NULL AND ClientName LIKE \''.$_POST['Client'].'%\' AND CheckNo LIKE \'%'.$_POST['CheckNo'].'%\' GROUP BY m.TxnID';
		} else {
			$sql='SELECT upl.UndepPDCId AS TxnID, CRNo AS CollectNo, BranchSeriesNo,ClientName,PDCBank AS CheckBank,PDCNo AS CheckNo, PDCBRSTN AS CheckBRSTN, DateofPDC AS DateofCheck, "From Last Period" AS Remarks, "" AS ReceivedBy, FORMAT(SUM(AmountOfPDC),2) AS AmtofCheck, Branch FROM `acctg_3undepositedpdcfromlastperiod` `upl` JOIN 1clients c ON c.ClientNo=upl.ClientNo LEFT JOIN acctg_3undepositedpdcfromlastperiodbounced uplb ON uplb.UndepPDCId=upl.UndepPDCId JOIN 1branches b ON b.BranchNo=upl.BranchSeriesNo WHERE uplb.UndepPDCId IS NULL AND ClientName LIKE \''.$_POST['Client'].'%\' AND PDCNo LIKE \'%'.$_POST['CheckNo'].'%\' GROUP BY upl.UndepPDCId';
		}

// echo $sql; exit();
        $subtitle='Is this the check that bounced?'; 
        $columnnames=array('CollectNo', 'ClientName', 'CheckBank', 'CheckNo', 'CheckBRSTN', 'DateofCheck','TxnID',  'Remarks', 'ReceivedBy', 'AmtofCheck', 'Branch');
        echo '<div style="background-color: FFCCCC; ">';
        include('../backendphp/layout/displayastablenosort.php');
        echo '</div>';
		
        $sql='SELECT m.TxnID, DateBounced, ShortAcctID AS Bank, CollectNo, BranchSeriesNo, ClientName, CheckBank, CheckNo, CheckBRSTN, DateofCheck, m.Remarks, ReceivedBy, FORMAT(SUM(s.Amount)-(SELECT IFNULL(SUM(Amount),0) FROM `acctg_2collectsubdeduct` csd WHERE csd.TxnID=m.TxnID),2) AS AmtofCheck, Branch FROM acctg_2collectmain m JOIN acctg_2collectsub s ON m.TxnID=s.TxnID JOIN 1clients c ON c.ClientNo=m.ClientNo JOIN acctg_2collectsubbounced sb ON sb.TxnID=m.TxnID
JOIN 1branches b ON b.BranchNo=m.BranchSeriesNo JOIN acctg_1chartofaccounts ca ON ca.AccountID=sb.CreditAccountID 
WHERE ClientName LIKE \''.$_POST['Client'].'%\' AND CheckNo LIKE \'%'.$_POST['CheckNo'].'%\' GROUP BY m.TxnID'; 
// echo $sql;
        $subtitle='<br><hr><br>Previously bounced checks';
        $columnnames=array('DateBounced', 'Bank', 'CollectNo', 'ClientName', 'CheckBank', 'CheckNo', 'CheckBRSTN', 'DateofCheck','TxnID',  'Remarks', 'ReceivedBy', 'AmtofCheck', 'Branch');
        unset($editprocess);
        include('../backendphp/layout/displayastableonlynoheaders.php');
    }
     break;	 

case 'Interbranch':
case 'Txfr':
if (!allowedToOpen(5951,'1rtc')) { echo 'No permission'; exit; }      
    $title='Add Interbranch Transfer';
    $columnnames=array(
                    array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')));
    
    $action='praddmain.php?w=Interbranch';
    $liststoshow=array();
     include('../backendphp/layout/inputmainform.php');
     break;    
     
     
// case 'Purchase':
//       if($_SESSION['bnum']==999){ $allowed=999;} else { $allowed=5962;}
//         if (!allowedToOpen($allowed,'1rtc')) { echo 'No permission'; exit;}   
//         header('Location: formpurch.php?w=AddMain');      
    // $title='Add Purchase';
    // $columnnames=array(
    //                 array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
    //                 array('field'=>'SupplierName','caption'=>'Supplier','type'=>'text','size'=>10,'required'=>true,'list'=>'suppliers'),
    //                 array('field'=>'SupplierInv','caption'=>'Invoice Number','type'=>'text','size'=>10,'required'=>true),
    //                 array('field'=>'DateofInv', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
    //                 array('field'=>'MRRNo','type'=>'text','size'=>10,'required'=>true),
    //                // array('field'=>'DebitAccount','type'=>'text','size'=>10,'required'=>true,'list'=>'accounts'),
    //                 array('field'=>'CreditAccount','type'=>'text','size'=>10,'required'=>true,'list'=>'accounts','value'=>'APTRADE'),
    //                // array('field'=>'Amount','type'=>'text','size'=>10,'required'=>true),
    //                 array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false),
    //                 array('field'=>'Branch', 'type'=>'text','size'=>10,'required'=>true,'list'=>'branchnames', 'value'=>$_SESSION['@brn']),
    //                 array('field'=>'RCompany', 'type'=>'text','size'=>10, 'required'=>false,'list'=>'companies'));
    
    // $action='praddmain.php?w=PurchaseMain';
    // $listcondition='';
    // $whichotherlist='acctg';
    // $otherlist=array('accounts');
    // $liststoshow=array('suppliers','companies','branchnames');
    //  include('../backendphp/layout/inputmainform.php');
 //    break;

// case 'CV':
//       if (!allowedToOpen(5401,'1rtc')) { echo 'No permission'; exit;} 
//       header('Location: formcv.php?w=AddMain'); exit;
// case 'FutureCV':
//       if (!allowedToOpen(5401,'1rtc')) { echo 'No permission'; exit;} 
// if ($whichqry=='FutureCV'){
//    $title='Add PDC'; $w='FutureCV'; $vchdate=strtotime("next year"); $table='acctg_4futurecvmain';
// } else {//Check Vouchers
//    $title='Add Check Voucher'; $w='CVMain'; $vchdate=strtotime((date('Y')==$currentyr?"today":''.$currentyr.'-01-01')); $table='acctg_2cvmain';
// }

// include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
// echo comboBox($link,'SELECT PaymentModeID,PaymentMode FROM `acctg_0paymentmodes` ORDER BY PaymentModeID;','PaymentModeID','PaymentMode','pmlist');

//     include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
//     $vchno=lastNum('CVNo',$table,((date('Y',$vchdate))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',$vchdate)).',2)')+1;
  
//     $columnnames=array(
//                     array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d',$vchdate)),
//                     array('field'=>'DueDate', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d',$vchdate)),
//                     array('field'=>'CVNo','type'=>'hidden','size'=>00,'value'=>$vchno),
//                     array('field'=>'PaymentMode','type'=>'text','size'=>12,'list'=>'pmlist','required'=>true),
//                     array('field'=>'CheckNo','type'=>'text','size'=>10,'required'=>true),
//                     array('field'=>'DateofCheck', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d',$vchdate)),
//                     array('field'=>'Payee','type'=>'text','size'=>10,'required'=>true,'list'=>'suppliers'),
//                     array('field'=>'CreditAccount','caption'=>'Bank','type'=>'text','size'=>10,'required'=>true,'list'=>'accounts'),
//                     array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false));
    
//     $action='praddmain.php?w='.$w;
	
//     $listcondition='';
//     $whichotherlist='acctg';
//     $otherlist=array('accounts');
//     $liststoshow=array('suppliers');
// 	$fieldsinrow='5';
//      include('../backendphp/layout/inputmainform.php');
//      break;

// case 'JV':
//    if (!allowedToOpen(5921,'1rtc')) { echo 'No permission'; exit;} 

//    header('Location: formjv.php?w=AddMain'); exit;
   
//    $title='Add Journal Voucher'; $adjdate=strtotime((date('Y')==$currentyr?"today":''.$currentyr.'-01-01'));
//    include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
//    $jvno=lastNum('JVNo','acctg_2jvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*10000+1000000)+1;
   
    
//     $columnnames=array(
//                     array('field'=>'JVDate', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d',$adjdate)),
//                     array('field'=>'JVNo','type'=>'hidden','size'=>00,'value'=>$jvno),
//                     array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false));
    
//     $action='praddmain.php?w='.$whichqry;
//     $liststoshow=array(); 
//      include('../backendphp/layout/inputmainform.php');
 //    break;

     }
  $link=null; $stmt=null;
    ?>
