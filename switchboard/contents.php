<?php
ob_start();
$path=$_SERVER['DOCUMENT_ROOT']; 
if (!isset($home) or !$home) {  include_once $path.'/acrossyrs/js/polyfill/datalist.php'; }
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

if (allowedToOpen(2201,'1rtc')){
        error_reporting(E_ALL);
	ini_set('display_errors', 1);
}	

date_default_timezone_set('Asia/Manila'); 
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link; 

$sql0='SELECT COUNT(IDNo) AS cnt, (ProcessIDs) AS AllProcessIDs FROM `permissions_4ownswitch` WHERE IDNo='.$_SESSION['(ak0)'];
$stmt0=$link->query($sql0); 
$res0=$stmt0->fetch();

$switch = 'Show All';
$allswitch = 'Show OwnSwitch';
if (allowedToOpen(6064,'1rtc') AND (basename($_SERVER['REQUEST_URI']) == "index.php")){ //AllowedToOpen
	echo '<form action="index.php" method="POST"><input type="submit" value="'.(isset($_SESSION['ownswitch'])?$switch:$allswitch).'" name="btnShowSwitch"/></form>';
}

if (isset($_POST['btnShowSwitch'])){
		if ($_POST['btnShowSwitch']==$allswitch){
			$_SESSION['ownswitch']=1;
		} else {
			unset($_SESSION['ownswitch']);
		}
		header('Location:index.php');
}
	
	if ((isset($_SESSION['ownswitch']) AND ($res0['cnt']>0))){
		$ownswitchcondi = 'AND ProcessID IN ('.$res0['AllProcessIDs'].')';
	}
	else {
		$ownswitchcondi = '';
	}
if (basename($_SERVER['REQUEST_URI']) == "index.php"){
        echo '<h3><a href="newsystem.php">Click me to try the NEW switchboard [ BETA VERSION ]</a></h3><br>';
}

//if pos = -1 no menu
$condition=' WHERE (`OnSwitch` > 0) AND ((FIND_IN_SET('.$_SESSION['&pos'].',`AllowedPos`)) '.($_SESSION['&pos']<>-1?'OR (FIND_IN_SET('.$_SESSION['(ak0)'].',`AllowedPerID`))':'').')';

$sqlmenugroup='SELECT SwitchID, switchname AS Switch FROM `permissions_00switch` s WHERE s.switchid IN (SELECT `OnSwitch` FROM `permissions_2allprocesses` '.$condition.' '.$ownswitchcondi.') '
        . ' OR s.switchid IN (SELECT `switchid` FROM  `permissions_01level1` `l1` JOIN `permissions_2allprocesses` ap ON ap.OnSwitch=l1.MenuID '.$condition.' '.$ownswitchcondi.') ORDER BY switchorder;';
     
$stmt=$link->query($sqlmenugroup); $resultgroup=$stmt->fetchAll();
isset($home)?include('switchboard/'.(strpos($_SERVER['HTTP_HOST'],'1rtc')?'1rtc':'').'switchstylehome.php'):include((strpos($_SERVER['HTTP_HOST'],'1rtc')?'1rtc':'').'switchstyle.php'); 
if (!allowedToOpen(2201,'1rtc')){ echo '<script type="text/javascript" language="javascript" src="https://'. $_SERVER['HTTP_HOST'].'/acrossyrs/js/disablerclick.js"></script>';}

?>
<div id="wrapper">
<?php
if (isset($home) and $home){ 
$switch='<div style="float:right; "><ul id="navmenu"><li><ul id="navmenu"><li><a href="/yr'.substr($lastyr,2,2).'/index.php">'.$lastyr.' Data</a></li><li><a href="/yr'.substr($nextyr,2,2).'/index.php">'.$nextyr.' Data</a></li>';
$switch.='<li><a href="/logout.php">Logout</a></li>';
$switch.='</ul></div><ul id="navmenu">';  
} else {
$switch='<ul id="navmenu"><li><a href="/'.$url_folder.'/index.php">Home</a></li>';
}
foreach ($resultgroup as $group){
    $switch=$switch.'<li><a href="#">'.$group['Switch'].'</a><ul class="sub1">';
    
 
    $sqlmenu='SELECT MenuID, Menu, MenuID AS ProcessID, Menu AS ProcessTitle, "#" AS ProcessAddress, OrderBy, 1 AS WithSub FROM `permissions_01level1` `l1` WHERE switchid='.$group['SwitchID'].' AND MenuID IN (SELECT `OnSwitch` FROM `permissions_2allprocesses` '.$condition.'  '.$ownswitchcondi.') 
        UNION
        SELECT ProcessID AS MenuID, ProcessTitle AS Menu, ProcessID, ProcessTitle, ProcessAddress, OrderBy, 0 AS WithSub FROM `permissions_2allprocesses` ap '.$condition.'  '.$ownswitchcondi.' AND OnSwitch='.$group['SwitchID'].' ORDER BY OrderBy; ';
           
    $stmt=$link->query($sqlmenu);    $result=$stmt->fetchAll();
    
    foreach ($result as $command){
            $commandlink=(is_integer(strrpos($command['ProcessAddress'],'action_token'))?$command['ProcessAddress'].$_SESSION['action_token']:$command['ProcessAddress']);
            
            $switch=$switch."<li><a href='".$commandlink."'>".$command['ProcessTitle']."</a>";
            if ($command['WithSub']==1){
                   $sqlmenusub='SELECT ProcessID, ProcessTitle, ProcessAddress, OrderBy FROM `permissions_2allprocesses` ap '.$condition.'  '.$ownswitchcondi.' AND OnSwitch='.$command['MenuID'].' ORDER BY OrderBy;';
                    $stmt=$link->query($sqlmenusub);
                    $resultsub=$stmt->fetchAll();
                    $switch=$switch.'<ul class="sub2">';
                    foreach ($resultsub as $commandsub){
                        $commandlinksub=(is_integer(strrpos($commandsub['ProcessAddress'],'action_token'))?$commandsub['ProcessAddress'].$_SESSION['action_token']:$commandsub['ProcessAddress']);
                        
                       // $switch=$switch.'<li><a href="'.$commandlinksub.'">'.$commandsub['ProcessTitle'].'</a></li>';
                        $switch=$switch.'<li><a href="'.$commandlinksub.'"
'.(isset($home)?'style="'.((strlen($commandsub['ProcessTitle'])>30)?'margin-top:3px;height:25px;line-height:12px;font-size:8pt;':'height:25px;line-height:16px;').'";':'').'>'.$commandsub['ProcessTitle'].'</a></li>';
                    }
                    $switch=$switch.'</ul>';
            }
            $switch=$switch.'</li>';
    }
    
    $switch=$switch.'</ul></li>';
}

echo (isset($home) and $home)?$switch:$switch.'<li><a href="/logout.php">Logout</a></li>';
?>
</ul> <!-- end div navmenu -->
</div> <!-- end div wrapper -->
<?php
if (isset($home) and $home){
        include_once $path.'/'.$url_folder.'/generalinfo/closedbranchesandattendanceerrors.php';
} else {
echo '<br><br>';
include_once  $path.'/'.$url_folder.'/backendphp/layout/showchoosebranch.php';
echo '<div style="float: left;  display:block;"><br><br>';

if (isset($_SESSION['&pos'])){
	
		
		$url = $_SERVER['REQUEST_URI']; 
		if((strpos($url,'eos') !== false) or (strpos($url,'acrossyrs') !== false)){

		}else{
                   
                  //  include_once $path.'/'.$url_folder.'/approvals/forapprovalallpages.php';
		}
		
	}
echo '</div><br>';//."\n";
}

?>
<br class="clearFloat" />
<div id="footer">
<p>Copyright &copy; <?php echo $currentyr; ?> - 1Rotary Trading Corporation</p>
</div>
</body></html>