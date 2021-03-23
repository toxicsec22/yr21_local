<?php
$path=$_SERVER['DOCUMENT_ROOT'];

$showbranches=false;
$which=(!isset($_GET['w'])?'Encoding':$_GET['w']);

if($_GET['w']!='Page'){
include_once('../switchboard/contents.php');
} else {
	$currentyr=date('Y');
		include_once $path.'/acrossyrs/dbinit/userinit.php';
        $link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
}

switch ($which){
	case'Page':
?>
<style>
body{
	background-color:#f2f2f2;
}
h1{
	text-align:center;
	font-size:50px;
	color:#1a1a1a;
}
ol,li{
	list-style:none;
}
</style>
<?php

		echo'<title>Public Information Page</title><hr style="border: 11px solid #0f4c82;">
				<h1>Public Information Page</h1>
			<hr style="border: 1px solid #0f4c82;">';
		
			$sql='select * from systools_2publicinfopage Order By `OrderBy`';
			$stmt=$link->query($sql); $result=$stmt->fetchAll();
			$ol='<ol>';
			foreach($result as $res){
				
				if(empty($res['UnderTitleID'])){
					if(!empty($res['Link'])){
						$title='<a style="text-decoration:none; color:black;" href="/acrossyrs/publicinfopage/'.$res['Link'].'">'.$res['Title'].'</a>';
					}else{
						$title=''.$res['Title'].'';
					}
				
					$ol.='<li><h3>'.$title.'</h3></li>';
					//level2
					$sqls='select * from systools_2publicinfopage where UnderTitleID='.$res['TitleID'].' Order By `OrderBy`';
					$stmts=$link->query($sqls); $results=$stmts->fetchAll();
						foreach($results as $ress){
							if(!empty($ress['Link'])){
								$title2='<a style="text-decoration:none; color:black;" href="/acrossyrs/publicinfopage/'.$ress['Link'].'">'.$ress['Title'].'</a>';
							}else{
								$title2=''.$ress['Title'].'';
							}
							$ol.='<ol><li><h3>'.$title2.'</h3></li>';
							//level3
							$sqlt='select * from systools_2publicinfopage where UnderTitleID='.$ress['TitleID'].' Order By `OrderBy`';
							$stmtt=$link->query($sqlt); $resultt=$stmtt->fetchAll();
								foreach($resultt as $rest){
								if(!empty($ress['Link'])){
									$title3='<a style="text-decoration:none; color:black;" href="/acrossyrs/publicinfopage/'.$rest['Link'].'">'.$rest['Title'].'</a>';
								}else{
									$title3=''.$rest['Title'].'';
								}
									$ol.='<ol><li><h3>'.$title3.'</h3></li>';
										//level4
										$sqlf='select * from systools_2publicinfopage where UnderTitleID='.$rest['TitleID'].' Order By `OrderBy`';
										$stmtf=$link->query($sqlf); $resultf=$stmtf->fetchAll();
										foreach($resultf as $resf){
										if(!empty($resf['Link'])){
											$title4='<a style="text-decoration:none; color:black;" href="/acrossyrs/publicinfopage/'.$resf['Link'].'">'.$resf['Title'].'</a>';
										}else{
											$title4=''.$resf['Title'].'';
										}	
											$ol.='<ol><h3>'.$title4.'</h3>';
											$ol.='</ol>';
										}
									$ol.='</ol>';
								}
							$ol.='</ol>';
						}

				}

				
			}
			$ol.='</ol>';
		echo $ol;
	break;
	
	case'UploadedFiles':
		$dir='../../acrossyrs/publicinfopage/';
		$files = scandir($dir);
		echo '<title>Uploaded Files</title></br><h3>Uploaded Files</h3></br>';
		$c=1;
		foreach($files as $file){
			if($file != '.' and $file != '..'){
				echo ' <b>'.$c.'.</b> <a style="text-decoration:none; color:blue;" href="../../acrossyrs/publicinfopage/'.$file.'">'.$file.'</a> </br> ';
				$c++;	
			}
		}
	break;
	
	case'Encoding':
	if (!allowedToOpen(6691,'1rtc')) { echo 'No permission'; exit();}
	$title='Public Information Page';
	if(isset($_GET['status'])){
		$status='<b>'.$_GET['status'].'</b></br></br>';
	}else{
		$status='';
	}
	$formdesc='</i></br>'.$status.'<div style="background-color:#f2f2f2; width:310px; border: 1px solid #404040; padding:5px;" >
	<b>Upload:</b>
	<a style="display:inline; text-decoration:none; float:right;" href="publicinfopage.php?w=UploadedFiles"> View Uploaded Files Here</a></br></br>
	<form action="../acctg/uploadreceipt.php" method="POST" enctype="multipart/form-data">
   <input type="hidden" name="newdir" value="../../acrossyrs/publicinfopage"> 
   <input type="hidden" name="directory" value="../systools/publicinfopage.php?w=Encoding"> 
	<input type="file" name="userfile"><input type="submit" name="submit" value="Submit"> 
    </font> </form></div>
	
</br>
<div style="background-color:#f2f2f2; width:820px; border: 1px solid #404040; padding:5px;">
<b>Encoding:</b></br></br><form method="post" action="publicinfopage.php?w=Add">
					TitleID: <input type="text" name="TitleID" size="2" required>
					UnderTitleID: <input type="text" name="UnderTitleID" size="2" > 
					Title: <input type="text" name="Title" required> 					
					Link: <input type="text" name="Link"> 
					OrderBy: <input type="text" name="OrderBy" size="1" required>
					<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'"> 
					<input type="submit" name="submit"> 						
				</form></div></br><a style="text-decoration:none;" href="publicinfopage.php?w=Page"><b>View Announcement Page Here</b></a>';
		$sql='select * from systools_2publicinfopage Order By `TitleID`';
		$columnnames=array('TitleID','UnderTitleID','Title','Link','OrderBy');
		$txnidname='TitleID';
		$editprocess='publicinfopage.php?w=Edit&TitleID=';
		$editprocesslabel='Edit';
		$delprocess='publicinfopage.php?w=Delete&TitleID=';
		include('../backendphp/layout/displayastablenosort.php');
	
	break;
	
	case'Add':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	
	if(!empty($_POST['UnderTitleID'])){
		$undertitleid=',UnderTitleID=\''.$_POST['UnderTitleID'].'\'';
	}else{
		$undertitleid=',UnderTitleID=null';
	}
	
	$sql='INSERT INTO `systools_2publicinfopage` set TitleID=\''.$_POST['TitleID'].'\' '.$undertitleid.' ,Link=\''.$_POST['Link'].'\',Title=\''.$_POST['Title'].'\', OrderBy=\''.$_POST['OrderBy'].'\',  EncodedByNo=\''.$_SESSION['(ak0)'].'\', TimeStamp=Now()';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:publicinfopage.php?w=Encoding');
	break;
	
	case'Delete':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$TitleID=intval($_GET['TitleID']);
		$sql='delete from systools_2publicinfopage where TitleID=\''.$TitleID.'\'';
		// echo $sql; exit();
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:publicinfopage.php?w=Encoding');
	break;
	
	case'Edit':
	$TitleID= intval($_GET['TitleID']);
	$sql='select * from `systools_2publicinfopage` where TitleID=\''.$TitleID.'\'';
	// echo $sql;
	$stmt=$link->query($sql); $result=$stmt->fetch();
	echo '<title>Edit</title></br><h3>Edit?</h3>';
	echo'<form method="post" action="publicinfopage.php?w=EditProcess&TitleID='.$TitleID.'">
			TitleID <input type="text" name="TitleID" value="'.$result['TitleID'].'" size="2" required>
			UnderTitleID <input type="text" name="UnderTitleID" value="'.$result['UnderTitleID'].'" size="2" >
			Title <input type="text" name="Title" value="'.$result['Title'].'" required>
			Link <input type="text" name="Link" value="'.$result['Link'].'">
			OrderBy <input type="text" name="OrderBy" size="1" value="'.$result['OrderBy'].'" required>
			<input type="hidden" name="action_token" value="'.$_SESSION['action_token'].'">
			<input type="submit" name="submit" value="Edit">
		</form>';
	break;
	
	case'EditProcess':
	require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$TitleID= intval($_GET['TitleID']);
	
	if(!empty($_POST['UnderTitleID'])){
		$undertitleid=',UnderTitleID=\''.$_POST['UnderTitleID'].'\'';
	}else{
		$undertitleid=',UnderTitleID=null';
	}
	
	$sql='Update `systools_2publicinfopage` set TitleID =\''.$_POST['TitleID'].'\' '.$undertitleid.' ,Link =\''.$_POST['Link'].'\',Title=\''.$_POST['Title'].'\',OrderBy=\''.$_POST['OrderBy'].'\', EncodedByNo='.$_SESSION['(ak0)'].', TimeStamp=Now() where TitleID=\''.$TitleID.'\'';
	// echo $sql; exit();
	$stmt=$link->prepare($sql); $stmt->execute();
	header('Location:publicinfopage.php?w=Encoding');
	break;
}

?>