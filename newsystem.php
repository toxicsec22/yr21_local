<?php 
$title='ARWAN';
$path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/myswitch/generic/header.php');
include_once($path.'/acrossyrs/myswitch/generic/sidebar2.php');
include_once($path.'/acrossyrs/myswitch/generic/topbar.php');
		  ?>

        
        <!-- End of Topbar -->

        <!-- Begin Page Content -->

        <div class="container-fluid">
       

        <div class="row">
    
    <?php 
    // include_once('graphs/dashboardgraphs.php');
    include_once('switchboard/messagesonswitch.php');
    include_once('approvals/forapprovalswitchboard.php');
    echo '<br><br>';
    ?>

        </div>
          <!-- Content Row -->
          <div class="row">

            
          <?php

$condition=' WHERE (`OnSwitch` > 0) AND ((FIND_IN_SET('.$_SESSION['&pos'].',`AllowedPos`)) '.($_SESSION['&pos']<>-1?'OR (FIND_IN_SET('.$_SESSION['(ak0)'].',`AllowedPerID`))':'').')';

$sqlmenugroup='SELECT SwitchID, switchname AS Switch FROM `permissions_00switch` s WHERE s.switchid IN (SELECT `OnSwitch` FROM `permissions_2allprocesses` '.$condition.') '
        . ' OR s.switchid IN (SELECT `switchid` FROM  `permissions_01level1` `l1` JOIN `permissions_2allprocesses` ap ON ap.OnSwitch=l1.MenuID '.$condition.') ORDER BY switchorder;';
     
$stmt=$link->query($sqlmenugroup); $resultgroup=$stmt->fetchAll();

foreach($resultgroup AS $res){

?>

            <div class="col-xl-3 col-md-6 mb-4">
            <a href="switch.php?switchid=<?php echo $res['SwitchID'];?>">
              <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><font color="#4F94CD"><?php echo $res['Switch'];?></font></div>
                      <div class="h5 mb-0 font-weight-bold text-gray-800"></div>
                    </div>
                    <div class="col-auto">
                    </div>
                  </div>
                </div>
              </div>
              </a>
            </div>
		<?php } 
    include_once($path.'/acrossyrs/myswitch/generic/footer.php');
    ?>