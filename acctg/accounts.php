<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(512,'1rtc')) { echo 'No permission'; exit; }
include_once('../switchboard/contents.php');

  
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
?><br><div id="section" style="display: block;"><?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);

$columnstoadd=array('AccountID', 'ShortAcctID', 'AccountDescription', 'OrderNo', 'Remarks', 'NormBal','AccumDepAcctOf','ContraAccountOf','Fcy');
if (in_array($which,array('List','EditSpecifics'))){
   echo comboBox($link,'SELECT * FROM `acctg_1chartofaccounts` ORDER BY `AccountID`;','ShortAcctID','AccountID','accountslist');
   echo comboBox($link,'SELECT * FROM `acctg_1accounttype` ORDER BY AcctTypeDescription;','AccountType','AcctTypeDescription','accounttype');
   echo comboBox($link,'SELECT GroupID, AccountGroup FROM `acctg_1accountgroup` ORDER BY AccountGroup','GroupID','AccountGroup','accountgroups');
   echo comboBox($link,'SELECT deptid, department FROM 1departments ORDER BY department','deptid','department','departments');
   
   echo comboBox($link,'SELECT "Yes" AS YesNo, 1 AS YesNoValue UNION SELECT "No" AS YesNo, 0 AS YesNoValue','YesNoValue','YesNo','yesno');
   
   $sql='SELECT ca.*, AcctTypeDescription, IF(AccumDepAcctOf=0,"",AccumDepAcctOf) AS AccumDepAcctOf, IF(ContraAccountOf=0,"",ContraAccountOf) AS ContraAccountOf, if(Budgeted=1,"Yes","No") as Budgeted,
       CompleteDescription, AccountGroup, department AS Department, IF(Fcy=0,"","USD") AS Fcy, OwnedByCoNo, e.Nickname as EncodedBy, ca.AccountID AS TxnID FROM acctg_1chartofaccounts ca
        JOIN `acctg_1accounttype` t ON t.AccountType=ca.AccountType JOIN 1departments d ON ca.DeptID=d.deptid LEFT JOIN `acctg_0descriptionofaccts` da ON ca.AccountID=da.AccountID
        JOIN `acctg_1accountgroup` a ON a.GroupID=ca.GroupID
        LEFT JOIN `1employees` e ON e.IDNo=ca.EncodedByNo ';
   $columnnameslist=array('AcctTypeDescription', 'AccountID', 'ShortAcctID', 'AccountDescription', 'OrderNo', 'Remarks', 'NormBal','AccountGroup','Department', 'AccumDepAcctOf','ContraAccountOf','CompleteDescription','Fcy', 'OwnedByCoNo');//,'EncodedBy','TimeStamp');
   
} 

if (in_array($which,array('Add','Edit'))){
   $accttype=comboBoxValue($link,'`acctg_1accounttype`','AcctTypeDescription',addslashes($_POST['AcctTypeDescription']),'AccountType');
   $deptid=comboBoxValue($link,'1departments','department',addslashes($_POST['Department']),'deptid');
   if(empty($_POST['AccumDepAcctOf'])){ $columnstoadd=array_diff($columnstoadd,array('AccumDepAcctOf'));}
   if(empty($_POST['ContraAccountOf'])){ $columnstoadd=array_diff($columnstoadd,array('ContraAccountOf'));}
   if($_POST['AccountGroup']<>'No group') {
        $acctgroup=comboBoxValue($link,'`acctg_1accountgroup`','AccountGroup',addslashes($_POST['AccountGroup']),'GroupID');
        } else { $acctgroup=0;}
        }

switch ($which){
   case 'List':
       if (!allowedToOpen(512,'1rtc')) { echo 'No permission'; exit; } 
         $title='Chart of Accounts'; $formdesc='BE CAREFUL.  This affects the entire accounting system.'; $method='post';
         $columnnames=array(
                    array('field'=>'AccountID', 'type'=>'text','size'=>8,'required'=>true, 'list'=>'accountslist'),
                    array('field'=>'AcctTypeDescription','caption'=>'Account Type','type'=>'text','size'=>10,'required'=>true, 'list'=>'accounttype'),
                    array('field'=>'ShortAcctID','type'=>'text','size'=>10,'required'=>true),
		    array('field'=>'AccountDescription','type'=>'text','size'=>25,'required'=>true),
                    array('field'=>'OrderNo','type'=>'text','size'=>5,'required'=>false, 'value'=>0),
                    array('field'=>'Remarks','type'=>'text','size'=>10,'required'=>false),
                    array('field'=>'NormBal', 'caption'=>'Normal Balance (1=positive, -1=negative)','type'=>'text','size'=>2,'required'=>true),
                    array('field'=>'AccountGroup','type'=>'text','size'=>10,'required'=>true, 'list'=>'accountgroups', 'value'=>'"No Group"'),
                    array('field'=>'Department','type'=>'text','size'=>10,'required'=>true, 'list'=>'departments', 'value'=>'Others'),
                    array('field'=>'AccumDepAcctOf','type'=>'text','size'=>5,'required'=>false),
                    array('field'=>'ContraAccountOf','type'=>'text','size'=>5,'required'=>false),
                    array('field'=>'Fcy','caption'=>'Currency (0=PHP, 1=USD)','type'=>'text','size'=>3,'required'=>true, 'value'=>0));
                     
      $action='accounts.php?w=Add'; $fieldsinrow=4; $liststoshow=array();
      if (allowedToOpen(5121,'1rtc')){
	 include('../backendphp/layout/inputmainform.php');
	 $delprocess='accounts.php?w=Delete&AccountID=';
         $addlprocess='accounts.php?w=Distri&AccountID='; $addlprocesslabel='AssignToBranches';
         $columnstoedit=array('AcctTypeDescription', 'AccountID', 'ShortAcctID', 'AccountDescription', 'OrderNo', 'Remarks', 'NormBal');
	 } else { $columnstoedit=array('CompleteDescription');}
      
      $title=''; $formdesc='';$txnid='AccountID';
      $columnnames=$columnnameslist;
	  $columnnames[]='Budgeted';
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' AcctTypeDescription,OrderNo'); $columnsub=$columnnames;
        $sql=$sql.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');         
        $editprocess='accounts.php?w=EditSpecifics&AccountID='; $editprocesslabel='Edit'; 
      include('../backendphp/layout/displayastable.php');       
        break;
    case 'Add':
        if (allowedToOpen(5121,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `acctg_1chartofaccounts` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' AccountType='.$accttype.', GroupID='.$acctgroup.', DeptID='.$deptid.', TimeStamp=Now()'; 
        
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if (allowedToOpen(5122,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `acctg_1begbal` WHERE AccountID='.$_GET['AccountID'];
        $stmt=$link->prepare($sql); $stmt->execute();
        $sql='DELETE FROM `acctg_1chartofaccounts` WHERE AccountID='.$_GET['AccountID'];
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;   
    case 'Distri':
        if (allowedToOpen(5121,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='INSERT INTO `acctg_1begbal` (`AccountID`,`BegBalance`,`BranchNo`, `EncodedByNo`, `TimeStamp`)
            SELECT '.$_GET['AccountID'].',0,b.BranchNo,'.$_SESSION['(ak0)'].',Now() FROM `1branches` b 
            WHERE BranchNo NOT IN (SELECT BranchNo FROM `acctg_1begbal` WHERE AccountID='.$_GET['AccountID'].') AND b.BranchNo NOT IN (95,999) AND b.Active=1;';
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'EditSpecifics':
         $title='Edit Specifics'; $formdesc='OwnedByCoNo must be an array with no spaces. Leave blank if used by all companies. Fcy must have either 0 (PHP) or 1 (USD)';
	 $txnid=$_GET['AccountID']; 
	 $sql=$sql.'WHERE ca.AccountID='.$txnid;
	 array_push($columnstoadd,'AcctTypeDescription','AccountGroup','Department','CompleteDescription','Budgeted','OwnedByCoNo');
	 $columnstoedit=$columnstoadd;
	 
	 $columnnames=$columnnameslist;
	 $columnswithlists=array('AcctTypeDescription','AccountGroup','Department','Budgeted');
         $listsname=array('AcctTypeDescription'=>'accounttype','AccountGroup'=>'accountgroups','Department'=>'departments','Budgeted'=>'yesno');
	 $editprocess='accounts.php?w=Edit&AccountID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	if (!empty($_POST['CompleteDescription'])){
	 $stmt0=$link->query('SELECT AccountID FROM `acctg_0descriptionofaccts` WHERE AccountID='.$_GET['AccountID']);
	 $res0=$stmt0->fetch();
	 if ($stmt0->rowCount()>0) {
	       $sql='UPDATE `acctg_0descriptionofaccts` SET CompleteDescription=\''.addslashes($_POST['CompleteDescription']).'\', EncodedByNo='.$_SESSION['(ak0)'].',
	       TimeStamp=Now() WHERE AccountID='.$_GET['AccountID']; }
	       else {$sql='INSERT INTO `acctg_0descriptionofaccts` SET AccountID='.$_GET['AccountID'].', CompleteDescription=\''.addslashes($_POST['CompleteDescription']).'\',
	       EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now()';}
        $stmt=$link->prepare($sql); $stmt->execute();
	}
	if (allowedToOpen(5121,'1rtc')){
		if($_POST['Budgeted']=='Yes'){
			$budgeted=1;
		}else{
			$budgeted=0;
		}
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `acctg_1chartofaccounts` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' AccountType='.$accttype.',GroupID='.$acctgroup.',  DeptID='.$deptid.', TimeStamp=Now(),Budgeted=\''.$budgeted.'\' WHERE AccountID='.$_GET['AccountID']; 
		// echo $sql; exit();
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:accounts.php");
        break;
    
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body></html>