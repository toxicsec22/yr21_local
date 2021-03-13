<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(605,'1rtc')){ echo 'No permission'; exit;}
 
include_once('../switchboard/contents.php');

 $title='Inventory Tools';
$sql0='SELECT MONTH(`DataClosedBy`) as ClosedMonth FROM `00dataclosedby` WHERE (`00dataclosedby`.`ForDB` = 0)';
        $stmt=$link->query($sql0); $res0=$stmt->fetch(); $closedmonth=$res0['ClosedMonth'];
$reportmonth=!isset($_REQUEST['reportmonth'])?$closedmonth:$_REQUEST['reportmonth'];
$company=(!isset($_POST['company'])?'1Rotary':$_REQUEST['company']);
$branch=(!isset($_POST['branch'])?$_SESSION['@brn']:$_REQUEST['branch']);
$waccol=str_pad($reportmonth,2,STR_PAD_LEFT,'0');
$fromtable=(($reportmonth>$closedmonth OR $closedmonth==12)?'`' . $currentyr . '_static`.`invty_unialltxns`':'invty_20uniallposted');
$submitvalue='" size=0px>Category <input type="text" name="Category" list="categories" size=40 autocomplete="off"><input type=submit name="percat" value="Lookup" size=10px>'
        . '&nbsp &nbsp &nbsp<input type=submit name="showall" value="All Categories - Prepare for Download'; 
$action='invtytools.php?w=InvtyMovement';
//include_once('../generalinfo/lists.inc');$liststoshow=array('categories');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT CatNo, Category FROM `invty_1category` ORDER BY Category;','CatNo','Category','categories');
include_once('../backendphp/layout/choosebranchcompanymonth.php'); 
//if (!isset($_GET['w'])) { goto noreport;} else {
//    echo 'Company: '.$_POST['company'].str_repeat('&nbsp;',5).'Branch: '.$_POST['branch'].str_repeat('&nbsp;',5).;
//}
$which=$_GET['w'];

switch ($which){
    case 'InvtyMovement':
        if (!isset($_POST['percat'])){ 
            if (!isset($_POST['showall'])){ echo 'Please choose category.'; goto noreport;} else { $catno=''; $formdesc='All Categories';}
        } else {$catno=' AND i.CatNo='.comboBoxValue ($link,'invty_1category','Category',$_POST['Category'],'CatNo'); $formdesc=$_POST['Category'];}
        //echo 'Legend<br>&nbsp &nbsp &nbsp QtyPurchased = ';
        
        include_once('../backendphp/functions/getnumber.php');
        
        $title=' Invty Movement'; $formdesc=$formdesc.' - '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$reportmonth.'-1')));
        switch ($_POST['groupby']){
            case 0: //Branch 
                $title=$_POST['branch'].$title; 
                $sqlwhere=' JOIN `invty_1items` i ON i.ItemCode=s.ItemCode WHERE MONTH(s.`Date`)<='.$reportmonth.$catno.' AND s.BranchNo='.getNumber('Branch',$_POST['branch']); $sqlgroupby=' GROUP BY s.ItemCode, s.BranchNo';
                break;
            case 1: //Company
                $title=$_POST['company'].$title;  $sqlgroupby=' GROUP BY s.ItemCode, b.CompanyNo';
                $sqlwhere=' JOIN `invty_1items` i ON i.ItemCode=s.ItemCode JOIN `1branches` b ON s.BranchNo=b.BranchNo WHERE MONTH(s.`Date`)<='.$reportmonth.$catno.' AND b.CompanyNo='.getNumber('Company',$_POST['company']);
                break;
            case 2: //All
                if (!allowedToOpen(6051,'1rtc')) { goto noreport;}
                $title='ALL'.$title;  $sqlgroupby=' GROUP BY s.ItemCode'; $sqlwhere=' JOIN `invty_1items` i ON i.ItemCode=s.ItemCode WHERE MONTH(s.`Date`)<='.$reportmonth.$catno;
                break;
            default:
                goto noreport;
                break;
        }
        
       
        $sql0='CREATE TEMPORARY TABLE qtypermonth AS SELECT s.ItemCode, SUM(CASE WHEN '.($reportmonth<=1?'txntype=0':('MONTH(`Date`)<'.$reportmonth)).' THEN Qty END ) AS Beginning, SUM(Qty) AS EndInv FROM '.$fromtable. ' s '.$sqlwhere.$sqlgroupby; //.' HAVING Beginning<>0 AND EndInv<>0';
        //echo $sql0; break;
        $stmt0=$link->prepare($sql0); $stmt0->execute();
      
        $sql0='CREATE TEMPORARY TABLE currenttxns AS SELECT  s.ItemCode, IFNULL(SUM(CASE WHEN txntype IN (6,8) THEN Qty END),0) AS QtyPurchased, 
            IFNULL(SUM(CASE WHEN txntype IN (7) THEN Qty END),0) AS QtyTxfrIn,
            IFNULL(SUM(CASE WHEN txntype IN (4) THEN Qty END),0) AS QtyTxfrOut, 
            IFNULL(SUM(CASE WHEN txntype IN (1,2,10,11) THEN Qty END),0) AS QtySold, 
            IFNULL(SUM(CASE WHEN txntype IN (3,9,12,20,21) THEN Qty END),0) AS QtyAdjEtc, 
            s.BranchNo FROM '.$fromtable. ' s '.$sqlwhere.' AND MONTH(`Date`)='.$reportmonth.$sqlgroupby;
        //echo $sql0; break;
        $stmt0=$link->prepare($sql0); $stmt0->execute();
        
        $sql='SELECT s.ItemCode, c.Category, i.ItemDesc, FORMAT(`'.$waccol.'`,2) AS  WtdAveCost, Beginning, `'.str_pad(($reportmonth-1),2,STR_PAD_LEFT,'0').'` AS BegCost, FORMAT(Beginning*`'.str_pad(($reportmonth-1),2,STR_PAD_LEFT,'0').'`,2) AS BegVal, '
                . ' QtyPurchased, FORMAT(QtyPurchased*`'.$waccol.'`,2) AS QtyPurchasedValue, '
                . ' QtyTxfrIn, FORMAT(QtyPurchased*`'.$waccol.'`,2) AS QtyTxfrInValue, '
                . ' QtyTxfrOut, FORMAT(QtyPurchased*`'.$waccol.'`,2) AS QtyTxfrOutValue, '
                . ' QtySold, FORMAT(QtyPurchased*`'.$waccol.'`,2) AS QtySoldValue, '
                . ' QtyAdjEtc, FORMAT(QtyPurchased*`'.$waccol.'`,2) AS QtyAdjEtcValue, '
                . ' EndInv, FORMAT(EndInv*`'.$waccol.'`,2) AS EndVal FROM qtypermonth s '
                . ' LEFT JOIN currenttxns ct ON s.ItemCode=ct.ItemCode'
                . ' JOIN `' . $currentyr . '_static`.`invty_weightedavecost` wac ON wac.ItemCode=s.ItemCode'
                . ' JOIN `invty_1items` i ON i.ItemCode=s.ItemCode JOIN `invty_1category` c ON c.CatNo=i.CatNo';
        //echo $sql; break;
        $columnnames=array('ItemCode','Category','ItemDesc','Beginning','BegCost','WtdAveCost','QtyPurchased',
            'QtyTxfrIn','QtyTxfrOut','QtySold','QtyAdjEtc','QtyAdjEtc',
            'EndInv');
        if (allowedToOpen(6052,'1rtc')) { array_push($columnnames,'BegVal','QtyPurchasedValue','QtyTxfrInValue','QtyTxfrOutValue','QtySoldValue',
                'QtyAdjEtcValue','QtyAdjEtcValue','EndVal');}
        
        if (!isset($_POST['showall'])){ include('../backendphp/layout/displayastable.php'); goto noreport;}         
        else {
            $sql=$sql.' ORDER BY Category, ItemCode ';
            include('../backendphp/layout/downloadastable.php');
        $filetype='xls';
    echo '<form style="display: inline" action="../acctg/downloadacctg.php" method="post">
   <input type="submit" name="download" value="Download">
   <input type="hidden" name="acctgdata" value="'.$textfordisplay.'">
   <input type="hidden" name="filename" value="'.$title.'_'.$formdesc.'.'.$filetype.'"></form>';
        }        
        
        
        
        break;
}

noreport:
     $link=null; $stmt=null; 
?>