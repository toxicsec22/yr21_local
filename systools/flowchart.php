<!DOCTYPE html>
<?php
	$path=$_SERVER['DOCUMENT_ROOT'];
	include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
	include_once $path.'/acrossyrs/dbinit/userinit.php';
	if (isset($_GET['w']) AND $_GET['w']<>'Preview'){
		if (!allowedToOpen(83000,'1rtc')){ echo 'No Permission'; exit();}
		include_once('../switchboard/contents.php');
	}
	$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;
	
	
	$which=(!isset($_GET['w'])?'List':$_GET['w']);
	if (in_array($which, array('List','EditSpecificsProcesses'))){
		$sql='SELECT TxnID,FlowChartTitle,FCPosition,Theme,Posted,CONCAT(Nickname," ",Surname) AS EncodedBy FROM systools_2flowchartmain fcm JOIN 1_gamit.0idinfo id ON fcm.EncodedByNo=id.IDNo';
		$columnnameslist=array('FlowChartTitle','FCPosition','Theme','Posted','EncodedBy');
		// print_r($columnnameslist);
		$columnstoadd=array('FlowChartTitle','FCPosition','Theme');
		echo '<datalist id="direction"><option value="TB">Top Bottom</option><option value="BT">Bottom Top</option><option value="RL">Rigth Left</option><option value="LR">Left Right</option></datalist>';
		echo '<datalist id="theme"><option value="default"></option><option value="dark"></option><option value="neutral"></option></datalist>';
	}

?>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Flowchart Creation Tool</title>
</head>
<body>


<?php switch ($which) {
	
	case 'List':
		$title='List of Processes';
		$formdesc='Add New Process.';
		$method='POST';
				$columnnames=array(
				array('field'=>'FlowChartTitle','caption'=>'Process Title','type'=>'text','size'=>25,'required'=>true),
				array('field'=>'FCPosition','caption'=>'FlowChart Direction','type'=>'text','size'=>15,'required'=>true,'list'=>'direction'),
				array('field'=>'Theme','type'=>'text','size'=>15,'required'=>true,'list'=>'theme')
				);
							
		$action='flowchart.php?w=AddProcess'; $fieldsinrow=4; $liststoshow=array();
		include('../backendphp/layout/inputmainform.php');
		
		$delprocess='flowchart.php?w=DeleteProcess&TxnID=';
		  
		$addlprocess='flowchart.php?w=Preview&TxnID=';
		$addlprocesslabel='Flowchart';
		
		$title=''; $formdesc=''; 
                
        $txnidname='TxnID';
		$columnnames=$columnnameslist;
		$width="70%";
		include('../backendphp/layout/displayastable.php');
	break;
	
	
	case 'AddProcess':
		require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql = 'INSERT INTO systools_2flowchartmain (FlowChartTitle, FCPosition, Theme, EncodedByNo) VALUES (\''.$_POST['FlowChartTitle'].'\',\''.$_POST['FCPosition'].'\',\''.$_POST['Theme'].'\','.$_SESSION['(ak0)'].')';
		$stmt=$link->prepare($sql); $stmt->execute();
		header('Location:'.$_SERVER['HTTP_REFERER']);
	break;
	
	
	case 'DeleteProcess':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
		$sql='DELETE FROM `systools_2flowchartmain` WHERE TxnID='.intval($_GET['TxnID']).' AND TxnID<>1';
		$stmt=$link->prepare($sql); $stmt->execute();
        header("Location:".$_SERVER['HTTP_REFERER']);
    break;
	
	
	case 'Preview':
	
	$txnid=intval($_GET['TxnID']);
	//main
	$sql = 'SELECT fcm.*,CONCAT(Nickname," ",Surname) AS LastUpdateBy FROM systools_2flowchartmain fcm JOIN 1_gamit.0idinfo id ON fcm.EncodedByNo=id.IDNo WHERE fcm.TxnID='.intval($txnid).'';
	$stmt = $link->query($sql); $rowtitle=$stmt->fetch();
	
	
	
	if (isset($_GET['print'])){ goto escform; }
	echo '<h3><a href="flowchart.php?w=Preview&print=1&TxnID='.$rowtitle['TxnID'].'" target="_blank">'.$rowtitle['FlowChartTitle'].' Flowchart</a></h3>';
	
	if (($rowtitle['EncodedByNo']==$_SESSION['(ak0)'] AND $rowtitle['Posted']==0)){
		echo '<form action="flowchart.php?w=FlowchartStyle" method="POST">Flowchart Title: <input type="text" name="FlowChartTitle" value="'.$rowtitle['FlowChartTitle'].'"> Direction: <select name="FCPosition"><option value="TB" '.($rowtitle['FCPosition']=='TB'?'selected':'').'>Top Bottom</option><option value="BT" '.($rowtitle['FCPosition']=='BT'?'selected':'').'>Bottom Top</option><option value="RL" '.($rowtitle['FCPosition']=='RL'?'selected':'').'>Right Left</option><option value="LR" '.($rowtitle['FCPosition']=='LR'?'selected':'').'>Left Right</option></select> Theme: <select name="Theme"><option value="default" '.($rowtitle['Theme']=='default'?'selected':'').'>Default</option><option value="dark" '.($rowtitle['Theme']=='dark'?'selected':'').'>Dark</option><option value="neutral" '.($rowtitle['Theme']=='neutral'?'selected':'').'>Neutral</option></select><input type="hidden" name="TxnID" value="'.$txnid.'"> <input type="submit" value="Change"></form>';
	}
	escform:
	
	
	echo '<br><div>';
	if (!isset($_GET['print'])){
		echo '<div><form method="POST" action="flowchart.php?w=AddNote&TxnID='.$txnid.'"><input type="text" size="30" name="Note" autocomplete="off"> <input type="submit" name="btnAddNote" value="Add Note"></form></div><br>';
	}
	$sql='SELECT Note,TxnSubNoteid FROM systools_2flowchartsubnotes WHERE TxnID='.$txnid.' ORDER BY TxnID';
	$stmt = $link->query($sql); $rownote=$stmt->fetchAll();
	
		if($stmt->rowCount()>0){
			echo '<table style="border:1px solid;padding:2px;">';
			echo '<tr>';
			echo '<td colspan=2>';
			echo '<b>Important Notes</b>';
			echo '</td>';
			echo '</tr>';
			
			foreach ($rownote as $note){
				echo '<tr><td>*</td><td>'.$note['Note'].' '.(!isset($_GET['print'])?'<a style="color:red;" href="flowchart.php?w=DeleteNote&TxnID='.$txnid.'&TxnSubNoteid='.$note['TxnSubNoteid'].'"" OnClick="return confirm(\'Really delete this?\');"><sup>del</sup></a>':'').'</td></tr>';
			}
			
			echo '</table>';
		}
		echo '</div>';
	?>


<div class="mermaid">
<?php
	//sub
	$sql = 'SELECT fcs.* FROM systools_2flowchartsub fcs JOIN systools_2flowchartmain fcm ON fcs.TxnID=fcm.TxnID WHERE fcs.TxnID='.$txnid.' ORDER By NodeID,NextNode';
	$stmt = $link->query($sql);

	//start constant A-B
	echo '
	graph '.$rowtitle['FCPosition'].'
	A['.$rowtitle['FlowChartTitle'].'] -.- B( Start '.($rowtitle['Posted']==0?'<sup><i><b>B</b></i></sup>':'').')
	';
	//additional
	while ($row=$stmt->fetch()) {
		/* 	Shapes
			1 - oval, 2 - diamond, 3 - box, 4 - Asymmetric, 5 - circle
		*/
		echo $row['NodeID']; echo ($row['DottedLine?']==0?'-->':'-.->'); echo (!empty($row['CommentInLine'])?'|'.$row['CommentInLine'].'|':''); echo $row['NextNode']; echo ($row['Shape']==6?'':(($row['Shape']==1?'(':($row['Shape']==2?'{':($row['Shape']==3?'[':($row['Shape']==4?'>':'((')))).'<center>'.$row['CommentInShape'].' '.($rowtitle['Posted']==0?'<sup><i><b>'.$row['NextNode'].'</b></i></sup> ':'').'</center>'.($row['Shape']==1?')':($row['Shape']==2?'}':($row['Shape']==3?']':($row['Shape']==4?']':'))')))))); echo "\r\n";

	}
?>
</div>


<script src="../../acrossyrs/js/flowchart/mermaid.js"></script>
<script>
	mermaid.initialize({startOnLoad: true, theme: <?php echo "'".$rowtitle['Theme']."'";?>});
</script>
<?php
	if ($rowtitle['TxnID']<>1 AND (!isset($_GET['print']))){
		include_once('../backendphp/layout/linkstyle.php');
		echo '<a id="link" href="flowchart.php?w=Preview&TxnID=1">Show Demo</a>';
	}
	echo '<br><br>';
	if (isset($_GET['print'])){ goto escinputform; }
	$stmt2 = $link->query($sql); //flowchart sub query
	echo ($rowtitle['EncodedByNo']==$_SESSION['(ak0)']?'<form action="flowchart.php?w=PostUnpost" method="POST"><input type="hidden" name="Posted" value="'.$rowtitle['Posted'].'"><input name="TxnID" type="hidden" value="'.$txnid.'"><input style="background-color:'.($rowtitle['Posted']==0?'orange':'yellow').';" type="submit" value="'.($rowtitle['Posted']==1?'UN':'').'Post Flowchart?"></form>':'').''.(($rowtitle['Posted']==0 AND $rowtitle['EncodedByNo']<>$_SESSION['(ak0)'])?'<form action="flowchart.php?w=Claim&TxnID='.$rowtitle['TxnID'].'" method="POST"> Last Update By: '.$rowtitle['LastUpdateBy'].' ('.$rowtitle['Timestamp'].')<br><input style="background-color:blue;color:white;" type="submit" value="Claim/Update Flowchart"></form>':'').'<br>';
	if ($rowtitle['Posted']==0 AND $rowtitle['EncodedByNo']==$_SESSION['(ak0)']){
		echo '<table style="background-color:skyblue;border-collapse: collapse;" border="1px solid"><thead><th style="width:103px;">NodeID</th><th style="width:103px;">NextNode</th><th style="width:84px;">Shape</th><th style="width:68px;">Comment<br>InLine</th><th style="width:243px;">CommentInShape</th><th style="width:60px;">Dotted<br>Line?</th><th></th></thead>';
		while ($row2=$stmt2->fetch()) {
			echo '<tr><form action="flowchart.php?w=UpdateFlowChartSub" method="POST" autocomplete="off">';
			echo '<td style="width:103px;"><input type="text" name="NodeID" value="'.$row2['NodeID'].'" size="10"/></td>';
			echo '<td style="width:103px;"><input type="text" name="NextNode" value="'.$row2['NextNode'].'" size="10"/></td>';
			// echo '<td><input type="text" name="Shape" value="'.$row2['Shape'].'" size="10"/></td>';
			echo '<td style="width:84px;"><select name="Shape"/><option value="1" '.($row2['Shape']==1?'selected':'').'>Oval</option><option value="2" '.($row2['Shape']==2?'selected':'').'>Diamond</option><option value="3" '.($row2['Shape']==3?'selected':'').'>Box</option><option value="5" '.($row2['Shape']==5?'selected':'').'>Circle</option><option value="6" '.($row2['Shape']==6?'selected':'').'>Connect</option><option value="0">Unset</option></select></td>';
			echo '<td style="width:68px;"><input type="text" name="CommentInLine" value="'.$row2['CommentInLine'].'" size="5"/></td>';
			echo '<td style="width:243px;"><input type="text" name="CommentInShape" value="'.$row2['CommentInShape'].'" size="30"/></td>';
			echo '<td style="width:60px;"><select name="DottedLine?"/><option value="0" '.($row2['DottedLine?']==0?'selected':'').'>No</option><option value="1" '.($row2['DottedLine?']==1?'selected':'').'>Yes</option><option value="0">Unset</option></select></td>';
			// echo '<td><input type="text" name="ExtraCommentStyle" value="'.$row2['ExtraCommentStyle'].'" size="5"/></td>';
			// echo '<td><input type="text" name="OrderBy" value="'.$row2['OrderBy'].'" size="5"/></td>';
			echo '<input type="hidden" name="TxnSubid" value="'.$row2['TxnSubid'].'" size="5"/>';
			echo '<input type="hidden" name="TxnID" value="'.$row2['TxnID'].'" size="5"/>';
			echo '<td>'.($rowtitle['Posted']==0?'<input type="submit" name="UpdateFlowChartSub" value="Enter">':'').'</td></tr></form>';
		}
		echo '</table>';
	} else if ($rowtitle['Posted']==1) {
		$columnnameslist=array('NodeID', 'NextNode','CommentInShape');
		$title='';
		$columnnames=$columnnameslist;
		$hidecontents=true;
		$width="40%";
		include('../backendphp/layout/displayastable.php');
		
	}
	
	
	if ($rowtitle['Posted']==0 AND $rowtitle['EncodedByNo']==$_SESSION['(ak0)']){
		echo '<table style="background-color:black;border-collapse: collapse;" border="1px solid"><tr>';
		echo '<form action="flowchart.php?w=InsertFlowChartSub" method="POST" autocomplete="off" auto="off">';
		echo '<td style="width:103px;"><input type="text" name="NodeID" placeholder="New Entry Here" size="10"></td>';
		echo '<td style="width:103px;"><input type="text" name="NextNode" size="10" placeholder="New Entry Here"/></td>';
		// echo '<td><input type="text" name="Shape" size="10" placeholder="New Entry Here"/></td>';
		echo '<td style="width:84px;"><select name="Shape"><option value="3">Box</option><option value="1">Oval</option><option value="2">Diamond</option><option value="5">Circle</option><option value="6">Connect</option></select></td>';
		echo '<td style="width:68px;"><input type="text" name="CommentInLine" size="5" placeholder="New Entry Here"/></td>';
		echo '<td style="width:243px;"><input type="text" name="CommentInShape" size="30" placeholder="New Entry Here"/></td>';
		echo '<td style="width:60px;"><select name="DottedLine?"><option value="0">No</option><option value="1">Yes&nbsp;&nbsp;&nbsp;</option></select></td>';
		// echo '<td><input type="text" name="ExtraCommentStyle" size="5" placeholder="New Entry Here"/></td>';
		// echo '<td><input type="text" name="OrderBy" size="5" placeholder="New Entry Here"/></td>';
		echo '<input type="hidden" name="TxnID" value="'.$_GET['TxnID'].'" size="5"/>';
		echo '<td><input type="submit" name="InsertFlowChartSub" value="ADD NEW STEP"/></td>';
		
		echo '</form>';
		echo '</tr></table>';
	}
	
	escinputform:
	if (($_GET['TxnID']==1 AND (!isset($_GET['print'])))){
		echo '<style type="text/css">
            .oval {
                width: 90px;
                height: 25px;
                background: #ffffff;
                border-radius: 40px;
				border: 1px solid black;
            }
			.arrow {
				width:128px;
			}
			.line {
				margin-top:14px;
				width:103px;
				background:skyblue;
				height:5px;
				float:left;
			}
			.point {    
				width: 0;
				height: 0; 
				border-top: 15px solid transparent;
				border-bottom: 15px solid transparent;
				border-left: 25px solid skyblue;
				float:right;
			}
			.circle {
			  height: 35px;
			  width: 35px;
			  background-color: #ffffff;
			  border-radius: 50%;
			  border: 1px solid black;
			}
			
			#diamond {
			  width: 0;
			  height: 0;
			  border: 20px solid transparent;
			  border-bottom-color: lightgray;
			  position: relative;
			  top: -20px;
			}
			#diamond:after {
			  content: "";
			  position: absolute;
			  left: -20px;
			  top: 20px;
			  width: 0;
			  height: 0;
			  border: 20px solid transparent;
			  border-top-color: lightgray;
			}
			 #pointer {
			  width: 120px;
			  height: 40px;
			  position: relative;
			  background: skyblue;
			}
			#pointer:after {
			  content: "";
			  position: absolute;
			  left: 0;
			  bottom: 0;
			  width: 0;
			  height: 0;
			  border-left: 20px solid white;
			  border-top: 20px solid transparent;
			  border-bottom: 20px solid transparent;
			}
			.rectangle {
			  height: 20px;
			  width: 90px;
			  background-color: #ffffff;
			  border: 1px solid black;
			}
        </style>';
		echo '<div>';
			echo '<div style="float:left;">';
				echo '<br style="line-height:10px;"><h3>Symbols and Meaning</h3>';
				echo '<table><tr><td><b>Shape</b></td><td><b>Symbols</b></td><td><b>Meaning</b></td></tr>';
				echo '<tr><td> Oval</td><td><div class="oval"></div></td><td>Start/End</td></tr>';
				// echo '<tr><td><div class="arrow"><div class="line"></div><div class="point"></div></div></td><td> <b>Arrows</b> - A <b>line</b> is a connector that shows relationships between the representative shapes.</td><td></td></tr>';
				echo '<tr><td> Diamond</td><td align="center"><div id="diamond"></div></td><td>Decision</td></tr>';
				echo '<tr><td> Box</td><td align="center"><div class="rectangle"></div></td><td>Process</td></tr>';
				// echo '<tr><td align="center"><div id="pointer"></div></td><td> <b>Asymmetric</b> - </td></tr>';
				echo '<tr><td> Circle</td><td align="center"><div class="circle"></div></td><td>Connector</td></tr>';
				echo '</table>';
			echo '</div>';
			echo '<div style="margin-left:25%;">';
				echo '<br><h3>Allowed Tags</h3>';
				echo '<table><tr><td><b>Tag</b></td><td><b>Sample</b></td><td><b>Output</b></td></tr>';
				echo '<tr><td>&lt;br&gt;</td><td>&lt;br&gt; </td><td> New line.</td></tr>';
				echo '<tr><td>&lt;b&gt;</td><td>&lt;b&gt;Bold&lt;/b&gt; </td><td> <b>Bold</b></td></tr>';
				echo '<tr><td>&lt;i&gt;</td><td>&lt;i&gt;Italic&lt;/i&gt; </td><td> <i>Italic</i></td></tr>';
				echo '<tr><td>&lt;u&gt;</td><td>&lt;u&gt;Underline&lt;/u&gt; </td><td> <u>Underline</u></td></tr>';
				echo '</table>';
			echo '</div>';
		echo '</div>';
	}
	break;

	case 'UpdateFlowChartSub';
		if ((empty($_POST['NodeID'])) AND (empty($_POST['NextNode'])) AND (empty($_POST['Shape'])) AND (empty($_POST['CommentInShape'])) AND (empty($_POST['CommentInLine'])) AND (empty($_POST['DottedLine']))){
			$sql='DELETE FROM `systools_2flowchartsub` WHERE TxnSubid='.$_POST['TxnSubid'];
		} else {
			$sql='UPDATE `systools_2flowchartsub` SET EncodedByNo='.$_SESSION['(ak0)'].',NodeID="'.$_POST['NodeID'].'",NextNode="'.$_POST['NextNode'].'",Shape="'.$_POST['Shape'].'",CommentInShape="'.$_POST['CommentInShape'].'",CommentInLine="'.$_POST['CommentInLine'].'",`DottedLine?`="'.$_POST['DottedLine?'].'", TimeStamp=Now() WHERE TxnSubid='.$_POST['TxnSubid'];
		}
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:flowchart.php?w=Preview&TxnID=".$_POST['TxnID']);
	break;

	case 'InsertFlowChartSub';
		$sql='INSERT INTO `systools_2flowchartsub` SET TxnID='.$_POST['TxnID'].',EncodedByNo='.$_SESSION['(ak0)'].',NodeID="'.$_POST['NodeID'].'",NextNode="'.$_POST['NextNode'].'",Shape="'.$_POST['Shape'].'",CommentInShape="'.$_POST['CommentInShape'].'",CommentInLine="'.$_POST['CommentInLine'].'",`DottedLine?`="'.$_POST['DottedLine?'].'",TimeStamp=Now()';
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:flowchart.php?w=Preview&TxnID=".$_POST['TxnID']);
	break;
	
	case 'FlowchartStyle';
		$sql='UPDATE `systools_2flowchartmain` SET EncodedByNo='.$_SESSION['(ak0)'].',FlowChartTitle="'.$_POST['FlowChartTitle'].'",Theme="'.$_POST['Theme'].'",FCPosition="'.$_POST['FCPosition'].'", TimeStamp=Now() WHERE TxnID='.$_POST['TxnID'];
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:flowchart.php?w=Preview&TxnID=".$_POST['TxnID']);
	break;
	
	case 'PostUnpost';
		$sql='UPDATE systools_2flowchartmain SET EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=Now(),Posted='.($_POST['Posted']==1?0:1).' WHERE TxnID='.$_POST['TxnID'];
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:flowchart.php?w=Preview&TxnID=".$_POST['TxnID']);
	break;
	
	case 'Claim';
		$txnid=intval($_GET['TxnID']);
		$sql='UPDATE systools_2flowchartmain SET EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=Now() WHERE Posted=0 AND TxnID='.$txnid;
		$stmt=$link->prepare($sql); $stmt->execute();
		header("Location:flowchart.php?w=Preview&TxnID=".$txnid);
	break;
	
	case 'AddNote':
	$sql='INSERT INTO systools_2flowchartsubnotes SET Note="'.$_POST['Note'].'",EncodedByNo='.$_SESSION['(ak0)'].',TimeStamp=Now(),TxnID='.$_GET['TxnID'];
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:flowchart.php?w=Preview&TxnID=".$_GET['TxnID']);
	break;
	
	case 'DeleteNote':
	// $sql='DELETE FROM systools_2flowchartsubnotes WHERE TxnSubNoteid='.$_GET['TxnSubNoteid'].' AND EncodedByNo='.$_SESSION['(ak0)'];
	$sql='DELETE sn.* FROM systools_2flowchartsubnotes sn JOIN systools_2flowchartmain m ON sn.TxnID=m.TxnID WHERE Posted<>1 AND TxnSubNoteid='.$_GET['TxnSubNoteid'].' AND sn.EncodedByNo='.$_SESSION['(ak0)'];
	$stmt=$link->prepare($sql); $stmt->execute();
	header("Location:flowchart.php?w=Preview&TxnID=".$_GET['TxnID']);
	break;
	
$link=null; $stmt=null;
}
?>
</body>
</html>