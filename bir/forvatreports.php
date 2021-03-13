<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
// check if allowed
        $allowed=array(657,658,659,660);$allow=0;
        foreach ($allowed as $ok) { if (allowedToOpen($ok,'1rtc')) { $allow=($allow+1); goto allowed; } else { $allow=$allow; }}
        if ($allow==0) { echo 'No permission'; exit;}
        allowed:
        // end of check
include_once('../switchboard/contents.php');

 

$which=(!isset($_GET['r'])?'Suppliers':$_GET['r']);
$month=(!isset($_GET['m'])?date('m'):$_GET['m']);
?>
<form method="get" style="display:inline"
      action="forvatreports.php" enctype="multipart/form-data">
                Choose Month (1 - 12):  <input type="text" name="m" value="<?php echo $month; ?>"></input>
                <input type="hidden" value="<?php echo $which; ?>" name="r"></input>
                <input type="submit" name="l" value="Lookup Per Month">
</form>
<?php
switch ($which){
        case 'Suppliers':
            if (!allowedToOpen(660,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
            $sql0='CREATE TEMPORARY TABLE purch AS '
                . 'SELECT RCompany,s.`NameonCheck` AS Supplier,s.TIN, s.Address, ROUND(SUM(ps.Amount),2) AS GrossTaxable, 
                    ROUND(SUM(CASE WHEN DebitAccountID=510 THEN Amount END),2) 
                    AS InputTax FROM `acctg_2purchasemain` m JOIN acctg_2purchasesub ps ON m.TxnID=ps.TxnID JOIN `1suppliers` s ON s.SupplierNo=m.SupplierNo
                    WHERE RCompany IS NOT NULL AND Month(Date)='.$month.' GROUP BY RCompany,TIN;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
            $sql1='SELECT RCompany, CompanyName AS Company FROM purch p JOIN `1companies` c ON c.CompanyNo=p.RCompany GROUP BY RCompany'; 
            $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
            $sql2='SELECT RCompany, Supplier, TIN, Address, FORMAT(GrossTaxable,2) AS GrossTaxable, FORMAT(InputTax,2) AS InputTax FROM purch';
            $groupby='RCompany'; $orderby='ORDER BY Supplier';
            $columnnames1=array('Company');
            $columnnames2=array('Supplier','TIN','Address','GrossTaxable','InputTax');
            $title='Input VAT - Purchases from Regular Suppliers'; $formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$month.'-1')).'<br>'; 
            $sqlsubtotal='SELECT FORMAT(SUM(GrossTaxable),2) AS GrossTaxable, FORMAT(SUM(InputTax),2) AS InputTax FROM purch ';
            $colsubtotals=array('GrossTaxable','InputTax');
            include('../backendphp/layout/displayastablewithsub.php');
            break;
        
        case 'Encashments':
            if (!allowedToOpen(659,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
            $sql0='CREATE TEMPORARY TABLE vatinencash AS
                    SELECT dm.Date, dm.TxnID, BranchNo, Amount, TIN, de.EncashDetails, de.TypeID FROM `acctg_2depositmain` dm 
                    JOIN `acctg_2depencashsub` de ON dm.TxnID=de.TxnID WHERE de.DebitAccountID=510 AND Month(Date)='.$month; 
            $stmt0=$link->prepare($sql0); $stmt0->execute();
            $sql0='CREATE TEMPORARY TABLE `list` AS 
                SELECT CompanyNo, v.Date, v.BranchNo, v.Amount AS InputTax, (de.Amount+v.Amount) AS GrossTaxable, v.TIN, de.EncashDetails, de.TypeID 
                FROM `acctg_2depencashsub` de JOIN vatinencash v ON de.EncashDetails=v.EncashDetails AND de.TypeID=v.TypeID AND de.TxnID=v.TxnID 
                JOIN `1branches` b ON b.BranchNo=v.BranchNo'; 
            $stmt0=$link->prepare($sql0); $stmt0->execute();
            $sql1='SELECT l.CompanyNo, Company FROM `list` l JOIN `1companies` c ON c.CompanyNo=l.CompanyNo GROUP BY l.CompanyNo'; 
            $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
            $sql2='SELECT l.CompanyNo, CompanyName AS Supplier, Address, FORMAT(SUM(InputTax),2) AS InputTax, FORMAT(SUM(GrossTaxable),2) AS GrossTaxable, l.TIN FROM `list` l '
                    . ' LEFT JOIN `gen_info_1tinforexpenses` t ON t.TIN=l.TIN ';
            $secondcondition='';
            $groupby='CompanyNo'; $orderby=' GROUP BY l.TIN ORDER BY Supplier';
            $columnnames1=array('Company');
            $columnnames2=array('Supplier','TIN','Address','GrossTaxable','InputTax');
            $title='Input VAT - Purchases from Encashments  INCORRECT DATA AS OF NOW'; $formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$month.'-1')).'<br>'; 
            $sqlsubtotal='SELECT FORMAT(SUM(GrossTaxable),2) AS GrossTaxable, FORMAT(SUM(InputTax),2) AS InputTax FROM list ';
            $colsubtotals=array('GrossTaxable','InputTax');
            include('../backendphp/layout/displayastablewithsub.php');
            break;
        
        case 'CheckPayments':
            if (!allowedToOpen(657,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
            $sql0='CREATE TEMPORARY TABLE vatinchecks AS
                    SELECT vm.Date, vm.TxnID, vs.BranchNo, Amount, TIN, REPLACE(vs.Particulars,"Vat for ","") AS Particulars 
                    FROM `acctg_2cvmain`vm JOIN `acctg_2cvsub` vs ON vm.TxnID=vs.TxnID 
                    WHERE vs.DebitAccountID=510 AND Month(Date)='.$month; 
            $stmt0=$link->prepare($sql0); $stmt0->execute();
            $sql0='CREATE TEMPORARY TABLE `list` AS 
                SELECT CompanyNo, v.Date, v.BranchNo, v.Amount AS InputTax, (vs.Amount+v.Amount) AS GrossTaxable, v.TIN, vs.Particulars FROM `acctg_2cvsub` vs 
                JOIN `vatinchecks` v ON vs.TxnID=v.TxnID AND vs.Particulars=v.Particulars JOIN `1branches` b ON b.BranchNo=v.BranchNo'; 
            $stmt0=$link->prepare($sql0); $stmt0->execute();
            $sql1='SELECT l.CompanyNo, Company FROM `list` l JOIN `1companies` c ON c.CompanyNo=l.CompanyNo GROUP BY l.CompanyNo'; 
            $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
            $sql2='SELECT l.CompanyNo, CompanyName AS Supplier, Address, FORMAT(SUM(InputTax),2) AS InputTax, FORMAT(SUM(GrossTaxable),2) AS GrossTaxable, l.TIN '
                    . ' FROM `list` l LEFT JOIN `gen_info_1tinforexpenses` t ON t.TIN=l.TIN ';
            $groupby='CompanyNo'; $orderby=' GROUP BY l.TIN ORDER BY Supplier';
            $columnnames1=array('Company');
            $columnnames2=array('Supplier','TIN','Address','GrossTaxable','InputTax');
            $title='Input VAT - Purchases from Check Payments INCORRECT DATA AS OF NOW'; $formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$month.'-1')).'<br>'; 
            $sqlsubtotal='SELECT FORMAT(SUM(GrossTaxable),2) AS GrossTaxable, FORMAT(SUM(InputTax),2) AS InputTax FROM list ';
            $colsubtotals=array('GrossTaxable','InputTax');
            include('../backendphp/layout/displayastablewithsub.php');
            break;
        
        case 'Clients':
            if (!allowedToOpen(658,'1rtc')) { header ('Location:/'.$url_folder.'/index.php?denied=true');}
            $sql0='CREATE TEMPORARY TABLE sales AS '
                . 'SELECT b.CompanyNo, c.ClientName, c.TIN, CONCAT(Barangay," ",TownOrCity," ",Province) AS Address, c.VatTypeNo, 
                    ROUND(SUM(CASE WHEN VatTypeNo=0 THEN (ss.Qty*ss.UnitPrice) END),2) AS `Vatable(12%)`,
                ROUND(SUM(CASE WHEN VatTypeNo=1 THEN (ss.Qty*ss.UnitPrice) END),2) AS `Vat-Exempt`,
                ROUND(SUM(CASE WHEN VatTypeNo=2 THEN (ss.Qty*ss.UnitPrice) END),2) AS `Zero-Rated`,
                ROUND(SUM(CASE WHEN VatTypeNo=3 THEN (ss.Qty*ss.UnitPrice) END),2) AS `Government(12%)`,
                ROUND((((SUM(CASE WHEN VatTypeNo=0 THEN (ss.Qty*ss.UnitPrice) END))*0.12)/1.12),2) AS OutputVat
                FROM `invty_2sale` sm JOIN `invty_2salesub` ss ON sm.TxnID=ss.TxnID
                JOIN `1clients` c ON c.ClientNo=sm.ClientNo
                JOIN `1branches` b ON b.BranchNo=sm.BranchNo
                WHERE sm.ClientNo<>10001 AND txntype IN (1,2) AND Month(Date)='.$month.'
                GROUP BY b.CompanyNo,sm.ClientNo,c.VatTypeNo;'; $stmt0=$link->prepare($sql0); $stmt0->execute();
            $sql1='SELECT s.CompanyNo, CompanyName AS Company FROM sales s JOIN `1companies` c ON c.CompanyNo=s.CompanyNo GROUP BY s.CompanyNo'; 
            $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll();
            $sql2='SELECT CompanyNo, ClientName, TIN, Address, FORMAT(`Vatable(12%)`,2) AS `Vatable(12%)`, FORMAT(`Vat-Exempt`,2) AS `Vat-Exempt`, '
                    . ' FORMAT(`Zero-Rated`,2) AS `Zero-Rated`, FORMAT(`Government(12%)`,2) AS `Government(12%)`, '
                    . ' FORMAT(OutputVat,2) AS OutputVat FROM sales';
            $groupby='CompanyNo'; $orderby='ORDER BY ClientName';
            $columnnames1=array('Company');
            $columnnames2=array('ClientName','TIN','Address','Vatable(12%)','Vat-Exempt','Zero-Rated','Government(12%)','OutputVat');
            $title='ALL Sales'; $formdesc='For the month of '. date('F',strtotime(''.$currentyr.'-'.$month.'-1')).'<br>'; 
            $sqlsubtotal='SELECT FORMAT(SUM(`Vatable(12%)`),2) AS `Vatable(12%)`, FORMAT(SUM(`Vat-Exempt`),2) AS `Vat-Exempt`, '
                    . 'FORMAT(SUM(`Zero-Rated`),2) AS `Zero-Rated`, FORMAT(SUM(`Government(12%)`),2) AS `Government(12%)`, FORMAT(SUM(`OutputVat`),2) AS `OutputVat` '
                    . 'FROM sales ';
            $colsubtotals=array('Vatable(12%)','Vat-Exempt','Zero-Rated','Government(12%)','OutputVat');
            include('../backendphp/layout/displayastablewithsub.php');
            break;
         
        default:
            break;
     }
      $link=null; $stmt=null; $stmt0=null; $stmt1=null;
?>