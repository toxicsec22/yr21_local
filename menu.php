<?php 
$title='ARWAN';
$path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/myswitch/generic/header.php');
include_once($path.'/acrossyrs/myswitch/generic/sidebar2.php');
include_once($path.'/acrossyrs/myswitch/generic/topbar.php');
		  ?>

        
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <nav aria-label="breadcrumb" class="classBreadCrumb">
        <ol class="breadcrumb">
            <!--<li class="breadcrumb-item"><a href="kwentongfeb.php?w=ReadStories">Read Stories</a></li>-->
            <?php
            $sqlswitch='SELECT switchid,switchname FROM permissions_00switch WHERE switchid='.intval($_GET['switchid']).'';
            $stmtswitch=$link->query($sqlswitch); $resultswitch=$stmtswitch->fetch();
            ?>
            <li class="breadcrumb-item"><a href="switch.php?switchid=<?php echo $resultswitch['switchid'];?>"><?php echo $resultswitch['switchname'];?></a></li>

            <?php
            $sqlmenu='SELECT Menu FROM permissions_01level1 WHERE MenuID='.intval($_GET['menuid']).'';
            $stmtmenu=$link->query($sqlmenu); $resultmenu=$stmtmenu->fetch();
            ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $resultmenu['Menu'];?></li>
        </ol>
    </nav> 
        <div class="container-fluid">
       

          <!-- Content Row -->
          <div class="row">

            
          <?php

$condition=' WHERE (`OnSwitch` > 0) AND ((FIND_IN_SET('.$_SESSION['&pos'].',`AllowedPos`)) '.($_SESSION['&pos']<>-1?'OR (FIND_IN_SET('.$_SESSION['(ak0)'].',`AllowedPerID`))':'').')';

$sqlmenugroup='
SELECT ProcessTitle, ProcessID, ProcessAddress,OrderBy FROM `permissions_2allprocesses` ap '.$condition.' AND OnSwitch='.intval($_GET['menuid']).' ORDER BY OrderBy';

$stmt=$link->query($sqlmenugroup); $resultgroup=$stmt->fetchAll();

foreach($resultgroup AS $res){

?>

            <div class="col-xl-3 col-md-6 mb-4">
            <a href="<?php echo $res['ProcessAddress'];?>">
              <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                     
                      <font color="#4F94CD"><?php echo $res['ProcessTitle'];?></font>
                    
                        </div>
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