<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(2201,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
include_once('../switchboard/contents.php');



include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

//DEFAULT TIMEZONE
date_default_timezone_set('Asia/Manila'); $diraddress='../';

?>

<br><div id="section" style="display: block;">

<?php
include_once('../backendphp/layout/linkstyle.php');
	
if (allowedToOpen(2201,'1rtc')) {
	echo '<a id=\'link\' href="zipcodes.php?w=List">List Of Zip Codes</a> ';
	echo '<a id=\'link\' href="zipcodes.php?w=UploadCityOrProvince">Upload CityOrProvince</a> ';
	echo '<a id=\'link\' href="zipcodes.php?w=UploadBarangayOrTown">Upload BarangayOrTown</a> ';
	echo '<br><br>';
}
$which=(!isset($_GET['w'])?'List':$_GET['w']); 


switch ($which)
{
	case 'List':
	if (allowedToOpen(2201,'1rtc')) {
		$title='List Of Zip Codes'; 
          $columnnames=array('CityOrProvince','BarangayOrTown','ZipCode');      
         $sql='SELECT CityOrProvince,BarangayOrTown,ZipCode FROM 1_gamit.0cityorprovince cp LEFT JOIN 1_gamit.0barangayortown bt ON cp.CPID=bt.CPID ORDER BY CityOrProvince,BarangayOrTown'; 
		
		$width='40%';
		
		include('../backendphp/layout/displayastable.php');
	} else {
		echo 'No permission'; exit;
	}
	break;
	
		
		case 'UploadCityOrProvince':
                if (allowedToOpen(2201,'1rtc')){
             $title='Upload City Or Province';
        $colnames=array('CPID','CityOrProvince');
        $requiredcol=array('CPID','CityOrProvince');
        $required='';  foreach($requiredcol as $req){ $required.='<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed.='<li>'.$col.'</li>'; }
        $specific_instruct='Note: CPID = City Or Province ID '
                . '<br><br><i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname='1_gamit.0cityorprovince'; $firstcolumnname='CPID';
        $DOWNLOAD_DIR="../../uploads/"; $link=$link;
        include('../backendphp/layout/uploaddata.php');
        if(($row-1)>0){ echo '<a href="zipcodes.php?w=List" target="_blank">Lookup Newly Imported Data</a>';}
                } else {
                        echo 'No permission'; exit;
                }
        break;
		
	
		case 'UploadBarangayOrTown':
                if (allowedToOpen(2201,'1rtc')){
             $title='Upload Barangay Or Town';
        $colnames=array('BTID','BarangayOrTown','ZipCode','CPID');
        $requiredcol=array('BTID','BarangayOrTown','ZipCode','CPID');
        $required='';  foreach($requiredcol as $req){ $required.='<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed.='<li>'.$col.'</li>'; }
        $specific_instruct='Note: BTID = Barangay or Town ID, CPID = City Or Province ID '
                . '<br><br><i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname='1_gamit.0barangayortown'; $firstcolumnname='BTID';
        $DOWNLOAD_DIR="../../uploads/"; $link=$link;
        include('../backendphp/layout/uploaddata.php');
        if(($row-1)>0){ echo '<a href="zipcodes.php?w=List" target="_blank">Lookup Newly Imported Data</a>';}
                } else {
                        echo 'No permission'; exit;
                }
        break;
	
}


 $link=null; $stmt=null;
?>
</div> <!-- end section -->
