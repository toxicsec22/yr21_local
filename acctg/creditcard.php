<html>
<head>
<title>Credit Card Txns</title>
<?php
 
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$showbranches=true; include_once('../switchboard/contents.php');
include_once('../backendphp/layout/showencodedbybutton.php');
include_once('../backendphp/layout/regulartablestyle.php');
?>
<style>
    table,td,tr {padding: 4px;}
</style>
</head>
<body>
<?php
if (!allowedToOpen(527,'1rtc')) { echo 'No permission'; exit; }

 
include_once('../generalinfo/lists.inc');
include_once "../acctg/acctglists.inc";include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

$whichqry=!isset($_GET['w'])?'CCTransactions':$_GET['w'];
if (in_array($whichqry,array('ProcessAddTxn','ProcessEditTxn'))){
	$frombudgetof=comboBoxValue($link, 'acctg_1budgetentities', 'Entity', $_POST['FromBudgetOf'], 'EntityID');
}
if (in_array($whichqry,array('CCTransactions','EditTxns'))){
echo comboBox($link,'SELECT EntityID,Entity FROM `acctg_1budgetentities` ORDER BY Entity;','EntityID','Entity','entities');
}
    ?>
    <a href="creditcard.php?w=UsualPayees">Usual Payees</a>&nbsp &nbsp
    <a href="creditcard.php?w=CCTransactions">Credit Card Transactions</a>
    <?php
    
$ccidfilter=!isset($_REQUEST['ccid'])?(!isset($_POST['CreditCard'])?'':'&ccid='.$_POST['CreditCard']):'&ccid='.$_REQUEST['ccid'];

echo '<h4>Credit Card Txns</h4>';    
switch ($whichqry){
case 'UsualPayees':
$title='Default Transactions';
$addlmenu=renderotherlist('accounts','');
$addlmenu.=renderlist('branchnames').renderlist('companies'); $listname='branchnames';
$addlmenu.='<form method=post action="creditcard.php?w=ProcessAdd">
    Payee Details<input type=text name="PayeeDetails" autocomplete=off>
    Default Expense Account<input type=text name="Account" list="accounts" autocomplete=off>
    Default Branch<input type=text name="DefaultBranch" list="'.$listname.'" autocomplete=off>
    <input type=submit name=submit value="Submit">
</form>';

$sql='SELECT u.*, b.Branch, ca.ShortAcctID as Account FROM gen_info_5creditcardusualpayees u 
join `1branches` b on b.BranchNo=u.DefaultBranch
join acctg_1chartofaccounts ca on ca.AccountID=u.DefaultAcctID;';
$txnidname='CCExpID';
$editprocess='creditcard.php?w=EditPayees&edit=2&CCExpID=';$editprocesslabel='Edit';

    $columnnames=array('PayeeDetails','Account','Branch');
    include('../backendphp/layout/displayastablewithedit.php');

break;
case 'EditPayees':
$txnid=$_REQUEST['CCExpID'];
$columnnames=array('PayeeDetails','DefaultAcctID','Account','DefaultBranch','Branch', 'DefaultCompany');
$columnstoedit=array('PayeeDetails','Account','Branch');
	
$columnslist=array('Account','Branch');
$listsname=array('Account'=>'accounts','Branch'=>'branchnames');
$liststoshow=array('branchnames');
$listcondition='';
$whichotherlist='acctg';
$otherlist=array('accounts');
$method='POST';
$action='creditcard.php?w=ProcessEdit&edit=2&CCExpID='.$txnid;

$sql='SELECT u.*, b.Branch, ca.ShortAcctID as Account FROM gen_info_5creditcardusualpayees u 
join `1branches` b on b.BranchNo=u.DefaultBranch
join acctg_1chartofaccounts ca on ca.AccountID=u.DefaultAcctID where CCExpID='.$txnid;
$processblank='';
$processlabelblank='';
include('../backendphp/layout/rendersubform.php');
    
    break;

case 'ProcessEdit':
    $txnid=$_REQUEST['CCExpID'];
        include_once('../backendphp/functions/getnumber.php');
        $acctid=getNumber('Account',addslashes($_POST['Account']));
	$branch=getNumber('Branch',addslashes($_POST['Branch']));
	$sqlupdate='UPDATE `gen_info_5creditcardusualpayees` SET  ';
        $sql='';
        $columnstoedit=array('PayeeDetails','DefaultBranch');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' DefaultAcctID='.$acctid.', DefaultBranch='.$branch.' where CCExpID='.$txnid; 
	//echo $sql;
        
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header("Location:creditcard.php?w=UsualPayees");
    break;

case 'ProcessAdd':
include_once('../backendphp/functions/getnumber.php');
        $acctid=getNumber('Account',addslashes($_POST['Account']));
	$branch=getNumber('Branch',addslashes($_POST['DefaultBranch']));
	$sqlinsert='Insert into `gen_info_5creditcardusualpayees` SET  ';
        $sql='';
        $columnstoedit=array('PayeeDetails');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.' DefaultAcctID='.$acctid.', DefaultBranch='.$branch; 
	//echo $sql;break;
        
        $stmt=$link->prepare($sql);
	$stmt->execute();
        
        $sql='Select CCExpID from gen_info_5creditcardusualpayees where PayeeDetails like \''.$_POST['PayeeDetails'].'\'';
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header("Location:creditcard.php?w=UsualPayees");
   
    break;

case 'CCTransactions':
   
    $showall=!isset($_POST['showall'])?0:$_POST['showall'];
    echo '<br><br><h4>Credit Card Transactions</h4>';
    $action='creditcard.php?w=CCTransactions';
   
    include_once $path.'/acrossyrs/commonfunctions/renderspeciallist.php';
    $sql='Select CCExpID as listlabel, PayeeDetails as listvalue from gen_info_5creditcardusualpayees order by PayeeDetails';
    renderspeciallist($link,$sql,'usualpayees');
    $sql='SELECT `CCID` AS listvalue, concat(`Bank`," ",`AcctName`," - ",`CreditCardNo`) as listlabel FROM acctg_1creditcards; ';
    renderspeciallist($link,$sql,'creditcards');
    $show=!isset($_POST['show'])?0:$_POST['show'];
    
    if (allowedToOpen(5272,'1rtc')) { renderotherlist('branchnamesallforacctg',''); $listname='branchnamesallforacctg';} else {renderlist('branchnames'); $listname='branchnamesall';}
renderotherlist('accounts','');//.renderlist('companies')
   
    ?>
    <br><form style="display: inline" method=post action="creditcard.php?w=CCTransactions">
    Choose Credit Card <input type=text list='creditcards' name='ccid'>
    <input type=hidden name="show" value=0>
    <input type=submit name=submit value="Lookup Per Credit Card">
    </form>
    <form style="display: inline" method='post' action="creditcard.php?w=CCTransactions">
    <input type=hidden name="showall" value=<?php echo ($showall==1?0:1); ?>>
    <input type=submit name=submit value="Show/Hide All">
    </form>
    <?php
    //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="E6FFCC";
        $rcolor[1]="FFFFFF";
?>
<form style="display: inline;" method="post" action="#" action="creditcard.php?w=CCTransactions">
   <input type=hidden name="show" value=1>
   <!--<input type="submit" name="submit" value="Show All Cards">-->
</form><br><br>
    <form method=post  style="background-color: #E6FFCC;" action="creditcard.php?w=ProcessAddTxn">
    Credit Card<input type="text" name="CreditCard" list="creditcards" size="20">&nbsp &nbsp
    Charge Date<input type="date" name="ChargeDate" size="7">&nbsp &nbsp
    Particulars<input type=text name="Particulars" list="usualpayees" autocomplete=off>&nbsp &nbsp
    TIN (numbers only)<input type='text' name='TIN' size=10>&nbsp &nbsp
    Expense Account<input type=text name="Account" list="accounts" autocomplete=off size=10></br>
    Branch<input type=text name="Branch" list="<?php echo $listname;?>" autocomplete=off size=10>&nbsp &nbsp
	FromBudgetOf<input type="text" name="FromBudgetOf" list="entities" size="20" value="<?php echo $_SESSION['@brn'];?>">&nbsp &nbsp
<!--    Company<input type=text name="Company" list="companies" autocomplete=off size=10 value="<?php //echo $companyname; ?>">&nbsp &nbsp-->
    Amount<input type=text name="Amount" autocomplete=off size=10>
    
    <input type=submit name=add value="Add">
</form>
<?php

    if (!isset($_REQUEST['ccid']) or $show==1){
        $sql='SELECT CCID AS listlabel, concat(`Bank`," ",`AcctName`," - ",`CreditCardNo`) AS  listvalue, CCID FROM acctg_1creditcards; ';
        $stmt=$link->query($sql);
    $result=$stmt->fetchAll();
    } else {
        $sql='SELECT CCID AS listlabel, concat(`Bank`," ",`AcctName`," - ",`CreditCardNo`) AS  listvalue, CCID FROM acctg_1creditcards having CCID like "'.$_REQUEST['ccid'].'"';
        //echo $sql; break;
        $stmt=$link->query($sql);
    $result=$stmt->fetchAll();
    $creditcard=$_REQUEST['ccid'];
    }
    
    $columnnames=array('ChargeDate', 'Particulars', 'TIN', 'Account', 'ChargeAmount', 'Branch','FromBudgetOf', 'Reconciled');
    if ($showenc==1) { array_push($columnnames,'EncodedBy','TimeStamp'); } else { $columnnames=$columnnames; }
    
    foreach ($result as $cc){
    $totalpercard=0; $percardunreconciled=0; $percardreconciled=0;    
        echo '<div >'.$cc['listvalue'].'<br><table>';
	$sqlgroupby='';
	if (!isset($_REQUEST['ccid']) or $showall==1){ /*All */ $sqlgroupby='';} else { $sqlgroupby=' AND `CreditCardNo`='.$_REQUEST['ccid'];//' AND b.CompanyNo='.$company;
      }
	
        $sql='Select cc.*, format(Amount,2) as ChargeAmount, date_format(ChargeDate,\'%Y-%m-%d\') as ChargeDate, b.Branch, ca.ShortAcctID as Account, e.Nickname as EncodedBy,Entity as FromBudgetOf from acctg_4creditcardsub cc
        join `1branches` b on b.BranchNo=cc.BranchNo
	join acctg_1chartofaccounts ca on ca.AccountID=cc.DebitAccountID LEFT JOIN `acctg_1budgetentities` be on be.EntityID=cc.FromBudgetOf
left join `1employees` as e on e.IDNo=cc.EncodedByNo WHERE cc.CreditCardNo='.$cc['CCID'].$sqlgroupby;
   // echo $sql; break;
    $stmt=$link->query($sql);
    $resultsub=$stmt->fetchAll();
    $txnidname='CCTxnSubId';
    $cctxnlist='';
    foreach ($columnnames as $col){ $cctxnlist=$cctxnlist.'<td>'.$col.'</td>';}
    $cctxnlist='<tr>'.$cctxnlist.'</tr>';
    $delprocess='creditcard.php?w=DelTxn'.$ccidfilter.'&CCTxnSubId='; //.'&CoNo='.$company
    $editprocess='creditcard.php?w=EditTxns&edit=2'.$ccidfilter.'&CCTxnSubId=';
    $autoprocess='creditcard.php?w=Reconcile'.$ccidfilter.'&CCTxnSubId='; //.'&CoNo='.$company
    foreach ($resultsub as $txn){
            $cctxnlist=$cctxnlist.'<tr style="background-color:'. $rcolor[$colorcount%2].'">';
            foreach($columnnames as $col){
            $cctxnlist=$cctxnlist.'<td>'.$txn[$col]."</td>";
            }
            $cctxnlist=$cctxnlist."<td><a href='".$autoprocess.addslashes($txn[$txnidname])."&action_token=".$_SESSION['action_token']."'>Reconcile</a></td><td><a href='".$editprocess.addslashes($txn[$txnidname])."'>Edit</a></td><td><a href='".$delprocess.addslashes($txn[$txnidname])."&action_token=".$_SESSION['action_token']."' OnClick=\" return confirm('Really delete this?');\"'>Del</a></td></tr>"; 
            $totalpercard=$totalpercard+$txn['Amount'];
            $percardunreconciled=$percardunreconciled+($txn['Reconciled']==0?$txn['Amount']:0);
            $percardreconciled=$percardreconciled+($txn['Reconciled']<>0?$txn['Amount']:0);
            $colorcount++;       
        }
        echo $cctxnlist.'</table>Total: '.number_format($totalpercard,2).str_repeat('&nbsp',10).'Unreconciled:'.number_format($percardunreconciled,2).str_repeat('&nbsp',10).'Reconciled:'.number_format($percardreconciled,2).'&nbsp &nbsp
    <a href="creditcard.php?w=PayCC&ccid='.$cc['CCID'].'">Pay Credit Card</a></div><br>'; //&CoNo='.$company.'
        
    }
    break;

case 'ProcessAddTxn':
include_once('../backendphp/functions/getnumber.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

        $acctid=getNumber('Account',addslashes($_POST['Account']));
        $branchno=getNumber('Branch',addslashes($_POST['Branch']));
	//$co=getNumber('Company',addslashes($_POST['Company']));
	$cc=$_POST['CreditCard'];//comboBoxValue ($link,'acctg_1creditcards','CCID',$_POST['CreditCard'],'CCID');
	if (isset($_POST['TIN']) AND !empty($_POST['TIN'])){$tin=' TIN=\''.str_replace("-","",$_POST['TIN']).'\', ';} else { $tin='';}
	$sqlinsert='Insert into `acctg_4creditcardsub` SET  ';
        $sql='';
        $columnstoedit=array('ChargeDate','Particulars','Amount');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlinsert.$sql.$tin.' DebitAccountID='.$acctid.', CreditCardNo='.$cc.', BranchNo='.$branchno.',FromBudgetOf='.$frombudgetof.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()'; 
	// echo $sql; break;
        
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header("Location:creditcard.php?w=CCTransactions".$ccidfilter); //."&CoNo=".$company
   
    break;
case 'DelTxn':
$txnid=$_REQUEST['CCTxnSubId'];
$sql='Delete from `acctg_4creditcardsub` where CCTxnSubId='.$txnid;
$stmt=$link->prepare($sql);
	$stmt->execute();
        header("Location:creditcard.php?w=CCTransactions".$ccidfilter); //."&CoNo=".$_REQUEST['CoNo']
break;

case 'EditTxns':
$txnid=$_REQUEST['CCTxnSubId'];
include_once $path.'/acrossyrs/commonfunctions/renderspeciallist.php';
    $sql='Select CCExpID as listlabel, PayeeDetails as listvalue from gen_info_5creditcardusualpayees order by PayeeDetails';
    renderspeciallist($link,$sql,'usualpayees');
    $sql='SELECT CCID AS listvalue, concat(`Bank`," ",`AcctName`," - ",`CreditCardNo`) as listlabel FROM acctg_1creditcards; ';
    renderspeciallist($link,$sql,'creditcards');
$columnnames=array('CreditCardNo','ChargeDate','Particulars','TIN','Account','Branch','FromBudgetOf','Amount');
$columnstoedit=array('CreditCardNo','ChargeDate','Particulars','TIN','Account','Branch','FromBudgetOf','Amount');
	
$columnslist=array('CreditCardNo','Particulars','Account','Branch','FromBudgetOf');
$listsname=array('CreditCardNo'=>'creditcards','Particulars'=>'usualpayees','Account'=>'accounts','Branch'=>'branchnames','FromBudgetOf'=>'entities');
$liststoshow=array('branchnames');
$listcondition='';
$whichotherlist='acctg';
$otherlist=array('accounts');
$method='POST';
$action='creditcard.php?w=ProcessEditTxn&edit=2'.$ccidfilter.'&CCTxnSubId='.$txnid;

$sql='Select cc.*, format(Amount,2) as ChargeAmount, b.Branch, ca.ShortAcctID as Account,  `CCID` AS CreditCardNo,Entity as FromBudgetOf from acctg_4creditcardsub cc
        join `1branches` b on b.BranchNo=cc.BranchNo LEFT JOIN `acctg_1budgetentities` be on be.EntityID=cc.FromBudgetOf
	JOIN `acctg_1creditcards` card ON card.CCID=cc.CreditCardNo
join acctg_1chartofaccounts ca on ca.AccountID=cc.DebitAccountID where CCTxnSubId='.$txnid;

$processblank='';
$processlabelblank='';
include('../backendphp/layout/rendersubform.php');
    
    break;

case 'ProcessEditTxn':
    $txnid=$_REQUEST['CCTxnSubId'];
        include_once('../backendphp/functions/getnumber.php'); include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        $acctid=getNumber('Account',addslashes($_POST['Account']));
        $branchno=getNumber('Branch',addslashes($_POST['Branch']));
	//$co=getNumber('Company',addslashes($_POST['Company']));
	$cc=$_POST['CreditCardNo'];//comboBoxValue ($link,'acctg_1creditcards','RIGHT(CreditCardNo,4)',$_POST['CreditCardNo'],'CCID');
	$sqlupdate='UPDATE `acctg_4creditcardsub` SET  ';
        $sql='';
        $columnstoedit=array('ChargeDate','Particulars','Amount');
       
	foreach ($columnstoedit as $field) {
		$sql=$sql.' `' . $field. '`=\''.$_POST[$field].'\', '; 
	}
	$sql=$sqlupdate.$sql.' DebitAccountID='.$acctid.', CreditCardNo='.$cc.', TIN='.(empty($_POST['TIN'])?'null':$_POST['TIN']).', BranchNo='.$branchno.', FromBudgetOf='.$frombudgetof.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() where CCTxnSubId='.$txnid;
	//echo $sql;
        
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header("Location:creditcard.php?w=CCTransactions".$ccidfilter); //."&CoNo=".$company
    break;

case 'Reconcile':
    $txnid=$_REQUEST['CCTxnSubId']; 
    $sql='Select Reconciled from `acctg_4creditcardsub` where CCTxnSubId='.$txnid;
    $stmt=$link->query($sql);
    $result=$stmt->fetch();
    $reconcile=$result['Reconciled']==0?1:0;
	$sql='UPDATE `acctg_4creditcardsub` SET  Reconciled='.$reconcile.' where CCTxnSubId='.$txnid;
	//echo $sql;
        
        $stmt=$link->prepare($sql);
	$stmt->execute();
        header("Location:creditcard.php?w=CCTransactions".$ccidfilter);//&CoNo=".$company
    break;
case 'PayCC':
    include_once $path.'/acrossyrs/commonfunctions/lastnum.php'; 
    $vchno=lastNum('CVNo','acctg_2cvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*100000, 'WHERE LEFT(CVNo,2)=Right('.((date('Y',strtotime($currentyr.'-01-01')))).',2)')+1;
        
   // get reconciled txns
   $sql='CREATE TEMPORARY TABLE reconciled AS SELECT b.CompanyNo,cc.* FROM acctg_4creditcardsub cc JOIN `1branches` b ON b.BranchNo=cc.BranchNo where CreditCardNo='.$_GET['ccid'].' and Reconciled<>0';
   $stmt=$link->prepare($sql);   $stmt->execute();
   
   // get companies
   $sqlco='SELECT CompanyNo FROM reconciled r GROUP BY CompanyNo';
    $stmt=$link->query($sqlco);   $resultco=$stmt->fetchAll();
    $chkno=0;
    foreach ($resultco as $co){
      $vchno=$vchno+1;  
	  $chkno=$chkno+1;
      $sqlinsert='Insert into acctg_2cvmain Set CVNo='.$vchno.', CheckNo='.$chkno.', Date=Now(), DateofCheck=Now(), PayeeNo=1002, Payee="Jennifer Y. Eusebio", CreditAccountID=403, Remarks="for credit card '.$_GET['ccid'].'", TimeStamp=Now(), EncodedByNo='.$_SESSION['(ak0)'].', PostedByNo='.$_SESSION['(ak0)'];
      $stmt=$link->prepare($sqlinsert); $stmt->execute();      
      
   $sql='SELECT CVNo FROM acctg_2cvmain where CVNo='.$vchno;
   $stmt=$link->query($sql);   $result=$stmt->fetch();   $txnid=$result['CVNo'];
       
   $sql='SELECT * FROM reconciled r WHERE CompanyNo='.$co['CompanyNo'];
   $stmt=$link->query($sql);  $result=$stmt->fetchAll();
   
   foreach ($result as $sub){ //to add CV sub
      $sqlinsert='Insert into acctg_2cvsub Set CVNo='.$txnid.', FromBudgetOf='.$sub['FromBudgetOf'].',Particulars=concat("'.$sub['Particulars'].'"," ",date_format(\''.$sub['ChargeDate'].'\',\'%Y-%m-%d\')), TIN='.(empty($sub['TIN'])?'null':$sub['TIN']).', DebitAccountID='.$sub['DebitAccountID'].', Amount='.$sub['Amount'].', BranchNo='.$sub['BranchNo'].', TimeStamp=Now(), EncodedByNo=\''.$_SESSION['(ak0)'].'\';';
     // echo $sqlinsert; 
      $stmt=$link->prepare($sqlinsert);  $stmt->execute();
            }  
    }
    $sqldel='DELETE FROM acctg_4creditcardsub where CreditCardNo='.$_GET['ccid'].' and Reconciled<>0';
      //echo $sqlinsert; break;
      $stmt=$link->prepare($sqldel);    $stmt->execute();
      header("Location:txnsperday.php?perday=1&w=CV");	
   
   break;
        
	    
}
noform:
      $link=null; $stmt=null;
?>
</body>
</html>