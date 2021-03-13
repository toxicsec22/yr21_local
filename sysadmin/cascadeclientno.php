<style>
hr.myhrline{
     margin-top: 10px;
     margin-bottom: 10px;
  }
  </style>
<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(2201,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');


$tableswithclientno=array("acctg_2collectmain","acctg_2depositsub","acctg_2salesub","approvals_2directdeposits","calllogs_2telsub","calllogs_2visitsub","calllogs_3arsub","comments_30branchreportonclients","comments_5clientsonhold","comments_5commentsonclients","events_1motorcyclepromo","events_1tempclients","gen_info_1branchesclientsjxn","gen_info_1clientbdays","invty_2sale","invty_6lostsales","invty_6verbalquotes","quotations_2quotemain");
print_r($tableswithclientno);
echo '<br><hr><br>';
foreach($tableswithclientno AS $tbl){
	echo '<b>-- '.$tbl.'</b><br>';
	 $sqlfetchtable='SHOW CREATE TABLE '.$tbl;
    $stmtfetchtable=$link->query($sqlfetchtable);
    $resulttable=$stmtfetchtable->fetch();
	
	
	echo '-- '.str_replace('ClientNo','<b style="color:red;">ClientNo</b>',$resulttable['Create Table']).'<br><br>';
	echo 'ALTER TABLE `'.$tbl.'` 
	ADD CONSTRAINT `fk_'.$tbl.'`
  FOREIGN KEY (`ClientNo`)
  REFERENCES `1clients` (`ClientNo`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;';
	echo '<br><hr class="myhrline">';
}


?>
