<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  

$showbranches=false; include_once('../switchboard/contents.php');

$method='POST';
$which=(isset($_GET['w'])?$_GET['w']:'PromoList');

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
include_once('../backendphp/layout/linkstyle.php');
?>
<br><div id="section" style="display: block;">

    <div><a id='link' href="promocalc.php?w=PromoList">Promo Lists</a>
    </div><br/>
    
<?php 
switch ($which){

    case 'PromoAction':
        if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  
    $sqlarea='SELECT AreaNo,Area FROM 0area WHERE AreaNo<>0';
    $stmtarea=$link->query($sqlarea); $resultareas=$stmtarea->fetchAll();
    
            if(isset($_GET['TxnID'])){
                $sqle='select PromoBranchNos,PromoName,PromoFromDate,PromoToDate FROM mktg_2promocalcmain WHERE TxnID='.intval($_GET['TxnID']);
                $stmte=$link->query($sqle); $resulte=$stmte->fetch();
                $promoname=$resulte['PromoName'];
                $datefrom=$resulte['PromoFromDate'];
                $dateto=$resulte['PromoToDate'];
                $submitval='Edit Promo';
                $actiond='';
                $actiond='EditMain&TxnID='.$_GET['TxnID'];
                $promobranchnos=$resulte['PromoBranchNos'];
            } else {
                $promoname='';
                $datefrom=date('Y-m-d');
                $dateto=date('Y-m-d');
                $submitval='Add Promo';
                $actiond='AddMain';
                $promobranchnos=-1;
            }

            $title=$submitval;
            echo '<title>'.$title.'</title>';
            echo'<br><h3>'.$title.'</h3><br><div style="padding:5px; background-color:1b3d6d; color:white; width:880px;">';
            
			echo '<form method="post" action="promocalc.php?w='.$actiond.'">
				Promo Name: <input type="text" name="PromoName" size="25" value="'.$promoname.'" required>
                Date FROM: <input type="date" name="PromoFromDate" value="'.$datefrom.'" required>
                Date TO: <input type="date" name="PromoToDate" value="'.$dateto.'" required>
				 <input type="hidden" name="action_token" value="'.($_SESSION['action_token']).'">
				&nbsp; <input type="submit" name="submit" value="'.$submitval.'"><br><i><font style="font-size:9pt;"><br>* Select same price levels ONLY.</font></i><br>';
				
                foreach($resultareas AS $resultarea){
                    $sqlb='select BranchNo, Branch, PriceLevel from 1branches where Active=1 and PseudoBranch=0 AND AreaNo='.$resultarea['AreaNo'].' ORDER BY Branch';
                        $stmtb=$link->query($sqlb); $resultb=$stmtb->fetchAll();
                        $input='';
                       
                        foreach($resultb as $resb){
                            $input.='&nbsp; &nbsp; <input type="checkbox" name="checkbox[]" value="'.$resb['BranchNo'].'" '.(in_array($resb['BranchNo'],explode(",",$promobranchnos)) !== false ? 'checked = "checked"': '').'> '.$resb['Branch'].' <b>'.$resb['PriceLevel'].'</b></br>';
                        }
                        echo '<hr><h3>'.$resultarea['Area'].'</h3>';
                        echo $input;
                    }
				echo '
			</form></div>';
			
			
		// }
    
    
     break;


     case 'PromoList':
        if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  
        $title='Promo Lists';
        $sql='select TxnID,PromoName,PromoBranchNos AS BranchNos,PromoFromDate AS DateFrom,PromoToDate AS DateTo FROM mktg_2promocalcmain pm Order By pm.TimeStamp DESC';
        $columnnames=array('PromoName','BranchNos','DateFrom','DateTo');
        $txnidname='TxnID';
        $editprocess='promocalc.php?w=Lookup&TxnID=';
        $editprocesslabel='Lookup';
        $formdesc='</i><br><a href="promocalc.php?w=PromoAction">Add New Promo</a><i>';
    include('../backendphp/layout/displayastablenosort.php');

    break;

     case 'AddMain':
        if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            $columnstoadd=array('PromoName','PromoFromDate','PromoToDate');
            
            $promobranchno='';
            foreach($_POST['checkbox'] as $branchno){
                $promobranchno.=$branchno.',';
            }
            $promobranchno=substr($promobranchno, 0, -1);
            $sql='';
            foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
            $sql='INSERT INTO `mktg_2promocalcmain` SET PromoBranchNos="'.$promobranchno.'",'.$sql.' EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW()';
            
            $stmt=$link->prepare($sql); $stmt->execute();


            $sql='Select TxnID from `mktg_2promocalcmain` WHERE PromoName="'.addslashes($_POST['PromoName']).'" AND PromoBranchNos="'.$promobranchno.'" AND PromoFromDate="'.$_POST['PromoFromDate'].'" AND PromoToDate="'.$_POST['PromoToDate'].'" ';
	$stmt=$link->query($sql);
	$result=$stmt->fetch();
		
	header("Location:promocalc.php?w=Lookup&TxnID=".$result['TxnID']);

    break;


    case 'EditMain':

        if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
            $columnstoadd=array('PromoName','PromoFromDate','PromoToDate');
            $txnid=$_GET['TxnID'];

            $sqlfetch='SELECT PromoFromDate FROM mktg_2promocalcmain WHERE TxnID='.$txnid;
            $stmtfetch=$link->query($sqlfetch);
            $resultfetch=$stmtfetch->fetch();

            if(''.date('Y-m-d').''>''.$resultfetch['PromoFromDate'].''){
                echo 'You cannot edit. PromoFromDate should be future.'; exit();
            }

            $promobranchno='';
            foreach($_POST['checkbox'] as $branchno){
                $promobranchno.=$branchno.',';
            }
            $promobranchno=substr($promobranchno, 0, -1);
            $sql='';
            foreach ($columnstoadd as $field) { $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
            $sql='UPDATE `mktg_2promocalcmain` SET PromoBranchNos="'.$promobranchno.'",'.$sql.' EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE Posted=0 AND PromoToDate>"'.$_SESSION['nb4'].'" AND TxnID='.$txnid.'';
            
            $stmt=$link->prepare($sql); $stmt->execute();


            $sqlsub='SELECT TxnSubID,ItemCode FROM mktg_2promocalcsub WHERE TxnID='.$txnid;
            $stmtsub=$link->query($sqlsub);
            $resultsub=$stmtsub->fetchAll();



            
            foreach($resultsub AS $res){
              
                $sqlpricelevel='SELECT DISTINCT(PriceLevel) AS PL FROM 1branches WHERE BranchNo IN ('.$promobranchno.')';
                $stmtpricelevel=$link->query($sqlpricelevel);
                $resultpricelevel=$stmtpricelevel->fetch();
                
                $pl='PriceLevel'.$resultpricelevel['PL'];
                $sql0='CREATE TEMPORARY TABLE
                SumQty
                AS
                SELECT (SUM(Qty)) AS SumQtyPerDay FROM invty_2salesub s JOIN invty_2sale m ON m.TxnID=s.TxnID WHERE BranchNo IN ('.$promobranchno.') AND ItemCode='.$res['ItemCode'].' GROUP BY m.Date;';
                $stmt0=$link->prepare($sql0);
                $stmt0->execute();

                $sqlaveqty='SELECT IFNULL(TRUNCATE(AVG(SumQtyPerDay),2),0) AS AveQty FROM SumQty';
                $stmtaveqty=$link->query($sqlaveqty);
                $resultaveqty=$stmtaveqty->fetch();
                $aveqty=$resultaveqty['AveQty'];
                

                $sqlprice='SELECT '.$pl.' FROM invty_5latestminprice WHERE ItemCode='.$res['ItemCode'].'';
                $stmtprice=$link->query($sqlprice);
                $resultprice=$stmtprice->fetch();

                
                $sql='UPDATE `mktg_2promocalcsub` SET AveQty='.$aveqty.', EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now() WHERE TxnSubID='.$res['TxnSubID'].'';
                $stmt=$link->prepare($sql);
                $stmt->execute();


                $sql='DROP TEMPORARY TABLE SumQty;';
                $stmt=$link->prepare($sql);
                $stmt->execute();
            }


	header("Location:promocalc.php?w=Lookup&TxnID=".$txnid);

    break;


    case 'Lookup':
        if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  
        $txnid=intval($_GET['TxnID']);
        
        $sqlmain='Select pcm.*,(DATEDIFF(PromoToDate,PromoFromDate)+1) AS NoOfDays,CONCAT(e.Nickname," ",e.SurName) AS EncodedBy 
        FROM mktg_2promocalcmain pcm
left join `1employees` as e on pcm.EncodedByNo=e.IDNo 
 where TxnID='.$txnid;

 $stmt=$link->query($sqlmain);
 $result=$stmt->fetch();

 $title='Promo: '.$result['PromoName'];
$posted=$result['Posted'];
 $sqlbranches='SELECT GROUP_CONCAT(CONCAT(Branch, " (",PriceLevel,")") ORDER BY Branch) AS Branches FROM 1branches WHERE BranchNo IN ('.$result['PromoBranchNos'].')';
 $stmtbranches=$link->query($sqlbranches);
 $resultbranches=$stmtbranches->fetch();
$branches=$resultbranches['Branches'];

 $noofdays=$result['NoOfDays'];

 if ($result['Posted']==0){
    $columnnamesmain=array('PromoName','PromoFromDate','PromoToDate');
 } else {
 $columnnamesmain=array('PromoName','PromoFromDate','PromoToDate','Posted');
 }
 $main=''; 
 $columnnames=array(
                 array('field'=>'ItemCode', 'type'=>'text','size'=>10,'required'=>true,'list'=>'items'),
                 array('field'=>'PromoPrice', 'type'=>'text','size'=>10, 'required'=>true, 'value'=>0));
 $liststoshow=array();
if($posted==0){
        $editmain='<td><a href="promocalc.php?w=PromoAction&TxnID='.$txnid.'">Edit</a>&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp<a href="promocalc.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=DelMain" OnClick="return confirm(\'Really delete this?\');">Delete</a></td>';
        $editsub=true;
        $columnstoedit=array('PromoPrice','ProjectedAveQty');
    } else {
        $editmain='';
        $editsub=false;
        $columnstoedit=array();
    }
        
        $columnsub=array('ItemCode','Category','ItemDesc','UnitCost','Price','AveQty','AveProfit','PromoPrice','ProjectedAveQty','ProjectedAveProfit');
       

    $showenc=0;
    if ($showenc==1) { array_push($columnnamesmain,'TimeStamp','EncodedBy','PostedByNo'); array_push($columnsub,'TimeStamp','EncodedBy');}
      else {$columnnamesmain=$columnnamesmain; $columnsub=$columnsub;}  
          
    $colno=0; $fieldsinrow=(isset($fieldsinrow)?$fieldsinrow:4);
    foreach ($columnnamesmain as $rowmain){
        $colno=$colno+1;
        $main=$main.'<td><font face="arial" size="2">'.$rowmain.'</font>: '.$result[$rowmain].str_repeat('&nbsp',5).'</td>'.($colno%$fieldsinrow==0?'</tr><tr>':'');
    }
    $main='<table><tr><td colspan=4>Branches: '.$branches.'</td></tr><tr>'.$main.$editmain.'<tr></table>';

    $sqlsub='Select pcs.*,FORMAT(Price,2) AS Price,
    FORMAT(((Price-UnitCost)*AveQty*'.$noofdays.'),2) AS AveProfit,FORMAT(((PromoPrice-UnitCost)*ProjectedAveQty*'.$noofdays.'),2) AS ProjectedAveProfit,FORMAT(UnitCost,2) AS UnitCost, c.Category, i.ItemDesc, e.Nickname as EncodedBy from mktg_2promocalcsub pcs join invty_1items i on i.ItemCode=pcs.ItemCode join invty_1category c on c.CatNo=i.CatNo left join `1employees` as e on pcs.EncodedByNo=e.IDNo 
    JOIN invty_5latestminprice lmp ON lmp.ItemCode=pcs.ItemCode 
    JOIN invty_52latestcost lc ON lc.ItemCode=pcs.ItemCode 
    where TxnID='.$txnid;
    
        $stmt=$link->query($sqlsub);
        $resultsub=$stmt->fetchAll();
        
     
        $action='promocalc.php?w=CalcSub&TxnID='.$txnid;
        
        
        $editprocess='promocalc.php?w=SubEdit&TxnID='.$txnid.'&TxnSubID='; $editprocesslabel='Enter';
        $delprocess='promocalc.php?TxnID='.$txnid.'&action_token='.$_SESSION['action_token'].'&w=DelSub&TxnSubID=';
        $txnsubid='TxnSubID';
    
        $left='90%'; $leftmargin='91%'; $right='9%';
        $nopost=true;
       if ($posted==0){
            $editok=true;
            $paction=1;
            $actionname='Post';
        } else {
            $editok=false;
            $paction=0;
            $actionname='Unpost';
        }

        $formdesc='<form action="promocalc.php?TxnID='.$txnid.'&w=PostUnpost&action_token='.$_SESSION['action_token'].'&action='.$paction.'" method="POST"><input type="submit" value="'.$actionname.'" name="bntUpdate"></form>';
        $withsub=true; include('../backendphp/layout/inputsubform.php');
    break;
        
    case 'PostUnpost':
        if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  
 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
 $txnid=$_GET['TxnID'];
        $sql='UPDATE mktg_2promocalcmain SET Posted='.$_GET['action'].' WHERE PromoToDate>"'.$_SESSION['nb4'].'" AND TxnID='.intval($txnid).'';
        $stmt=$link->prepare($sql);
        $stmt->execute();
    
        header("Location:promocalc.php?w=Lookup&TxnID=".$txnid);

        break;

    case 'DelMain':
        if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';

        $sql='DELETE FROM mktg_2promocalcmain WHERE Posted=0 AND TxnID='.intval($_GET['TxnID']).'';
        $stmt=$link->prepare($sql);
        $stmt->execute();
    
        header("Location:promocalc.php");

    break;

    case 'DelSub':
        if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $txnid=$_GET['TxnID'];
        $sql='DELETE ps.* FROM mktg_2promocalcsub ps JOIN mktg_2promocalcmain pm ON ps.TxnID=pm.TxnID WHERE Posted=0 AND TxnSubID='.intval($_GET['TxnSubID']).'';
        $stmt=$link->prepare($sql);
        $stmt->execute();
        header("Location:promocalc.php?w=Lookup&TxnID=".$txnid);
    break;


    case 'CalcSub':
    
        if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  
    $txnid=intval($_GET['TxnID']);
    $sqlbranches='SELECT PromoBranchNos FROM mktg_2promocalcmain WHERE TxnID='.$txnid.'';
    $stmtbranches=$link->query($sqlbranches);
    $resultbranches=$stmtbranches->fetch();

    $sqlpricelevel='SELECT DISTINCT(PriceLevel) AS PL FROM 1branches WHERE BranchNo IN ('.$resultbranches['PromoBranchNos'].')';
    $stmtpricelevel=$link->query($sqlpricelevel);
    $resultpricelevel=$stmtpricelevel->fetch();
    
    $pl='PriceLevel'.$resultpricelevel['PL'];
    $sql0='CREATE TEMPORARY TABLE
    SumQty
    AS
    SELECT ItemCode, (SUM(Qty)) AS SumQtyPerDay FROM invty_2salesub s JOIN invty_2sale m ON m.TxnID=s.TxnID WHERE BranchNo IN ('.$resultbranches['PromoBranchNos'].') AND ItemCode='.$_POST['ItemCode'].' GROUP BY m.Date;';
    $stmt0=$link->prepare($sql0);
    $stmt0->execute();

    $sqlaveqty='SELECT IFNULL(TRUNCATE(AVG(SumQtyPerDay),2),0) AS AveQty FROM SumQty';
    $stmtaveqty=$link->query($sqlaveqty);
    $resultaveqty=$stmtaveqty->fetch();
    $aveqty=$resultaveqty['AveQty'];
    

    $sqlprice='SELECT '.$pl.' FROM invty_5latestminprice WHERE ItemCode='.$_POST['ItemCode'].'';
    $stmtprice=$link->query($sqlprice);
    $resultprice=$stmtprice->fetch();


    $sqlinsert='INSERT INTO `mktg_2promocalcsub` SET AveQty='.$aveqty.',ProjectedAveQty='.$aveqty.',Price='.$resultprice[$pl].',TxnID='.$txnid.', ';
    $sql='';
    $columnstoadd=array('ItemCode', 'PromoPrice');
    foreach ($columnstoadd as $field) {
        $sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; 
    }
	$sql=$sqlinsert.$sql.' EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
    $stmt=$link->prepare($sql);
	$stmt->execute();

	header("Location:promocalc.php?w=Lookup&TxnID=".$txnid);

break;


case 'SubEdit':

    if (!allowedToOpen(6054,'1rtc')) {   echo 'No permission'; exit;}  
    require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
    $sql='UPDATE `mktg_2promocalcsub` ps JOIN mktg_2promocalcmain pm ON ps.TxnID=pm.TxnID SET PromoPrice="'.$_POST['PromoPrice'].'", ProjectedAveQty="'.$_POST['ProjectedAveQty'].'", ps.EncodedByNo='.$_SESSION['(ak0)'].',ps.TimeStamp=NOW() WHERE Posted=0 AND TxnSubID='.intval($_GET['TxnSubID']).'';
    echo $sql;
    $stmt=$link->prepare($sql); $stmt->execute();

header("Location:promocalc.php?w=Lookup&TxnID=".$_GET['TxnID']);

break;

     }
     
      $link=null; $stmt=null;
?>