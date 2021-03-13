<?php
include_once('../../../backendphp/logincodes/checkifloggedon.php');
include_once ('../../../backendphp/dbinit/userinit.php');
    $branchno=$_SESSION['bnum'];
    $title='Unpaid Receivables';
    $temptablesql='create temporary table temp_unpaidar(
`ar_id` INT(15) NOT NULL AUTO_INCREMENT,
`WHO` VARCHAR(100) NOT NULL,
  `SaleDate` date DEFAULT NULL,
`InvNo` varchar(175) NOT NULL,
`TotalAmt` double DEFAULT \'0\',
`PaidAmt` double DEFAULT \'0\',
  `InvBalance` double DEFAULT \'0\',
`Age` bigint(20) NOT NULL,
  `BranchNo` smallint(6) NOT NULL,
`Terms` int(11) DEFAULT \'0\',
  `CreditLimit` double default \'0\',
  PRIMARY KEY (`ar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8';
    $stmt=$link->prepare($temptablesql);
    $stmt->execute();
    
    $sqlinsert='insert into temp_unpaidar
(`WHO`, `SaleDate`, `InvNo`, `TotalAmt`,`PaidAmt`, `InvBalance`, `Age`,`BranchNo`, `Terms`, `CreditLimit`) 
select `WHO`, `SaleDate`, `InvNo`, `TotalAmt`,ifnull(`PaidAmt`,0), `PayBalance`, `Age`,`BranchNo`, `Terms`, `CreditLimit` from `UnpaidARAll` where BranchNo='. $branchno;


    $stmt=$link->prepare($sqlinsert);
    $stmt->execute();
 
    $sql='Select * from temp_unpaidar order by `WHO`, `SaleDate`,`InvNo`';
    $columnnames=array('SaleDate','WHO','InvNo','TotalAmt','PaidAmt','InvBalance','Age','BranchNo','Terms','CreditLimit');

     include('../../../backendphp/layout/displayastable.php');
     
    ?>
