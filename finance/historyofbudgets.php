<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(5172,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
$which=!isset($_GET['w'])?'lists':$_GET['w'];
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
echo comboBox($link,'SELECT EntityID, Entity FROM `acctg_1budgetentities` ORDER BY Entity','EntityID','Entity','entities'); 
echo comboBox($link,'SELECT AccountID, ShortAcctID AS Account, AccountDescription FROM `acctg_1chartofaccounts` ORDER BY ShortAcctID;','AccountID','Account','accounts');
switch ($which){	
		case'lists':
			echo'<title>History Of Budgets</title>';
			echo'<h3>History Of Budgets</h3>';
				echo'</br><form method="post" action="historyofbudgets.php?w=lists">
						Entity: <input type="text" name="entity" list="entities">
						Account: <input type="text" name="account" list="accounts">
								<input type="submit" name="submit">
				   </form>';

		$title='';
		
				if(isset($_REQUEST['entity'])){
				$entity=companyandbranchValue($link, 'acctg_1budgetentities', 'Entity', $_REQUEST['entity'], 'EntityID');
				if(isset($_REQUEST['account']) AND !empty($_REQUEST['account'])){
				$account=comboBoxValue($link, 'acctg_1chartofaccounts', 'ShortAcctID', $_REQUEST['account'], 'AccountID');
				}
			$formdesc=''.$_REQUEST['entity'].'';
			$sql='select *,ShortAcctID as Account,CONCAT(Nickname,\' \',SurName) as EditOrDelBy,if(EditOrDel=0,"Edit","Delete") as EditOrDel,EditOrDelTS from '.$currentyr.'_trail.budgetedits be left join acctg_1chartofaccounts ca on ca.AccountID=be.AccountID left join 1employees e on e.IDNo=be.EditOrDelByNo where EntityID=\''.$entity.'\' '.((isset($_REQUEST['account']) AND !empty($_REQUEST['account']))? ' and be.AccountID=\''.$account.'\' ':'').' Order by Account,EditOrDelTS Asc';
			$columnnames=array('Month','Account','Details','Specifics','Budget','EditOrDelBy','EditOrDel','EditOrDelTS');
			include_once('../backendphp/layout/displayastablenosort.php');
			}  
		break;

}
?>