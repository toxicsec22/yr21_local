<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
$allowed=array(7195,554,556,549,555, 554, 5770);
$allow=0;
foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
if ($allow==0) { echo 'No permission'; exit;}
allowed:
// end of check
    

    $showbranches=($_GET['w']=='CashSalePerClient'||$_GET['w']=='CreditLineUsage')?false:true; 
    include_once('../switchboard/contents.php');
    include_once('../backendphp/layout/linkstyle.php');
    echo '</br>';
    ?>
<!--buttons -->
    <div>
    <font size=4 face='sans-serif'>
        <?php if (allowedToOpen(5770,'1rtc')) {?> 
        <a id="link" href='txnhistoryperclient.php?w=CreditLineUsage'>Credit Line Usage</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
        
        <?php if (allowedToOpen(7195,'1rtc')) {?>
        <a id="link" href='txnhistoryperclient.php?w=CashSalePerClient'>Cash Sales Per Client</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>

        <?php if (allowedToOpen(556,'1rtc')) {?> 
        <a id="link" href='txnhistoryperclient.php?w=HoldHistory'>Hold History Per Client</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
        
        <?php if (allowedToOpen(549,'1rtc')) {?>
        <a id='link' href='txnhistoryperclient.php?w=SLPerClient'>AR-SL Per Client</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>

        <?php if (allowedToOpen(555,'1rtc')) {?>
        <a id='link' href='txnhistoryperclient.php?w=CommentsPerClient'>Comments Per Client</a><?php echo str_repeat('&nbsp',5)?><?php } ?>
        
        <?php if (allowedToOpen(554,'1rtc')) {?> 
        <a id='link' href='txnhistoryperclient.php?w=PurchandPay'>Client Purchases and Payments</a><?php echo str_repeat('&nbsp',5)?>
        <?php } ?>
        
    </font></div><br>
    <?php

    
    // $whichqry=(!isset($whichqry))?'Test':$_GET['w'];
    $whichqry=$_GET['w'];

switch ($whichqry){

case 'TxnHistPerClient':
break;

case 'PurchandPay':

    if (!allowedToOpen(554,'1rtc')) { echo 'No permission'; exit;}  

    
    $title='Client Purchases and Payments';
    $fieldname='Client';

    include_once('../generalinfo/lists.inc'); 
    renderlist('clientsemployees');
    ?>
    <form method="post" action="txnhistoryperclient.php?w=PurchandPay" enctype="multipart/form-data">
        For Client:  <input type="text" name="<?php echo $fieldname; ?>" list="clientsemployees"></input>&nbsp &nbsp &nbsp
        <input type="submit" name="lookup" value="Lookup"> </form>
        <?php
        if (!isset($_REQUEST[$fieldname])){
            include('../backendphp/layout/clickontabletoedithead.php');
            goto noform;
        } else {
            include('../backendphp/functions/getnumber.php');
            $clientno=getNumber('ClientEmployee',addslashes($_POST[$fieldname]));
            $title='Purchases and Payments - '.$_REQUEST[$fieldname].' ('.$clientno.')';
            include 'clientaraddlformdesc.php';

            $formdesc='</i>'.$addlformdesc.'<i><br><br> Only CLEARED Collections are counted<br><br>';
            $sql0='CREATE TEMPORARY TABLE clientpurchandpay AS 
            SELECT clp.ClientNo, 0 AS MonthPurchPay, "Purch" as Col, Sum(clp.Balance) AS ClientPurchPay
            FROM `acctg_3unpdclientinvlastperiod` clp WHERE ClientNo='.$clientno.'
            GROUP BY clp.ClientNo
            union all
            SELECT ss.ClientNo, Month(`Date`) AS MonthPurchPay, "Purch" as Col, Sum(ss.Amount) AS ClientPurchPay
            FROM acctg_2salemain sm JOIN acctg_2salesub ss ON sm.TxnId = ss.TxnId
            WHERE (((ss.DebitAccountID)=200) and ClientNo='.$clientno.')
            GROUP BY ss.ClientNo, Month(`Date`)
            union all
            SELECT ds.ClientNo, Month(`Date`) as MonthPurchPay, "Pay" as Col, Sum(ds.Amount) AS Payments
            FROM acctg_2depositmain dm INNER JOIN acctg_2depositsub ds ON dm.TxnID = ds.TxnID
            WHERE (ds.CreditAccountID=200) and (ClientNo='.$clientno.') AND (ds.ForChargeInvNo IS NOT NULL)
            GROUP BY ds.ClientNo, Month(`Date`)
            
            union all
            SELECT cm.ClientNo, Month(`m`.`Cleared`) as MonthPurchPay, "Pay" as Col, Sum(cs.Amount) AS Payments
            FROM acctg_2collectmain cm INNER JOIN acctg_2collectsub cs ON (cm.TxnID = cs.TxnID)
            JOIN `acctg_2depositsub` `s` ON (((`s`.`CRNo` = CONCAT("C-",cm.BranchSeriesNo,"-",`cm`.`CollectNo`))
            AND (`cm`.`CheckBank` = `s`.`CheckDraweeBank`)
            AND (`s`.`CheckNo` = `cm`.`CheckNo`)
            AND (`s`.`BranchNo` = `cs`.`BranchNo`)
            AND (`cm`.`ClientNo` = `s`.`ClientNo`)))
            JOIN `acctg_2depositmain` `m` ON `m`.`TxnID` = `s`.`TxnID`
            WHERE
            (`cs`.`ForChargeInvNo` IS NOT NULL)
            AND (`m`.`Cleared` IS NOT NULL)
            AND (((cs.CreditAccountID)>=200 And (cs.CreditAccountID)<=204 Or (cs.CreditAccountID)=705)) AND cm.ClientNo='.$clientno.'
            GROUP BY cm.ClientNo, Month(`m`.`Cleared`)';
            $stmt=$link->prepare($sql0);
            $stmt->execute();
        }
    $sql='select cpp.ClientNo,  cpp.MonthPurchPay as Month, sum(case when Col="Purch" then ClientPurchPay end) as Purchases, sum(case when Col="Pay" then ClientPurchPay end) as Payments, ifnull(sum(case when Col="Purch" then ClientPurchPay end),0)-ifnull(sum(case when Col="Pay" then ClientPurchPay end),0) as MonthBal  from clientpurchandpay cpp  group by cpp.ClientNo, cpp.MonthPurchPay';

    $coltototal='MonthBal';
    $columnnames=array('Month','Purchases','Payments','MonthBal');
    $showtotals=true; $showgrandtotal=true; $runtotal=true; $width='50%';
    include('../backendphp/layout/displayastablenosort.php');

break;



case 'CashSalePerClient':
    if (!allowedToOpen(7195,'1rtc')) { echo 'No permission'; exit; }  
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    $clientname = isset($_POST['ClientName'])?$_POST['ClientName']:null;
    $width = '30%';
    include_once('../backendphp/layout/linkstyle.php');
    $fieldname='ClientName';
    $title="Cash Sales Per Client";
    include_once('../backendphp/layout/clickontabletoedithead.php'); 
    ?>
    <form method="POST" action="txnhistoryperclient.php?w=<?php echo $whichqry;?>" required = "true">
        For Client: 

        <input type="text" name="ClientName"  list="allclients" size=40 autocomplete="off" value = <?php isset($_POST['ClientName'])?$_POST['ClientName']:null?>>

        <input type="submit" name="print" value="Lookup">
        <br>
        <?php if (isset($_POST['ClientName'])){ ?>
          <br>
          <h4>
            Client Name: <?php echo $clientname?>
        </h4>
        <br>
    <?php } 
    echo comboBox($link,'SELECT Left(`ClientName`,20) as `ClientName`, ClientNo FROM `1clients` WHERE Inactive<>1 ORDER BY ClientName','ClientNo','ClientName','allclients');
	
	include_once "../generalinfo/lists.inc";
	$clientno=getValue($link,'1clients','Left(`ClientName`,20)',addslashes($_POST['ClientName']),'ClientNo');
		
    $sql="SELECT Month(s.Date) as Month, FORMAT(SUM(ss.UnitPrice*ss.Qty),0) AS Amount FROM invty_2sale s
    JOIN invty_2salesub ss
    ON s.TxnID = ss.TxnID
    JOIN 1clients c
    ON c.ClientNo = s.ClientNo
    WHERE c.ClientNo = '".$clientno."'
    GROUP BY Month(s.Date);";
    $sql2="SELECT FORMAT(SUM(ss.UnitPrice*ss.Qty),0) AS Amount FROM invty_2sale s
    JOIN invty_2salesub ss
    ON s.TxnID = ss.TxnID
    JOIN 1clients c
    ON c.ClientNo = s.ClientNo
    WHERE c.ClientNo = '".$clientno."'";
    $columnnames=array('Month','Amount');
    if(isset($_POST['ClientName']))
    {
        $stmt=$link->prepare($sql2);
        $stmt->execute();
        $am=$stmt->fetch();
        $totalsum=$am["Amount"];
        include_once('../backendphp/layout/displayastableonlynoheaders.php');
        if(count($datatoshow)!=0)
        {
            echo '<br>Total Cash Sales of Client is: '.$totalsum;
        }
        if(count($datatoshow)==0)
        {
          echo "<br>No records";
      }
  }

  ?>
    </div>
    </form>
    </body>
<?php break; 
//end off

case 'CreditLineUsage':
    if (!allowedToOpen(5770,'1rtc')) { echo 'No permission'; exit; }  
    $title='Credit Line Usage';
    $formdesc='</br></i><b>Credit Usage:</b> (Purchases This Year / Number of Days to Date) x Number of Days Terms</br>Number of Days to Date: '.date('z').'</br>';
    $sql='Select ss.ClientNo,ClientName,Terms,format(CreditLimit,0) as CreditLimit,format(sum(Amount),0) as PurchasesToDate,format(((sum(Amount)/Dayofyear(SUBDATE(Curdate(),1)))*Terms),0) as AveragePurchases,concat(format(((((sum(Amount)/Dayofyear(SUBDATE(Curdate(),1)))*Terms)/CreditLimit)*100),1),"%") as \'%\'  from acctg_2salesub ss join acctg_2salemain sm on sm.TxnID=ss.TxnID join 1clients c on c.ClientNo=ss.ClientNo WHERE Terms<>0 AND CreditLimit<>0 AND Terms<>1 Group By ss.ClientNo ORDER BY Creditlimit Desc,\'%\' Asc';
    $columnnames=array('ClientNo','ClientName','Terms','CreditLimit','PurchasesToDate','AveragePurchases','%');
    $width='60%';
    include_once('../backendphp/layout/displayastablenosort.php');
    

break;
    
case 'HoldHistory':
    if (!allowedToOpen(556,'1rtc')) { echo 'No permission'; exit; } 
    //to make alternating rows have different colors
    $colorcount=0;
    $rcolor[0]="ddf4d7";
    $rcolor[1]="FFFFFF";

    if (!allowedToOpen(556,'1rtc')) { echo 'No permission'; exit; }  
    $title='Hold History Per Client';
    $fieldname='Client';

    include_once('../generalinfo/lists.inc'); 
    renderlist('clients');
    ?>
    <form style="display: inline"  method="post" action="txnhistoryperclient.php?w=<?php echo $whichqry; ?>" enctype="multipart/form-data">
    For Client:  <input type="text" name="<?php echo $fieldname; ?>" list="clients"></input>
    <input type="submit" name="lookup" value="Lookup"> </form> &nbsp &nbsp &nbsp

    <?php
    if (!isset($_REQUEST[$fieldname])){
        include('../backendphp/layout/clickontabletoedithead.php');
        goto noform;
    } 
    else {
    $showprint=true;
    $formdesc= '<i>'.$_POST[$fieldname].'</i>';
    include('../backendphp/functions/getnumber.php');
    $clientno=getNumber('Client',addslashes($_REQUEST[$fieldname]));
    }
    if (allowedToOpen(5561,'1rtc')) {
    ?>
    <form style="display: inline" method="post" action="prclientcomments.php?w=HoldHistory" enctype="multipart/form-data">
       Reason<input type=textarea name="Reason"></input>Remarks<input type=textarea name="Remarks"></input>
       <input type=hidden name="ClientNo" value="<?php echo $clientno; ?>"></input>
    <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" />
    <input type="submit" name="Submit" value="Hold"> or <input type="submit" name="Submit" value="Reset Hold"> or <input type="submit" name="Submit" value="Allow Temporarily"> or <input type="submit" name="Submit" value="Over Limit"> </form> &nbsp &nbsp &nbsp
    <?php
    }
    $lefttabletitle='<h3></h3>';
    $sqlleft='SELECT h.*, CASE WHEN Hold=1 THEN "Hold" WHEN Hold=0 THEN "Allowed" WHEN Hold=4 THEN "Over Limit" ELSE "Allowed Temporarily" END AS Hold, date_format(h.`TimeStamp`,"%Y-%m-%d") as EncodeDate, concat(e.Nickname, " ", e.Surname) as EncodedBy FROM `comments_5clientsonhold` h left join `1_gamit`.`0idinfo` e on h.EncodedByNo=e.IDNo WHERE h.ClientNo='.$clientno.' ORDER BY h.`TimeStamp` desc;';
 
    $columnnamesleft=array('EncodeDate','Reason', 'Remarks', 'Hold', 'EncodedBy');

    $righttabletitle='<h3>Client of these Branches</h3>';
    $sqlright='SELECT b.Branch FROM `gen_info_1branchesclientsjxn` as j join `1branches` b on b.BranchNo=j.BranchNo where j.ClientNo='.$clientno;
 
    $columnnamesright=array('Branch');

    include('../backendphp/layout/twotablessidebyside.php');

    break;

    case 'CommentsPerClient':

        if (!allowedToOpen(555,'1rtc')) { echo 'No permission'; exit; }  
        $title='Comments Per Client';
        $fieldname='Client';
        include_once('../generalinfo/lists.inc'); 
        renderlist('clients');
        ?>

      <form style="display: inline"  method="post" action="txnhistoryperclient.php?w=CommentsPerClient" enctype="multipart/form-data">
      For Client:  <input type="text" name="<?php echo $fieldname; ?>" list="clients"></input>
      <input type="submit" name="lookup" value="Lookup"> </form> &nbsp &nbsp &nbsp
      <?php
      if (!isset($_REQUEST[$fieldname])){
      include('../backendphp/layout/clickontabletoedithead.php');
      goto noform;
      } else {
      $showprint=true;
      $formdesc= '<i>'.$_POST[$fieldname].'</i>';
      include('../backendphp/functions/getnumber.php');
      $clientno=getNumber('Client',addslashes($_REQUEST[$fieldname]));
      }
      ?>
      <form style="display: inline" method="post" action="prclientcomments.php?w=CommentsPerClient" enctype="multipart/form-data">
         Comment Date<input type=date size=20 name="ContactDate" value="<?php echo date('Y-m-d'); ?>" required=true >
         Add Comment<input type=textarea name="Comment" required=true></input>
         <input type=hidden name="ClientNo" value="<?php echo $clientno; ?>"></input>
      <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']); ?>" />
      <input type="submit" name="Add" value="Submit"> </form> &nbsp &nbsp &nbsp
      <?php
      $lefttabletitle='<h3></h3>';
      $sqlleft='SELECT date_format(cc.ContactDate,"%Y-%m-%d") as ContactDate, max(case when cc.ARComment<>0 then cc.`Comment` end) as ARComments, max(case when cc.ARComment=0 then cc.`Comment` end) as SalesComments, cc.`TimeStamp`, concat(e.Nickname, " ", e.Surname) as EncodedBy FROM `comments_5commentsonclients` cc left join `1_gamit`.`0idinfo` e on cc.EncodedByNo=e.IDNo WHERE cc.ClientNo='.$clientno.' group by cc.Comment ORDER BY cc.`TimeStamp` desc;';
       
      $columnnamesleft=array('ContactDate','SalesComments', 'ARComments', 'EncodedBy','TimeStamp');

      $righttabletitle='<h3>Client of these Branches</h3>';
      $sqlright='SELECT b.Branch FROM `gen_info_1branchesclientsjxn` as j join `1branches` b on b.BranchNo=j.BranchNo where j.ClientNo='.$clientno ;
       
      $columnnamesright=array('Branch');

          include('../backendphp/layout/twotablessidebyside.php');

    break;

    case 'SLPerClient':
        if (!allowedToOpen(549,'1rtc')) { echo 'No permission'; exit; }
        
        
        $title='AR-SL Per Client'; 
        $fieldname='Client'; 
        $colorcount=0;
        $rcolor[0]="f6ebf9";
        $rcolor[1]="FFFFFF";

          $list=allowedToOpen(5502,'1rtc')?'employees':'clientsemployees';
//echo $list;
          
          include_once('../generalinfo/lists.inc'); 
          include('../backendphp/layout/clickontabletoedithead.php');
          renderlist($list);

          $monthfrom=(isset($_REQUEST['Month1'])?$_REQUEST['Month1']:date('m'));
          $monthto=(isset($_REQUEST['Month2'])?$_REQUEST['Month2']:date('m'));
          ?>
          <form method="post" action="txnhistoryperclient.php?w=SLPerClient" enctype="multipart/form-data">
            For Client:  <input type="text" name="<?php echo $fieldname; ?>" list="<?php echo $list; ?>" value="<?php echo (!isset($_REQUEST[$fieldname])?'':$_REQUEST[$fieldname]); ?>"></input>&nbsp &nbsp &nbsp
            From Month (1 - 12):  <input type="text" size=5 name="Month1" value="<?php echo $monthfrom; ?>"></input>&nbsp
            To Month (1 - 12):  <input type="text" size=5 name="Month2" value="<?php echo $monthto; ?>"></input>&nbsp &nbsp &nbsp 
            <input type="submit" name="lookup" value="Lookup"> </form>
            <?php
            if (!isset($_REQUEST[$fieldname])){
                goto noform;
            } else {

                $showprint=true;

                include('../backendphp/functions/getnumber.php');
                $clientno=getNumber('ClientEmployee',addslashes($_REQUEST[$fieldname]));

                include 'clientaraddlformdesc.php';

                $formdesc='<h3 style="color:blue;">For COLLECTION PURPOSES ONLY</h3> <br></i><b>'.$clientno.' - '.$_REQUEST[$fieldname].'</b>'.$addlformdesc.'<br><br>  <i>for the months '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$monthfrom.'-1'))).'&nbsp to '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$monthto.'-1'))).str_repeat('&nbsp',3).'';

                $acctid='(200,202)';
                $acctidarray=array(200,202);
//include('../acctg/sqlphp/sqlalltxnsperaccountpermonth.php');
                include('../acctg/sqlphp/createacctsched.php');
                include('../acctg/sqlphp/createacctbegbal.php');


                $sql0='Create temporary table slper (
                Date date  null,
                ControlNo varchar(150) null,
                `SuppNo/ClientNo` smallint(6) null,
                `Supplier/Customer/Branch` varchar(100) null,
                Particulars varchar(100) null,
                AccountID smallint(6) not null,
                BranchNo smallint(6) not null,
                Amount double null,
                Entry varchar(2) not null,
                w varchar(20) not null,
                TxnID int(11) not null
            )'.$sqlalltxns;
// echo $sql0; break;
            $stmt=$link->prepare($sql0);
            $stmt->execute();

            $sqllastyr='SELECT "Beginning" AS ControlNo, clp.`ClientNo`  as `SuppNo/ClientNo`, clp.`ARAccount`, BranchNo,\'\' as FromBudgetOf, Balance as SumofAmount, "DR" as Entry FROM `acctg_3unpdclientinvlastperiod` clp WHERE clp.`ARAccount` in (200,202)';
            $sql1='Create temporary table slperbegbal (
            ControlNo varchar(150) null,
            `SuppNo/ClientNo` smallint(6) null,
            AccountID smallint(6) not null,
            BranchNo smallint(6) not null,
            SumofAmount double null,
            Entry varchar(2) not null
        )'.($sqllastmonth==''?'':($sqllastmonth.', `SuppNo/ClientNo` UNION ALL ')).$sqllastyr;
// if($_SESSION['(ak0)']==1002){ echo $sql0.'<br><br>'.$sql1; break;}
       // echo$sql1; exit();
        $stmt=$link->prepare($sql1);

        $stmt->execute();

    }
//echo $monthfrom; break;
    $lastmonth=$monthfrom==1?'\''.(($currentyr-1).'-12-31\''):'Last_Day(\''.$currentyr.'-'.($monthfrom-1).'-1\')';
    $sql='SELECT '.$lastmonth.' as Date, "BegBal" as ControlNo, "Beginning Balance" as `Supplier/Customer/Branch`, "" as Particulars, b.Branch, Sum(SumofAmount) as Debit, 0 as Credit, "SLPerClient" as w, 0 as TxnID
    from `1branches` b join slperbegbal beg on b.BranchNo=beg.BranchNo where beg.`SuppNo/ClientNo`='.$clientno.' 
    UNION ALL
    SELECT Date, ControlNo, `Supplier/Customer/Branch`, Particulars, Branch, SUM(Case when Entry="DR" then Amount end) as Debit,SUM(Case when Entry="CR" then Amount*-1 end) as Credit, w, TxnID from `1branches` b join slper sp on sp.BranchNo=b.BranchNo where sp.`SuppNo/ClientNo`='.$clientno.' group by Date, ControlNo, `SuppNo/ClientNo`, Branch, Particulars order by Date,ControlNo';
//echo $sql; break;    
    $main='';
    $columnnames=array();

    $columnsub=array('Date', 'ControlNo', 'Branch','Particulars','Debit','Credit'); 
    $sub='';


    $stmt=$link->query($sql);   $result=$stmt->fetchAll();

    $subcol='';$runtotal=0;
    foreach ($columnsub as $colsub){
        $subcol=$subcol.'<td><font face="arial" size="2">'.$colsub.'</font></td>';
    }
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnsub as $colsub){
            $sub=$sub.'<td>'.$row[$colsub].'</td>';
        }
        $runtotal=$runtotal+((is_null($row['Debit']) or empty($row['Debit']))?0:($row['Debit']))-((is_null($row['Credit']) or empty($row['Credit']))?0:($row['Credit']));
        
        switch ($row['w']){
           case 'Sale':
           case 'Collect':
           case 'Prov':
           case 'Bounced':
           case 'Interbranch':
           $filetoopen='addeditclientside';
           break;
           Case 'Deposit':
           $filetoopen='addeditdep';
           break;
           Case 'Purchase':
           CASE 'CV':
           case 'JV':
           case 'Forex':
           $filetoopen='addeditsupplyside';
           break;
           default:
           $filetoopen='lookupgenacctg';
       }

       $sub=$sub.'<td>'.number_format($runtotal,2).'</td><td><a href="'.$filetoopen.'.php?w='.$row['w'].'&TxnID='.$row['TxnID'].'"  target=_blank>Lookup</a></tr>';
       $colorcount++;
   }
   $sub='<table><tr>'.$subcol.'<td>Running Sum</td><td>Lookup?</td></tr><tbody>'.$sub.'</tbody></table>';
   $sqlsum='Select ifnull(Sum(Case when s.Entry="DR" then s.Amount end),0)+(Select IFNULL(Sum(SumofAmount),0) from slperbegbal a where a.`SuppNo/ClientNo`='.$clientno.') as TotalDebit, ifnull(Sum(Case when s.Entry="CR" then s.Amount end),0) as TotalCredit from  `slper` s where s.`SuppNo/ClientNo`='.$clientno;
   
   $stmt=$link->query($sqlsum);
   $result=$stmt->fetch();
   $total='<b>Totals:'.str_repeat('&nbsp',4).'<font color="maroon">Debit:  '.number_format($result['TotalDebit'],2).str_repeat('&nbsp',7).'Credit:  '.number_format($result['TotalCredit'],2).str_repeat('&nbsp',7).'Net:  '.number_format($result['TotalDebit']+$result['TotalCredit'],2).'</font></b><br><br>';
// echo $sql; break;

   ?>
   <style type="text/css">
    #wrap {
     width:100%;
     margin:0 auto;
 }
 #left {
     float:left;
     width:50%; overflow: auto;
 }
 #right {
     float:right;
     width:50%;overflow: auto;
 }
 thead {color:darkblue;font-family:sans-serif;; font-size: small;}
 tbody {color:black; font-family:sans-serif;; font-size: small;}
 tfoot {color:darkblue;}
 table,th,td
 {
    border:1px solid black;
    border-collapse:collapse;
    padding: 3px;
}
</style><div id="wrap">
    <div id="left"><?php include('../backendphp/layout/lookupreport.php'); ?></div>
    <div id="right"><?php
    $title='';
    $formdesc='<h3 style="color:blue;">ACTUAL CLEARED COLLECTIONS</h3> <br></i><b>'.$clientno.' - '.$_REQUEST[$fieldname].'</b>'.$addlformdesc.'<br><br>  <i>for the months '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$monthfrom.'-1'))).'&nbsp to '.strtoupper(date('F',strtotime(''.$currentyr.'-'.$monthto.'-1'))).str_repeat('&nbsp',3).'</i><br><br>';
    $showprint=FALSE;
//$stmt1=$link->prepare("DROP TABLE IF EXISTS `ClearedPayments`;"); $stmt1->execute();
    $sql1='CREATE TEMPORARY TABLE `ClearedPayments` AS SELECT CONCAT(`om`.`CollectNo`, " Inv", `os`.`ForChargeInvNo`, "/", `om`.`Type`, "/", IFNULL(`om`.`CheckNo`,"")) AS `ClearedPaymt`, `m`.`Cleared`, `om`.`CollectNo` AS CollectNo FROM `acctg_2collectmain` `om`
    JOIN `acctg_2collectsub` `os` ON `om`.`TxnID` = `os`.`TxnID`
    JOIN `acctg_2depositsub` `s` ON `s`.`CRNo` = CONCAT("C-",om.BranchSeriesNo,"-",`om`.`CollectNo`)
    AND IF((ISNULL(`om`.`CheckNo`) OR (`om`.`CheckNo` LIKE "") OR (om.Type<>2)),"1=1",((`s`.`CheckNo` = `om`.`CheckNo`) AND (`om`.`CheckBank` = `s`.`CheckDraweeBank`)))
    AND (`s`.`BranchNo` = `os`.`BranchNo`) AND`om`.`ClientNo` = `s`.`ClientNo`
    JOIN `acctg_2depositmain` `m` ON `m`.`TxnID` = `s`.`TxnID`
    WHERE
    (`os`.`ForChargeInvNo` IS NOT NULL)
    AND (`os`.`CreditAccountID` = 200)
    AND (`m`.`Cleared` IS NOT NULL)
    AND (`om`.`ClientNo`='.$clientno.')'
    ;
    $stmt1=$link->prepare($sql1); $stmt1->execute();
    $sql1='CREATE TEMPORARY TABLE `ClearedPaymt` AS SELECT `ClearedPaymt` FROM `ClearedPayments` GROUP BY `ClearedPaymt`;'; 
    $stmt1=$link->prepare($sql1); $stmt1->execute();
    $sql1='CREATE TEMPORARY TABLE `Cleared` AS SELECT `ClearedPaymt`,`Cleared` FROM `ClearedPayments` GROUP BY `ClearedPaymt`,`Cleared`;'; 
    $stmt1=$link->prepare($sql1); $stmt1->execute();
    $sql1='CREATE TEMPORARY TABLE `CollectNo` AS SELECT  `ClearedPaymt`, `CollectNo` FROM `ClearedPayments` GROUP BY `ClearedPaymt`,`CollectNo`;'; 
    $stmt1=$link->prepare($sql1); $stmt1->execute();

    $sql='SELECT '.$lastmonth.' as Date, "BegBal" as ControlNo, "Beginning Balance" as `Supplier/Customer/Branch`, "" as Particulars, b.Branch, Sum(SumofAmount) as Amount,Sum(SumofAmount) as Debit, 0 as Credit, "SLPerClient" as w, 0 as TxnID
    FROM `1branches` b join slperbegbal beg on b.BranchNo=beg.BranchNo where beg.`SuppNo/ClientNo`='.$clientno.' 
    UNION ALL
    SELECT (IF((ControlNo LIKE "Collect%") OR (ControlNo LIKE "Prov%") , (SELECT `Cleared` FROM `Cleared` WHERE Particulars like ClearedPaymt ),`Date`)) AS `Date`, `ControlNo`, `Supplier/Customer/Branch`, IF((ControlNo LIKE "Prov%"), (SELECT CONCAT(Particulars," CollectNo.",CollectNo) FROM `CollectNo` WHERE Particulars like ClearedPaymt), Particulars) AS Particulars, Branch, Amount, SUM(Case when Entry="DR" then Amount end) as Debit,SUM(Case when Entry="CR" then Amount*-1 end) as Credit, w, TxnID from `1branches` b join slper sp on sp.BranchNo=b.BranchNo where sp.`SuppNo/ClientNo`='.$clientno.' AND (IF((ControlNo LIKE "Collect%") OR (ControlNo LIKE "Prov%"), Particulars IN (SELECT `ClearedPaymt` FROM `ClearedPaymt`),TRUE)) GROUP BY Date, ControlNo, `SuppNo/ClientNo`, Branch, Particulars
    ORDER BY Date,ControlNo';
    $columnnames=$columnsub; $runsum=true; $coltototal='Amount';
    $sqlsum='Select ifnull(Sum(Case when s.Entry="DR" then s.Amount end),0)+(Select IFNULL(Sum(SumofAmount),0) from slperbegbal a where a.`SuppNo/ClientNo`='.$clientno.') as TotalDebit, ifnull(Sum(Case when s.Entry="CR" then s.Amount end),0) as TotalCredit from  `slper` s where s.`SuppNo/ClientNo`='.$clientno.'  AND (IF((ControlNo LIKE "Collect%") OR (ControlNo LIKE "Prov%"), Particulars IN (SELECT `ClearedPaymt` FROM `ClearedPayments`),TRUE))';
//if($_SESSION['(ak0)']==1002){ echo $sql0.'<br><br>'.$sql1.'<br><br>'.$sql.'<br><br>'.$sqlsum; }

    
    $stmt=$link->query($sqlsum);
    $result=$stmt->fetch();
    $formdesc.='<b>Totals:'.str_repeat('&nbsp',4).'<font color="maroon">Debit:  '.number_format($result['TotalDebit'],2).str_repeat('&nbsp',7).'Credit:  '.number_format($result['TotalCredit'],2).str_repeat('&nbsp',7).'Net:  '.number_format($result['TotalDebit']+$result['TotalCredit'],2).'</font></b><br><br>';
    include('../backendphp/layout/displayastablenosort.php'); ?></div></div>
    <?php
    $stmt1=$link->prepare("DROP TABLE IF EXISTS `ClearedPayments`;"); $stmt1->execute();
    
    break;


    case 'UploadPic':
    $title='Upload Receipt'; 
    $directory='txnhistoryperclient.php?w=UploadPic&TxnID='.$_GET['TxnID'].'';
    echo '<title>'.$title.'</title>';
    if($_GET['w']){
        $txnval=$_GET['TxnID'];
        $show='hidden';
    }

    echo '<br/><br/><form action="uploadreceipt.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="directory" value='.$directory.' size=4"> 
    <input type="'.$show.'" name="UploadID" size=4 autocomplete="off" value='.$txnval.'> <input type="hidden" name="UploadID" value="'.$_GET['TxnID'].'">'.str_repeat('&nbsp;',10).' <input type="file" name="userfile" accept="image/jpg"><input type="submit" name="submit" value="Submit"> 
    </font> </form><br/><br/>';
    

    break;
}
noform:
      $link=null; $stmt=null;
?>