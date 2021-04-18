<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true; include_once('../switchboard/contents.php');
$method='POST';
// permissions
$lookupallow=592;
$addallow=5921;
$editallow=5921;
$delallow=20009;
$unpost=405;
$co='1rtc';
  
if (!allowedToOpen($lookupallow,'1rtc')) { echo 'No permission'; exit;} 

?><br><div id="section" style="display: block;"><?php
$file=basename($_SERVER['SCRIPT_FILENAME']);
$w=(!isset($_GET['w'])?'List':$_GET['w']);
$txnid='JVNo'; $txnidname='JVNo'; $form='JV'; $postfield='Posted';
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';


$columnnamesmain=array('JVDate',$txnidname,'Remarks','Posted');
$columnstoaddmain=array_diff($columnnamesmain,array($txnidname,'Posted'));
//$showforex=!isset($showforex)?0:$showforex;
$columnsub=array('Date','Particulars','Branch','FromBudgetOf','DebitAccount','CreditAccount','Amount','Forex','PHPAmount');
$table='acctg_2jvmain'; $subtable='acctg_2jvsub'; 

if (isset($_GET[$txnidname])){
   $txnid=intval($_GET[$txnidname]); 
   $sqlmain='Select m.*, e.Nickname AS EncodedBy FROM `'.$table.'` m 
	       JOIN `1employees` e ON e.IDNo=m.EncodedByNo WHERE m.JVNo='.$txnid;
   $sqlsub='Select s.*, (Amount*Forex) AS PHPVal, FORMAT(Amount*Forex,2) AS PHPAmount, b.Branch, ca.ShortAcctID as DebitAccount, ca1.ShortAcctID as CreditAccount, e.Nickname as EncodedBy,Entity as FromBudgetOf from '.$subtable.' s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID join acctg_1chartofaccounts ca1 on ca1.AccountID=s.CreditAccountID LEFT JOIN `1employees` e ON e.IDNo=s.EncodedByNo LEFT JOIN `acctg_1budgetentities` be on be.EntityID=s.FromBudgetOf
               join `1branches` b on b.BranchNo=s.BranchNo join '.$table.' m on m.JVNo=s.JVNo
               WHERE m.JVNo='.$txnid.' ';
}


if (in_array($w,array($form,$form.'MainEdit','EditMain','AddSub','EditSub'))){
        include_once('../backendphp/functions/editok.php');
}

if (in_array($w,array('EditMain','EditSub'))){
        include_once 'trailacctg.php';
}

if (in_array($w,array('AddSub','EditSub'))){
        $columnstoaddsub=array('Date','Particulars','Forex');
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$acctiddr=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',addslashes($_POST['DebitAccount']),'AccountID');
	$acctidcr=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',addslashes($_POST['CreditAccount']),'AccountID');
        $budgetof=comboBoxValue($link,'`acctg_1budgetentities`','Entity',addslashes($_POST['FromBudgetOf']),'EntityID');
        $branchno=comboBoxValue($link,'`1branches`','Branch',addslashes($_POST['Branch']),'BranchNo');
        $sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql.=' BranchNo='.$branchno.', FromBudgetOf='.$budgetof.', Amount='.(!is_numeric($_POST['Amount'])?str_replace(',', '',$_POST['Amount']):$_POST['Amount']).', DebitAccountID='.$acctiddr.', CreditAccountID='.$acctidcr.', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';

}

if (in_array($w,array($form,'Edit'.$form.'Sub'))){
        echo comboBox($link,'SELECT AccountID, ShortAcctID FROM `acctg_1chartofaccounts` ORDER BY ShortAcctID;','AccountID','ShortAcctID','accounts');
        echo comboBox($link,'SELECT BranchNo, Branch FROM `1branches` ORDER BY Branch','BranchNo','Branch','branches');
        echo comboBox($link,'SELECT EntityID, Entity FROM `acctg_1budgetentities` ORDER BY Entity','EntityID','Entity','entities');
	 
}

if (in_array($w,array('Edit'.$form.'Sub','EditSub'))){
        $txnsubid=intval($_GET['TxnSubId']); 
}

switch ($w){
   case 'List':
	if (!allowedToOpen($lookupallow,'1rtc')) { echo 'No permission'; exit;}
        $title='Journal Vouchers';
        include_once 'acctglayout/txnslistheader.php';
$columnnames=array('JVDate','JVNo','Remarks','Total','Posted');  
$sql='SELECT m.JVNo, m.JVDate, m.Remarks, IF(m.Posted<>1,"Unposted","") AS Posted, IF(Forex<>1,Forex,"") AS Forex, FORMAT(SUM(s.Amount*s.Forex),2) AS Total FROM acctg_2jvmain m LEFT JOIN acctg_2jvsub s ON m.JVNo=s.JVNo WHERE '.str_replace('Date','JVDate',$txndate) .' GROUP BY m.JVNo ';

$editprocess=$file.'?w='.$form.'&'.$txnidname.'=';
$editprocesslabel='Lookup';
$opennewtab=true; $width='60%';
include_once('../backendphp/layout/displayastable.php');
          
        break;


  case 'AddMain':
        if (!allowedToOpen($addallow,'1rtc')) { echo 'No permission'; exit;} 
   
        $title='Add Journal Voucher'; $adjdate=strtotime((date('Y')==$currentyr?"today":''.$currentyr.'-01-01'));
        include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
        $txnid=lastNum($txnidname,$table,((date('Y',strtotime($currentyr.'-01-01')))-2000)*10000+1000000)+1;
   
    
    $columnnames=array(
                    array('field'=>'JVDate', 'type'=>'date','size'=>20,'required'=>true,'value'=>date('Y-m-d',$adjdate)),
                    array('field'=>$txnidname,'type'=>'hidden','size'=>00,'value'=>$txnid),
                    array('field'=>'Remarks', 'type'=>'text','size'=>50, 'required'=>false));
    
        $action=$file.'?w=Add';
        $liststoshow=array(); 
     include('../backendphp/layout/inputmainform.php');
     break;      
		
    case 'Add':
        if (!allowedToOpen($addallow,'1rtc')) { echo 'No permission'; exit;}
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        
	//to check if editable
	$date='JVDate';
        include('../backendphp/functions/checkeditablemainacctg.php');

	$sqlinsert='INSERT INTO `'.$table.'` SET  Posted=0, ';
	$sql='';
        $columnstoadd=array('JVDate',$txnidname,'Remarks');
	
	foreach ($columnstoadd as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	
        $stmt=$link->prepare($sql);
	$stmt->execute();	

	header('Location:formjv.php?w=JV&'.$txnidname.'='.$_POST[$txnidname]);
        break;    
    
   case $form:
        if (!allowedToOpen($lookupallow,'1rtc')) { echo 'No permission'; exit;}
        
        include_once('../backendphp/layout/showencodedbybutton.php');
        $listcondition=' WHERE AccountID IN (SELECT AccountID FROM `acctg_1begbal` WHERE BranchNo='.$_SESSION['bnum'].') ';
        
        $title='Add/Edit '.$form; $coltototal='Amount';         
        
            $sqlmain='SELECT m.*, CONCAT(e.Nickname," ",e.SurName) as EncodedBy FROM `'.$table.'` m left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE m.JVNo='.$txnid;
            $columnstoeditmain=array('Date','Particulars','Branch','FromBudgetOf','DebitAccount','CreditAccount','Amount','Forex');
           
            if ($showenc==1) {
              array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo');
              array_push($columnsub,'EncodedBy','TimeStamp');
              } 
            
            $left='60%'; $leftmargin='65%'; $right='30%'; 
            
            $editprocessmainlabel='Edit'; $editprocessmain='formjv.php?w='.$form.'MainEdit&edit=2&'.$txnidname.'='.$txnid;
            $delprocessmain='..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w='.$table.'&l=acctg';
            
            $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'s.TimeStamp'); 
            $sqlsub.=' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
            
            $sqlsum='SELECT sum('.$coltototal.') as Total, SUM(CASE WHEN Forex<>1 THEN 1 ELSE 0 END) AS CountForex, SUM(Amount*Forex) AS PHPTotal FROM  `'.$subtable.'` s JOIN `'.$table.'` m ON m.JVNo=s.JVNo WHERE m.JVNo='.$txnid;
            $stmt=$link->query($sqlsum); $result=$stmt->fetch();
            $addlinfo='Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10);
            if ($result['CountForex']<>1) { $addlinfo.='PHP Total:'.number_format($result['PHPTotal'],2).str_repeat('&nbsp',10);}
            $addlinfo.='<a href="formjv.php?w=AddMain">Add '. $w.'</a>'.'<br><br>';
        
            
            $columnnames=array(
                             array('field'=>'Date', 'type'=>'date','size'=>15,'required'=>true, 'autofocus'=>true ),
                             array('field'=>'Particulars', 'type'=>'text','size'=>20,'required'=>false ),
                             array('field'=>'Branch', 'type'=>'text','size'=>15,'required'=>true,'list'=>'branches', 'value'=>$_SESSION['@brn']),
                             array('field'=>'FromBudgetOf', 'type'=>'text','size'=>15,'required'=>true,'list'=>'entities','value'=>$_SESSION['@brn']),
                             array('field'=>'DebitAccount', 'type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
                             array('field'=>'CreditAccount', 'type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
                             array('field'=>'Amount', 'type'=>'text','size'=>10,'required'=>true),
                             array('field'=>'Forex', 'type'=>'text','size'=>5,'value'=>1,'required'=>true),
                            array('field'=>$txnidname, 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                            );
           
            $addsub='formjv.php?w=AddSub&'.$txnidname.'='.$txnid;
            $liststoshow=array();
            //$whichotherlist='acctg'; $otherlist=array('accounts');
            // info for posting: $table has been defined
            $post='1';
              $fieldsinrow=4;
            $editprocesssublabel='Edit'; $editprocesssub='formjv.php?w=Edit'.$form.'Sub&'.$txnidname.'='.$txnid.'&TxnSubId=';
            
            $delprocesssub='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&w='.$subtable.'&l=acctg'.'&TxnSubId=';

        //     $sqltotal='SELECT'
            
            include('../backendphp/layout/mainandsubform.php');
            // to show totals
          //  $colamt=$coltototal;
            unset($textfordisplay,$sql,$columnnames,$editprocess,$delprocess,$addlprocess,$addlprocesslabel,$coltototal,$sortfield);
            
            $sql='SELECT FORMAT(SUM(`Forex`*Amount),2) AS Total, Branch FROM '.$subtable.' s join `1branches` b on b.BranchNo=s.BranchNo WHERE s.JVNo='.$txnid.' GROUP BY s.BranchNo ORDER BY Branch';
            $subtitle='<br/><br/>Totals Per Branch'; $columnnames=array('Branch','Total'); $width='40%';
           // echo '<div id="right">';
            include('../backendphp/layout/displayastableonlynoheaders.php');
            $sql0='CREATE TEMPORARY TABLE AdjTotal AS 
        SELECT DebitAccountID AS AccountID, TRUNCATE(SUM(Forex*Amount),2) AS Amount FROM acctg_2jvsub s WHERE JVNo='.$txnid.' GROUP BY DebitAccountID
        UNION ALL
        SELECT CreditAccountID AS AccountID, TRUNCATE(SUM(Amount)*-1,2) AS Amount FROM acctg_2jvsub s WHERE JVNo='.$txnid.' GROUP BY CreditAccountID';
            $stmt=$link->prepare($sql0); $stmt->execute();
            $sql='SELECT FORMAT(SUM(`Amount`),2) AS NetDRLessCR, ShortAcctID AS Account FROM AdjTotal s join `acctg_1chartofaccounts` ca on ca.AccountID=s.AccountID  GROUP BY s.AccountID ORDER BY Account';
            $subtitle='Totals Per Account'; $columnnames=array('Account','NetDRLessCR'); $width='40%';
            include('../backendphp/layout/displayastableonlynoheaders.php');
          //  echo '</div id="right">'; 
	 break;
   
   case $form.'MainEdit':
        
        $title='Add/Edit '.$form;  
        // edit rendersubform to allow no processblank...
        $processblank=''; $processlabelblank='';
        $columnnames=array('JVDate',$txnidname,'Remarks');
        $columnstoedit=$columnnames;
                
        $columnslist=array();
        $listsname=array();
        $liststoshow=array();
        $method='POST';
        $action=$file.'?w=EditMain&'.$txnidname.'='.$txnid;

        $sql='Select m.* from `'.$table.'` m where '.$txnidname.'='.$txnid;

        include('../backendphp/layout/rendersubform.php');
	 break;
      
   case 'EditMain':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if (!allowedToOpen($editallow,'1rtc')) { echo 'No permission'; exit;}
	$title='Add/Edit '.$form; 
    
	//to check if editable
	if (editOk($table,$txnid,$link,$w)){
	recordtrail($txnid,$table,$link,0);
	$sqlupdate='UPDATE `'.$table.'` SET  ';
        $sql='';
        $columnstoedit=array('JVDate',$txnidname,'Remarks');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now() where Posted=0 and '.$txnidname.'='.$txnid; 
	$stmt=$link->prepare($sql);	$stmt->execute();
	} 
	header('Location:formjv.php?w='.$form.'&'.$txnidname.'='.$_POST[$txnidname]);
        break;
   
   case 'AddSub':
       if (allowedToOpen($addallow,$co)){
        
        $sql='INSERT INTO `'.$subtable.'` SET JVNo='.$_GET[$txnidname].', '.$sql; 
	   // echo $sql; exit();
	   $stmt=$link->prepare($sql); $stmt->execute();	   
	   }
        header('Location:formjv.php?w='.$form.'&'.$txnidname.'='.$_GET[$txnidname]);
        break;
   
   case 'Edit'.$form.'Sub':
	 $title='Edit Journal Voucher Details';
	 $main=$table;
	 $sql=$sqlsub.' AND TxnSubId='.$txnsubid; $columnsub = array_diff($columnsub,array('PHPAmount')); $columnnames=$columnsub; 
         if (allowedToOpen($editallow,$co)){
         $columnstoedit=$columnsub;
         $columnswithlists=array('DebitAccount','CreditAccount','FromBudgetOf','Branch'); 
         $listsname=array('CreditAccount'=>'accounts','DebitAccount'=>'accounts','Branch'=>'branches','FromBudgetOf'=>'entities');
         $editprocess='formjv.php?w=EditSub&'.$txnidname.'='.$txnid.'&TxnSubId='.$txnsubid;}
	 include('../backendphp/layout/editspecifics.php');
	 break;
      
   case 'EditSub':
       if (allowedToOpen($editallow,$co)){
        recordtrail($txnsubid,$subtable,$link,0);
        $sql='UPDATE `'.$subtable.'` SET '.$sql.' WHERE TxnSubId='.$_GET['TxnSubId']; 
       $stmt=$link->prepare($sql); $stmt->execute();}
        header('Location:formjv.php?w='.$form.'&'.$txnidname.'='.$_GET[$txnidname]);
        break;

}
$link=null; $linkacctg=null;
?>
</div> <!-- end section -->
</body></html>