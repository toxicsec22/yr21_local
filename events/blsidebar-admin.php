
 <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar" style="background-color:#135ea0;">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
        <div class="sidebar-brand-icon rotate-n-15">
          <i class="fas fa-dumbbell"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Biggest Loser</div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - Dashboard -->
	   <li class="nav-item">
        <a class="nav-link" href="biggestloser.php?w=Announcement">
          <i class="fas fa-scroll"></i>
          <span>Guidelines</span></a>
      </li>
	  
	  
      <li class="nav-item">
        <a class="nav-link" href="biggestloser.php?w=Participants">
          <i class="fas fa-users"></i>
          <span>Participants</span></a>
      </li>
	  <?php $condition=date('Y-m-d');if($condition>=$startofevent){ ?>
	   <li class="nav-item">
        <a class="nav-link" href="biggestloser.php?w=Teams">
          <i class="fas fa-chart-line"></i>
          <span>TeamGraph</span></a>
      </li>
	  
	  <li class="nav-item">
        <a class="nav-link" href="biggestloser.php?w=Individuals">
          <i class="fas fa-chart-line"></i>
          <span>IndividualGraph</span></a>
      </li>
	  <?php }?>
	  <?php if (allowedToOpen(2204,'1rtc')) { ?>
	  <li class="nav-item">
        <a class="nav-link" href="biggestloser.php?w=Encode">
           <i class="fas fa-fw fa-pen"></i>
          <span>Encode Weight and Date</span></a>
      </li>
	<?php }?>
      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      

    </ul>