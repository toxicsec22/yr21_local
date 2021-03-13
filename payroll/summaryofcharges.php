<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    ?>

    <br>
    <?php

    
    $whichqry=(!isset($whichqry))?'SLPerClient':$_GET['w'];

switch ($whichqry){
    case 'SLPerClient':
       
        
        $title='Summary of Charges'; 
        $fieldname='Client'; 
        $colorcount=0;
        $rcolor[0]="f6ebf9";
        $rcolor[1]="FFFFFF";

          $monthfrom=1;
          $monthto=date('m');
          ?>
          
            <?php
            
                $showprint=false;
                $clientno=$_SESSION['(ak0)'];

               
                $acctid='(200,202)';
                $acctidarray=array(200,202);

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

    // }
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
        

		
       $sub=$sub.'<td>'.number_format($runtotal,2).'</td></tr>';
       $colorcount++;
   }
   echo '<h3>'.$title.'</h3>';
   $name=comboBoxValue($link,'attend_30currentpositions','IDNo',$_SESSION['(ak0)'],'FullName');
   $sub=$name.'<table><tr>'.$subcol.'<td>Running Balance</td></tr><tbody>'.$sub.'</tbody></table>';
   $sqlsum='Select ifnull(Sum(Case when s.Entry="DR" then s.Amount end),0)+(Select IFNULL(Sum(SumofAmount),0) from slperbegbal a where a.`SuppNo/ClientNo`='.$clientno.') as TotalDebit, ifnull(Sum(Case when s.Entry="CR" then s.Amount end),0) as TotalCredit from  `slper` s where s.`SuppNo/ClientNo`='.$clientno;
   
   $stmt=$link->query($sqlsum);
   $result=$stmt->fetch();
 

   ?>
   <div id="wrap">
    <div id="left"><?php include('../backendphp/layout/lookupreport.php'); ?></div>
    </div>
    <?php
	$link=connect_db("".$currentyr."_1rtc",1);
    $stmt1=$link->prepare("DROP TABLE IF EXISTS `ClearedPayments`;"); $stmt1->execute();
    
    break;


   
}

      $link=null; $stmt=null;
?>