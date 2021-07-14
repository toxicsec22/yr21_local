<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(6489,'1rtc')) { echo 'No permission'; exit; }

$which=(!isset($_GET['w'])?'IDPrint':$_GET['w']);

if($which<>'Print'){
    $showbranches=false;
    include_once('../switchboard/contents.php');
} else {
    include_once($path.'/acrossyrs/dbinit/userinit.php');
	$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;

  echo '<style>
  @media print {
    #printPageButton {
      display: none;
    }
    @page { size: landscape;
      margin: 0;
    }



  }

  </style>';
}
    include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
    include_once('../backendphp/layout/linkstyle.php');
?>
<br><div id="section" style="display: block;">
<a id='link' href="idprinting.php" >Print ID for Probationary</a>
<a id='link' href="idprinting.php?w=UploadPicAndSignature" >Upload Picture and Signature for Regular Employees</a> 
<a id='link' href="idprinting.php?w=ReloadAppData" >Reload App Data</a>
<br>
<?php




switch ($which)
{
		case 'IDPrint':

			$title='Print ID for Probationary';

            $sql='SELECT e.IDNo AS TxnID,e.IDNo,FullName, cp.Position,

            IF(cp.deptid NOT IN (1,2,3,10),cp.Department,Branch) AS `Dept`,

            (CASE
                WHEN cp.deptid IN (0,2,10) THEN Department
                WHEN cp.deptid=1 THEN "Supply Chain Department"
                WHEN cp.deptid=11 THEN "Sales Department"
                WHEN cp.deptid IN (30,50,55) THEN CONCAT(Department," Dept.")
                ELSE CONCAT(Department," Department")

            END) AS `DeptName`


             FROM `1employees` e LEFT JOIN `1companies` c on e.RCompanyNo=c.CompanyNo JOIN `1_gamit`.`0idinfo` i ON i.IDNo=e.IDNo JOIN attend_30currentpositions cp ON cp.IDNo=e.IDNo WHERE e.IDNo NOT IN (1001,1002) AND EmpStatus=0 AND Resigned<>1 ';
            $columnnameslist=array('IDNo','FullName','DeptName');

	 $sql.=' ORDER BY e.DateHired DESC,dept';

	 $columnnames=$columnnameslist;
			$width='70%';
			$editprocess='idprinting.php?w=Print&IDNo='; $editprocesslabel='Print ID';
			include('../backendphp/layout/displayastable.php');

		break;



case 'Print':
    echo '<title>Print ID</title>';
echo '<style>

@font-face {
    font-family: MetropolisRegular;
    src: url("fonts/Metropolis.otf") format("opentype");
 }

 @font-face {
    font-family: MetropolisBold;
    src: url("fonts/Metropolis-Bold.otf") format("opentype");
 }

 @font-face {
    font-family: MetropolisSemiBold;
    src: url("fonts/Metropolis-SemiBold.otf") format("opentype");
 }

 .fontreg {
    font-family: MetropolisRegular;
 }
 .fontsemibold {
    font-family: MetropolisSemiBold;
 }
 .fontbold {
    font-family: MetropolisBold;
 }


';



?>

  body{
		  	 background:#FFFFFF;
		  }
#bg {
  width: 1000px;
  height: 450px;

  margin:60px;
 	float: left;

}

#id {
  width:204.48px;
  height:324.4px;
  position:absolute;
  opacity: 0.88;
font-family: sans-serif;

		  	transition: 0.4s;
		  	background-color: #FFFFFF;
              border:1px solid gray;
		}


</style>


<?php
    $idx = $_GET['IDNo'];
      $sql ="SELECT id.IDNo,cp.deptid,DATE_FORMAT(DATE_ADD(e.DateHired, INTERVAL 6 MONTH),'%M %Y') AS IDValidity,CompanyNo,LENGTH(ICEAddress) AS lenadd,id.Nickname,ICEPerson,ICEAddress,id.TIN,id.PHICNo,ICEContactInfo,CONCAT(id.FirstName,' ',id.SurName) AS FullName, IF(cp.deptid IN (50,55),'14.9px','16.9px') AS fontsize FROM 1_gamit.0idinfo id JOIN attend_30currentpositions cp ON id.IDNo=cp.IDNo JOIN 1employees e ON id.IDNo=e.IDNo JOIN 1companies c ON e.RCompanyNo=c.CompanyNo WHERE id.IDNo=$idx ";
      $stmt=$link->query($sql); $resinfo=$stmt->fetch();


          ?>

<div id="bg">
            <div id="id">
            	 <table>
        <tr> <td>

        	</td>
        <td></td>
       </tr>
    </table><center>
        <?php

      $IDNo=$resinfo['IDNo'];
      $FullName=$resinfo['FullName'];
      $Nickname=$resinfo['Nickname'];
      $TIN=$resinfo['TIN'];
      $PHICNo=$resinfo['PHICNo'];
      $ICEPerson=$resinfo['ICEPerson'];
      $ICEAddress=$resinfo['ICEAddress'];
      $ICEContactInfo=$resinfo['ICEContactInfo'];
      $fontsize=$resinfo['fontsize'];
      $IDValidity=$resinfo['IDValidity'];
      $CompanyNo=$resinfo['CompanyNo'];
      $lenadd=$resinfo['lenadd'];
      $frontid='idfront_'.$resinfo['deptid'];
      $backid='idback_'.$resinfo['CompanyNo'];
      ?>

<style>
#id::before {
  content: "";
  position: absolute;
  width: 100%;
  height: 100%;
  background: url('pics/<?php echo $frontid;?>.jpg');
  background-repeat:repeat-x;
  background-size: 204.48px 324.4px;
  z-index: -1;
  text-align:center;

}
 .container{
			  margin-left:31px;
		  }
		 .id-1{
		  	transition: 0.4s;
		  	width:204.48px;
		  	height:324.4px;
		  	background: url('pics/<?php echo $backid;?>.jpg');
		  	text-align:center;
            background-repeat:repeat-x;
            background-size: 204.48px 324.4px;
		  	float: left;
		  	margin:auto;
		  	margin-left:250px;
            border: 1px solid gray;


		  }
      </style>


      <?php


             	 	echo '<div style="transform: rotate(-90deg);position:fixed;color:white;font-size:'.$fontsize.';"><font class="fontsemibold"></font></div>';
									echo"<div style='margin-top:90px;margin-left:33px;'><img src='../generalinfo/employeepics/$IDNo.jpg' height='90px' width='90px' alt=''></div>";


             	 	 ?>   </center>              <div class="container" align="center">

      	<p style="margin-top:45px;font-size:12pt;" class="fontbold"><?php echo $Nickname;?><br><font style="font-size:10pt;"><?php echo $FullName;?></font></p>
		  <p style="margin-top:30px;font-size:6.5pt;" class="fontbold">Employee ID No. <?php echo $IDNo;?><br><font class="fontreg">Valid until: <?php echo $IDValidity;?></font></p>
      </div>
            </div>
            <div class="id-1">
    	<?php



        echo '<br>
        <font class="fontreg" style="font-size:6pt;">
        TIN No.: '.$TIN.'<hr style="margin-top:-1px;visibility:hidden;" />
        PHILHEALTH No.: '.$PHICNo.'
        <hr width="80%" size="1px">Person to notify in case of emergency:
            <hr style="margin-top:-1px;visibility:hidden;" />'.$ICEPerson.'<hr style="margin-top:-1px;visibility:hidden;" />Contact No.: '.$ICEContactInfo.'<hr style="margin-top:-1px;visibility:hidden;" />'.$ICEAddress.'</font>';

        ?>

     </div>
</div>

        </div>


          <?php
    echo '<button id="printPageButton" style="background-color:green;color:white;font-size:18pt;" onclick="window.print()">Print / Save as PDF</button>';

break;



case 'UploadPicAndSignature':
  	$title='Upload Picture and Signature for Regular Employees';
    $formdesc='</i>'.(isset($_GET['done'])?'<br><font color="green">DONE. Pls check smart id app.</font>':'').'<i><br><br>- In uploading images, make sure that the ID Picture you are going to upload is a png file and has a size of 900px X 900px for good quality <br>
    - For the signature, it must be a png file with a size of 647px X 371px for good quality<br><br><br><form action="idprinting.php?w=uploadprocess" method="POST" enctype="multipart/form-data">Insert ID Number<i style=color:"red">*</i> <input type="text" name="IDNum" size=4 autocomplete="off" list="employees" required> ID Picture<i style color:"red">*</i> <input type="file" name="userfile" required> Signature<i style color:"red">*</i> <input type="file" name="usersign" required>
                 <input type="submit" name="submit" value="Submit"> </form>';
              // $sql='SELECT *,CONCAT(id.Nickname," ",id.MiddleName," ",id.SurName) AS FullName,dept AS DeptName FROM 1_gamit.foridprinting WHERE Picture IS NOT NULL AND `Signature` IS NOT NULL';
              $sql='SELECT `id`.`IDNo` AS `IDNo`,CONCAT(id.Nickname," ",id.MiddleName," ",id.SurName) AS FullName,case when `d`.`deptid` in (0,2,10) then `d`.`department` when `d`.`deptid` = 1 then "Supply Chain Department" when `d`.`deptid` = 11 then "Sales Department" when `d`.`deptid` in (30,50,55) then concat(`d`.`department`," Dept.") else concat(`d`.`department`," Department") end AS `DeptName` from (((((`1_gamit`.`0idinfo` `id` join `attend_30latestpositionsinclresigned` `lpir` on(`id`.`IDNo` = `lpir`.`IDNo`)) join `1employees` `e` on(`lpir`.`IDNo` = `e`.`IDNo`)) join `attend_1positions` `p` on(`lpir`.`PositionID` = `p`.`PositionID`)) join `1departments` `d` on(`p`.`deptid` = `d`.`deptid`)) join `1_gamit`.`1idpicsign` `idpic` on(`id`.`IDNo` = `idpic`.`IDNo`)) where `lpir`.`Resigned` <> 1';
              $columnnameslist=array('IDNo','FullName','DeptName');

  	 $sql.=' ';

  	 $columnnames=$columnnameslist;
  			$width='70%';
        $txnidname='IDNo';
        $delcommand='Delete';
  			$delprocess='idprinting.php?w=DeletePicAndSignature&IDNo=';
  			include('../backendphp/layout/displayastable.php');

        include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
        echo comboBox($link,'SELECT CONCAT(Nickname, " - ", FirstName, " ", SurName) AS Name, IDNo FROM `1employees` WHERE Resigned=0','Name','IDNo','employees');

  break;

case 'ReloadAppData':

  $sql2='DELETE FROM 1_gamit.foridprintingdata';
  $stmt=$link->prepare($sql2); $stmt->execute();

  $sql2='INSERT INTO 1_gamit.foridprintingdata SELECT `id`.`IDNo` AS `IDNo`,`id`.`Nickname` AS `Nickname`,concat(`id`.`FirstName`," ",LEFT(`id`.`MiddleName`,1),". ",`id`.`SurName`) AS `FullName`,concat("Valid until ",monthname(curdate())," ",year(curdate()) + 2) AS `ValidUntil`,`id`.`ICEPerson` AS `ICEPerson`,`id`.`ICEContactInfo` AS `ICEContactInfo`,`id`.`ResTel` AS `ICETel`,`id`.`ICEAddress` AS `ICEAddress`,`id`.`PHICNo` AS `PHICNO`,"" AS `SMART_PRTMARK`,"" AS `SMART_PRTINFO`,`idpic`.`Picture` AS `Picture`,`idpic`.`PictureConf` AS `PictureConf`,`idpic`.`Sign` AS `Signature`,case when `d`.`deptid` in (0,2,10) then `d`.`department` when `d`.`deptid` = 1 then "Supply Chain Department" when `d`.`deptid` = 11 then "Sales Department" when `d`.`deptid` in (30,50,55) then concat(`d`.`department`," Dept.") else concat(`d`.`department`," Department") end AS `dept` from (((((`1_gamit`.`0idinfo` `id` join `attend_30latestpositionsinclresigned` `lpir` on(`id`.`IDNo` = `lpir`.`IDNo`)) join `1employees` `e` on(`lpir`.`IDNo` = `e`.`IDNo`)) join `attend_1positions` `p` on(`lpir`.`PositionID` = `p`.`PositionID`)) join `1departments` `d` on(`p`.`deptid` = `d`.`deptid`)) join `1_gamit`.`1idpicsign` `idpic` on(`id`.`IDNo` = `idpic`.`IDNo`)) where `lpir`.`Resigned` <> 1';
  // echo $sql2;
  $stmt=$link->prepare($sql2); $stmt->execute();

  header("Location:idprinting.php?w=UploadPicAndSignature&done=1");

break;

case 'uploadprocess':
    $imgData = addslashes(file_get_contents($_FILES['userfile']['tmp_name']));

    $imgDataSign = addslashes(file_get_contents($_FILES['usersign']['tmp_name']));

    // $imagePic = getimageSize($_FILES['userfile']['tmp_name']);
    // $maxsize = 307200; //300KB
    $photo_filename=$_FILES['userfile']['name'];

    // $imageSign = getimageSize($_FILES['usersign']['tmp_name']);
    $maxsize = 307200; //300KB
    $photo_filename2=$_FILES['usersign']['name'];

    $ext=pathinfo($photo_filename, PATHINFO_EXTENSION);
    if($ext!== 'png' AND $ext !=='PNG'){echo 'Error! Invalid File Type.'; exit(); }
    if(($_FILES['userfile']['size'] >= $maxsize)){echo 'Error! Invalid File Size (MAX 300KB).'; exit(); }

    $ext2=pathinfo($photo_filename2, PATHINFO_EXTENSION);
    if($ext2!== 'png' AND $ext2 !=='PNG'){echo 'Error! Invalid File Type.'; exit(); }
    if(($_FILES['usersign']['size'] >= $maxsize)){echo 'Error! Invalid File Size (MAX 300KB).'; exit(); }

    $sql2='DELETE FROM 1_gamit.1idpicsign WHERE IDNo='.$_POST['IDNum'].'';
    $stmt=$link->prepare($sql2); $stmt->execute();

    $sql2='INSERT INTO 1_gamit.1idpicsign SET Picture="'.$imgData.'", Sign="'.$imgDataSign.'",IDNo="'.$_POST['IDNum'].'",TimeStamp=NOW()';
    $stmt=$link->prepare($sql2); $stmt->execute();

    header("Location:idprinting.php?w=UploadPicAndSignature");

  break;

case 'DeletePicAndSignature':
    $sql2='DELETE FROM 1_gamit.1idpicsign WHERE IDNo='.$_GET['IDNo'].'';
    $stmt=$link->prepare($sql2); $stmt->execute();

    header("Location:idprinting.php?w=UploadPicAndSignature");

  break;


}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
