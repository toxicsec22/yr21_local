<?php

include_once $path.'/acrossyrs/commonfunctions/listoptions.php';

if(isset($outside) and $outside){ $yrpath=$path.'/'.$url_folder.'/backendphp/layout/'; } else { $yrpath='../backendphp/layout/'; }
   
if (in_array($which,array($list,$editspecs))){ 
   if(isset($showenc)) {   include_once($yrpath.'showencodedbybutton.php');}
   foreach($listssql as $listlookup){ echo comboBox($link,$listlookup['sql'],$listlookup['listvalue'],$listlookup['label'],$listlookup['listname']);}
   
if (isset($showenc) and $showenc==1) { array_push($columnnameslist,'EncodedBy','TimeStamp');}
} 

if (in_array($which,array($addcommand,$editcommand))){
   
        }

switch ($which){
    case $list:
       
            $columnnames=$columnentriesarray;
            $action=$file.$addcommand;
			
			if(!empty($columnentriesarray)){
				include($yrpath.'inputmainform.php');
				}
         
        $formdesc=!isset($upload)?'':'<br><a href='.$upload.'>Upload Data</a><br><br>'; 
	$columnnames=$columnnameslist;
	$title='';
	include($yrpath.'displayastable.php');       
	break; //End of Case List
    
    case $addcommand:            
	if (allowedToOpen($addallowed,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        if(isset($verify) and !$verify){  echo 'No permission'; exit;}
        // echo $firstfield; exit();
        foreach ($columnstoadd as $field) { //echo $field;
		$_POST[$field]=($field==$strrepfield?str_replace($strrepfieldfrom,$strrepfieldto,$_POST[$field]):$_POST[$field]);
            if(isset($firstfield) and $field==$firstfield){
            $sqlinsert.=' `' . $field. '`=\''.addslashes($_POST[$field]).'\'';
        } else {
        $sqlinsert.=', `' . $field. '`=\''.addslashes($_POST[$field]).'\''; }
        
        }
		
		if(isset($encodedbyno)){
			$sqlinsert.=', EncodedByNo='.$_SESSION['(ak0)'];
		}
		
        if($_SESSION['(ak0)']==1002) { echo $sqlinsert;} 
        $link->query($sqlinsert);
        } else { echo 'No permission'; exit;}
        header("Location:".$file.$list);
            break;
        
    case 'Upload':
        $title='Upload ';
		if(!isset($colnames)){
			$allcolumns='SHOW COLUMNS FROM '.$table; $stmt=$link->query($allcolumns); $allcols=$stmt->fetchAll();
			$colnames=array(); foreach ($allcols as $col) { $colnames[]=$col['Field']; }
		}
		
		if(!isset($requiredcol)){
			$notnullcolumns='SHOW COLUMNS FROM '.$table.' WHERE `Null` LIKE "NO"'; $stmt=$link->query($notnullcolumns); $notnullcols=$stmt->fetchAll();
			$requiredcol=array(); foreach ($notnullcols as $col) { $requiredcol[]=$col['Field']; }     
		}
 
        $required='';  foreach($requiredcol as $req){ $required=$required.'<li>'.$req.'</li>'; }
        $allowed=''; foreach($colnames as $col){ $allowed=$allowed.'<li>'.$col.'</li>'; }
        $specific_instruct=(!isset($specific_instruct)?'':$specific_instruct) // set in other file;
                . '<br><br><i>Required columns</i><ol>'.$required.'</ol><br><i>Allowed column titles</i><ol>'.$allowed.'</ol>';
        $tblname=$table; $firstcolumnname=$txnidname;
        $DOWNLOAD_DIR="../../uploads/"; 
			
			if(isset($requiredts) and isset($requireencodedby)){
				$requiredts=true;
				$requireencodedby=true;
			}
		
        include($yrpath.'uploaddata.php');
        if(($row-1)>0){ echo '';} //set this up <a href="properties.php?w=List" target="_blank">Lookup Newly Imported Data</a>';}
        
        break;
        
	//Start Of Case Delete
    case $delcommand:
	//access
         if (allowedToOpen($delallowed,'1rtc')){
         require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `'.$table.'` WHERE '.$txnidname.'='.$_GET[$txnidname].(isset($delcondition)?$delcondition:'');
		//echo $sql; exit();
                $link->query($sql);
				header('Location:'.$_SERVER['HTTP_REFERER']);
         } else { echo 'No permission'; exit;}
        break; //End of Case Delete
	
	//Start Of Case EditSpecifics
    case $editspecs:
        $title='Edit Specifics';
	$txnid=$_GET[$txnidname];

	//Condition For Edit Specifics
	$sql=$sql.' WHERE '.$txnidname.'='.$txnid; 
	$columnnames=$columnnameslist;
	
	//Input List
        
	$editprocess=$file.$editcommand.'&'.$txnidname.'='.$txnid;
        include($yrpath.'editspecificsforlists.php');
	break;
	//End of Case EditSpecifics

	//Start Of Case Edit
    case $editcommand:
        if (allowedToOpen($editallowed,'1rtc')){
            require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $txnid=$_GET[$txnidname];
	foreach ($columnstoedit as $field) {
            if(isset($firstfield) and $field==$firstfield){
            $sqlupdate.=' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\''; 
        } else {
            $sqlupdate.=', `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\''; 
        }
        }
		if(isset($encodedbyno)){
			$sqlupdate.=', EncodedByNo='.$_SESSION['(ak0)'];
		}
		
        $sqlupdate.=' WHERE '.$txnidname.'='.$txnid.(isset($editcondition)?$editcondition:''); if($_SESSION['(ak0)']==1002) { echo $sqlupdate;}
	$link->query($sqlupdate);
        }
    header("Location:".$file.$list);
    break;
    //End Of Case Edit
	
	
	//move to other table
	case $editcommand2:
	if (allowedToOpen($editallowed2,'1rtc')){
		 require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		 $txnid=$_GET[$txnidname];
		 
		 $sqlfetch.=' WHERE TxnID='.$txnid.'';
		 $stmtfetch=$link->query($sqlfetch); $resultfetch=$stmtfetch->fetch();
		 
		 $sqladdlins='';
		if(isset($getcolumn1)){
		  $sqladdlins.=' '.$columninsert1.'="'.$resultfetch[$getcolumn1].'",';
		}
		if($getcolumn2){
		  $sqladdlins.=' '.$columninsert2.'="'.$resultfetch[$getcolumn2].'",';
		 }
		 
		 if($resultfetch[$requiredcol]==''){
			 echo $errornotif;
			 exit();
		 }
		  
		  $sqlnewinsert.=' '.$sqladdlins.$sqlnewinsertaddl;
		  // echo $sqlnewinsert; exit();
		 $link->query($sqlnewinsert);
		 
		 if(isset($sqlupdate2)){
			 $sqlupdate2.=' WHERE TxnID='.$txnid;
			 // echo $sqlupdate2; exit();
			$link->query($sqlupdate2);
		 }
		  header("Location:".$file.$list);
		 
		 
	}
	
	break;
}
      $link=null; $stmt=null;
?>