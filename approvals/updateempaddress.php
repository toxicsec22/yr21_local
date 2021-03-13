<?php 
$path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/dbinit/userinit.php');
include_once($path.'/acrossyrs/js/includesscripts.php');
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

$sqlcheckempinfo='SELECT IDNo,CTCNo FROM 0employeeinfo WHERE IDNo='.$_SESSION['(ak0)'];
    $stmtcheckinfo=$link->query($sqlcheckempinfo);
	
   if($stmtcheckinfo->fetch()>0){
	   $stmtcheckinfo=$link->query($sqlcheckempinfo);
	   $result=$stmtcheckinfo->fetch();
	  
	   if($result['CTCNo']==''){
			echo '<br><a style="background-color:white;color:red;padding:5px;" href="../'.$url_folder.'/generalinfo/idinfo.php?w=MyPersonalInfo" target="_blank"><i>Update Community Tax Certificate (CEDULA) '.$currentyr.'</i></a><br><br>';
		}
	   goto updated;
   }

?>
<script type="text/javascript">
function fetch_select(val)
{
 $.ajax({
 type: 'post',
 url: 'approvals/load_data.php',
 data: {
  get_option:val
 },
 success: function (response) {
  document.getElementById("new_select").innerHTML=response; 
 }
 });
}

function fetch_select2(val)
{
 $.ajax({
 type: 'post',
 url: 'approvals/load_data2.php',
 data: {
  get_option:val
 },
 success: function (response) {
  document.getElementById("new_select2").innerHTML=response; 
 }
 });
}

$(function () {
        $("#chkPermanent").click(function () {
            if ($(this).is(":checked")) {
                $("#dvPermanent").hide();
            } else {
                $("#dvPermanent").show();
            }
        });
    }); 
</script>

<div style="background-color:white;padding:20px;">
<form action="approvals/updatepersonalinfo.php" method="POST">
<h3>Update Information <br></h3>
<br>
<fieldset style="padding:6px;background-color:yellowgreen;">
          <legend style="background-color:yellow;">
      <span style="color:blue;"><b>&nbsp;PRESENT ADDRESS&nbsp;</b></span>
    </legend><br>
        <div>
        <div> <label>Street <span>*</span></label>
<input type="text" id="StreetPresent" name="StreetPresent" value="" size="30" maxlength="255" autocomplete="off" required/></div></div>
<br>
<div><div> <label>City / Province <span>*</span></label>

 <select onchange="fetch_select(this.value);" name="CPID_Present">
  <option>Select City / Province</option>
  <?php

 $sqlcityorprovince = "SELECT CityOrProvince,CPID FROM 1_gamit.0cityorprovince ORDER BY CityOrProvince";
			$stmtcityorprovince=$link->query($sqlcityorprovince); $rescityorprovince=$stmtcityorprovince->fetchAll();
			
            foreach ($rescityorprovince AS $row){
                $cpid = $row['CPID'];
                $cpname = $row['CityOrProvince'];
              
                echo "<option value='".$cpid."' >".$cpname."</option>";
            }
 ?>
 </select>

</div>
       <br>
		<div> <label>Barangay / Town <span>*</span></label>

<select id="new_select" name="BTID_Present">
 </select>
	
</div>



</div>
 
</fieldset>
<br>
<label for="chkPermanent">
    <input type="checkbox" name="SameAddress" id="chkPermanent" />
    Permanent is the same with the Present Address
</label>
<br><br>
<div id="dvPermanent" style="display: block">
   
<div><div><div><div>

<fieldset style="padding:6px;background-color:yellowgreen;">
          <legend style="background-color:yellow;"> 
      <span style="color:blue;"><b>&nbsp;PERMANENT ADDRESS&nbsp;</b></span>
     </legend>
	
<br>
        <div>
        <div><div> <label>Street <span>*</span></label>
<input type="text" name="StreetPermanent" value="" size="30" maxlength="255" autocomplete="off"/></div></div>
<br>

<div><div> <label>City / Province <span>*</span></label>
 <select onchange="fetch_select2(this.value);" name="CPID_Permanent">
  <option>Select City / Province</option>
  <?php

 $sqlcityorprovince = "SELECT CityOrProvince,CPID FROM 1_gamit.0cityorprovince ORDER BY CityOrProvince";
			$stmtcityorprovince=$link->query($sqlcityorprovince); $rescityorprovince=$stmtcityorprovince->fetchAll();
			
            foreach ($rescityorprovince AS $row){
                $cpid = $row['CPID'];
                $cpname = $row['CityOrProvince'];
              
                // Option
                echo "<option value='".$cpid."' >".$cpname."</option>";
            }
 ?>
 </select>

</div> 

<br><div> <label>Barangay / Town <span>*</span></label>

<select id="new_select2" name="BTID_Permanent">
 </select>
			
			
</div>

</div>
  </div>
</div></div></div></div>
</div>

</fieldset>
<br>


<fieldset style="padding:6px;background-color:yellowgreen;">
          <legend style="background-color:yellow;"> 
      <span style="color:blue;"><b>&nbsp;IN CASE OF EMERGENCY&nbsp;</b></span>
     </legend>
	
<br>
        <div>
        <div><div> <label>ICE Person <span>*</span></label>
<input type="text" name="ICEPerson" value="" size="30" maxlength="255" autocomplete="off" required/></div></div><br>
        <div><div> <label>Relationship to Employee <span>*</span></label>
<input type="text" name="RelationshiptoEmployee" value="" size="30" maxlength="255" autocomplete="off" required/></div></div>
<br>
<div><div> <label>ICE Contact Info <span>*</span></label>
<input type="text" name="ICEContactInfo" value="" size="30" maxlength="255" autocomplete="off" required/></div></div>
<br>
<div><div> <label>ICE Address <span>*</span></label>
<input type="text" name="ICEAddress" value="" size="30" maxlength="255" autocomplete="off" required/></div></div>
<br>

			
			
</div>

</fieldset>
<br>
<fieldset style="padding:6px;background-color:yellowgreen;">
          <legend style="background-color:yellow;"> 
      <span style="color:blue;"><b>&nbsp;OTHER PERSONAL INFO&nbsp;</b></span>
     </legend>
	
<br>
        <div>
        <div><div> <label>Mobile Number <span>*</span></label>
<input type="text" name="MobileNo" value="" size="30" maxlength="255" autocomplete="off" required/></div></div><br>
        <div><div> <label>Civil Status <span>*</span></label>
<select name="CivilStatus"><option value="">- Please Select -</option><option value="S">Single</option><option value="M">Married</option></select></div></div>
<br>
<div>
        <div><div> <label>Email </label>
<input type="text" name="Email" value="" size="30" maxlength="255" autocomplete="off"/></div></div><br>
			
			
</div>

</fieldset>

<br><i style="font-size:9pt;">Please double check your encoded information. *</i><br>
<input type="submit" style="background-color:blue;padding:5px;color:yellow;" value="Update My Information" OnClick="return confirm('All informations are correct? This action cannot be undone.');"></input>
</form>
</div>
<?php 
updated:
 ?>
