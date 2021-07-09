<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true; include_once('../switchboard/contents.php');
$method='POST';
// permissions
$lookupallow=598;
$addallow=5962;
$editallow=5962;
$delallow=20007;
$unpost=406;
$co='1rtc';
  
if (!allowedToOpen($lookupallow,'1rtc')) { echo 'No permission'; exit;} 

?><br><div id="section" style="display: block;"><?php
$file=basename($_SERVER['SCRIPT_FILENAME']);
$w=(!isset($_GET['w'])?'List':$_GET['w']);
$txnidname='TxnID'; $form='Purchase'; $postfield='Posted';
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$table='acctg_2purchasemain'; $subtable='acctg_2purchasesub'; 

if (isset($_GET[$txnidname])){
   $txnid=intval($_GET[$txnidname]); 
   $columnnamesmain=array('Supplier','SupplierInv','Branch','Date','DateofInv','MRRNo','Terms','CreditAccount','Remarks','RCompany','Posted');
   $columnsub=array('DebitAccount','FromBudgetOf','Amount','Forex','PHPAmount');     
   $sqlmain='SELECT m.*,c.Company as RCompany,  s.SupplierName AS Supplier, ca.ShortAcctID as CreditAccount, b.Branch as Branch, CONCAT(e.Nickname," ",e.SurName) AS EncodedBy, CONCAT(e1.Nickname," ",e1.SurName) AS PostedBy FROM `'.$table.'` m 
JOIN `1branches` b ON b.BranchNo=m.BranchNo
JOIN `1suppliers` s ON s.SupplierNo=m.SupplierNo
JOIN acctg_1chartofaccounts ca ON ca.AccountID=m.CreditAccountID
LEFT JOIN `1employees` e ON e.IDNo=m.EncodedByNo LEFT JOIN `1employees` e1 ON e1.IDNo=m.PostedByNo 
LEFT JOIN `1companies` c ON c.CompanyNo=m.RCompany
WHERE m.TxnID='.$txnid;
 
   $sqlsub='Select s.*, (Amount*Forex) AS PHPVal, FORMAT(Amount*Forex,2) AS PHPAmount,ca.ShortAcctID as DebitAccount, e.Nickname as EncodedBy,Entity as FromBudgetOf from '.$subtable.' s JOIN acctg_1chartofaccounts ca ON ca.AccountID=s.DebitAccountID  LEFT JOIN `1employees` e ON e.IDNo=s.EncodedByNo LEFT JOIN `acctg_1budgetentities` be ON be.EntityID=s.FromBudgetOf
               JOIN '.$table.' m ON m.TxnID=s.TxnID
               WHERE m.TxnID='.$txnid.' ';
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

	$columnstoadd=array('Date','SupplierInv','DateofInv','MRRNo','Remarks');
        $acctid=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',addslashes($_POST['CreditAccount']),'AccountID');
        $branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['Branch']),'BranchNo');
        $suppno=comboBoxValue($link,'`1suppliers`','SupplierName',addslashes($_POST['Supplier']),'SupplierNo');
	$terms=comboBoxValue($link,'`1suppliers`','SupplierName',addslashes($_POST['Supplier']),'Terms');
        
        if (!isset($_POST['RCompany']) or empty($_POST['RCompany'])){$co='';}
        else{$co='RCompany='.comboBoxValue ($link,'`1companies`','Company',addslashes($_POST['RCompany']),'CompanyNo').', ';}
        
        $sql='';
        foreach ($columnstoadd as $field) {$sql.=' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql.=' SupplierNo='.$suppno.',  Terms='.$terms.', BranchNo='.$branchno.', CreditAccountID='.$acctid.', '.$co.' EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now(),  PostedByNo='.$_SESSION['(ak0)'].'';
        
}

if (in_array($w,array('AddSub','EditSub'))){
        $columnstoaddsub=array('Forex');
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        
	$acctid=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',addslashes($_POST['DebitAccount']),'AccountID');
	$budgetof=comboBoxValue($link,'`acctg_1budgetentities`','Entity',addslashes($_POST['FromBudgetOf']),'EntityID');
        
        $sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql.='FromBudgetOf='.$budgetof.', Amount='.(!is_numeric($_POST['Amount'])?str_replace(',', '',$_POST['Amount']):$_POST['Amount']).', DebitAccountID='.$acctid.',  EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';

}

if (in_array($w,array($form,$form.'MainEdit','Edit'.$form.'Sub'))){
        echo comboBox($link,'SELECT AccountID, ShortAcctID FROM `acctg_1chartofaccounts` ORDER BY ShortAcctID;','AccountID','ShortAcctID','accounts');
        echo comboBox($link,'SELECT PaymentModeID,PaymentMode FROM `acctg_0paymentmodes` ORDER BY PaymentModeID;','PaymentModeID','PaymentMode','pmlist');
        echo comboBox($link,'SELECT BranchNo, Branch FROM `1branches` ORDER BY Branch','BranchNo','Branch','branches');
        echo comboBox($link,'SELECT CompanyNo, Company FROM `1companies` ORDER BY Company','CompanyNo','Company','companynames');
        echo comboBox($link,'SELECT EntityID, Entity FROM `acctg_1budgetentities` ORDER BY Entity','EntityID','Entity','entities');
	 
}

if (in_array($w,array('Edit'.$form.'Sub','EditSub'))){
        $txnsubid=intval($_GET['TxnSubId']); 
}

switch ($w){
   case 'List':
	if (!allowedToOpen($lookupallow,'1rtc')) { echo 'No permission'; exit;}


        $title='Purchase';
        include_once 'acctglayout/txnslistheader.php';

        
       
        $sql='SELECT m.TxnID, m.SupplierNo, s.SupplierName AS Supplier, SupplierInv AS InvNo, Branch, m.`Date`, DateofInv AS InvDate, ForPONo, m.MRRNo, m.Terms, m.Remarks, Company, SUM(Amount) AS Amount, IF(m.Posted<>1,"Unposted","") AS Posted FROM acctg_2purchasemain m 
        JOIN acctg_2purchasesub ps ON m.TxnID=ps.TxnID JOIN 1suppliers s ON s.SupplierNo=m.SupplierNo
        LEFT JOIN 1companies c ON c.CompanyNo=m.RCompany JOIN 1branches b ON b.BranchNo=m.BranchNo
        JOIN acctg_1chartofaccounts ca ON ca.AccountID=m.CreditAccountID 
        LEFT JOIN invty_2mrr mm ON mm.MRRNo=m.MRRNo AND mm.BranchNo=m.BranchNo WHERE '.$txndate.' GROUP BY m.TxnID';
        
        $editprocess=$file.'?w='.$form.'&'.$txnidname.'=';
        $editprocesslabel='Lookup';
        $opennewtab=true;
        

        $columnnames=array('Date', 'Supplier', 'InvNo', 'InvDate', 'Amount', 'MRRNo', 'ForPONo', 'Company', 'Remarks', 'Posted'); 

        $width='95%';
        include_once('../backendphp/layout/displayastable.php');
          
        break;


  case 'AddMain':
        if (!allowedToOpen($addallow,'1rtc')) { echo 'No permission'; exit;} 
   
        $title='Add Purchase'; 
        
        $columnnames=array(
                array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                array('field'=>'Supplier','type'=>'text','size'=>10,'required'=>true,'list'=>'suppliers'),
                array('field'=>'SupplierInv','caption'=>'Invoice Number','type'=>'text','size'=>10,'required'=>true),
                array('field'=>'DateofInv', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d')),
                array('field'=>'MRRNo','type'=>'text','size'=>10,'required'=>true),
                array('field'=>'CreditAccount','type'=>'text','size'=>10,'required'=>true,'list'=>'accounts','value'=>'APTRADE'),
                array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false),
                array('field'=>'Branch', 'type'=>'text','size'=>10,'required'=>true,'list'=>'branches', 'value'=>$_SESSION['@brn']),
                array('field'=>'RCompany', 'type'=>'text','size'=>10, 'required'=>false,'list'=>'companynames'));
    
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

        $sql='Select TxnID from `'.$table.'` where SupplierInv=\''.$_POST['SupplierInv'].'\' and SupplierNo='.$suppno;
	$stmt=$link->query($sql);
	$result=$stmt->fetch();

	header('Location:formpurch.php?w=Purchase&'.$txnidname.'='.$result[$txnidname]);
        break;    
    
   case $form:
        if (!allowedToOpen($lookupallow,'1rtc')) { echo 'No permission'; exit;}
        
        include_once('../backendphp/layout/showencodedbybutton.php');
        
        $title='Add/Edit '.$form; $coltototal='Amount';         
        
            $columnstoeditmain=array('Date','Particulars','Branch','FromBudgetOf','DebitAccount','CreditAccount','Amount','Forex');
           
            if ($showenc==1) {
              array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedBy');
              array_push($columnsub,'EncodedBy','TimeStamp');
              } 
            
            
            
            $editprocessmainlabel='Edit'; $editprocessmain='formpurch.php?w='.$form.'MainEdit&edit=2&'.$txnidname.'='.$txnid;
            $delprocessmain='..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w='.$table.'&l=acctg';
            
            $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'s.TimeStamp'); 
            $sqlsub.=' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
            
            $sqlsum='SELECT sum('.$coltototal.') as Total, SUM(CASE WHEN Forex<>1 THEN 1 ELSE 0 END) AS CountForex, SUM(Amount*Forex) AS PHPTotal FROM  `'.$subtable.'` s JOIN `'.$table.'` m ON m.TxnID=s.TxnID WHERE m.TxnID='.$txnid;
            $stmt=$link->query($sqlsum); $result=$stmt->fetch();
            $addlinfo='Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10);
            if ($result['CountForex']<>0) { $addlinfo.='PHP Total:  '.number_format($result['PHPTotal'],2).str_repeat('&nbsp',10);}
            $addlinfo.='<a href="formpurch.php?w=AddMain">Add '. $w.'</a>'.'<br><br>';
        

            $columnnames=array(
                array('field'=>'DebitAccount','type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
                array('field'=>'FromBudgetOf','type'=>'text','size'=>15,'value'=>$_SESSION['@brn'],'required'=>true,'list'=>'entities'),
                array('field'=>'Amount','type'=>'text','size'=>15,'required'=>true),
                array('field'=>'Forex', 'type'=>'text','size'=>5,'value'=>1,'required'=>true),
                array('field'=>$txnidname, 'type'=>'hidden', 'size'=>0,'value'=>$txnid));
           
            $addsub='formpurch.php?w=AddSub&'.$txnidname.'='.$txnid;
            
            // info for posting: $table has been defined
            $postvalue='1';
              $fieldsinrow=6;
            $editprocesssublabel='Edit'; $editprocesssub='formpurch.php?w=Edit'.$form.'Sub&'.$txnidname.'='.$txnid.'&TxnSubId=';
            
            $delprocesssub='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&w='.$subtable.'&l=acctg'.'&TxnSubId=';
    
            include('../backendphp/layout/mainandsubform.php');
    
	 break;
   
   case $form.'MainEdit':
        
        $title='Add/Edit '.$form;  
        // edit rendersubform to allow no processblank...
        $processblank=''; $processlabelblank='';
      
        $columnnames=$columnnamesmain;
        $columnstoedit=array_diff($columnnamesmain,array('Posted'));
                
        $columnslist=array('Supplier','CreditAccount','Branch','RCompany');
        $listsname=array('CreditAccount'=>'accounts','Supplier'=>'suppliers','Branch'=>'branches','RCompany'=>'companynames');
        $liststoshow=array('suppliers');
        $method='POST';
        $action=$file.'?w=EditMain&'.$txnidname.'='.$txnid;

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
	header('Location:formpurch.php?w='.$form.'&'.$txnidname.'='.$txnid);
        break;
   
   case 'AddSub':
       if (allowedToOpen($addallow,$co)){
           $sql='INSERT INTO `'.$subtable.'` SET TxnID='.$_GET[$txnidname].', '.$sql; 
	   $stmt=$link->prepare($sql); $stmt->execute();	   
	   }
        header('Location:formpurch.php?w='.$form.'&'.$txnidname.'='.$_GET[$txnidname]);
        break;
   
   case 'Edit'.$form.'Sub':
	 $title='Edit '.$form.' Details';
	 $main=$table;
	 $sql=$sqlsub.' AND TxnSubId='.$txnsubid; $columnsub = array_diff($columnsub,array('PHPAmount')); $columnnames=$columnsub; 
         if (allowedToOpen($editallow,$co)){
         $columnstoedit=$columnsub;
         $columnswithlists=array('DebitAccount','FromBudgetOf'); 
         $listsname=array('DebitAccount'=>'accounts','Branch'=>'branches','FromBudgetOf'=>'entities');
         $editprocess='formpurch.php?w=EditSub&'.$txnidname.'='.$txnid.'&TxnSubId='.$txnsubid;}
	 include('../backendphp/layout/editspecifics.php');
	 break;
      
   case 'EditSub':
       if (allowedToOpen($editallow,$co)){
        recordtrail($txnsubid,$subtable,$link,0);

        $sql='UPDATE `'.$subtable.'` SET '.$sql.' WHERE TxnSubId='.$txnsubid; 
        if($_SESSION['(ak0)']==1002){ echo $sql;}
       $stmt=$link->prepare($sql); $stmt->execute();}
        header('Location:formpurch.php?w='.$form.'&'.$txnidname.'='.$_GET[$txnidname]);
        break;

}
$link=null; $linkacctg=null;
?>
</div> <!-- end section -->
</body></html>