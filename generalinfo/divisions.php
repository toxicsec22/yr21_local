<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';

include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

if (!allowedToOpen(array(6448,100),'1rtc')) { echo 'No permission'; exit; }
$showbranches=false;
	
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
$diraddress='../';

include_once('../backendphp/layout/linkstyle.php');


?>

<br><div id="section" style="display: block;">
<?php if (allowedToOpen(100,'1rtc')) { ?>
<a id='link' href="divisions.php?w=AddUpdateDivisionPage">Add New Division</a> 
<?php } ?>
<a id='link' href="divisions.php?w=List">List of <b>MY</b> Department Divisions</a> 

<a id='link' href="divisions.php?w=ListAllDivisions">List of <b>ALL</b> Divisions</a>
<?php

echo '<br>';
$which=(!isset($_GET['w'])?'List':$_GET['w']);

if(in_array($which,array('AddDivisionProcess','UpdateDivisionProcess'))){
    $deptid=comboBoxValue($link,'`1departments`','dept',addslashes($_POST['dept']),'deptid');
	$divisionheadidno=comboBoxValue($link,'`attend_30currentpositions`','FullName',addslashes($_POST['DivisionHead']),'IDNo');
    if($_POST['DivisionBy']=='Position'){
        $isposition=0;
    } else {
        $isposition=1;
    }
}

if(in_array($which,array('List','AddUpdateDivisionPage'))){
	$sqldeptmain=' FROM attend_30currentpositions WHERE (deptheadpositionid='.$_SESSION['&pos'].' OR deptid=(SELECT deptid FROM attend_30currentpositions WHERE IDNo='.$_SESSION['(ak0)'].')) AND deptid<>10 ORDER BY dept';

	$stmtdept=$link->query('SELECT IFNULL(GROUP_CONCAT(DISTINCT(deptid)),-1) AS deptid '.$sqldeptmain); $rowdept=$stmtdept->fetch();
}

switch ($which)
{
	case 'List':
	
		$sql='SELECT DivisionDesc,FullName AS DivisionHead,dp.dept AS Department,IF(IsPosition=0,"Position","IDNo") AS DivisionBy, DID,
        IF(IsPosition=1,(SELECT GROUP_CONCAT(FullName ORDER BY FullName SEPARATOR "<br>") FROM attend_30currentpositions WHERE FIND_IN_SET(IDNo,PositionIDsorIDNos)),((SELECT GROUP_CONCAT(Position ORDER BY Position SEPARATOR "<br>") FROM attend_0positions WHERE FIND_IN_SET(PositionID,PositionIDsorIDNos)))) AS PositionsOrEmployee
         FROM `1divisions` dv LEFT JOIN 1departments dp ON dv.deptid=dp.deptid LEFT JOIN attend_30currentpositions cp ON dv.DivisionHeadIDNo=cp.IDNo WHERE dv.deptid IN ('.$rowdept['deptid'].') ORDER BY dp.dept DESC';
       
		$columnnameslist=array('Department','DivisionHead', 'DivisionDesc', 'DivisionBy', 'PositionsOrEmployee');
   

        $title='List of MY Department Divisions';

   		 $formdesc=''; $txnidname='DID';
		$columnnames=$columnnameslist;       
		if (allowedToOpen(100,'1rtc')) {
			$delprocess='divisions.php?w=Delete&DID=';
        	$editprocess='divisions.php?w=AddUpdateDivisionPage&DID='; $editprocesslabel='Edit';
		}
			include('../backendphp/layout/displayastablenosort.php'); 
	
	break;
	

	case 'ListAllDivisions':

		$title='List of ALL Divisions';
		echo '<title>'.$title.'</title>';
		echo '<br><h3>'.$title.'</h3><br>';
		$stmtalldept=$link->query('SELECT DISTINCT(deptid) AS ddeptid FROM 1divisions ORDER BY deptid'); $rowalldepts=$stmtalldept->fetchAll();
		foreach($rowalldepts AS $rowalldept){
			
			$title=''; $formdesc='</i>'.comboBoxValue($link, '1departments', 'deptid', $rowalldept['ddeptid'], 'department').'<i>';

			$sql='SELECT DivisionDesc,FullName AS DivisionHead,dp.dept AS Department,IF(IsPosition=0,"Position","IDNo") AS DivisionBy, DID,
			IF(IsPosition=1,(SELECT GROUP_CONCAT(FullName ORDER BY FullName SEPARATOR "<br>") FROM attend_30currentpositions WHERE FIND_IN_SET(IDNo,PositionIDsorIDNos)),((SELECT GROUP_CONCAT(Position ORDER BY Position SEPARATOR "<br>") FROM attend_0positions WHERE FIND_IN_SET(PositionID,PositionIDsorIDNos)))) AS PositionsOrEmployee
			 FROM `1divisions` dv LEFT JOIN 1departments dp ON dv.deptid=dp.deptid LEFT JOIN attend_30currentpositions cp ON dv.DivisionHeadIDNo=cp.IDNo WHERE dv.deptid='.$rowalldept['ddeptid'].' ORDER BY DivisionDesc';
			 $columnnameslist=array('Department','DivisionHead','DivisionDesc', 'DivisionBy', 'PositionsOrEmployee');
			 $columnnames=$columnnameslist;
			 echo '<div style="background-color:#ffffff;width:40%;padding:4px;">';
			include('../backendphp/layout/displayastablenosort.php'); 
			echo '</div><br>';
		}
	break;
	
	
	case 'AddUpdateDivisionPage':
       if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit; }     
            echo '<br><br>';
            echo comboBox($link,'SELECT DISTINCT(deptid) AS deptid, dept '.$sqldeptmain,'deptid','dept','deptlist');

			echo comboBox($link,'SELECT IDNo,FullName AS DivisionHead '.$sqldeptmain,'IDNo','DivisionHead','divisionheadlist');
			

		
            echo comboBox($link,'SELECT 0 AS IsPosition, "Position" AS DivisionBy UNION SELECT 1 AS IsPosition, "IDNo" AS DivisionBy','IsPosition','DivisionBy','divisionlist');
			

            if (isset($_GET['DID'])){
				
				$title1 = 'Update Division';
				
				$did = intval($_GET['DID']);
				$sqlvalue ="SELECT dv.*,FullName,IF(IsPosition=0,'Position','IDNo') AS DivisionBy,dp.dept FROM `1divisions` dv LEFT JOIN 1departments dp ON dv.deptid=dp.deptid LEFT JOIN attend_30currentpositions cp ON dv.DivisionHeadIDNo=cp.IDNo WHERE DID=".$did.";";
				$stmtvalue=$link->query($sqlvalue); $rowvalue=$stmtvalue->fetch();
				
				$DID = $rowvalue['DID'];
				$DivisionHead=$rowvalue['FullName'];
				$DivisionDesc = $rowvalue['DivisionDesc'];
				$dept = $rowvalue['dept'];
                $divisionby = $rowvalue['DivisionBy'];
                $isposition = $rowvalue['IsPosition'];
				$PositionIDsorIDNos = $rowvalue['PositionIDsorIDNos'];
				$path = 'UpdateDivisionProcess&DID='.$did.'';
				$submitlabel = "Update division";
				
			}
			else {
				$title1 = 'Add New Division';
				
				$DID = '';
				$DivisionHead='';
				$DivisionDesc = '';
				$dept = '';
                $divisionby = '';
                $isposition = '';
				$PositionIDsorIDNos = '';
				$path='AddDivisionProcess';
				$submitlabel = "Add new division";
			}

			$sqlfetchposition='SELECT Position,PositionID FROM attend_0positions WHERE deptid IN ('.$rowdept['deptid'].')';
			$stmtfetchposition=$link->query($sqlfetchposition);
			$rowpos=$stmtfetchposition->fetchAll();

			$positions='';
			foreach($rowpos AS $rowpo){

				$positions.='<input type="checkbox" name="Position[]" value="'.$rowpo['PositionID'].'" '.(isset($_GET['DID'])?(in_array($rowpo['PositionID'],explode(",",$PositionIDsorIDNos)) !== false ? 'checked = "checked"': ''):'').'> '.$rowpo['Position'].'<br>';
			}


			$sqlfetchidno='SELECT IDNo,FullName FROM attend_30currentpositions WHERE deptid IN ('.$rowdept['deptid'].')';
			$stmtfetchidno=$link->query($sqlfetchidno);
			$rowidnos=$stmtfetchidno->fetchAll();

			$idno='';
			foreach($rowidnos AS $rowidno){
				$idno.='<input type="checkbox" name="IDNo[]" value="'.$rowidno['IDNo'].'" '.(isset($_GET['DID'])?(in_array($rowidno['IDNo'],explode(",",$PositionIDsorIDNos)) !== false ? 'checked = "checked"': ''):'').'> '.$rowidno['FullName'].'<br>';
			}

			
            echo '<title>'.$title1.'</title>';
			echo '<h2>'.$title1.'</h2><br/>';
			
			
			echo '<div>';
			echo '<div style="float:left;">';
			echo '<form action="divisions.php?w='.$path.'" method="post" autocomplete=off><table>';
			
			echo '<tr><td><input name="DID" type="hidden" placeholder="" value="'.$DID.'" /></td></tr>';
			echo '<tr><td>Division Head:</td><td><input name="DivisionHead" type="text" size="50" placeholder="" value="'.$DivisionHead.'" list="divisionheadlist"/></td></tr>';
			echo '<tr><td>Division Description:</td><td><input name="DivisionDesc" type="text" size="50" placeholder="" value="'.$DivisionDesc.'"/></td></tr>';
            echo '<tr><td>Department:</td><td><input name="dept" type="text" size="15" placeholder="" value="'.$dept.'" list="deptlist"/></td></tr>';
			echo '<tr><td>By:</td><td></td></tr>';
			
			?>
			<style>
    .box{
        color: #fff;
        padding: 20px;
        display: none;
        margin-top: 20px;
    }
    .Position{ background: maroon; }
    .IDNo{ background: #228B22; }
    label{ margin-right: 15px; }
</style>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function(){
    $('input[type="radio"]').click(function(){
        var inputValue = $(this).attr("value");
        var targetBox = $("." + inputValue);
        $(".box").not(targetBox).hide();
        $(targetBox).show();
    });
});
</script>

<tr><td colspan=2>
<div style="margin-left:10%;">
        <label><input type="radio" name="DivisionBy" value="Position" <?php echo ((isset($_GET['DID']) AND $divisionby==0)?'checked':'')?>> Position</label>
        <label><input type="radio" name="DivisionBy" value="IDNo" <?php echo ((isset($_GET['DID']) AND $divisionby==1)?'checked':'')?>> IDNo</label>
    </div>
    <div class="Position box">
			<?php echo $positions;?>
	</div>
    <div class="IDNo box">
	<?php echo $idno;?>
	</div>
	</td></tr>

			<?php
			
			
            echo '<tr><td style="padding:10px;"></td></tr><tr><td></td><td align="right"><input type="submit" value="'.$submitlabel.'"/></td></tr>';
			
			echo '</table></form>';
			echo '</div>';
			
			if(isset($_GET['DID'])){
				echo '<div style="margin-left:50%">';
				$sql ="SELECT IFNULL(PositionIDsorIDNos,0) AS PositionIDsorIDNos FROM 1divisions WHERE DID=".$DID.";";
				$stmt=$link->query($sql); $rowh=$stmt->fetch();
				
				
				if ($rowh['PositionIDsorIDNos']<>0){
					echo '<br><b>Positions/Employees</b><br>';
					$sql ="SELECT CONCAT(Nickname,' ',SurName) AS Employee FROM 1employees WHERE IDNo IN (".$rowh['PositionIDsorIDNos'].") UNION SELECT Position FROM attend_0positions WHERE PositionID IN (".$rowh['PositionIDsorIDNos'].")";
					$stmt=$link->query($sql); $row=$stmt->fetchAll();
					foreach($row AS $res){
						echo '&nbsp; &nbsp; '.$res['Employee'].'<br>';
					}
				}
				echo '</div>';
            }  
                
                echo '</div>';
			
		
	break;
	
	
	
	case 'UpdateDivisionProcess':
		if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit; }
		if(isset($_POST['DivisionBy']) AND $_POST['DivisionBy']=='Position'){
			$posordino=implode(",",$_POST['Position']);
		}
		if(isset($_POST['IDNo']) AND $_POST['DivisionBy']=='IDNo'){
			$posordino=implode(",",$_POST['IDNo']);
		}

	
		$sql='UPDATE `1divisions` SET DivisionDesc="'.addslashes($_POST['DivisionDesc']).'",deptid='.$deptid.',IsPosition='.$isposition.',PositionIDsorIDNos="'.$posordino.'",DivisionHeadIDNo='.$divisionheadidno.',EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW() WHERE DID='.intval($_GET['DID']);
		
        
		$stmt = $link->prepare($sql);
		$stmt->execute();
		header("Location:divisions.php?w=AddUpdateDivisionPage&DID=".$_POST['DID']);
	
	break;

    case 'AddDivisionProcess':
		if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit; }
		if(isset($_POST['DivisionBy']) AND $_POST['DivisionBy']=='Position'){
			$posordino=implode(",",$_POST['Position']);
		}
		if(isset($_POST['IDNo']) AND $_POST['DivisionBy']=='IDNo'){
			$posordino=implode(",",$_POST['IDNo']);
		}

		
	
		$sql='INSERT INTO `1divisions` SET DivisionDesc="'.addslashes($_POST['DivisionDesc']).'",deptid='.$deptid.',IsPosition='.$isposition.',PositionIDsorIDNos="'.$posordino.'",DivisionHeadIDNo='.$divisionheadidno.',EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=NOW();';
		
		$stmt = $link->prepare($sql);
		$stmt->execute();
		header("Location:divisions.php");
	
	break;


	case 'Delete':
	
		if (!allowedToOpen(100,'1rtc')) { echo 'No permission'; exit; }
		$sql='DELETE FROM 1divisions WHERE EncodedByNo='.$_SESSION['(ak0)'].' AND DID='.intval($_GET['DID']);
		
		$stmt = $link->prepare($sql);
		$stmt->execute();
		header("Location:divisions.php?w=List");
	
	break;
	


}
$link=null; $stmt=null; 

?>
</div> <!-- end section -->
