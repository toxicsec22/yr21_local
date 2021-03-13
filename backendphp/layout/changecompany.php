<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$login=$_SESSION['(ak0)'];
include_once($path.'/acrossyrs/dbinit/userinit.php');
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

if (allowedToOpen(301,'1rtc')) {
                        $condition='';
        } else {
                        goto notpermitted;
        }
		
if(allowedToOpen(array(306,307,308,309,311,312,313),'1rtc')){			
if (allowedToOpen(313,'1rtc')) {
		$compcond='';
	}else{
		$compcond='c.CompanyNo in ('.$_GET['c'].') and';
	}
}else{
	$compcond='';
}
          
$coname=$_POST['company'];
$sql="SELECT c.CompanyNo, CompanyName, RepBranchNo, Branch FROM 1companies c JOIN 1branches b ON c.CompanyNo=b.CompanyNo AND c.RepBranchNo=b.BranchNo WHERE ".$compcond." CompanyName LIKE '" . $coname . "'".$condition; //if($_SESSION['(ak0)']==1002){ echo $sql; break;}
$res=$link->query($sql);
$row=$res->fetch(PDO::FETCH_ASSOC);
if ($res->rowCount()==0){ goto notpermitted;}
$_SESSION['*cnum']=$row['CompanyNo'];
$_SESSION['*cname']=$row['CompanyName'];
$_SESSION['bnum']=$row['RepBranchNo'];
$_SESSION['@brn']=$row['Branch'];
                
notpermitted:
header('Location: ' . $_SERVER['HTTP_REFERER']);
?>