<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(64313,'1rtc')) { echo 'No permission'; exit;}  
$showbranches=false; include_once('../switchboard/contents.php');

  
?><br><div id="section" style="display: block;"><?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);

$columnstoedit=array('LocalNo','Email','mobilenumbers','WorkAssign');

$columnstoeditbranch=array('Landline','Mobile','Email','RegisteredAddress');
$columnstoeditdept=array('Landline','Address','deptheadpositionid');
if (in_array($which,array('List','EditSpecifics'))){
   $sql='SELECT pu.*, FullName, department AS Department, e.Nickname as EncodedBy, pu.IDNo AS TxnID FROM `1_gamit`.`1rtcusers` pu
        JOIN `attend_30currentpositions` cp ON cp.IDNo=pu.IDNo LEFT JOIN `1employees` e ON e.IDNo=pu.EncodedByNo ';
   $columnnameslist=array('Department','IDNo','FullName','LocalNo','Email','mobilenumbers','WorkAssign');
   $sqlbranch='SELECT BranchNo, Branch, Landline, Mobile, Email, RegisteredAddress, CONCAT(BranchNo,"&Branch=true") AS TxnID FROM `1branches` WHERE Active=1 AND Pseudobranch<>1 ';
   $columnnameslistbranch=array('Branch','Landline','Mobile','Email','RegisteredAddress');
   $sqldept='SELECT deptid,deptheadpositionid, department AS Department, tel AS Landline, address AS Address, CONCAT(deptid,"&Dept=true") AS TxnID FROM `1departments`  ';
   $columnnameslistdept=array('Department','Landline','Address','deptheadpositionid');
} 
if (allowedToOpen(2201,'1rtc')){
	array_push($columnstoedit,'ProgCookie');
	array_push($columnnameslist,'ProgCookie');
}

switch ($which){
   case 'List':
         $title='Directory Entries'; 
		 if (allowedToOpen(2201,'1rtc')){
			 include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
			  echo comboBox($link, 'Select IDNo, FullName FROM `attend_30currentpositions`', 'FullName', 'IDNo', 'emplist');
			$formdesc='<br><form action="directoryedit.php?w=UpdateProgcookie" method="POST"><input type="text" name="IDNo" list="emplist"> <input type="submit" value="Update Cookie" name="submit"></form>';
		 }
      	 $editprocess='directoryedit.php?w=EditSpecifics&IDNo='; $editprocesslabel='Edit'; 
         
			
      $txnid='IDNo';
      $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' Department, FullName'); $columnsub=$columnnames;
        $sql=$sql.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');    
      include('../backendphp/layout/displayastable.php');   
      $columnnames=$columnnameslistbranch;
      $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' Branch'); $columnsub=$columnnames;
      $sql=$sqlbranch.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
      $subtitle='Branches';
      include('../backendphp/layout/displayastableonlynoheaders.php'); 
      $columnnames=$columnnameslistdept;
      $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' Department'); $columnsub=$columnnames;
      $sql=$sqldept.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC'); 
      $subtitle='Departments';
      include('../backendphp/layout/displayastableonlynoheaders.php'); 
        break;
    
	case 'UpdateProgcookie':
		if (!allowedToOpen(2201,'1rtc')){
			echo 'No Permission'; exit();
		}
		include_once $path.'/acrossyrs/commonfunctions/fxngenrandpass.php';
		$progcookie=generatePassword(45);
		
		$sql='UPDATE `1_gamit`.`1rtcusers` SET EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now(), ProgCookieOld=ProgCookie,ProgCookie="'.$progcookie.'" WHERE IDNo='.intval($_POST['IDNo']);
		// echo $sql; exit();
		
        $stmt=$link->prepare($sql); $stmt->execute();
		header("Location:directoryedit.php");
	break;
	
    case 'EditSpecifics':
         if (allowedToOpen(64313,'1rtc')){
	 $title='Edit Specifics'; $formdesc='For the fields Tel, Mobile, & WorkAssign, you may use a semi-colon (;) to force a next line in the display.';
	 if (isset($_GET['Branch'])) { $sql=$sqlbranch.' AND BranchNo='.$_GET['IDNo']; $txnid=$_GET['IDNo'].'&Branch=true'; $columnnames=$columnnameslistbranch; $columnstoedit=$columnstoeditbranch;}
         elseif (isset($_GET['Dept'])) { $sql=$sqldept.' WHERE deptid='.$_GET['IDNo']; $txnid=$_GET['IDNo'].'&Dept=true'; $columnnames=$columnnameslistdept; 
		 
		 $columnstoedit=$columnstoeditdept;
		 
		 }
         else { $txnid=intval($_GET['IDNo']); $sql=$sql.'WHERE pu.IDNo='.$txnid; $columnnames=$columnnameslist;}
	 
	 $editprocess='directoryedit.php?w=Edit&IDNo='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
	 } else { header("Location:".$_SERVER['HTTP_REFERER']);}
         break;
		 
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	if (allowedToOpen(64313,'1rtc')){
            if (isset($_GET['Branch'])) {
             $columnstoedit=$columnstoeditbranch;  
             foreach ($columnstoedit as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
             $sql='UPDATE `1branches` SET '.$sql.' AreaNo=AreaNo WHERE BranchNo='.$_GET['IDNo']; 
            } elseif (isset($_GET['Dept'])) {
             $columnstoedit=array_diff($columnstoeditdept,array('Landline')); 
             foreach ($columnstoedit as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
             $sql='UPDATE `1departments` SET '.$sql.' tel=\''.addslashes($_REQUEST['Landline']).'\' WHERE deptid='.$_GET['IDNo']; 
            } else {                
        $sql=($_REQUEST['LocalNo']==0 or $_REQUEST['LocalNo']=='' )?'  LocalNo=null, ':' LocalNo=\''.addslashes($_REQUEST['LocalNo']).'\', ';
        $columnstoedit=array_diff($columnstoedit,array('LocalNo')); 
        foreach ($columnstoedit as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `1_gamit`.`1rtcusers` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' TimeStamp=Now() WHERE IDNo='.$_GET['IDNo']; }
        $stmt=$link->prepare($sql); $stmt->execute();
        }
        header("Location:directoryedit.php");
        break;
    
}
  $link=null; $stmt=null; 
?>
</div> <!-- end section -->
</body></html>