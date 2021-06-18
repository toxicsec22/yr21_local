<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 
if (!allowedToOpen(array(532,533,534,535,5333,5334),'1rtc')) { echo 'No permission'; exit;}

$showbranches=false; $outside=false;
include_once('../switchboard/contents.php'); 
// open at office only
if (!allowedToOpen(5331,'1rtc')) {
    
    if($_SERVER['REMOTE_ADDR']<>'120.28.114.245' AND $_SERVER['REMOTE_ADDR']<>'120.28.99.129'){  echo 'No permission'; exit;}
}

 

$title=!isset($title)?'FS':$title;

$alternatecolor="ADEBFF";
include_once "../generalinfo/lists.inc"; include_once('../backendphp/functions/getnumber.php');
$sql0='SELECT MONTH(`DataClosedBy`) as ClosedMonth FROM `00dataclosedby` WHERE (`00dataclosedby`.`ForDB` = 1)';
        $stmt=$link->query($sql0); $res0=$stmt->fetch(); $closedmonth=((substr($currentyr,0,4)<>substr($_SESSION['nb4A'],0,4)))?0:$res0['ClosedMonth'];

$which=$_GET['w'];
        
if($which=='DeptMonth') { $reportmonth=date('m'); goto noaccttype;} else {$reportmonth=!isset($_REQUEST['reportmonth'])?$closedmonth:$_REQUEST['reportmonth'];}
$company=(!isset($_POST['company'])?'':$_REQUEST['company']);
$branch=(!isset($_POST['branch'])?'':$_REQUEST['branch']); //$_SESSION['@brn']
$book=!isset($_POST['book'])?0:$_POST['book'];
$showcurrent=$reportmonth>$closedmonth?1:0;
$formdesc=$showcurrent==1?'Active Data':'Protected Data';

switch ($which){
    case 'TBMonth': $showinbranches=true; $actionbranch='fs.php?w=TBMonth'; include('../backendphp/layout/fsfilters.php'); break;
    case 'WS' : $showinmonths=true; $actionmonth='fs.php?w=WS'; include('../backendphp/layout/fsfilters.php'); break;
    case 'ISMonth': case 'ISMonthGrouped': case 'ISBranch': case 'ISBranchGrouped': $showinmonths=true; $actionmonth='fs.php?w=ISMonth';
            $showinbranches=true; $actionbranch='fs.php?w=ISBranch'; $actioncompany='fs.php?w=ISCompany'; 
            include('../backendphp/layout/fsfilters.php'); break;
    case 'BSMonth': case 'BSBranch': $showinmonths=true; $actionmonth='fs.php?w=BSMonth';
            $showinbranches=true; $actionbranch='fs.php?w=BSBranch'; 
            $actioncompany='fs.php?w=BSCompany'; 
            include('../backendphp/layout/fsfilters.php'); break;
    case 'CFMonth': case 'CFMonthGrouped': case 'CFBranch': case 'CFBranchGrouped': $showinmonths=true; $actionmonth='fs.php?w=CFMonth';
            $showinbranches=true; $actionbranch='fs.php?w=CFBranch'; $actioncompany='fs.php?w=CFCompany'; 
            include('../backendphp/layout/fsfilters.php'); break;
			
	
	case 'Lookup':
		unset($formdesc);
		$title='Lookup';
		$sql='select Date,ControlNo,`Supplier/Customer/Branch`,Particulars,format(Amount,2) as Amount from '.$currentyr.'_static.acctg_0unialltxns ut where FromBudgetOf=\''.$_GET['FromBudgetOf'].'\' AND AccountID=\''.$_GET['AccountID'].'\'';
		$columnnames=array('Date','ControlNo','Supplier/Customer/Branch','Particulars','Amount');
		include('../backendphp/layout/displayastablenosort.php'); break;
		// echo $sql; exit();

	break;		
    
}

renderlist('companies');renderlist('branchnamesall');
if (!isset($_POST['groupby'])) { echo '<title>FS : '.$which.'</title>'; goto noform;} // AND !isset($_POST['groupbybranch'])
$groupby=$_POST['groupby'];
switch ($groupby){
   case 0: //Branch in Month columns 
      $title=$_POST['branch']; $sqlgroupby=' and b.BranchNo='.getNumber('Branch',$_POST['branch']).' GROUP BY ca.AccountID, b.BranchNo';
      break;
   case 1: //Company in Month columns
      $title=$_POST['company'];  $sqlgroupby=' AND b.CompanyNo='.getNumber('Company',$_POST['company']).' GROUP BY ca.AccountID, b.CompanyNo';
      break;
   case 2: //Company in columns 
      $title='Companies';      $formdesc='For the month of '. strtoupper(date('F',strtotime(''.$currentyr.'-'.$_POST['reportmonth'].'-01'))); 
      $sqlgroupby=' `FSMonth`='.$reportmonth;     $sqlcurrgroupby='='.$reportmonth;    
      break;
   case 3: //Per Month in Branch columns
      $title=$_POST['company']; $formdesc='For the month of '. strtoupper(date('F',strtotime(''.$currentyr.'-'.$_POST['reportmonth'].'-01'))); $sqlgroupby=' `FSMonth`='.$reportmonth;
      $sqlcurrgroupby='='.$reportmonth;       
      break;
   case 4: //Month As Of in Branch columns
      $title=$_POST['company']; $formdesc='As of '. strtoupper(date('F',strtotime(''.$currentyr.'-'.$_POST['reportmonth'].'-01')));  
      $sqlgroupby=' `FSMonth`<='.$reportmonth;       $sqlcurrgroupby='<='.$reportmonth;
      break;
  case 5: //Company in columns As Of
      $title='Companies';  $formdesc='As of '. strtoupper(date('F',strtotime(''.$currentyr.'-'.$_POST['reportmonth'].'-01'))); 
      $sqlgroupby=' `FSMonth`<='.$reportmonth;       $sqlcurrgroupby='<='.$reportmonth;    
      break;
  case 10: //All 
      $title='ALL';  $formdesc=''; 
      $sqlgroupby=' GROUP BY ca.AccountID';      
      break;
  default: //All in Month columns
      $title='ALL'; $sqlgroupby=' GROUP BY ca.AccountID';
      break;
}
if(strpos($_POST['submit'],'ALL')){ $title='All Branches'; $allactive=1; } 
if(strpos($_POST['submit'],'rouped',1)) { $grouped=1;  } else { $grouped=0;}

$downloadsub='';

include('sqlphp/fsaccttype.php');

noaccttype:

switch ($which){

case 'ISMonth':
    if (!allowedToOpen(533,'1rtc')) {  echo 'No permission'; exit; } 
   
$title='Income Statement '.$title;
include('sqlphp/fsreportmonths.php');
$columnnames[]='Year'; $columnnames=array_diff($columnnames,array('Beginning'));
include('acctglayout/fslayout.php');
?><!-- <div id="wrap"><div id="left"> --><?php echo $fieldlist ;
$downloadsub.=$downloadsubcol;
if($grouped==1){ 
    $alternatecolor="FFFFFF";
    $sql=getSqlPerGroup($sales,' or AccountID=810 ','i',$months,-1,$reportmonth);
    include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;}
else {
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($sales,' or AccountID=810 ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
}
$sql=getSqlSumPerTypeIS($sales,' or AccountID=810 ','Net Sales',$months,-1,$reportmonth);
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($cogs,' and AccountID<>810 ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS($cogs,'','Cost of Goods Sold',$months,1,$reportmonth);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS("$sales,$cogs",'','Gross Profit',$months,-1,$reportmonth);
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;

if($grouped==1){ 
    $alternatecolor="FFFFFF";
    $sql=getSqlPerGroup("$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense",' ','i',$months,1,$reportmonth);
    include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;}
else {
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($vatpaymts,'','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS("$vatpaymts",'','Vat Payments (Net)',$months,1,$reportmonth);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($opsexpense,'','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS("$opsexpense",'','Operations Expenses',$months,1,$reportmonth);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($genandadmin,' ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS($genandadmin,'','General Admin Expenses',$months,1,$reportmonth);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($salesexpense,' ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS($salesexpense,' ','Sales Expenses',$months,1,$reportmonth);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($auditexpense,' ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS($auditexpense,' ','Audit Expenses',$months,1,$reportmonth);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($hrexpense,' ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS($hrexpense,' ','Human Resource Expenses',$months,1,$reportmonth);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($otherexpense,' ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS($otherexpense,' ','Other Operating Expenses',$months,1,$reportmonth);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
}
$sql=getSqlSumPerTypeIS("$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense",'','Total Expenses',$months,1,$reportmonth);
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
//$alternatecolor="FFFFFF";
//$sql=getSqlPerAcct($genandadmin,' and AccountID=900 ','i');
//include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS("$sales,$cogs,$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense",'','Gross Income',$months,-1,$reportmonth);
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlPerAcct($otherincome,'','i');
$alternatecolor="FFFFFF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeIS("$sales,$cogs,$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense,$otherincome",'','Net Income',$months,-1,$reportmonth);
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
?></table>
<?php 
goto noform;

break;

case 'BSMonth':
    if (!allowedToOpen(532,'1rtc')) { echo 'No permission'; exit; }
$title='Balance Sheet '.$title;
include('sqlphp/fsreportmonths.php');
$months[]='Beginning';
include('acctglayout/fslayout.php');

echo $fieldlist ; $downloadsub.=$downloadsubcol;
if($grouped==1){ 
    $alternatecolor="FFFFFF";
    $sql=getSqlPerGroup("$cashcib,$ar,$arothers,$invty,$othercurrassets",' or AccountID=404 ','b',$months,1,$reportmonth);
    include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
    } else { 
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($cashcib,' and AccountDescription not like \'CIB%\' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBS($cashcib,' and AccountDescription NOT like \'CIB%\' ','<i>Total Cash</i>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($cashcib,' and AccountDescription like \'CIB%\' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBS($cashcib,' and AccountDescription LIKE \'CIB%\' ','<i>Total CIB</i>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($ar,' and AccountID<>204 ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBS($ar,' and AccountID<>204 ','<i>AR and ADVANCES</i>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($arothers,'','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBS($arothers,'','<i>Other Receivables</i>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct("$ar,$arothers,$ap",' and (AccountID=204 or AccountID=404) ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBS("$ar,$arothers,$ap",' and (AccountID=204 or AccountID=404) ','<i>AR/AP - BRANCH TRANSFERS</i>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct("$invty",' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBS($invty,' ','<i>Total Inventory</i>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($othercurrassets,' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBS($othercurrassets,' ','<i>Total Other Current Assets</i>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
    }
$alternatecolor="E5FFE5";
$sql=getSqlSumPerTypeBS("$cashcib,$ar,$arothers,$invty,$othercurrassets",' or AccountID=404 ','<i>Current Assets<i>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($ppe,'','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBS($ppe,'','<i>Total PPE</i>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($othernoncurrassets,' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBS($othernoncurrassets,'','<i>Total Other Non-Current Assets</i>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="CCE6FF";
$sql=getSqlSumPerTypeBS("$cashcib,$ar,$arothers,$invty,$othercurrassets,$ppe,$othernoncurrassets",' or AccountID=404 ','<b>TOTAL ASSETS</b>',$months,1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct("$ap,$othercurrliab,$noncurrliab",' and AccountID<>404 ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="E5FFE5";
$sql=getSqlSumPerTypeBS("$ap,$othercurrliab,$noncurrliab",' and AccountID<>404 ','<i>Total Liabilities</i>',$months,-1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcct($capital,'','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlSumPerTypeBS("$sales,$cogs,$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense,$otherincome",'','Net Income (Loss)',$months,-1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="CCE6FF";
$sql=getSqlSumPerTypeBS("$ap,$othercurrliab,$noncurrliab,$capital,$sales,$cogs,$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense,$otherincome",' and AccountID<>404 ','<b>TOTAL LIABILITIES & CAPITAL</b>',$months,-1,$reportmonth);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
?></table><?php
   break;

//FOLLOWING PER BRANCH FS
case 'ISBranch':
    if (!allowedToOpen(533,'1rtc')) { echo 'No permission'; exit; }
    if (in_array($_POST['groupby'],array(2,5))){ include('sqlphp/fsreportcompanies.php'); } else { include('sqlphp/fsreportbranches.php'); }
$columnnames[]='Total';
require('acctglayout/fslayout.php');
echo $fieldlist; $downloadsub.=$downloadsubcol;
if($grouped==1){ 
  $alternatecolor="FFFFFF";
    $sql=getSqlSumPerGroupBranch($sales,' or AccountID=810 ','i',$resultbranch,-1);
    include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;}
else {  
    
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($sales,' or AccountID=810 ','i');
// echo $sql;break;
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
}
$sql=getSqlSumPerTypeISBranch($sales,' or AccountID=810 ','Net Sales',$resultbranch,-1);
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($cogs,' and AccountID<>810 ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeISBranch("$sales,$cogs",'','Gross Profit',$resultbranch,-1);
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;

if($grouped==1){ 
  $alternatecolor="FFFFFF";
    $sql=getSqlSumPerGroupBranch("$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense",' ','i',$resultbranch,1);
    include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;}
else { 
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($vatpaymts,'','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeISBranch($vatpaymts,'','VAT Payments (Net)',$resultbranch,1);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($opsexpense,'','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeISBranch($opsexpense,'','Operations Expenses',$resultbranch,1);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($genandadmin,'','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeISBranch($genandadmin,'','General Admin Expenses',$resultbranch,1);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($salesexpense,' ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeISBranch($salesexpense,' ','Sales Expenses',$resultbranch,1);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($auditexpense,' ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeISBranch($auditexpense,' ','Audit Expenses',$resultbranch,1);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($hrexpense,' ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeISBranch($hrexpense,' ','Human Resource Expenses',$resultbranch,1);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($otherexpense,' ','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeISBranch($otherexpense,' ','Other Operating Expenses',$resultbranch,1);
$alternatecolor="E5FFE5";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
}
$sql=getSqlSumPerTypeISBranch("$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense",'','Total Expenses',$resultbranch,1);
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
//$alternatecolor="FFFFFF";
//$sql=getSqlPerAcctBranch($genandadmin,'','i');
//include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload; 
$sql=getSqlSumPerTypeISBranch("$sales,$cogs,$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense",'','Gross Income',$resultbranch,-1);
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlPerAcctBranch($otherincome,'','i');
$alternatecolor="FFFFFF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeISBranch("$sales,$cogs,$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense,$otherincome",'','Net Income',$resultbranch,-1);
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;

break;

case 'BSBranch':
    if (!allowedToOpen(532,'1rtc')) { echo 'No permission'; exit; }
    if (in_array($_POST['groupby'],array(2,5))){ include('sqlphp/fsreportcompanies.php'); } else { echo include('sqlphp/fsreportbranches.php'); }
$columnnames[]='Total';
require('acctglayout/fslayout.php');
echo $fieldlist ; $downloadsub.=$downloadsubcol;
if($grouped==1){ 
    $alternatecolor="FFFFFF";
    $sql=getSqlSumPerGroupBranch("$cashcib,$ar,$arothers,$invty,$othercurrassets",' or AccountID=404 ','b',$resultbranch,1);
    include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
    } else {
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($cashcib,' and AccountDescription NOT like \'CIB%\' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBSBranch($cashcib,' and AccountDescription NOT like \'CIB%\' ','<i>Total Cash</i>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($cashcib,' and AccountDescription like \'CIB%\' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBSBranch($cashcib,' and AccountDescription like \'CIB%\' ','<i>Total CIB</i>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($ar,' and AccountID<>204 ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBSBranch($ar,' and AccountID<>204 ','<i>AR and ADVANCES</i>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($arothers,'','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBSBranch($arothers,'','<i>Other Receivables</i>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch("$ar,$arothers,$ap",' and (AccountID=204 or AccountID=404) ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBSBranch("$ar,$arothers,$ap",' and (AccountID=204 or AccountID=404) ','<i>AR/AP - BRANCH TRANSFERS</i>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($invty,' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBSBranch($invty,' ','<i>Total Inventory</i>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($othercurrassets,' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBSBranch($othercurrassets,' ','<i>Total Other Current Assets</i>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
    }
$alternatecolor="E5FFE5";
$sql=getSqlSumPerTypeBSBranch("$cashcib,$ar,$arothers,$invty,$othercurrassets",' or AccountID=404 ','<i>Current Assets<i>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($ppe,'','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBSBranch($ppe,'','<i>Total PPE</i>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($othernoncurrassets,' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlSumPerTypeBSBranch($othernoncurrassets,'','<i>Total Other Non-Current Assets</i>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="CCE6FF";
$sql=getSqlSumPerTypeBSBranch("$cashcib,$ar,$arothers,$invty,$othercurrassets,$ppe,$othernoncurrassets",' or AccountID=404 ','<b>TOTAL ASSETS</b>',$resultbranch,1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch("$ap,$othercurrliab,$noncurrliab",' and AccountID<>404 ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="E5FFE5";
$sql=getSqlSumPerTypeBSBranch("$ap,$othercurrliab,$noncurrliab",' and AccountID<>404 ','<i>Total Liabilities</i>',$resultbranch,-1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlPerAcctBranch($capital,'','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFFF";
$sql=getSqlSumPerTypeBSBranch("$sales,$cogs,$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense,$otherincome",'','Net Income (Loss)',$resultbranch,-1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="CCE6FF";
$sql=getSqlSumPerTypeBSBranch("$ap,$othercurrliab,$noncurrliab,$capital,$sales,$cogs,$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense,$otherincome",' and AccountID<>404 ','<b>TOTAL LIABILITIES & CAPITAL</b>',$resultbranch,-1);
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
break;

case 'CFMonth': 
    if (!allowedToOpen(533,'1rtc')) {  echo 'No permission'; exit; } 
   
$title='Cash Flow '.$title;
include('sqlphp/fsreportmonths.php');
include('sqlphp/fscashflowfxn.php');
$columnnames[]='Year'; $columnnames=array_diff($columnnames,array('Beginning','AccountID'));
include('acctglayout/fslayout.php');
?><!-- <div id="wrap"><div id="left"> --><?php echo $fieldlist ;

$sql=getSqlSumPerTypeIS("$sales,$cogs,$vatpaymts,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense,$otherincome",'','Net Income',$months,-1,$reportmonth);
$stmtcf=$link->prepare('CREATE TEMPORARY TABLE cf AS '.$sql); $stmtcf->execute();
$alternatecolor="CCE6FF";
include('../backendphp/layout/displayassubtable.php'); 

$sql=getSqlPerGroupCF(6350,'', 'i',$months,'Add: ',$reportmonth); 
$stmtcf=$link->prepare('INSERT INTO cf '.$sql); $stmtcf->execute();
$alternatecolor="FFFFFF"; 
include('../backendphp/layout/displayassubtable.php'); 

$sql=getSqlBSChangeCF('ca.AccountType IN (2)', -1, $months,'Less: Change in AR Trade',$reportmonth); 
$stmtcf=$link->prepare('INSERT INTO cf '.$sql); $stmtcf->execute();
$alternatecolor="FFFFFF"; 
include('../backendphp/layout/displayassubtable.php'); 
$sql=getSqlBSChangeCF('ca.AccountType IN (3)', -1, $months,'Less: Change in AR Others',$reportmonth); 
$stmtcf=$link->prepare('INSERT INTO cf '.$sql); $stmtcf->execute();
include('../backendphp/layout/displayassubtable.php'); 
$sql=getSqlBSChangeCF('ca.AccountType IN (4)', -1, $months,'Less: Change in Inventory',$reportmonth); 
$stmtcf=$link->prepare('INSERT INTO cf '.$sql); $stmtcf->execute();
include('../backendphp/layout/displayassubtable.php'); 
$sql=getSqlBSChangeCF('ca.AccountType IN (5)', -1, $months,'Less: Change in Other Current Assets',$reportmonth); 
$stmtcf=$link->prepare('INSERT INTO cf '.$sql); $stmtcf->execute();
include('../backendphp/layout/displayassubtable.php'); 
$sql=getSqlBSChangeCF('ca.AccountType IN (8)', 1, $months,'Add: Change in AP Trade',$reportmonth); 
$stmtcf=$link->prepare('INSERT INTO cf '.$sql); $stmtcf->execute();
$alternatecolor="FFFFFF"; 
include('../backendphp/layout/displayassubtable.php'); 
$sql=getSqlBSChangeCF('ca.AccountType IN (9,10)', 1, $months,'Add: Change in Other Payables',$reportmonth); 
$stmtcf=$link->prepare('INSERT INTO cf '.$sql); $stmtcf->execute();
$alternatecolor="FFFFFF"; 
include('../backendphp/layout/displayassubtable.php'); 

$sql=getSqlBSChangeCF('ca.AccountType IN (2,3,4,5,8,9,10) OR ((ca.AccountType BETWEEN 100 AND 250) AND ca.GroupID<>6350)', 1, $months,'Cash Flows from Operations',$reportmonth); 
$alternatecolor="CCE6FF"; 
include('../backendphp/layout/displayassubtable.php'); 

$sql=getSqlBSChangeCF('ca.AccountType IN (6,7)', -1, $months,'Less: Change in PPE & Non-Curr Assets',$reportmonth); 
$stmtcf=$link->prepare('INSERT INTO cf '.$sql); $stmtcf->execute();
$alternatecolor="FFFFFF"; 
include('../backendphp/layout/displayassubtable.php'); 
$sql=getSqlBSChangeCF('ca.AccountType IN (11)', 1, $months,'Add: Change in Capital',$reportmonth); 
$stmtcf=$link->prepare('INSERT INTO cf '.$sql); $stmtcf->execute();
$alternatecolor="FFFFFF"; 
include('../backendphp/layout/displayassubtable.php'); 

$sql=getSqlBSChangeCF('ca.AccountType IN (6,7,11)', 1, $months,'Cash Flows - Others',$reportmonth); 
$alternatecolor="CCE6FF"; 
include('../backendphp/layout/displayassubtable.php'); 

$sql='SELECT "Net Cash Flow" AS AccountDescription, FORMAT(SUM(REPLACE(`Year`,",","")),2) AS `Year`';
foreach ($months as $fsmonth){
         if($fsmonth>$reportmonth){ goto skip;}
         $sql.=', FORMAT(SUM(REPLACE(`'.monthName($fsmonth).'`,",","")),2) AS `'.monthName($fsmonth).'` ';
}
$sql.=' FROM cf ';
//$sql='SELECT * FROM cf';
skip:
$alternatecolor="CCE6FF"; 
include('../backendphp/layout/displayassubtable.php'); 

?></table>
<?php 
goto noform;

break;

// FOLLOWING ARE FOR TRIAL BALANCE REPORTS
case 'TBMonth':
    if (!allowedToOpen(534,'1rtc')) { echo 'No permission'; exit; }
$title='Trial Balance for '.$company. ' - '.date('F',strtotime(''.$currentyr.'-'.$reportmonth.'-1'));
//$formdesc='Only Company and Month can be chosen.';
$allperco=0;
if (in_array($_POST['groupby'],array(2,5))){ include('sqlphp/fs-TBvaluescompanies.php'); } else { include('sqlphp/fs-TBvalues.php'); }


$sqlsum='Select format((sum(case when NormBal=1 then Balance end)),2) as DebitSum, format((Sum(case when NormBal<>1 then Balance*-1 end)),2) as CreditSum from `' . $currentyr . '_static`.`acctg_tbvalues`';
$stmtsum=$link->query($sqlsum);
$resultsum=$stmtsum->fetch();
$totalstext='TOTALS '.str_repeat('&nbsp',10).'DEBIT  '.$resultsum['DebitSum'].str_repeat('&nbsp',20).'CREDIT  '.$resultsum['CreditSum'];
include('../backendphp/layout/displayastable.php'); echo '<br><br>';
$sql=$sqltotals; $totalstext=null; $hidecount=true; include('../backendphp/layout/displayastableonlynoheaders.php'); 
?>
</body>
</html>
<?php
   break;

case 'WS':
    if (!allowedToOpen(535,'1rtc')) { echo 'No permission'; exit; }
$title='General Ledger Worksheet for '.$title;
//$allperco=0;
include('../acctg/sqlphp/fs-WSvalues.php'); 
$columnnames[]='Year'; //$months[]='Beginning';
include('acctglayout/fslayout.php');
?><div id="wrap"><div id="left"><?php echo $fieldlist ;
$downloadsub.=$downloadsubcol;
$alternatecolor="E5FFE5";
$sql=getSqlPerAcct($cashcib,' '); 
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlPerAcct($ar,'','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="CCE6FF";
$sql=getSqlPerAcct($arothers,'');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="E5FFE5";
$sql=getSqlPerAcct($invty,' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFFFCC";
$sql=getSqlPerAcct($othercurrassets,' ','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="CEE6FF";
$sql=getSqlPerAcct("$ppe,$othernoncurrassets",'','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="FFD8EF";
$sql=getSqlPerAcct("$ap,$othercurrliab,$noncurrliab",'');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="ffb3b3";
$sql=getSqlPerAcct($capital,'','b');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="e6ffcc";
$sql=getSqlPerAcct($sales,'');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="cceeff";
$sql=getSqlPerAcct($cogs,'');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="cceeff";
$sql=getSqlPerAcct($opsexpense,'');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="ffd9b3";
$sql=getSqlPerAcct("$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense",'');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$alternatecolor="CCE6FF";
$sql=getSqlPerAcct($otherincome,'','i');
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
$sql=getSqlSumPerTypeWS("$cashcib,$ar,$arothers,$invty,$othercurrassets,$ppe,$othernoncurrassets,$ap,$othercurrliab,$noncurrliab,$capital,$sales,$cogs,$opsexpense,$genandadmin,$salesexpense,$auditexpense,$hrexpense,$otherexpense,$otherincome",'','Net Balance',$months,$reportmonth); 
$alternatecolor="ffff66";
include('../backendphp/layout/displayassubtable.php'); $downloadsub.=$textfordownload;
break;
   
case 'DeptMonth':
	unset($title);
	echo'<title>Actual Expenses vs Budget</title></br><h3>Actual Expenses vs Budget</h3>';
	include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
	echo comboBox($link,'SELECT EntityID, Entity FROM `acctg_1budgetentities`
	union select BranchNo as EntityID, Branch as Entity from 1branches ORDER BY Entity','EntityID','Entity','entities'); 
	echo comboBox($link,'SELECT AccountID,ShortAcctID FROM acctg_1chartofaccounts WHERE Budgeted=1 ORDER BY ShortAcctID','AccountID','ShortAcctID','accountlist');
	if(isset($_REQUEST['entity'])){
	$entity=companyandbranchValue($link, 'acctg_1budgetentities', 'Entity', $_REQUEST['entity'], 'EntityID');
	if(empty($entity)){
		$sqlchecker='select BranchNo from 1branches where Branch=\''.$_REQUEST['entity'].'\'';
		$stmtchecker=$link->query($sqlchecker); $resultchecker=$stmtchecker->fetch();
		$entity=$resultchecker['BranchNo'];
	}
	}
	if(isset($_REQUEST['Account'])){
		$accountid=comboBoxValue($link, 'acctg_1chartofaccounts', 'ShortAcctID', $_REQUEST['Account'], 'AccountID');
		if(empty($accountid)){
			$sqlchecker='select AccountID from acctg_1chartofaccounts where ShortAcctID=\''.$_REQUEST['Account'].'\'';
			$stmtchecker=$link->query($sqlchecker); $resultchecker=$stmtchecker->fetch();
			$accountid=$resultchecker['AccountID'];
		}
	}
	echo'</br><form style="display:inline;" method="post" action="fs.php?w=DeptMonth">
				Entity: <input type="text" name="entity" list="entities" placeholder="Entity" '.(isset($_POST['submit'])?'value="'.$_POST['entity'].'"':'').'>
				<input type="submit" name="submit" value="Lookup">
				</form>';
				
				//totals
				
				//switch total	
					if(!isset($_POST['switchvaluetotal'])){
						$switchvaluetotal=0;
						$switchlabeltotal='Per Month';
						$label='Per Quarter';
					}
					if(isset($_POST['switchvaluetotal'])){
						if($_POST['switchvaluetotal']==0){
								$switchvaluetotal=1;
								$switchlabeltotal='Per Quarter';
								$label='Per Month';
						}else{
							$switchvaluetotal=0;
							$switchlabeltotal='Per Month';
							$label='Per Quarter';
						}
					}
				//
				echo''.str_repeat('&nbsp;',20).'<form style="display:inline;" method="post" action="fs.php?w=DeptMonth">
				Account <input type="text" name="Account" list="accountlist" placeholder="Account" '.(isset($_POST['LookupAccount'])?'value="'.$_POST['Account'].'"':'').'> <input type="submit" name="LookupAccount" value="Lookup">
				</form>';

				echo''.str_repeat('&nbsp;',20).'<form style="display:inline;" method="post" action="fs.php?w=DeptMonth">
				<b></b> <input type="submit" name="Total" value="Total Expenses and Total Budget '.$switchlabeltotal.'">
				<input type="hidden" name="switchvaluetotal" value="'.$switchvaluetotal.'">
				</form></br>';
				
				if(isset($_POST['Total'])){
					$title='';
					$formdesc='</i><b>Total Expenses and Total Budget '.$label.'</b>';
					$hidecount=true;
					$sqln='Create temporary table joiner as select * from budget_1budgets Group By AccountID';
					// echo $sqln.'<br><br>';
					$stmtn=$link->prepare($sqln); $stmtn->execute();
					$sqlte='Create temporary table arrange select month(Date) as Month,ut.AccountID,sum(Amount) as Amount,\'1\' AS Col from '.$currentyr.'_static.acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID left join joiner j on j.AccountID=ut.AccountID where ControlNo not like \'%BegBal\' AND Budgeted=1  Group By ut.AccountID,month(Date) 
					UNION
					select Month,AccountID,sum(Budget) as Amount,\'2\' AS Col from budget_1budgets Group By AccountID,Month';
					// echo $sqlte.'<br><br>';
					$stmtte=$link->prepare($sqlte); $stmtte->execute();
					
					$sqltotal='Create temporary table TotalExpensesAndTotalBudget as Select ShortAcctID as Account,
	sum(CASE WHEN Col=1 and Month=1 then Amount end) as JanExpense,sum(CASE WHEN Col=2 and Month=1 then Amount end) as JanBudget,
	sum(CASE WHEN Col=1 and Month=2 then Amount end) as FebExpense,sum(CASE WHEN Col=2 and Month=2 then Amount end) as FebBudget,
	sum(CASE WHEN Col=1 and Month=3 then Amount end) as MarExpense,sum(CASE WHEN Col=2 and Month=3 then Amount end) as MarBudget,
	sum(CASE WHEN Col=1 and Month=4 then Amount end) as AprExpense,sum(CASE WHEN Col=2 and Month=4 then Amount end) as AprBudget,
	sum(CASE WHEN Col=1 and Month=5 then Amount end) as MayExpense,sum(CASE WHEN Col=2 and Month=5 then Amount end) as MayBudget,
	sum(CASE WHEN Col=1 and Month=6 then Amount end) as JunExpense,sum(CASE WHEN Col=2 and Month=6 then Amount end) as JunBudget,
	sum(CASE WHEN Col=1 and Month=7 then Amount end) as JulExpense,sum(CASE WHEN Col=2 and Month=7 then Amount end) as JulBudget,
	sum(CASE WHEN Col=1 and Month=8 then Amount end) as AugExpense,sum(CASE WHEN Col=2 and Month=8 then Amount end) as AugBudget,
	sum(CASE WHEN Col=1 and Month=9 then Amount end) as SepExpense,sum(CASE WHEN Col=2 and Month=9 then Amount end) as SepBudget,
	sum(CASE WHEN Col=1 and Month=10 then Amount end) as OctExpense,sum(CASE WHEN Col=2 and Month=10 then Amount end) as OctBudget,
	sum(CASE WHEN Col=1 and Month=11 then Amount end) as NovExpense,sum(CASE WHEN Col=2 and Month=11 then Amount end) as NovBudget,
	sum(CASE WHEN Col=1 and Month=12 then Amount end) as DecExpense,sum(CASE WHEN Col=2 and Month=12 then Amount end) as DecBudget				
					
					,sum(CASE WHEN Col=1 then Amount end) as TotalExpense,sum(CASE WHEN Col=2 then Amount end) as TotalBudget from arrange a left join acctg_1chartofaccounts ca on ca.AccountID=a.AccountID where Budgeted=\'1\' Group By ShortAcctID ORDER By ShortAcctID';
					// echo $sqltotal.'<br><br>';
				$stmttotal=$link->prepare($sqltotal); $stmttotal->execute();
					
					$sql='SELECT Account, 
					format(JanBudget,2) as JanBudget, format(JanExpense,2) as JanExpense, 
					format(FebBudget,2) as FebBudget, format(FebExpense,2) as  FebExpense,
					format(MarBudget,2) as MarBudget, format(MarExpense,2) as MarExpense,
					format(AprBudget,2) as AprBudget, format(AprExpense,2) as AprExpense,
					format(MayBudget,2) as MayBudget, format(MayExpense,2) as MayExpense,
					format(JunBudget,2) as JunBudget, format(JunExpense,2) as JunExpense,
					format(JulBudget,2) as JulBudget, format(JulExpense,2) as JulExpense,
					format(AugBudget,2) as AugBudget, format(AugExpense,2) as AugExpense,
					format(SepBudget,2) as SepBudget, format(SepExpense,2) as SepExpense,
					format(OctBudget,2) as OctBudget, format(OctExpense,2) as OctExpense,
					format(NovBudget,2) as NovBudget, format(NovExpense,2) as NovExpense,
					format(DecBudget,2) as DecBudget, format(DecExpense,2) as DecExpense,
					
			format((JanBudget+FebBudget+MarBudget),2) as Q1Budget, format((JanExpense+FebExpense+MarExpense),2) as Q1Expenses,
			format((AprBudget+MayBudget+JunBudget),2) as Q2Budget, format((AprExpense+MayExpense+JunExpense),2) as Q2Expenses,
			format((JulBudget+AugBudget+SepBudget),2) as Q3Budget, format((JulExpense+AugExpense+SepExpense),2) as Q3Expenses,
			format((OctBudget+NovBudget+DecBudget),2) as Q4Budget, format((OctExpense+NovExpense+DecExpense),2) as Q4Expenses,
					
			format(TotalBudget,2) as TotalBudget, format(TotalExpense,2) as TotalExpense
					FROM TotalExpensesAndTotalBudget';
					if($switchvaluetotal==1){
					$columnnames=array('Account','JanBudget','JanExpense','FebBudget','FebExpense','MarBudget','MarExpense','AprBudget','AprExpense','MayBudget','MayExpense','JunBudget','JunExpense','JulBudget','JulExpense','AugBudget','AugExpense','SepBudget','SepExpense','OctBudget','OctExpense','NovBudget','NovExpense','DecBudget','DecExpense','TotalBudget','TotalExpense');
					}else{
					$columnnames=array('Account','Q1Budget','Q1Expenses','Q2Budget','Q2Expenses','Q3Budget','Q3Expenses','Q4Budget','Q4Expenses','TotalBudget','TotalExpense');	
					}
					// echo $sql.'<br><br>';
					include('../backendphp/layout/displayastablenosort.php');
					
					unset($title);
					$sqlst='Create temporary table st as Select ShortAcctID as Account,
	sum(CASE WHEN Col=1 and Month=1 then Amount end) as JanExpense,sum(CASE WHEN Col=2 and Month=1 then Amount end) as JanBudget,
	sum(CASE WHEN Col=1 and Month=2 then Amount end) as FebExpense,sum(CASE WHEN Col=2 and Month=2 then Amount end) as FebBudget,
	sum(CASE WHEN Col=1 and Month=3 then Amount end) as MarExpense,sum(CASE WHEN Col=2 and Month=3 then Amount end) as MarBudget,
	sum(CASE WHEN Col=1 and Month=4 then Amount end) as AprExpense,sum(CASE WHEN Col=2 and Month=4 then Amount end) as AprBudget,
	sum(CASE WHEN Col=1 and Month=5 then Amount end) as MayExpense,sum(CASE WHEN Col=2 and Month=5 then Amount end) as MayBudget,
	sum(CASE WHEN Col=1 and Month=6 then Amount end) as JunExpense,sum(CASE WHEN Col=2 and Month=6 then Amount end) as JunBudget,
	sum(CASE WHEN Col=1 and Month=7 then Amount end) as JulExpense,sum(CASE WHEN Col=2 and Month=7 then Amount end) as JulBudget,
	sum(CASE WHEN Col=1 and Month=8 then Amount end) as AugExpense,sum(CASE WHEN Col=2 and Month=8 then Amount end) as AugBudget,
	sum(CASE WHEN Col=1 and Month=9 then Amount end) as SepExpense,sum(CASE WHEN Col=2 and Month=9 then Amount end) as SepBudget,
	sum(CASE WHEN Col=1 and Month=10 then Amount end) as OctExpense,sum(CASE WHEN Col=2 and Month=10 then Amount end) as OctBudget,
	sum(CASE WHEN Col=1 and Month=11 then Amount end) as NovExpense,sum(CASE WHEN Col=2 and Month=11 then Amount end) as NovBudget,
	sum(CASE WHEN Col=1 and Month=12 then Amount end) as DecExpense,sum(CASE WHEN Col=2 and Month=12 then Amount end) as DecBudget,				
					sum(CASE WHEN Col=1 then Amount end) as TotalExpense,sum(CASE WHEN Col=2 then Amount end) as TotalBudget from arrange a left join acctg_1chartofaccounts ca on ca.AccountID=a.AccountID where Budgeted=\'1\' Group By ShortAcctID';
					// echo $sqlst.'<br><br>';
					$stmtst=$link->prepare($sqlst); $stmtst->execute();
					
					$title='';
					$formdesc='GrandTotals';
					if($switchvaluetotal==1){
					$columnnames=array('JanBudget','JanExpense','FebBudget','FebExpense','MarBudget','MarExpense','AprBudget','AprExpense','MayBudget','MayExpense','JunBudget','JunExpense','JulBudget','JulExpense','AugBudget','AugExpense','SepBudget','SepExpense','OctBudget','OctExpense','NovBudget','NovExpense','DecBudget','DecExpense','GrandTotalBudget','GrandTotalExpense');
					}else{
					$columnnames=array('Q1Budget','Q1Expenses','Q2Budget','Q2Expenses','Q3Budget','Q3Expenses','Q4Budget','Q4Expenses','GrandTotalBudget','GrandTotalExpense');	
					}
					$sql='select
					format(sum(JanBudget),2) as JanBudget,format(sum(JanExpense),2) as JanExpense,
					format(sum(FebBudget),2) as FebBudget,format(sum(FebExpense),2) as FebExpense,
					format(sum(MarBudget),2) as MarBudget,format(sum(MarExpense),2) as MarExpense,
					format(sum(AprBudget),2) as AprBudget,format(sum(AprExpense),2) as AprExpense,
					format(sum(MayBudget),2) as MayBudget,format(sum(MayExpense),2) as MayExpense,
					format(sum(JunBudget),2) as JunBudget,format(sum(JunExpense),2) as JunExpense,
					format(sum(JulBudget),2) as JulBudget,format(sum(JulExpense),2) as JulExpense,
					format(sum(AugBudget),2) as AugBudget,format(sum(AugExpense),2) as AugExpense,
					format(sum(SepBudget),2) as SepBudget,format(sum(SepExpense),2) as SepExpense,
					format(sum(OctBudget),2) as OctBudget,format(sum(OctExpense),2) as OctExpense,
					format(sum(NovBudget),2) as NovBudget,format(sum(NovExpense),2) as NovExpense,
					format(sum(DecBudget),2) as DecBudget,format(sum(DecExpense),2) as DecExpense,
					
			format((sum(JanBudget)+sum(FebBudget)+sum(MarBudget)),2) as Q1Budget,
			format((sum(JanExpense)+sum(FebExpense)+sum(MarExpense)),2) as Q1Expenses,
			format((sum(AprBudget)+sum(MayBudget)+sum(JunBudget)),2) as Q2Budget,
			format((sum(AprExpense)+sum(MayExpense)+sum(JunExpense)),2) as Q2Expenses,
			format((sum(JulBudget)+sum(AugBudget)+sum(SepBudget)),2) as Q3Budget,
			format((sum(JulExpense)+sum(AugExpense)+sum(SepExpense)),2) as Q3Expenses,
			format((sum(OctBudget)+sum(NovBudget)+sum(DecBudget)),2) as Q4Budget,
			format((sum(OctExpense)+sum(NovExpense)+sum(DecExpense)),2) as Q4Expenses,
			
					format(sum(TotalExpense),2) as GrandTotalExpense,format(sum(TotalBudget),2) as GrandTotalBudget

					from st';
					
					include('../backendphp/layout/displayastablenosort.php');
					
					exit();
					
				}
				
				if(isset($_POST['submit'])){
					
				//switch	
					if(!isset($_POST['switchvalue'])){
						$switchvalue=1;
						$switchlabel='Per Quarter';
					}
					if(isset($_POST['switchvalue'])){
						if($_POST['switchvalue']==1){
								$switchvalue=0;
								$switchlabel='Per Month';
						}else{
							$switchvalue=1;
							$switchlabel='Per Quarter';
						}
					}
				//
					
					echo'</br><form method="post" action="fs.php?w=DeptMonth">
				<input type="submit" name="filter" value="'.$switchlabel.'">
				<input type="hidden" name="entity" value="'.$_REQUEST['entity'].'">
				<input type="hidden" name="submit">
				<input type="hidden" name="switchvalue" value="'.$switchvalue.'">
				</form>';
					
	//arrange unialltxns 
    $sql='Create Temporary table Actual as select ut.AccountID,ShortAcctID,FromBudgetOf,sum(Amount) as Actual,month(Date) as month from '.$currentyr.'_static.acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where FromBudgetOf=\''.$entity.'\' and ControlNo not like \'%BegBal\' and Budgeted=\'1\' Group By month(Date),AccountID,FromBudgetOf;';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	
		$sqla='Create Temporary table MonthlyActual as select ShortAcctID,AccountID,FromBudgetOf,
				sum(CASE WHEN Month=1 THEN IFNULL(`Actual`,0) END) AS JanActual,sum(CASE WHEN Month=2 THEN IFNULL(`Actual`,0) END) AS FebActual,
				sum(CASE WHEN Month=3 THEN IFNULL(`Actual`,0) END) AS MarActual,sum(CASE WHEN Month=4 THEN IFNULL(`Actual`,0) END) AS AprActual,
				sum(CASE WHEN Month=5 THEN IFNULL(`Actual`,0) END) AS MayActual, sum(CASE WHEN Month=6 THEN IFNULL(`Actual`,0) END) AS JunActual,
				sum(CASE WHEN Month=7 THEN IFNULL(`Actual`,0) END) AS JulActual, sum(CASE WHEN Month=8 THEN IFNULL(`Actual`,0) END) AS AugActual,
				sum(CASE WHEN Month=9 THEN IFNULL(`Actual`,0) END) AS SepActual, sum(CASE WHEN Month=10 THEN IFNULL(`Actual`,0) END) AS OctActual,
			    sum(CASE WHEN Month=11 THEN IFNULL(`Actual`,0) END) AS NovActual, sum(CASE WHEN Month=12 THEN IFNULL(`Actual`,0) END) AS `DecActual`
				from Actual Group By AccountID,FromBudgetOf';
		$stmta=$link->prepare($sqla); $stmta->execute();		
		$sqlb='Create Temporary table MonthlyBudget as select ShortAcctID,b.AccountID,EntityID AS FromBudgetOf,
				sum(CASE WHEN Month=1 THEN IFNULL(`Budget`,0) END) AS JanBudget,sum(CASE WHEN Month=2 THEN IFNULL(`Budget`,0) END) AS FebBudget,
				sum(CASE WHEN Month=3 THEN IFNULL(`Budget`,0) END) AS MarBudget,sum(CASE WHEN Month=4 THEN IFNULL(`Budget`,0) END) AS AprBudget,
				sum(CASE WHEN Month=5 THEN IFNULL(`Budget`,0) END) AS MayBudget,sum(CASE WHEN Month=6 THEN IFNULL(`Budget`,0) END) AS JunBudget,
				sum(CASE WHEN Month=7 THEN IFNULL(`Budget`,0) END) AS JulBudget,sum(CASE WHEN Month=8 THEN IFNULL(`Budget`,0) END) AS AugBudget,
				sum(CASE WHEN Month=9 THEN IFNULL(`Budget`,0) END) AS SepBudget,sum(CASE WHEN Month=10 THEN IFNULL(`Budget`,0) END) AS OctBudget,
				sum(CASE WHEN Month=11 THEN IFNULL(`Budget`,0) END) AS NovBudget,sum(CASE WHEN Month=12 THEN IFNULL(`Budget`,0) END) AS `DecBudget`

				from budget_1budgets b left join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID where EntityID=\''.$entity.'\' and Budgeted=\'1\' Group By AccountID,EntityID';
		$stmtb=$link->prepare($sqlb); $stmtb->execute();
		
	
	$formdesc='</br>'.$_POST['entity'].'';

$sqleb='Create temporary table ExpensesWithAndWithoutBudget as select mb.ShortAcctID as Account,mb.AccountID,
	 JanActual, JanBudget, FebActual, FebBudget,
	 MarActual, MarBudget, AprActual, AprBudget,
	 MayActual, MayBudget, JunActual, JunBudget,
	 JulActual, JulBudget, AugActual, AugBudget,
	 SepActual, SepBudget, OctActual, OctBudget,
	 NovActual, NovBudget, DecActual, DecBudget,
	(ifnull(JanBudget,0)+ifnull(FebBudget,0)+ifnull(MarBudget,0)+ifnull(AprBudget,0)+ifnull(MayBudget,0)+ifnull(JunBudget,0)+ifnull(JulBudget,0)+ifnull(AugBudget,0)+ifnull(SepBudget,0)+ifnull(OctBudget,0)+ifnull(NovBudget,0)+ifnull(DecBudget,0)) as TotalBudget,
	(ifnull(JanActual,0)+ifnull(FebActual,0)+ifnull(MarActual,0)+ifnull(AprActual,0)+ifnull(MayActual,0)+ifnull(JunActual,0)+ifnull(JulActual,0)+ifnull(AugActual,0)+ifnull(SepActual,0)+ifnull(OctActual,0)+ifnull(NovActual,0)+ifnull(DecActual,0)) as TotalActual
	
	from MonthlyBudget mb left join MonthlyActual ma on CONCAT(ma.AccountID,ma.FromBudgetOf)=CONCAT(mb.AccountID,mb.FromBudgetOf) left join acctg_1budgetentities be on be.EntityID=mb.FromBudgetOf Where mb.FromBudgetOf=\''.$entity.'\'
	
	UNION ALL
	
	select ma.ShortAcctID as Account,ma.AccountID,
	JanActual, JanBudget, FebActual, FebBudget,
	MarActual, MarBudget, AprActual, AprBudget,
	MayActual, MayBudget, JunActual, JunBudget,
	JulActual, JulBudget,  AugActual, AugBudget,
	SepActual, SepBudget, OctActual, OctBudget,
	NovActual, NovBudget, DecActual, DecBudget,
	(ifnull(JanBudget,0)+ifnull(FebBudget,0)+ifnull(MarBudget,0)+ifnull(AprBudget,0)+ifnull(MayBudget,0)+ifnull(JunBudget,0)+ifnull(JulBudget,0)+ifnull(AugBudget,0)+ifnull(SepBudget,0)+ifnull(OctBudget,0)+ifnull(NovBudget,0)+ifnull(DecBudget,0)) as TotalBudget,
	(ifnull(JanActual,0)+ifnull(FebActual,0)+ifnull(MarActual,0)+ifnull(AprActual,0)+ifnull(MayActual,0)+ifnull(JunActual,0)+ifnull(JulActual,0)+ifnull(AugActual,0)+ifnull(SepActual,0)+ifnull(OctActual,0)+ifnull(NovActual,0)+ifnull(DecActual,0)) as TotalActual
	
	from MonthlyBudget mb right join MonthlyActual ma on CONCAT(ma.AccountID,ma.FromBudgetOf)=CONCAT(mb.AccountID,mb.FromBudgetOf) left join acctg_1budgetentities be on be.EntityID=ma.FromBudgetOf Where ma.FromBudgetOf=\''.$entity.'\' AND (ifnull(JanBudget,0)+ifnull(FebBudget,0)+ifnull(MarBudget,0)+ifnull(AprBudget,0)+ifnull(MayBudget,0)+ifnull(JunBudget,0)+ifnull(JulBudget,0)+ifnull(AugBudget,0)+ifnull(SepBudget,0)+ifnull(OctBudget,0)+ifnull(NovBudget,0)+ifnull(DecBudget,0))=0 ORDER BY Account
	
	';
	$stmteb=$link->prepare($sqleb); $stmteb->execute();
	// echo $sql; exit();
	$sql='select AccountID,Account,
	format(JanBudget,2) as JanBudget, format(JanActual,2) as JanActual,
	format(FebBudget,2) as FebBudget, format(FebActual,2) as FebActual,
	format(MarBudget,2) as MarBudget, format(MarActual,2) as MarActual,
	format(AprBudget,2) as AprBudget, format(AprActual,2) as AprActual,
	format(MayBudget,2) as MayBudget, format(MayActual,2) as MayActual,
	format(JunBudget,2) as JunBudget, format(JunActual,2) as JunActual,
	format(JulBudget,2) as JulBudget, format(JulActual,2) as JulActual,
	format(AugBudget,2) as AugBudget, format(AugActual,2) as AugActual,
	format(SepBudget,2) as SepBudget, format(SepActual,2) as SepActual,
	format(OctBudget,2) as OctBudget, format(OctActual,2) as OctActual,
	format(NovBudget,2) as NovBudget, format(NovActual,2) as NovActual,
	format(DecBudget,2) as DecBudget, format(DecActual,2) as DecActual,
	
	format((JanBudget+FebBudget+MarBudget),2) as Q1Budget, format((JanActual+FebActual+MarActual),2) as Q1Actual,
	format((AprBudget+MayBudget+JunBudget),2) as Q2Budget, format((AprActual+MayActual+JunActual),2) as Q2Actual,
	format((JulBudget+AugBudget+SepBudget),2) as Q3Budget, format((JulActual+AugActual+SepActual),2) as Q3Actual,
	format((OctBudget+NovBudget+DecBudget),2) as Q4Budget, format((OctActual+NovActual+DecActual),2) as Q4Actual,
	
	format(TotalBudget,2) as TotalBudget, format(TotalActual,2) as TotalActual
	
	from ExpensesWithAndWithoutBudget';
	
		$title='';
		$formdesc='</i><b>'.$_POST['entity'].'</b>';

		if($switchvalue==1){
		$columnnames=array('Account','JanBudget','JanActual','FebBudget','FebActual','MarBudget','MarActual','AprBudget','AprActual','MayBudget','MayActual','JunBudget','JunActual','JulBudget','JulActual','AugBudget','AugActual','SepBudget','SepActual','OctBudget','OctActual','NovBudget','NovActual','DecBudget','DecActual','TotalBudget','TotalActual');
		}else{
		$columnnames=array('Account','Q1Budget','Q1Actual','Q2Budget','Q2Actual','Q3Budget','Q3Actual','Q4Budget','Q4Actual','TotalBudget','TotalActual');
		}
		$txnidname='AccountID';
		$editprocess='fs.php?w=Lookup&FromBudgetOf='.$entity.'&AccountID=';
		$editprocesslabel='Lookup';
		
		include('../backendphp/layout/displayastablenosort.php');
		
		
	
	//totals table
	unset($formdesc);unset($title);unset($sql);unset($columnnames);unset($editprocess);
	$title='';
	$formdesc='</i><b>Totals</b>';
	$sql='select 
	format(sum(JanBudget),2) as JanBudget, format(sum(JanActual),2) as JanActual,
	format(sum(FebBudget),2) as FebBudget, format(sum(FebActual),2) as FebActual,
	format(sum(MarBudget),2) as MarBudget, format(sum(MarActual),2) as MarActual,
	format(sum(AprBudget),2) as AprBudget, format(sum(AprActual),2) as AprActual,
	format(sum(MayBudget),2) as MayBudget, format(sum(MayActual),2) as MayActual,
	format(sum(JunBudget),2) as JunBudget, format(sum(JunActual),2) as JunActual,
	format(sum(JulBudget),2) as JulBudget, format(sum(JulActual),2) as JulActual,
	format(sum(AugBudget),2) as AugBudget, format(sum(AugActual),2) as AugActual,
	format(sum(SepBudget),2) as SepBudget, format(sum(SepActual),2) as SepActual,
	format(sum(OctBudget),2) as OctBudget, format(sum(OctActual),2) as OctActual,
	format(sum(NovBudget),2) as NovBudget, format(sum(NovActual),2) as NovActual,
	format(sum(DecBudget),2) as DecBudget, format(sum(DecActual),2) as DecActual,
	
	format((sum(JanBudget)+sum(FebBudget)+sum(MarBudget)),2) as Q1Budget,
	format((sum(JanActual)+sum(FebActual)+sum(MarActual)),2) as Q1Actual,
	format((sum(AprBudget)+sum(MayBudget)+sum(JunBudget)),2) as Q2Budget,
	format((sum(AprActual)+sum(MayActual)+sum(JunActual)),2) as Q2Actual,
	format((sum(JulBudget)+sum(AugBudget)+sum(SepBudget)),2) as Q3Budget,
	format((sum(JulActual)+sum(AugActual)+sum(SepActual)),2) as Q3Actual,
	format((sum(OctBudget)+sum(NovBudget)+sum(DecBudget)),2) as Q4Budget,
	format((sum(OctActual)+sum(NovActual)+sum(DecActual)),2) as Q4Actual,
	
	format(sum(TotalBudget),2) as GrandTotalBudget, format(sum(TotalActual),2) as GrandTotalActual
	
	from ExpensesWithAndWithoutBudget ';
	if($switchvalue==1){
	$columnnames=array('JanBudget','JanActual','FebBudget','FebActual','MarBudget','MarActual','AprBudget','AprActual','MayBudget','MayActual','JunBudget','JunActual','JulBudget','JulActual','AugBudget','AugActual','SepBudget','SepActual','OctBudget','OctActual','NovBudget','NovActual','DecBudget','DecActual','GrandTotalBudget','GrandTotalActual');
	}else{
	$columnnames=array('Q1Budget','Q1Actual','Q2Budget','Q2Actual','Q3Budget','Q3Actual','Q4Budget','GrandTotalBudget','GrandTotalActual');
	}
	include('../backendphp/layout/displayastablenosort.php');
	
	
	
	
		
	
	
	
	}



	//Account
	if(isset($_POST['LookupAccount'])){
					
		//switch	
			if(!isset($_POST['switchvalue'])){
				$switchvalue=1;
				$switchlabel='Per Quarter';
			}
			if(isset($_POST['switchvalue'])){
				if($_POST['switchvalue']==1){
						$switchvalue=0;
						$switchlabel='Per Month';
				}else{
					$switchvalue=1;
					$switchlabel='Per Quarter';
				}
			}
		//
			
			echo'</br><form method="post" action="fs.php?w=DeptMonth">
		<input type="submit" name="filter" value="'.$switchlabel.'">
		<input type="hidden" name="Account" value="'.$_REQUEST['Account'].'">
		<input type="hidden" name="LookupAccount">
		<input type="hidden" name="switchvalue" value="'.$switchvalue.'">
		</form>';
			
//arrange unialltxns 
$sql='Create Temporary table Actual as select ut.AccountID,ShortAcctID,FromBudgetOf,sum(Amount) as Actual,month(Date) as month from '.$currentyr.'_static.acctg_0unialltxns ut left join acctg_1chartofaccounts ca on ca.AccountID=ut.AccountID where ut.AccountID=\''.$accountid.'\' and ControlNo not like \'%BegBal\' and Budgeted=\'1\' Group By month(Date),AccountID,FromBudgetOf;';
// echo $sql.'<br><br>';
$stmt=$link->prepare($sql); $stmt->execute();

$sqla='Create Temporary table MonthlyActual as select FromBudgetOf,
		sum(CASE WHEN Month=1 THEN IFNULL(`Actual`,0) END) AS JanActual,sum(CASE WHEN Month=2 THEN IFNULL(`Actual`,0) END) AS FebActual,
		sum(CASE WHEN Month=3 THEN IFNULL(`Actual`,0) END) AS MarActual,sum(CASE WHEN Month=4 THEN IFNULL(`Actual`,0) END) AS AprActual,
		sum(CASE WHEN Month=5 THEN IFNULL(`Actual`,0) END) AS MayActual, sum(CASE WHEN Month=6 THEN IFNULL(`Actual`,0) END) AS JunActual,
		sum(CASE WHEN Month=7 THEN IFNULL(`Actual`,0) END) AS JulActual, sum(CASE WHEN Month=8 THEN IFNULL(`Actual`,0) END) AS AugActual,
		sum(CASE WHEN Month=9 THEN IFNULL(`Actual`,0) END) AS SepActual, sum(CASE WHEN Month=10 THEN IFNULL(`Actual`,0) END) AS OctActual,
		sum(CASE WHEN Month=11 THEN IFNULL(`Actual`,0) END) AS NovActual, sum(CASE WHEN Month=12 THEN IFNULL(`Actual`,0) END) AS `DecActual`
		from Actual Group By FromBudgetOf';
		// echo $sqla.'<br><br>';
$stmta=$link->prepare($sqla); $stmta->execute();

$sqlb='Create Temporary table MonthlyBudget as select EntityID AS FromBudgetOf,
		sum(CASE WHEN Month=1 THEN IFNULL(`Budget`,0) END) AS JanBudget,sum(CASE WHEN Month=2 THEN IFNULL(`Budget`,0) END) AS FebBudget,
		sum(CASE WHEN Month=3 THEN IFNULL(`Budget`,0) END) AS MarBudget,sum(CASE WHEN Month=4 THEN IFNULL(`Budget`,0) END) AS AprBudget,
		sum(CASE WHEN Month=5 THEN IFNULL(`Budget`,0) END) AS MayBudget,sum(CASE WHEN Month=6 THEN IFNULL(`Budget`,0) END) AS JunBudget,
		sum(CASE WHEN Month=7 THEN IFNULL(`Budget`,0) END) AS JulBudget,sum(CASE WHEN Month=8 THEN IFNULL(`Budget`,0) END) AS AugBudget,
		sum(CASE WHEN Month=9 THEN IFNULL(`Budget`,0) END) AS SepBudget,sum(CASE WHEN Month=10 THEN IFNULL(`Budget`,0) END) AS OctBudget,
		sum(CASE WHEN Month=11 THEN IFNULL(`Budget`,0) END) AS NovBudget,sum(CASE WHEN Month=12 THEN IFNULL(`Budget`,0) END) AS `DecBudget`

		from budget_1budgets b left join acctg_1chartofaccounts ca on ca.AccountID=b.AccountID where b.AccountID=\''.$accountid.'\' and Budgeted=\'1\' Group By EntityID';
		// echo $sqlb.'<br><br>';
$stmtb=$link->prepare($sqlb); $stmtb->execute();


$formdesc='</br>'.$_POST['Account'].'';

$sqleb='Create temporary table ExpensesWithAndWithoutBudget as select EntityID,Entity,
JanActual, JanBudget, FebActual, FebBudget,
MarActual, MarBudget, AprActual, AprBudget,
MayActual, MayBudget, JunActual, JunBudget,
JulActual, JulBudget, AugActual, AugBudget,
SepActual, SepBudget, OctActual, OctBudget,
NovActual, NovBudget, DecActual, DecBudget,
(ifnull(JanBudget,0)+ifnull(FebBudget,0)+ifnull(MarBudget,0)+ifnull(AprBudget,0)+ifnull(MayBudget,0)+ifnull(JunBudget,0)+ifnull(JulBudget,0)+ifnull(AugBudget,0)+ifnull(SepBudget,0)+ifnull(OctBudget,0)+ifnull(NovBudget,0)+ifnull(DecBudget,0)) as TotalBudget,
(ifnull(JanActual,0)+ifnull(FebActual,0)+ifnull(MarActual,0)+ifnull(AprActual,0)+ifnull(MayActual,0)+ifnull(JunActual,0)+ifnull(JulActual,0)+ifnull(AugActual,0)+ifnull(SepActual,0)+ifnull(OctActual,0)+ifnull(NovActual,0)+ifnull(DecActual,0)) as TotalActual

from MonthlyBudget mb left join MonthlyActual ma on ma.FromBudgetOf=mb.FromBudgetOf left join acctg_1budgetentities be on be.EntityID=mb.FromBudgetOf 

UNION ALL

select EntityID,Entity,
JanActual, JanBudget, FebActual, FebBudget,
MarActual, MarBudget, AprActual, AprBudget,
MayActual, MayBudget, JunActual, JunBudget,
JulActual, JulBudget,  AugActual, AugBudget,
SepActual, SepBudget, OctActual, OctBudget,
NovActual, NovBudget, DecActual, DecBudget,
(ifnull(JanBudget,0)+ifnull(FebBudget,0)+ifnull(MarBudget,0)+ifnull(AprBudget,0)+ifnull(MayBudget,0)+ifnull(JunBudget,0)+ifnull(JulBudget,0)+ifnull(AugBudget,0)+ifnull(SepBudget,0)+ifnull(OctBudget,0)+ifnull(NovBudget,0)+ifnull(DecBudget,0)) as TotalBudget,
(ifnull(JanActual,0)+ifnull(FebActual,0)+ifnull(MarActual,0)+ifnull(AprActual,0)+ifnull(MayActual,0)+ifnull(JunActual,0)+ifnull(JulActual,0)+ifnull(AugActual,0)+ifnull(SepActual,0)+ifnull(OctActual,0)+ifnull(NovActual,0)+ifnull(DecActual,0)) as TotalActual

from MonthlyBudget mb right join MonthlyActual ma on ma.FromBudgetOf=mb.FromBudgetOf left join acctg_1budgetentities be on be.EntityID=ma.FromBudgetOf Where  (ifnull(JanBudget,0)+ifnull(FebBudget,0)+ifnull(MarBudget,0)+ifnull(AprBudget,0)+ifnull(MayBudget,0)+ifnull(JunBudget,0)+ifnull(JulBudget,0)+ifnull(AugBudget,0)+ifnull(SepBudget,0)+ifnull(OctBudget,0)+ifnull(NovBudget,0)+ifnull(DecBudget,0))=0 ORDER BY Entity

';
// echo $sqleb.'<br><br>';
$stmteb=$link->prepare($sqleb); $stmteb->execute();
// echo $sql; exit();
$sql='select EntityID,Entity,
format(JanBudget,2) as JanBudget, format(JanActual,2) as JanActual,
format(FebBudget,2) as FebBudget, format(FebActual,2) as FebActual,
format(MarBudget,2) as MarBudget, format(MarActual,2) as MarActual,
format(AprBudget,2) as AprBudget, format(AprActual,2) as AprActual,
format(MayBudget,2) as MayBudget, format(MayActual,2) as MayActual,
format(JunBudget,2) as JunBudget, format(JunActual,2) as JunActual,
format(JulBudget,2) as JulBudget, format(JulActual,2) as JulActual,
format(AugBudget,2) as AugBudget, format(AugActual,2) as AugActual,
format(SepBudget,2) as SepBudget, format(SepActual,2) as SepActual,
format(OctBudget,2) as OctBudget, format(OctActual,2) as OctActual,
format(NovBudget,2) as NovBudget, format(NovActual,2) as NovActual,
format(DecBudget,2) as DecBudget, format(DecActual,2) as DecActual,

format((JanBudget+FebBudget+MarBudget),2) as Q1Budget, format((JanActual+FebActual+MarActual),2) as Q1Actual,
format((AprBudget+MayBudget+JunBudget),2) as Q2Budget, format((AprActual+MayActual+JunActual),2) as Q2Actual,
format((JulBudget+AugBudget+SepBudget),2) as Q3Budget, format((JulActual+AugActual+SepActual),2) as Q3Actual,
format((OctBudget+NovBudget+DecBudget),2) as Q4Budget, format((OctActual+NovActual+DecActual),2) as Q4Actual,

format(TotalBudget,2) as TotalBudget, format(TotalActual,2) as TotalActual

from ExpensesWithAndWithoutBudget';
// echo $sql.'<br><br>';
$title='';
$formdesc='</i><b>'.$_POST['Account'].'</b>';

if($switchvalue==1){
$columnnames=array('Entity','JanBudget','JanActual','FebBudget','FebActual','MarBudget','MarActual','AprBudget','AprActual','MayBudget','MayActual','JunBudget','JunActual','JulBudget','JulActual','AugBudget','AugActual','SepBudget','SepActual','OctBudget','OctActual','NovBudget','NovActual','DecBudget','DecActual','TotalBudget','TotalActual');
}else{
$columnnames=array('Entity','Q1Budget','Q1Actual','Q2Budget','Q2Actual','Q3Budget','Q3Actual','Q4Budget','Q4Actual','TotalBudget','TotalActual');
}
$txnidname='EntityID';
$editprocess='fs.php?w=Lookup&AccountID='.$accountid.'&FromBudgetOf=';
$editprocesslabel='Lookup';

include('../backendphp/layout/displayastablenosort.php');



//totals table
unset($formdesc);unset($title);unset($sql);unset($columnnames);unset($editprocess);
$title='';
$formdesc='</i><b>Totals</b>';
$sql='select 
format(sum(JanBudget),2) as JanBudget, format(sum(JanActual),2) as JanActual,
format(sum(FebBudget),2) as FebBudget, format(sum(FebActual),2) as FebActual,
format(sum(MarBudget),2) as MarBudget, format(sum(MarActual),2) as MarActual,
format(sum(AprBudget),2) as AprBudget, format(sum(AprActual),2) as AprActual,
format(sum(MayBudget),2) as MayBudget, format(sum(MayActual),2) as MayActual,
format(sum(JunBudget),2) as JunBudget, format(sum(JunActual),2) as JunActual,
format(sum(JulBudget),2) as JulBudget, format(sum(JulActual),2) as JulActual,
format(sum(AugBudget),2) as AugBudget, format(sum(AugActual),2) as AugActual,
format(sum(SepBudget),2) as SepBudget, format(sum(SepActual),2) as SepActual,
format(sum(OctBudget),2) as OctBudget, format(sum(OctActual),2) as OctActual,
format(sum(NovBudget),2) as NovBudget, format(sum(NovActual),2) as NovActual,
format(sum(DecBudget),2) as DecBudget, format(sum(DecActual),2) as DecActual,

format((sum(JanBudget)+sum(FebBudget)+sum(MarBudget)),2) as Q1Budget,
format((sum(JanActual)+sum(FebActual)+sum(MarActual)),2) as Q1Actual,
format((sum(AprBudget)+sum(MayBudget)+sum(JunBudget)),2) as Q2Budget,
format((sum(AprActual)+sum(MayActual)+sum(JunActual)),2) as Q2Actual,
format((sum(JulBudget)+sum(AugBudget)+sum(SepBudget)),2) as Q3Budget,
format((sum(JulActual)+sum(AugActual)+sum(SepActual)),2) as Q3Actual,
format((sum(OctBudget)+sum(NovBudget)+sum(DecBudget)),2) as Q4Budget,
format((sum(OctActual)+sum(NovActual)+sum(DecActual)),2) as Q4Actual,

format(sum(TotalBudget),2) as GrandTotalBudget, format(sum(TotalActual),2) as GrandTotalActual

from ExpensesWithAndWithoutBudget ';
if($switchvalue==1){
$columnnames=array('JanBudget','JanActual','FebBudget','FebActual','MarBudget','MarActual','AprBudget','AprActual','MayBudget','MayActual','JunBudget','JunActual','JulBudget','JulActual','AugBudget','AugActual','SepBudget','SepActual','OctBudget','OctActual','NovBudget','NovActual','DecBudget','DecActual','GrandTotalBudget','GrandTotalActual');
}else{
$columnnames=array('Q1Budget','Q1Actual','Q2Budget','Q2Actual','Q3Budget','Q3Actual','Q4Budget','GrandTotalBudget','GrandTotalActual');
}
include('../backendphp/layout/displayastablenosort.php');








}
goto noform;
break;


}
$downloadsub=str_replace('<td>','', $downloadsub);
$downloadsub=str_replace('</td>',','.PHP_EOL, $downloadsub);
$downloadsub=str_replace('</tr>',PHP_EOL, $downloadsub);
// echo $downloadsub;
if (allowedToOpen(5332,'1rtc')){
    $filetype='csv';
    echo '<div id="right"><form style="display: inline" action="downloadacctg.php" method="post">
   <input type="submit" name="download" value="Download Report">
   <input type="hidden" name="acctgdata" value="'.$downloadsub.'"><input type="hidden" name="type" value="'.$filetype.'">
   <input type="hidden" name="filename" value="'.$which.'_'.$title.'.'.$filetype.'"></form></div>';
    }

noform:
      $link=null; $stmt=null;
?>