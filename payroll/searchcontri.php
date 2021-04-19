<?php

function getContriEE($monthlybasic,$gov){
	global $currentyr;
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
    switch ($gov){
        case 'sss':
            $sql='SELECT (SSEE+ECEE+MPFEE) AS Contri FROM `payroll_0ssstable` WHERE '.$monthlybasic.' BETWEEN RangeMin AND RangeMax';
            break;
        case 'ssser':
            $sql='SELECT (SSER+ECER+MPFER) AS Contri FROM `payroll_0ssstable` WHERE '.$monthlybasic.' BETWEEN RangeMin AND RangeMax';
            break;
        case 'phic': // new philhealth rules per year starting 2019
            $sql='SELECT * FROM payroll_0phicrate WHERE ApplicableYear='.$currentyr;
            $stmt=$link->query($sql); $result=$stmt->fetch(); 
            if ($monthlybasic<=$result['MinBasic']) { $empshare=($result['MinPremium']/2); } elseif ($monthlybasic>$result['MinBasic'] AND $monthlybasic<=$result['MaxBasic']) { $empshare=($monthlybasic*($result['PremiumRate']/100)/2); } else { $empshare=($result['MaxPremium']/2); }
            $sql='SELECT TRUNCATE('.$empshare.',2) AS `Contri`';
            break;
        default:
            break;
    }
	
    $stmt=$link->prepare($sql);
    $stmt->execute();
    $result=$stmt->fetch();  $link=null; $stmt=null;
    return $result['Contri'];
}

function getSalaryCredit($monthlybasic,$gov){
 global $currentyr;
$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

    switch ($gov){
        case 'sss':
            $sql='SELECT SSECCredit FROM `payroll_0ssstable` WHERE '.$monthlybasic.' BETWEEN RangeMin AND RangeMax';
            break;
//        case 'phic':
//            $sql='SELECT SalaryBase as SalaryCredit FROM `0philhealthtable` WHERE '.$monthlybasic.' BETWEEN RangeMin AND RangeMax';
//            break;
        default:
            break;
    }
    $stmt=$link->prepare($sql);
    $stmt->execute();
    $result=$stmt->fetch();  $link=null; $stmt=null;
    return $result['SSECCredit'];
}
?>