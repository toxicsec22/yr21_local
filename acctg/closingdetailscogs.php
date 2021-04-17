<?php 
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if(!allowedToOpen(6457,'1rtc')) { echo 'No permission'; exit;}

$showbranches=false; include_once('../switchboard/contents.php');
$link=connect_db(''.$currentyr.'_1rtc',1); 

//to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="eeffe6";
        $rcolor[1]="FFFFFF";
       
$which=!isset($_GET['w'])?'COGSList':$_GET['w'];

if (!isset($_REQUEST['month'])){ 
    $closedmonth=((substr($_SESSION['nb4A'],0,4)<$currentyr))?0:date('m', strtotime($_SESSION['nb4A'])); 
    $reportmonth=$closedmonth+1;    
} else { $reportmonth=$_REQUEST['month'];}

$monthform='Choose month (1-12)<input type="text" name="month" autocomplete="off" size="2" style="text-align: center" value="'. $reportmonth.'">';

if(in_array($which,array('COGSList','COGSAdj'))){
    $formdesc='</i><br/><br/><form action="closingdetailscogs.php?w=COGSList&month='. $reportmonth.'" method="POST" style="display: inline;">'. $monthform.'
    <input type="submit" name="submit" value="COGS List">
    </form>';
    $formdesc.=str_repeat('&nbsp;',5).'<form action="closingdetailscogs.php?w=COGSAdj&month='. $reportmonth.'"  method="POST" style="display: inline;">'
            . '<input type="hidden" name="month" autocomplete="off" size="2" style="text-align: center" value="'. $reportmonth.'">'
            . '<input type="submit" name="submit" value="Auto-Encode COGS - Month '.$reportmonth.'"></form><i><br/><br/>';
    
    if (!isset($_REQUEST['month'])){ $formdesc.='<h1>Please choose month<br><br></h1>'; }
    else {    $whichdata='withcurrent'; require('maketables/makefixedacctgdata.php'); require('maketables/makeacctgendinv.php'); }
    
}


switch ($which){
    case 'COGSList':
    $title='COGS Details - '.date('F',strtotime(''.$currentyr.'-'.$reportmonth.'-1'));
    //GOOD ITEMS 
    //(SELECT ShortAcctID FROM acctg_1chartofaccounts WHERE AccountID=800) AS DebitAccount, 
      //  (SELECT ShortAcctID FROM acctg_1chartofaccounts WHERE AccountID=300) AS CreditAccount, 
    $subtitle='GOOD Items:  DR-COGS  CR-Inventory';    
    if (!isset($_REQUEST['month'])){ $sql=''; } 
    else  {
    $sql='SELECT Branch, TRUNCATE((AcctgEndInv-InvtyEndInv),2) as CogsValue, FORMAT((AcctgEndInv-InvtyEndInv),2) as Cogs from acctg_endvalues ev
    JOIN 1branches b ON b.BranchNo=ev.BranchNo';}
    $columnnames=array('Branch','Cogs'); $width='30%'; $coltototal='CogsValue'; $showgrandtotal=true;
        include('../backendphp/layout/displayastable.php');
        echo '<br/><br/><br/>';
    //DEFECTIVE 
    //(SELECT ShortAcctID FROM acctg_1chartofaccounts WHERE AccountID=800) AS DebitAccount, 
        //(SELECT ShortAcctID FROM acctg_1chartofaccounts WHERE AccountID=331) AS CreditAccount,
    $subtitle='DEFECTIVE Items:  DR-COGS  CR-InvtyDefective';    
    $sql='SELECT Branch,  TRUNCATE((AcctgEndInv-InvtyEndInv),2) as DefCogsValue,  FORMAT((AcctgEndInv-InvtyEndInv),2) as DefCogs from acctg_defendvalues ev
            JOIN 1branches b ON b.BranchNo=ev.BranchNo'; $coltototal='DefCogsValue'; $showgrandtotal=true;
    $columnnames=array('Branch','DefCogs');
    include('../backendphp/layout/displayastableonlynoheaders.php');
	exit();
    break;

case 'COGSAdj':
    include_once('../backendphp/functions/checkexists.php');
    include_once($path.'/acrossyrs/commonfunctions/lastnum.php'); 
    $stringtomatch='Cogs'.str_pad($reportmonth,2,'0',STR_PAD_LEFT);
     $jvno=checkExists($stringtomatch,'Remarks','acctg_2jvmain','JVNo',$link);
	if ($jvno==0){
                $jvno=lastNum('JVNo','acctg_2jvmain',((date('Y',strtotime($currentyr.'-01-01')))-2000)*10000+1000000)+1;
                $done=1; 
    
	 
	$sql='INSERT INTO `acctg_2jvmain` SET JVNo='.$jvno.', Posted=0, JVDate=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), Remarks=concat("Cogs","'.str_pad($reportmonth,2,"0",STR_PAD_LEFT).'"), EncodedByNo=\''.$_SESSION['(ak0)'].'\', PostedByNo=\''.$_SESSION['(ak0)'].'\',TimeStamp=Now();'; 
	
        $stmt=$link->prepare($sql); $stmt->execute();
	
	} else { $done=2; }
        
        
	// GOOD ITEMS
	$sqlgood='select *, truncate((AcctgEndInv-InvtyEndInv),0) as Cogs from acctg_endvalues;';
        $stmt=$link->query($sqlgood); $resultgood=$stmt->fetchAll();
	foreach ($resultgood as $row){ // 800 Cogs; 300 Inventory
		$sqlinsert='Insert into `acctg_2jvsub` SET JVNo='.$jvno.', `Date`=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), FromBudgetOf='.$row['BranchNo'].',BranchNo='.$row['BranchNo'].', DebitAccountID=800, CreditAccountID=300, Amount='.($row['Cogs']).',  EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	    $stmt=$link->prepare($sqlinsert);
	    $stmt->execute();
	}
	
	// DEFECTIVE ITEMS
	$sqldefective='select *, truncate((AcctgEndInv-InvtyEndInv),0) as DefCogs from acctg_defendvalues;';
	$stmt=$link->query($sqldefective); $resultdefective=$stmt->fetchAll();
	
	foreach ($resultdefective as $row){ // 800 Cogs; 331 DefectiveInvty
		$sqlinsert='Insert into `acctg_2jvsub` SET JVNo='.$jvno.', `Date`=Last_Day(\''.$currentyr.'-'.$reportmonth.'-1\'), FromBudgetOf='.$row['BranchNo'].',BranchNo='.$row['BranchNo'].', DebitAccountID=800, CreditAccountID=331, Amount='.($row['DefCogs']).',  EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now();'; 
	    $stmt=$link->prepare($sqlinsert);
	    $stmt->execute();
	}
	
	existingcogs:
	header("Location:addeditsupplyside.php?w=JV&JVNo=".$jvno."&done=".$done);
    break;

}
$link=null;