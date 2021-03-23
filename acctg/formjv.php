<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true; include_once('../switchboard/contents.php');
$method='POST';
  
if (!allowedToOpen(5921,'1rtc')) { echo 'No permission'; exit;} 

?><br><div id="section" style="display: block;"><?php
$file=basename($_SERVER['SCRIPT_FILENAME']);
$w=(!isset($_GET['w'])?'List':$_GET['w']);
$txnid='JVNo'; $txnidname='JVNo'; $form='JV';
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

echo comboBox($link,'SELECT AccountID, ShortAcctID FROM `acctg_1chartofaccounts` ORDER BY ShortAcctID;','AccountID','ShortAcctID','accounts');
$columnnamesmain=array('JVDate',$txnidname,'Remarks','Posted');
$columnstoaddmain=array_diff($columnnamesmain,array($txnidname,'Posted'));
$showforex=!isset($showforex)?0:$showforex;
$columnsub=array('Date','Particulars','DebitAccount','CreditAccount','Amount');

// not sure if this is to be used
if($showforex==1){
		$forex = array('Forex');
		array_splice( $columnsub, 3, 0, $forex );
		$TotalAmount = array('TotalAmount');
		array_splice( $columnsub, 5, 0, $TotalAmount );
	 }
	 
$columnstoaddsub=array('Particulars');

if (isset($_GET[$txnidname])){
   $txnid=intval($_GET[$txnidname]); 
   $sqlmain='Select m.*, e.Nickname AS EncodedBy FROM `acctg_2adjustmain` m 
	       JOIN `1employees` e ON e.IDNo=m.EncodedByNo WHERE m.JVNo='.$txnid;
   $sqlsub='Select s.*, ca.ShortAcctID AS DebitAccount, ca1.ShortAcctID AS CreditAccount, FORMAT(Amount,2) AS Amount, FORMAT(Amount*Forex,2) AS TotalAmount, e.Nickname AS EncodedBy
	       FROM `acctg_2adjustmain` m JOIN `acctg_2adjustsub` s ON m.JVNo=s.JVNo JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=s.DebitAccountID
	       JOIN `acctg_1chartofaccounts` ca1 ON ca1.AccountID=s.CreditAccountID LEFT JOIN `1employees` e ON e.IDNo=s.EncodedByNo
	       WHERE m.JVNo='.$txnid;
}

if (in_array($w,array('Add',$form,$form.'MainEdit','EditMain'))){
        $table='acctg_2'.strtolower($form).'main'; $subtable='acctg_2'.strtolower($form).'sub'; 
}

if (in_array($w,array($form,$form.'MainEdit','EditMain','AddSub','EditSub'))){
        include_once('../backendphp/functions/editok.php');
}

if (in_array($w,array('EditMain'))){
        include_once 'trailacctg.php';
}

switch ($w){
   case 'List':
	if (!allowedToOpen(592,'1rtc')) { echo 'No permission'; exit;}
        $title='Journal Vouchers';
        include_once 'acctglayout/txnslistheader.php';
$columnnames=array('JVDate','JVNo','Remarks','Total','Posted');  
$sql='select m.JVNo, m.JVDate, m.Remarks, m.Posted, format(sum(s.Amount),2) as Total from acctg_2jvmain as m join acctg_2jvsub s on m.JVNo=s.JVNo where '.str_replace('Date','JVDate',$txndate) .' group by m.JVNo  
union select m.JVNo, m.JVDate, m.Remarks, m.Posted, 0 as Total from acctg_2jvmain as m left join acctg_2jvsub s on m.JVNo=s.JVNo where s.JVNo is null and '. str_replace('Date','JVDate',$txndate) .' order by JVDate, JVNo';

$editprocess=$file.'?w='.$form.'&'.$txnidname.'=';
$editprocesslabel='Lookup';
$opennewtab=true;
include_once('../backendphp/layout/displayastable.php');
          
        break;


  case 'AddMain':
        if (!allowedToOpen(5921,'1rtc')) { echo 'No permission'; exit;} 
   
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
        if (!allowedToOpen(5921,'1rtc')) { echo 'No permission'; exit;}
	
        
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
	// echo $sql; break;
        $stmt=$link->prepare($sql);
	$stmt->execute();	

	header('Location:form'.strtolower($form).'.php?w=JV&'.$txnidname.'='.$_POST[$txnidname]);
        break;    
    
   case $form:
        if (!allowedToOpen(592,'1rtc')) { echo 'No permission'; exit;}
        
        include_once('../backendphp/layout/showencodedbybutton.php');
        $listcondition=' WHERE AccountID IN (SELECT AccountID FROM `acctg_1begbal` WHERE BranchNo='.$_SESSION['bnum'].') ';
        $txnid=intval($_REQUEST[$txnidname]);
           $title='Add/Edit '.$form; $coltototal='Amount'; $amttoedit='Amount';
        
        
            $sqlmain='SELECT m.*, e.Nickname as EncodedBy FROM `'.$table.'` m left join `1employees` as e on e.IDNo=m.EncodedByNo WHERE m.JVNo='.$txnid;
            $stmt=$link->query($sqlmain);    $result=$stmt->fetch();
           
            $columnsub=array('Date','Particulars','Branch','FromBudgetOf','DebitAccount','CreditAccount',$amttoedit,'Forex','PHPAmount');
            if ($w=='Forex'){array_push($columnsub,'Forex',$coltototal);}
            $columnnamesmain=array('JVDate',$txnidname,'Remarks','Posted');
            if ($showenc==1) {
              array_push($columnnamesmain,'EncodedBy','TimeStamp','PostedByNo');
              array_push($columnsub,'EncodedBy','TimeStamp');
              } else { $columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}
            
            
            if ($result['Posted']==0){
                 $columnstoedit=array('Date','Particulars','Branch','FromBudgetOf','DebitAccount','CreditAccount',$amttoedit,'Forex');
                 
                $left='65%'; $leftmargin='69%'; $right='30%'; 
                $topmargin='10%';
            } else {
            
            $columnstoedit=array(); 
            $left='65%'; $leftmargin='69%'; $right='30%'; $topmargin='0%';
            }
            
            
            $main='';
                
            if (editOk($table,$txnid,$link,$w) and allowedToOpen(5921,'1rtc')){
                $editmain='<td><a href="form'.strtolower($form).'.php?w='.$form.'MainEdit&edit=2&'.$txnidname.'='.$txnid.'">Edit</a>'.str_repeat('&nbsp',8).'<a href=..\backendphp\functions\delrecords.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w='.$table.'&l=acctg OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
                $editok=true; $editsub=true;
            } else {
                $editmain=''; $editok=false; $editsub=false; 
            }
            
            $colno=0;
            foreach ($columnnamesmain as $rowmain){
                $colno=$colno+1;
                $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%4==0?'</tr><tr>':'');
            }
            $main='<table><tr>'.$main.$editmain.'</tr></table>';
            $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:'s.TimeStamp'); 
            $sqlsub='Select s.*, (Amount*Forex) AS PHPVal, FORMAT(Amount*Forex,2) AS PHPAmount, b.Branch, ca.ShortAcctID as DebitAccount, ca1.ShortAcctID as CreditAccount, e.Nickname as EncodedBy,Entity as FromBudgetOf from '.$subtable.' s join acctg_1chartofaccounts ca on ca.AccountID=s.DebitAccountID join acctg_1chartofaccounts ca1 on ca1.AccountID=s.CreditAccountID left join `1employees` as e on s.EncodedByNo=e.IDNo LEFT JOIN `acctg_1budgetentities` be on be.EntityID=s.FromBudgetOf
            join `1branches` b on b.BranchNo=s.BranchNo join '.$table.' m on m.JVNo=s.JVNo
            WHERE m.JVNo='.$txnid.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');
            
            $sqlsum='SELECT sum('.$coltototal.') as Total FROM  `'.$subtable.'` s JOIN `'.$table.'` m ON m.JVNo=s.JVNo WHERE m.JVNo='.$txnid;
            $stmt=$link->query($sqlsum); $result=$stmt->fetch();
            $addlinfo='Total:  '.number_format($result['Total'],2).str_repeat('&nbsp',10).'<a href="form'.strtolower($form).'.php?w=AddMain">Add '. $w.'</a>'.'<br><br>';
        
            
            $columnnames=array(
                             array('field'=>'Date', 'type'=>'date','size'=>20,'required'=>true, 'autofocus'=>true ),
                             array('field'=>'Particulars', 'type'=>'text','size'=>20,'required'=>false ),
                            array('field'=>'Branch', 'type'=>'text','size'=>15,'required'=>true,'list'=>'branchnames', 'value'=>$_SESSION['@brn']),
                                                array('field'=>'FromBudgetOf', 'type'=>'text','size'=>15,'required'=>true,'list'=>'entities','value'=>$_SESSION['@brn']),
                             array('field'=>'DebitAccount', 'type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
                             array('field'=>'CreditAccount', 'type'=>'text','size'=>15,'required'=>true,'list'=>'accounts'),
                             array('field'=>'Amount', 'type'=>'text','size'=>15,'required'=>true),
                             array('field'=>'Forex', 'type'=>'text','size'=>7,'value'=>1,'required'=>true),
                            array('field'=>$txnidname, 'type'=>'hidden', 'size'=>0,'value'=>$txnid)
                            );
           
            $action='praddsub.php?w='.$w.'SubAdd&JVNo='.$txnid;
            $liststoshow=array('branchnames','companies');
            $whichotherlist='acctg'; $otherlist=array('accounts');
            // info for posting: $table has been defined
            $post='1';
              $fieldsinrow=2;
            $editprocesslabel='Enter'; $editprocess='preditsupplyside.php?w='.$w.'SubEdit&JVNo='.$txnid.'&TxnSubId=';$txnsubid='TxnSubId';
            $delprocess='..\backendphp\functions\delrecordssub.php?TxnID='.$txnid.'&w='.$subtable.'&l=acctg'.'&TxnSubId=';
            $withsub=true; 
            include('../backendphp/layout/inputsubform.php');
            // to show totals
            $colamt=$coltototal;
            unset($textfordisplay,$sql,$columnnames,$editprocess,$delprocess,$coltototal,$addlprocess,$addlprocesslabel,$sortfield);
            
            $sql='SELECT FORMAT(SUM(`'.$colamt.'`),2) AS Total, Branch FROM '.$subtable.' s join `1branches` b on b.BranchNo=s.BranchNo WHERE s.JVNo='.$txnid.' GROUP BY s.BranchNo ORDER BY Branch';
            $subtitle='<br/><br/>Totals Per Branch'; $columnnames=array('Branch','Total'); $width='40%';
            echo '<div id="right">';
            include('../backendphp/layout/displayastableonlynoheaders.php');
            $sql0='CREATE TEMPORARY TABLE AdjTotal AS 
        SELECT DebitAccountID AS AccountID, TRUNCATE(SUM(Amount),2) AS Amount FROM acctg_2jvsub s WHERE JVNo='.$txnid.' GROUP BY DebitAccountID
        UNION ALL
        SELECT CreditAccountID AS AccountID, TRUNCATE(SUM(Amount)*-1,2) AS Amount FROM acctg_2jvsub s WHERE JVNo='.$txnid.' GROUP BY CreditAccountID';
            $stmt=$link->prepare($sql0); $stmt->execute();
            $sql='SELECT FORMAT(SUM(`Amount`),2) AS NetDRLessCR, ShortAcctID AS Account FROM AdjTotal s join `acctg_1chartofaccounts` ca on ca.AccountID=s.AccountID  GROUP BY s.AccountID ORDER BY Account';
            $subtitle='Totals Per Account'; $columnnames=array('Account','NetDRLessCR'); $width='40%';
            include('../backendphp/layout/displayastableonlynoheaders.php');
            echo '</div id="right">'; 
	 break;
   
   case $form.'MainEdit':
        $txnid=intval($_REQUEST[$txnidname]);
        $title='Add/Edit '.$form;  
        // edit rendersubform to allow no processblank...
        $processblank=''; $processlabelblank='';
        $columnnames=array('JVDate',$txnidname,'Remarks');
        $columnstoedit=$columnnames;
                
        $columnslist=array();
        $listsname=array();
        $liststoshow=array();
        $method='POST';
        $action=$file.'?w=EditMain&JVNo='.$txnid;

        $sql='Select m.* from `'.$table.'` m where JVNo='.$txnid;

        include('../backendphp/layout/rendersubform.php');
	 break;
      
   case 'EditMain':
       require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if (!allowedToOpen(5921,'1rtc')) { echo 'No permission'; exit;}
	$txnid=intval($_GET[$txnidname]);
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
	//echo $sql; exit();
        $stmt=$link->prepare($sql);	$stmt->execute();
	} 
	header('Location:form'.strtolower($form).'.php?w='.$form.'&'.$txnidname.'='.$_POST[$txnidname]);
        break;
   
   case 'AddSub':
       if (allowedToOpen(12,$co)){
        $path=$_SERVER['DOCUMENT_ROOT']; require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$acctiddr=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',addslashes($_POST['DebitAccount']),'AccountID');
	$acctidcr=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',addslashes($_POST['CreditAccount']),'AccountID');
        $sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `acctg_2adjustsub` SET JVNo='.$_GET[$txnidname].', EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' Forex="'.$_POST['Forex'].'",Amount='.(!is_numeric($_POST['Amount'])?str_replace(',', '',$_POST['Amount']):$_POST['Amount']).', DebitAccountID='.$acctiddr.',
       CreditAccountID='.$acctidcr.', TimeStamp=Now()'; 
	   // echo $sql; exit();
	   $stmt=$link->prepare($sql); $stmt->execute();
	   
	   }
        header("Location:adjust.php?w=Adjust&JVNo=".$_GET[$txnidname]);
        break;
      
   case 'DeleteSub':
       if (allowedToOpen(12,$co)){
        $path=$_SERVER['DOCUMENT_ROOT']; require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='DELETE FROM `acctg_2adjustsub` WHERE TxnSubId='.$_REQUEST['TxnSubId'];
       $stmt=$link->prepare($sql); $stmt->execute();}
        header("Location:adjust.php?w=Adjust&JVNo=".$_REQUEST[$txnidname]);
        break;
   
   case 'EditAdjustSub':
	 $title='Edit Adjustment Details';
	 $txnid=intval($_GET[$txnidname]); $txnsubid=intval($_GET['TxnSubId']); $main='acctg_2adjustmain';
	 $sql=$sqlsub.' AND TxnSubId='.$txnsubid; $columnsub = array_diff($columnsub,array('TotalAmount')); $columnnames=$columnsub; 
         if (allowedToOpen(12,$co)){
         $columnstoedit=$columnsub;
	 $columnswithlists=array('DebitAccount','CreditAccount'); $listsname=array('CreditAccount'=>'accounts','DebitAccount'=>'accounts');
         $editprocess='adjust.php?w=EditSub&JVNo='.$txnid.'&TxnSubId='.$txnsubid;}
	 include('../backend/layout/editspecifics.php');
	 break;
      
   case 'EditSub':
       if (allowedToOpen(12,$co)){
        $path=$_SERVER['DOCUMENT_ROOT']; require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$acctiddr=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',addslashes($_POST['DebitAccount']),'AccountID');
                $acctidcr=comboBoxValue($link,'`acctg_1chartofaccounts`','ShortAcctID',addslashes($_POST['CreditAccount']),'AccountID');
		$sql='';
        foreach ($columnstoaddsub as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='UPDATE `acctg_2adjustsub` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' Forex="'.$_POST['Forex'].'", Amount='.(!is_numeric($_POST['Amount'])?str_replace(',', '',$_POST['Amount']):$_POST['Amount']).', DebitAccountID='.$acctiddr.', CreditAccountID='.$acctidcr.', TimeStamp=Now() WHERE TxnSubId='.$_GET['TxnSubId']; 
       $stmt=$link->prepare($sql); $stmt->execute();}
        header("Location:adjust.php?w=Adjust&JVNo=".$_GET[$txnidname]);
        break;

}
$link=null; $linkacctg=null;
?>
</div> <!-- end section -->
</body></html>