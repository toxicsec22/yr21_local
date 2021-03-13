<?php
    if (!allowedToOpen(301,'1rtc')) { goto noform;}
    elseif(isset($showbranches) and $showbranches==FALSE){ goto noform;}
if(allowedToOpen(array(306,307,308,309,311,312,313),'1rtc')){
	
	$sqlc='select 
	group_concat(CASE 
	WHEN ProcessID="306" then "1"
	WHEN ProcessID="307" then "2"
	WHEN ProcessID="308" then "3"
	WHEN ProcessID="309" then "4"
	WHEN ProcessID="311" then "5"
	WHEN ProcessID="312" then "6"
	end) as CompanyNo from permissions_2allprocesses where ProcessID in (306,307,308,309,311,312) and FIND_IN_SET('.$_SESSION['(ak0)'].',`AllowedPerID`)';
	$stmtc=$link->query($sqlc); $resultc=$stmtc->fetch();
	if (allowedToOpen(313,'1rtc')) {
		$compcond='where Active=1';
	}else{
		$compcond='where CompanyNo in ('.$resultc['CompanyNo'].')';
	}
 $sqlco='select CompanyName as Company, CompanyNo from 1companies '.$compcond.' ';	
}else{
	if (allowedToOpen(302,'1rtc')){ // stl, sam
		$sqlco='SELECT bg.CompanyNo, `CompanyName` as Company FROM `attend_1branchgroups` bg JOIN `1companies` c ON c.CompanyNo=bg.CompanyNo WHERE c.Active<>0 AND (bg.CNC='.$_SESSION['(ak0)'].' OR bg.TeamLeader='.$_SESSION['(ak0)'].' OR bg.SAM='.$_SESSION['(ak0)'].') GROUP BY bg.CompanyNo ORDER BY `CompanyName`'; 
                 $companycondition=' AND c.Active<>0 AND b.BranchNo IN (SELECT bg.BranchNo FROM `attend_1branchgroups` bg WHERE (bg.CNC='.$_SESSION['(ak0)'].' OR bg.TeamLeader='.$_SESSION['(ak0)'].' OR bg.SAM='.$_SESSION['(ak0)'].'))';
            } else if (allowedToOpen(305,'1rtc')){ //1central 2central
				$sqlco='SELECT CompanyNo, `CompanyName` as Company FROM `1companies` WHERE Active<>0 AND CompanyNo IN (1,2) ORDER BY `CompanyName`'; $companycondition=' AND b.BranchNo IN (40,100)';
			}  else 	    { $sqlco='SELECT CompanyNo, `CompanyName` as Company FROM `1companies` WHERE Active<>0 ORDER BY `CompanyName`'; $companycondition='';}
			$resultc['CompanyNo']='';
	
}
	                
	$sqlbranches='SELECT Branch, BranchNo from 1branches where BranchNo>=0 AND Active=1 AND CompanyNo='.$_SESSION['*cnum'].' ORDER BY Branch';
         
	include_once $path.'/acrossyrs/commonfunctions/reloadpage.php';
	
        echo '<br><div style="float:left;">'.strtoupper($_SESSION['*cname']).'&nbsp &nbsp &nbsp Branch  ' . $_SESSION['bnum'] . ' : &nbsp<font style="font-size: medium; background-color: #FFF;">' . strtoupper($_SESSION['@brn']).'</font>'; ?>
        &nbsp;
        <form action="/<?php echo $url_folder; ?>/backendphp/layout/changecompany.php?c=<?php echo $resultc['CompanyNo']; ?>" method="POST" style="display: inline-block;">
            <input type="text" name="company" list="companies" size=20 onchange="this.form.submit()" onkeydown="ignoreEnter()" placeholder="Choose company" style='font-style: italic;'>
		<datalist id="companies" style="height:500px ; overflow: auto; overflow-y:scroll;">
                <?php 
                    foreach($link->query($sqlco) as $row) {
                        ?>
                            <option value="<?php echo $row['Company']; ?>" label="<?php echo $row['CompanyNo']; ?>"></option>
                            <?php
                            } // end foreach
                            ?>
                                   
                    </datalist>
        </form>
        <form action="/<?php echo $url_folder; ?>/backendphp/layout/changebranch.php?c=<?php echo $resultc['CompanyNo']; ?>" method="POST" style="display: inline-block;">
	    <input type="text" name="branchname" list="branches" size=15 onchange="this.form.submit()" onkeydown="ignoreEnter()" placeholder="Choose branch" style='font-style: italic;'>
                <datalist id="branches" style="height:500px ; overflow: auto; overflow-y:scroll;">
                <?php // used this instead of select because list is automatically filtered, even if text is within the string; select sees start of string only, REMOVED:  --!.' - '.$row['Company'] 
                    foreach($link->query($sqlbranches) as $row) {
                        ?>
                            <option value="<?php echo $row['Branch']; ?>" label="<?php echo $row['BranchNo']; ?>"></option>
                            <?php
                            } // end foreach
                            ?>
                                   
                    </datalist>
       <!--  <input type="submit" name="select" value="Change Branch">           -->
        </form></div>
        <?php noform:
            $stmt=null;
//	}

?>