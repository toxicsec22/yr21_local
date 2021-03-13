<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(661,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
include_once('../switchboard/contents.php');

 

$which=(!isset($_GET['r'])?'Sales':$_GET['r']);
$month=(!isset($_GET['m'])?date('m'):$_GET['m']);
?>
<form method="get" style="display:inline"
      action="summaries.php" enctype="multipart/form-data">
                Choose Month (1 - 12):  <input type="text" name="m" value="<?php echo $month; ?>"></input>
                <input type="hidden" value="<?php echo $which; ?>" name="r"></input>
                <input type="submit" name="l" value="Lookup Per Month">
</form>
<?php
switch ($which){
        case 'Sales':
            $sql0='CREATE TEMPORARY TABLE sales AS '
                . 'SELECT sm.TxnID, DebitAccountID AS AccountID, SUM(Amount) AS Sales FROM `acctg_2salemain` sm JOIN `acctg_2salesub` ss ON sm.TxnID=ss.TxnID WHERE MONTH (`Date`)='.$month.' GROUP BY sm.TxnID, DebitAccountID
UNION ALL SELECT sm.TxnID, CreditAccountID, SUM(Amount)*-1 FROM `acctg_2salemain` sm JOIN `acctg_2salesub` ss ON sm.TxnID=ss.TxnID  WHERE MONTH (`Date`)='.$month.' GROUP BY sm.TxnID, CreditAccountID;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
            $sql0='CREATE TEMPORARY TABLE salestotals AS
SELECT sm.TxnID, sm.Date, sm.BranchNo, ss.AccountID, SUM(Sales)*NormBal AS Sales FROM `acctg_2salemain` sm JOIN `sales` ss ON sm.TxnID=ss.TxnID 
JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=ss.AccountID WHERE (ss.AccountID BETWEEN 700 AND 703) OR ss.AccountID=509
GROUP BY sm.TxnID, ss.AccountID;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
            $sql0='CREATE TEMPORARY TABLE salestable AS
SELECT CompanyNo, s.BranchNo, Branch, `Date`, SUM(CASE WHEN s.AccountID=700 THEN Sales END) AS `Vatable(12%)`,
SUM(CASE WHEN s.AccountID=701 THEN Sales END) AS `Vat-Exempt`,
SUM(CASE WHEN s.AccountID=702 THEN Sales END) AS `Zero-Rated`,
SUM(CASE WHEN s.AccountID=703 THEN Sales END) AS `Government(12%)`,
SUM(CASE WHEN s.AccountID=509 THEN Sales END) AS `OutputVAT`
 FROM `salestotals` s JOIN `acctg_1chartofaccounts` ca ON ca.AccountID=s.AccountID 
 JOIN `1branches` b ON b.BranchNo=s.BranchNo GROUP BY s.TxnID ORDER BY BranchNo, Date;'; $stmt0=$link->prepare($sql0); $stmt0->execute();

            $sql='SELECT CompanyName, FORMAT(IFNULL(SUM(`Vatable(12%)`),0),2) AS `Vatable(12%)`, FORMAT(IFNULL(SUM(`Vat-Exempt`),0),2) AS `Vat-Exempt`, '
                    . 'FORMAT(IFNULL(SUM(`Zero-Rated`),0),2) AS `Zero-Rated`, FORMAT(IFNULL(SUM(`Government(12%)`),0),2) AS `Government(12%)`, FORMAT(SUM(`OutputVat`),2) AS `OutputVat`  FROM salestable s '
                    . ' JOIN `1companies` c ON c.CompanyNo=s.CompanyNo GROUP BY s.CompanyNo';
            $columnnames=array('CompanyName','Vatable(12%)','Vat-Exempt','Zero-Rated','Government(12%)','OutputVat');
            echo '<br><br>';
            include('../backendphp/layout/displayastableonlynoheaders.php');       
            $sql1='SELECT s.BranchNo, CONCAT(Company, " - ", Branch) AS Branch FROM salestable s '
                    . ' JOIN `1companies` c ON c.CompanyNo=s.CompanyNo GROUP BY s.BranchNo'; 
            $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
            $sql2='SELECT `Date`,Branch, FORMAT(`Vatable(12%)`,2) AS `Vatable(12%)`, FORMAT(`Vat-Exempt`,2) AS `Vat-Exempt`, '
                    . ' FORMAT(`Zero-Rated`,2) AS `Zero-Rated`, FORMAT(`Government(12%)`,2) AS `Government(12%)`, '
                    . ' FORMAT(OutputVAT,2) AS OutputVat FROM salestable';
            $groupby='BranchNo'; $orderby='ORDER BY `Date`';
            $columnnames1=array('Branch');
            $columnnames2=array('Date','Vatable(12%)','Vat-Exempt','Zero-Rated','Government(12%)','OutputVat');
            $title='Sales Per Month'; $formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$month.'-1')).'<br>'; 
            $sqlsubtotal='SELECT FORMAT(SUM(`Vatable(12%)`),2) AS `Vatable(12%)`, FORMAT(SUM(`Vat-Exempt`),2) AS `Vat-Exempt`, '
                    . 'FORMAT(SUM(`Zero-Rated`),2) AS `Zero-Rated`, FORMAT(SUM(`Government(12%)`),2) AS `Government(12%)`, FORMAT(SUM(`OutputVat`),2) AS `OutputVat` '
                    . 'FROM salestable ';
            $colsubtotals=array('Vatable(12%)','Vat-Exempt','Zero-Rated','Government(12%)','OutputVat');
            include('../backendphp/layout/displayastablewithsub.php');
            $stmt1=null;
            break;
        
        
        default:
            break;
     }
  $link=null; $stmt=null;  $stmt0=null;       
?>