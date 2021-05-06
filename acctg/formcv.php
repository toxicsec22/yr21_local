<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true; include_once('../switchboard/contents.php');
$method='POST';
// permissions
$lookupallow=598;
$addallow=5401;
$editallow=5401;
$delallow=20005;
$unpost=407;
$co='1rtc';
  
if (!allowedToOpen($lookupallow,'1rtc')) { echo 'No permission'; exit;} 

?><br><div id="section" style="display: block;"><?php
$file=basename($_SERVER['SCRIPT_FILENAME']);
$w=(!isset($_GET['w'])?'List':$_GET['w']);
$txnid='CVNo'; $txnidname='CVNo'; $form='CV'; $postfield='Posted';
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$table='acctg_2cvmain'; $subtable='acctg_2cvsub'; 

if (isset($_GET[$txnidname])){
   $txnid=intval($_GET[$txnidname]); 
   $columnnamesmain=array('Date','CVNo','DueDate','DateofCheck','PaymentMode','CheckNo','CreditAccount','PayeeNo','Payee','Remarks','ReleaseDate','CheckReceivedBy','Posted','Cleared');
   $columnsub=array('Particulars','Branch','FromBudgetOf','ForInvoiceNo','TIN','DebitAccount','Amount','Forex','PHPAmount');     
   $sqlmain='Select m.*, ca.ShortAcctID as CreditAccount, e.Nickname AS EncodedBy, PaymentMode FROM `'.$table.'` m JOIN acctg_1chartofaccounts ca ON ca.AccountID=m.CreditAccountID
                LEFT JOIN acctg_0paymentmodes pm ON m.PaymentModeID=pm.PaymentModeID
	        LEFT JOIN `1employees` e ON e.IDNo=m.EncodedByNo WHERE m.CVNo='.$txnid;
   $sqlsub='Select s.*, (Amount*Forex) AS PHPVal, FORMAT(Amount*Forex,2) AS PHPAmount, b.Branch, ca.ShortAcctID as DebitAccount, e.Nickname as EncodedBy,Entity as FromBudgetOf from '.$subtable.' s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID  LEFT JOIN `1employees` e ON e.IDNo=s.EncodedByNo LEFT JOIN `acctg_1budgetentities` be on be.EntityID=s.FromBudgetOf
               join `1branches` b on b.BranchNo=s.BranchNo join '.$table.' m on m.CVNo=s.CVNo
               WHERE m.CVNo='.$txnid.' ';
}


if (in_array($w,array($form,$form.'MainEdit','EditMain','AddSub','EditSub','CVSubAutoAdd'))){
        include_once('../backendphp/functions/editok.php');
}

if (in_array($w,array('EditMain','EditSub'))){
        include_once 'trailacctg.php';
}

if (in_array($w,array('Add','EditMain'))){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        //to check if editable
	$date='Date';
        include('../backendphp/functions/checkeditablemainacctg.php');

	$columnstoadd=array('Date','DueDate','CVNo','CheckNo','DateofCheck','Payee','Remarks');
        $acctid=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',addslashes($_POST['CreditAccount']),'AccountID');
        $payeeno=comboBoxValue($link,'`1suppliers`','SupplierName',addslashes($_POST['Payee']),'SupplierNo');
        $paytype=comboBoxValue($link,'`acctg_0paymentmodes`','PaymentMode',addslashes($_POST['PaymentMode']),'PaymentModeID');
	$sql='';
        foreach ($columnstoadd as $field) {$sql.=' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql.=' CreditAccountID='.$acctid.', PaymentModeID='.$paytype.', PayeeNo='.(empty($payeeno)?'NULL':$payeeno).', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now(),  PostedByNo='.$_SESSION['(ak0)'].'';
        
}

if (in_array($w,array('AddSub','EditSub'))){
        $columnstoaddsub=array('Particulars','Forex');
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if (isset($_POST['TIN']) AND !empty($_POST['TIN'])){$tin=' TIN=\''.str_replace("-","",$_POST['TIN']).'\', ';} else { $tin='';}
	$acctid=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',addslashes($_POST['DebitAccount']),'AccountID');
	$budgetof=comboBoxValue($link,'`acctg_1budgetentities`','Entity',addslashes($_POST['FromBudgetOf']),'EntityID');
        $branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['Branch']),'BranchNo');
        if (isset($_POST['TIN']) AND !empty($_POST['TIN'])){$tin=' TIN=\''.str_replace("-","",$_POST['TIN']).'\', ';} else { $tin='';}
        $sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql.=' BranchNo='.$branchno.', FromBudgetOf='.$budgetof.', '.$tin.' Amount='.(!is_numeric($_POST['Amount'])?str_replace(',', '',$_POST['Amount']):$_POST['Amount']).', DebitAccountID='.$acctid.',  EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';

}

if (in_array($w,array($form,$form.'MainEdit','Edit'.$form.'Sub'))){
        echo comboBox($link,'SELECT AccountID, ShortAcctID FROM `acctg_1chartofaccounts` ORDER BY ShortAcctID;','AccountID','ShortAcctID','accounts');
        echo comboBox($link,'SELECT PaymentModeID,PaymentMode FROM `acctg_0paymentmodes` ORDER BY PaymentModeID;','PaymentModeID','PaymentMode','pmlist');
        echo comboBox($link,'SELECT BranchNo, Branch FROM `1branches` ORDER BY Branch','BranchNo','Branch','branches');
        echo comboBox($link,'SELECT EntityID, Entity FROM `acctg_1budgetentities` ORDER BY Entity','EntityID','Entity','entities');
	 
}

if (in_array($w,array('Edit'.$form.'Sub','EditSub'))){
        $txnsubid=intval($_GET['TxnSubId']); 
}

switch ($w){
   case 'List':
	if (!allowedToOpen($lookupallow,'1rtc')) { echo 'No permission'; exit;}


        $title='Check Vouchers';
        include_once 'acctglayout/txnslistheader.php';

        ?>
        <p>Note: Choosing 0 for the month will show uncleared checks from previous year.</p>
        <br><br>
        <form action="printvoucher.php?w=CV" method="POST" target=_blank>
        Print FROM <input type="text" name="FromVch">  TO <input type="text" name="ToVch"> 
        <input type="Submit" name="Print" value="Print">
        </form>
        <form action="printvoucher.php?w=Check" method="POST" target=_blank>
        Print Check Number 
        <input type="text" name="CheckNo">  
        <input type="Submit" name="PrintCheck" value="Print Check (mm-dd-yyyy)">
        <input type="Submit" name="PrintCheck" value="Print Check (mm/dd/yy)">      
        </form>
        <?php

        
        if (isset($_REQUEST['Month']) and $_REQUEST['Month']==0){
                $sql='SELECT \''.$lastyr.'-12-31\' AS `Date`, `CVNo`, `DateofCheck`,
                `CheckNo`, FromAccount AS `CreditAccount`, `PayeeNo`, `Payee`, "Last Yr" AS PaymentMode,
                FORMAT(AmountofCheck,2) AS `Amount`, "From Last Yr" AS `Remarks`, ReleaseDate, CheckReceivedBy, `Cleared`
              FROM 
                `acctg_3unclearedchecksfromlastperiod` ';
             //   $columnnames=array('Date','CVNo','DateofCheck','CreditAccount','PayeeNo','Payee','Amount','Remarks','Cleared'); 
              
        } else {
        $sql='SELECT m.CVNo, PaymentMode,  m.Date, m.DateofCheck, m.CheckNo, ca.ShortAcctID as CreditAccount, m.PayeeNo, m.Payee, m.Cleared, IF(m.Posted<>1,"Unposted","") AS Posted, FORMAT(SUM(s.Amount*s.Forex),2) AS Amount, m.Remarks,ReleaseDate, CheckReceivedBy
      FROM 
        acctg_2cvmain as m 
        JOIN acctg_1chartofaccounts ca ON ca.AccountID=m.CreditAccountID 
        JOIN acctg_0paymentmodes pm ON m.PaymentModeID=pm.PaymentModeID
        LEFT JOIN acctg_2cvsub s on m.CVNo=s.CVNo WHERE '.$txndate.' GROUP BY m.CVNo  
        ORDER BY Date, m.CVNo';
        
        $editprocess=$file.'?w='.$form.'&'.$txnidname.'=';
        $editprocesslabel='Lookup';
        $opennewtab=true;
        }

        $columnnames=array('Date','CVNo','DateofCheck','PaymentMode','CheckNo','CreditAccount','PayeeNo','Payee','Amount','Remarks','ReleaseDate','CheckReceivedBy','Cleared'); 

        $width='95%';
        include_once('../backendphp/layout/displayastable.php');
          
        break;


  case 'AddMain':
        if (!allowedToOpen($addallow,'1rtc')) { echo 'No permission'; exit;} 
   
        $title='Add Check Voucher'; 
        $vchdate=strtotime((date('Y')==$currentyr?"today":''.$currentyr.'-01-01')); 
        include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
        $txnid=lastNum($txnidname,$table,((date('Y',$vchdate))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.(date('Y',$vchdate)).',2)')+1;
   
        echo comboBox($link,'SELECT PaymentModeID,PaymentMode FROM `acctg_0paymentmodes` ORDER BY PaymentModeID;','PaymentModeID','PaymentMode','pmlist');
        
        $columnnames=array(
                array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d',$vchdate)),
                array('field'=>'DueDate', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d',$vchdate)),
                array('field'=>'CVNo','type'=>'hidden','size'=>00,'value'=>$txnid),
                array('field'=>'PaymentMode','type'=>'text','size'=>12,'list'=>'pmlist','required'=>true),
                array('field'=>'CheckNo','type'=>'text','size'=>10,'required'=>true),
                array('field'=>'DateofCheck', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d',$vchdate)),
                array('field'=>'Payee','type'=>'text','size'=>30,'required'=>true,'list'=>'suppliers'),
                array('field'=>'CreditAccount','caption'=>'CreditAccount','type'=>'text','size'=>10,'required'=>true,'list'=>'accounts'),
                array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false));
    
        $action=$file.'?w=Add';
        $listcondition='';
        $whichotherlist='acctg';
        $otherlist=array('accounts');
        $liststoshow=array('suppliers');
	$fieldsinrow='5';
     include('../backendphp/layout/inputmainform.php');
     break;      
		
    case 'Add':
        if (!allowedToOpen($addallow,'1rtc')) { echo 'No permission'; exit;}
	
	$sql='INSERT INTO `'.$table.'` SET  Posted=0, '.$sql; 
	$stmt=$link->prepare($sql);
	$stmt->execute();	

	header('Location:formcv.php?w=CV&'.$txnidname.'='.$_POST[$txnidname]);
        break;    
    
   case $form:
        if (!allowedToOpen($lookupallow,'1rtc')) { echo 'No permission'; exit;}
        
        include_once('../backendphp/layout/showencodedbybutton.php');
        $listcondition=' WHERE AccountID IN (SELECT AccountID FROM `acctg_1begbal` WHERE BranchNo='.$_SESSION['bnum'].') ';
        
        $title='Add/Edit '.$form; $coltototal='Amount';         
        
            //$sqlmain='SELECT m.*, CONCAT(e.Nickname," ",e.SurName) as EncodedBy FROM `'.$table.'` m left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE m.CVNo='.$txnid;
            
            $columnstoeditmain=array('Date','Particulars','Branch','FromBudgetOf','DebitAccount','CreditAccount','Amount','Forex');
           
            if ($showenc==1) {
              array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo');
              array_push($columnsub,'EncodedBy','TimeStamp');
              } 
            
            
            
            $editprocessmainlabel='Edit'; $editprocessmain='formcv.php?w='.$form.'MainEdit&edit=2&'.$txnidname.'='.$txnid;
            $delprocessmain='..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w='.$table.'&l=acctg';
            
            $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'s.TimeStamp'); 
            $sqlsub.=' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
            
            $sqlsum='SELECT Posted,PayeeNo,CheckNo,sum('.$coltototal.') as Total, SUM(CASE WHEN Forex<>1 THEN 1 ELSE 0 END) AS CountForex, SUM(Amount*Forex) AS PHPTotal FROM  `'.$subtable.'` s JOIN `'.$table.'` m ON m.CVNo=s.CVNo WHERE m.CVNo='.$txnid;
            $stmt=$link->query($sqlsum); $result=$stmt->fetch();
            $editsub=$result['Posted']==0?true:false;
            $suppno=$result['PayeeNo'];
            $addlinfo='Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10);
            if ($result['CountForex']<>0) { $addlinfo.='PHP Total:  '.number_format($result['PHPTotal'],2).str_repeat('&nbsp',10);}
            $addlinfo.='<a href="formcv.php?w=AddMain">Add '. $w.'</a>'.'<br><br>';
        

           $columnnames=array(
                                array('field'=>'Particulars', 'type'=>'text','size'=>40,'required'=>false, 'autofocus'=>true ),
                               array('field'=>'Branch', 'type'=>'text','size'=>15,'required'=>true,'list'=>'branches', 'value'=>$_SESSION['@brn']),
                               array('field'=>'TIN', 'caption'=>'TIN (numbers only)', 'type'=>'text','size'=>10),
                                array('field'=>'DebitAccount', 'type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
                                array('field'=>'FromBudgetOf', 'type'=>'text','size'=>15,'required'=>true,'list'=>'entities','value'=>$_SESSION['@brn']),
                                array('field'=>'Amount', 'type'=>'text','size'=>15,'required'=>true),
                                array('field'=>'Forex', 'type'=>'text','size'=>5,'value'=>1,'required'=>true),
                               array('field'=>$txnidname, 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                               );
           
            $addsub='formcv.php?w=AddSub&'.$txnidname.'='.$txnid;
            
            // info for posting: $table has been defined
            $post='1';
              $fieldsinrow=3;
            $editprocesssublabel='Edit'; $editprocesssub='formcv.php?w=Edit'.$form.'Sub&'.$txnidname.'='.$txnid.'&TxnSubId=';
            
            $delprocesssub='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&w='.$subtable.'&l=acctg'.'&TxnSubId=';
            $postedprocess='printvoucher.php?w=CV&FromVch='.$txnid.'&ToVch='.$txnid.'">Print CV</a>&nbsp; &nbsp;<a href="printvoucher.php?w=Check&CheckNo='.$result['CheckNo'].'&CVNo='.$txnid.'"';
            $postedprocesslabel='Print Check (mm-dd-yyyy)';

            
    // start of unpaid inv list
    if(is_null($suppno)){
        //  $lookupdata='';
          goto nounpaid;
        }
        $colorcount=0;
        $rcolor[0]="FFE3E3";
        $rcolor[1]="FFFFFF";
        $sqlunpd='Select SupplierInv, concat(date_format(`Date`,\'%Y-%m-%d\')) as Details, PayBalance,format(PayBalance,2) as Amount, DateDue, b.Branch from acctg_23balperinv i join `1branches` as b on b.BranchNo= i.BranchNo where i.PayBalance<>0 and i.SupplierNo='.$suppno.' order by DateDue, SupplierInv';
    
    $stmt=$link->query($sqlunpd);
        $result=$stmt->fetchAll();
        
        if ($stmt->rowCount()>0){
        $columnsub2=array('SupplierInv','Details','Branch','DateDue','Amount');
        $lookupdata='';$subcol=''; 
        foreach ($columnsub2 as $colsub){
            $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
        } 
        foreach($result as $row){
            $lookupdata=$lookupdata.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
            foreach ($columnsub2 as $colsub){
                $lookupdata=$lookupdata.'<td>'.$row[$colsub].'</td>';
            }
            $lookupdata=$lookupdata.($editsub?'<td><a href="formcv.php?w=CVSubAutoAdd&action_token='.$_SESSION['action_token'].'&SupplierInv='.$row['SupplierInv'].'&SupplierNo='.$suppno.'&CVNo='.$txnid.'">Pay</a></td>':'').'</tr>';
            $colorcount++;
        }
        $lookupdata='<br><br>Unpaid Invoices<br><table><tr>'.$subcol.'<td>Pay?</td></tr><tbody>'.$lookupdata.'</tbody></table>';
        $left='60%'; $width='35%'; $widthoftotal=$width;
        } else { $left='70%'; $width='30%'; $widthoftotal=$width;}
        // end of unpaid inv list
        nounpaid:
        
            include('../backendphp/layout/mainandsubform.php');
           
            // to show totals
            unset($textfordisplay,$sql,$columnnames,$editprocess,$delprocess,$coltototal,$addlprocess,$addlprocesslabel,$sortfield);
            $color1='e6e8e6';
            $sql='SELECT FORMAT(SUM(Forex*Amount),2) AS Subtotal, Branch FROM '.$subtable.' s join `1branches` b on b.BranchNo=s.BranchNo WHERE s.CVNo='.$txnid.' GROUP BY s.BranchNo ORDER BY Branch';
            $subtitle='<br/><br/>Totals Per Branch'; $columnnames=array('Branch','Subtotal'); 
            echo '<div id="wrap"><div id="total">';
            include('../backendphp/layout/displayastableonlynoheaders.php');
            echo '<br><br><br></div id="total">';
            $sql='SELECT FORMAT(SUM(Forex*Amount),2) AS Subtotal, ShortAcctID AS Account FROM '.$subtable.' s join `1branches` b on b.BranchNo=s.BranchNo JOIN acctg_1chartofaccounts ca ON ca.AccountID=s.DebitAccountID WHERE s.CVNo='.$txnid.' GROUP BY s.DebitAccountID ORDER BY Account';
            //$sql='SELECT FORMAT(SUM(`Amount`),2) AS NetDRLessCR, ShortAcctID AS Account FROM Totals s join `acctg_1chartofaccounts` ca on ca.AccountID=s.AccountID  GROUP BY s.AccountID ORDER BY Account';
            $subtitle='Totals Per Account'; $columnnames=array('Account','Subtotal'); 
            echo '<div id="total">';
            include('../backendphp/layout/displayastableonlynoheaders.php');
            echo '</div id="total"></div id="wrap">'; 
	 break;
   
   case $form.'MainEdit':
        
        $title='Add/Edit '.$form;  
        // edit rendersubform to allow no processblank...
        $processblank=''; $processlabelblank='';
      
        $columnnames=$columnnamesmain;
        $columnstoedit=array_diff($columnnamesmain,array('PayeeNo','ReleaseDate','CheckReceivedBy','Posted','Cleared'));
                
        $columnslist=array('PaymentMode','CreditAccount','Payee');
        $listsname=array('PaymentMode'=>'pmlist','CreditAccount'=>'accounts','Payee'=>'suppliers');
        $liststoshow=array('suppliers');
        // $liststoshow=array();
        $method='POST';
        $action=$file.'?w=EditMain&'.$txnidname.'='.$txnid;

        //$sql='Select m.* from `'.$table.'` m where '.$txnidname.'='.$txnid;
        $sql=$sqlmain;
        include('../backendphp/layout/rendersubform.php');
	 break;
      
   case 'EditMain':
        if (!allowedToOpen($editallow,'1rtc')) { echo 'No permission'; exit;}
	$title='Add/Edit '.$form; 
    
	//to check if editable
	if (editOk($table,$txnid,$link,$w)){
	recordtrail($txnid,$table,$link,0);
	
	$sql='UPDATE `'.$table.'` SET  '.$sql.' WHERE Posted=0 and '.$txnidname.'='.$txnid; 
	$stmt=$link->prepare($sql);	$stmt->execute();
	} 
	header('Location:formcv.php?w='.$form.'&'.$txnidname.'='.$_POST[$txnidname]);
        break;
   
   case 'AddSub':
       if (allowedToOpen($addallow,$co)){
           $sql='INSERT INTO `'.$subtable.'` SET CVNo='.$_GET[$txnidname].', '.$sql; 
	   $stmt=$link->prepare($sql); $stmt->execute();	   
	   }
        header('Location:formcv.php?w='.$form.'&'.$txnidname.'='.$_GET[$txnidname]);
        break;
   
   case 'Edit'.$form.'Sub':
	 $title='Edit Check Voucher Details';
	 $main=$table;
	 $sql=$sqlsub.' AND TxnSubId='.$txnsubid; $columnsub = array_diff($columnsub,array('PHPAmount')); $columnnames=$columnsub; 
         if (allowedToOpen($editallow,$co)){
         $columnstoedit=$columnsub;
         $columnswithlists=array('DebitAccount','FromBudgetOf','Branch'); 
         $listsname=array('DebitAccount'=>'accounts','Branch'=>'branches','FromBudgetOf'=>'entities');
         $editprocess='formcv.php?w=EditSub&'.$txnidname.'='.$txnid.'&TxnSubId='.$txnsubid;}
	 include('../backendphp/layout/editspecifics.php');
	 break;
      
   case 'EditSub':
       if (allowedToOpen($editallow,$co)){
        recordtrail($txnsubid,$subtable,$link,0);

        $sql='UPDATE `'.$subtable.'` SET '.$sql.' WHERE TxnSubId='.$txnsubid; 
        if($_SESSION['(ak0)']==1002){ echo $sql;}
       $stmt=$link->prepare($sql); $stmt->execute();}
        header('Location:formcv.php?w='.$form.'&'.$txnidname.'='.$_GET[$txnidname]);
        break;

   case 'CVSubAutoAdd':
                if (!allowedToOpen($addallow,'1rtc')) { echo 'No permission'; exit;}
                //to check if editable
                if (editOk($table,$txnid,$link,$w)){
                        $sql0='Select SupplierInv, PayBalance, BranchNo, CreditAccountID from acctg_23balperinv i  where SupplierInv like \''.$_REQUEST['SupplierInv'].'\' and i.SupplierNo='.$_REQUEST['SupplierNo'];
                        $stmt=$link->query($sql0);
                        $result=$stmt->fetch();
                $sql='INSERT INTO `acctg_2cvsub` SET `CVNo`=\''.$txnid.'\', DebitAccountID='.$result['CreditAccountID'].', ForInvoiceNo=\''.$result['SupplierInv'].'\', Amount='.$result['PayBalance'].', FromBudgetOf='.$result['BranchNo'].', BranchNo='.$result['BranchNo'].', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
                // echo $sql;break;
                $stmt=$link->prepare($sql);
                $stmt->execute();	
                        } 
                        header('Location:formcv.php?w='.$form.'&'.$txnidname.'='.$txnid);
                break;

}
$link=null; $linkacctg=null;
?>
</div> <!-- end section -->
</body></html>