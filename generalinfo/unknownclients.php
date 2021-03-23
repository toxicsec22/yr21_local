<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';  
// if (!allowedToOpen(array(2,7,8,9,10,11,91,1111,1112,1113,1114),'1rtc')) { echo 'No permission'; exit();}

$showbranches=true; 

include_once('../switchboard/contents.php');        
 
$which=(!isset($_GET['w'])?'List':$_GET['w']);

    echo '</br>';
    ?>
<!--buttons -->
    
    <?php

    $title='Unknown Client Types - Purchases in Percent (%)';
    
    
switch ($which){
   case 'List': 
       $formdesc='';
	   

       $sql0='CREATE TEMPORARY TABLE SumPerClientPerItemType AS
	   SELECT c.ClientNo,ClientName,SUM(Auto) AS TotalAuto,SUM(Ref) AS TotalRef,SUM(Aircon) AS TotalAircon FROM 1clients c JOIN gen_info_1branchesclientsjxn bcj ON c.ClientNo=bcj.ClientNo JOIN invty_2sale s ON c.ClientNo=s.ClientNo JOIN invty_2salesub ss ON s.TxnID=ss.TxnID JOIN invty_1items i ON ss.ItemCode=i.ItemCode WHERE bcj.BranchNo='.$_SESSION['bnum'].' AND c.ClientNo NOT IN (10000,10001,10004) AND ClientType=0 GROUP BY c.ClientNo';
	  $stmt0=$link->prepare($sql0); $stmt0->execute();

	
	   $sql='SELECT ClientNo,ClientName,ROUND(((TotalAuto/(TotalAuto+TotalRef+TotalAircon))*100),2) AS `Auto`,ROUND(((TotalRef/(TotalAuto+TotalRef+TotalAircon))*100),2) AS Ref,ROUND(((TotalAircon/(TotalAuto+TotalRef+TotalAircon))*100),2) AS Aircon FROM SumPerClientPerItemType;';

       $columnnames=array('ClientNo', 'ClientName', 'Auto', 'Ref', 'Aircon');
        
        include('../backendphp/layout/displayastablenosort.php');

  break;

	
}
          $link=null; $stmt=null;
    ?>
	

</body>
</html>
