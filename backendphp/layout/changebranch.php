<?php
if (isset($_POST['branchname'])){
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
 $login=$_SESSION['(ak0)'];

include_once($path.'/acrossyrs/dbinit/userinit.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

		if (allowedToOpen(301,'1rtc')) {

			$branchname=$_POST['branchname'];

			if(allowedToOpen(array(306,307,308,309,311,312,313),'1rtc')){
if (allowedToOpen(313,'1rtc')) {
		$compcond='';
	}else{
		$compcond='and c.CompanyNo in ('.$_GET['c'].')';
	}
$sql='select b.BranchNo, b.Branch, c.CompanyNo, c.CompanyName from 1branches b join 1companies c on c.CompanyNo=b.CompanyNo where Branch= \''.$branchname.'\' '.$compcond.' ';
}else{
if (allowedToOpen(array(302,314),'1rtc')) {
                    $sql='SELECT b.Branch,b.BranchNo, b.CompanyNo, CompanyName from `1branches` as b
		    JOIN `1companies` c ON c.CompanyNo=b.CompanyNo
		    JOIN `attend_1branchgroups` as bt on b.BranchNo=bt.BranchNo
                    where b.Active=1 and (bt.CNC='.$_SESSION['(ak0)'].' or bt.TeamLeader='.$_SESSION['(ak0)']
                            .' or bt.SAM='.$_SESSION['(ak0)']
                            .' or bt.InventoryPlanner='.$_SESSION['(ak0)']
                    .' or AcctgGroup='.$_SESSION['(ak0)'].') AND Branch like \'' . $branchname . "'";
                } else if(allowedToOpen(305,'1rtc')){
					$sql="SELECT BranchNo, Branch, b.CompanyNo, CompanyName from `1branches` b
		    JOIN `1companies` c ON c.CompanyNo=b.CompanyNo where BranchNo IN (40,100) AND Branch like '" . $branchname . "'";
				} else { $sql="SELECT BranchNo, Branch, b.CompanyNo, CompanyName from `1branches` b
		    JOIN `1companies` c ON c.CompanyNo=b.CompanyNo where Branch like '" . $branchname . "'";}

}

		$res=$link->query($sql);
		$row=$res->fetch(PDO::FETCH_ASSOC);
                if ($res->rowCount()==0){ echo 'No permission'; exit; }
                    $_SESSION['bnum']=$row['BranchNo'];
                    $_SESSION['@brn']=$row['Branch'];

		    if ($_SESSION['*cnum']<>$row['CompanyNo']){
                        $_SESSION['*cnum']=$row['CompanyNo'];
                        $_SESSION['*cname']=$row['CompanyName'];

		    }
                header('Location: ' . $_SERVER['HTTP_REFERER']);
		} else {
                    echo 'No permission'; exit;
		//nothing should change since branch users should not see other branches
		}
}
?>
