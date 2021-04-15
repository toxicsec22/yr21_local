<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(5363,'1rtc')) { echo 'No permission'; exit; }
$showbranches=false; include_once('../switchboard/contents.php');


$which=!isset($_GET['w'])?'ShoutoutToday':$_GET['w'];
include_once('../backendphp/layout/linkstyle.php');
echo'</br>';
echo '<a id="link" href="shoutout.php?w=List">Shoutout Board</a> <a id="link" href="shoutout.php?w=ShoutoutToday">Shoutout Today</a><br>';

if (in_array($which,array('List','ManageShoutout','ShoutoutToday'))){
    $sql='select TxnID,ShoutStat,so.Remarks,Shoutout,(CASE 
    WHEN ShoutStat=0 THEN "For Approval"
    WHEN ShoutStat=1 THEN "Approved"
    WHEN ShoutStat=2 THEN "Denied"
    ELSE ""
END) AS ShoutStatus,ShoutStatTS AS ApprovedTS,so.TimeStamp,FullName AS ShoutedBy,Position,IF(deptid IN (2,10),Branch,dept) AS Branch FROM mktg_2shoutouts so left join attend_30currentpositions cp on cp.IDNo=so.EncodedByNo';
 }

switch($which){
	case 'List':
		$title='Shoutout Board';

        $sqlcondi=' WHERE ShoutStat=0 ';
        $shoutstat='For Approval';
        $shoutstatno=0;
        if(isset($_POST['btnLookup'])){
            if($_POST['ShoutStat']==1){
                $sqlcondi=' WHERE ShoutStat=1 ';
                $shoutstat='Approved';
                $shoutstatno=1;
            } elseif($_POST['ShoutStat']==2){
                $sqlcondi=' WHERE ShoutStat=2 ';
                $shoutstat='Denied';
                $shoutstatno=2;
            }
        }


        $formdesc='<br></i><form action="shoutout.php" method="POST"><select name="ShoutStat">
        <option value="0" '.($shoutstatno==0?'selected':'').'>For Approval</option>
        <option value="1" '.($shoutstatno==1?'selected':'').'>Approved</option>
        <option value="2" '.($shoutstatno==2?'selected':'').'>Denied</option>
        </select> <input type="submit" name="btnLookup" value="Lookup"></form><br><b>'.$shoutstat.'</b><i>';


        

            $sql.=$sqlcondi.' Order By ShoutStat,Branch,so.TimeStamp DESC';
			$columnnames=array('Shoutout','ShoutedBy','Branch','TimeStamp');
			$txnid='TxnID';
			$editprocess='shoutout.php?w=ManageShoutout&TxnID=';
			$editprocesslabel='Manage Shoutout';
			
			include('../backendphp/layout/displayastablenosort.php');
	break;
	


    case 'ManageShoutout':
        $txnid=intval($_GET['TxnID']);
        $sql.=' WHERE TxnID='.$txnid;
        $stmt=$link->query($sql); $result=$stmt->fetch();
    
        $title='Manage Shoutout';
        echo '<title>'.$title.'</title>';
        echo '<br><br>';
        echo '<div style="margin-left:30%;border:1px solid black;background-color:#fff;width:30%;padding:5px;"><h3>'.$title.'</h3><br>';
        echo 'Shouted by: '.$result['ShoutedBy'].' ('.$result['Branch'].')<br>';
        echo 'Shoutout: '.$result['Shoutout'].'<br>';
        echo 'Status: '.$result['ShoutStatus'].'<br>';
        echo '<br><br>';

        $disabled='';
        if($result['ShoutStat']<>0){
            $disabled='disabled';
        }
        
        echo '<b>Action</b>';
        echo '<form action="shoutout.php?w=ActionProcess&TxnID='.$result['TxnID'].'" method="POST">';
        echo 'Remarks: <br><textarea name="Remarks" cols=30 rows=3 '.$disabled.'>'.$result['Remarks'].'</textarea><br>';

        if($result['ShoutStat']==0){
            echo '<input type="submit" style="background-color:blue;color:white;padding:3px;" name="btnApprove" value="Approve">';
            echo str_repeat('&nbsp;',40);
            echo '<input type="submit" style="background-color:red;color:white;padding:3px;" name="btnDeny" value="Deny">';
        } else {
            echo '<input type="submit" style="background-color:green;color:white;padding:3px;" name="btnReOpen" value="ReOpen">'; 
        }
        echo '</form>';
        echo '</div>';
    break;

	case'ActionProcess':
        $txnid=intval($_GET['TxnID']);
        if(isset($_POST['btnApprove'])){
            $sqlu='ShoutStat=1,Remarks="'.addslashes($_POST['Remarks']).'"';
        } else if(isset($_POST['btnDeny'])){
            $sqlu='ShoutStat=2,Remarks="'.addslashes($_POST['Remarks']).'"';
        } else if(isset($_POST['btnReOpen'])){
            $sqlu='ShoutStat=0,Remarks=NULL';
        } else {
            exit();
        }
			$sql='UPDATE mktg_2shoutouts SET '.$sqlu.',ShoutStatByNo='.$_SESSION['(ak0)'].',ShoutStatTS=Now() WHERE TxnID='.$txnid.'';

    
			$stmt=$link->prepare($sql); $stmt->execute();
			header("Location:shoutout.php?w=ManageShoutout&TxnID=".$txnid);
	break;
	
case 'ShoutoutToday':

    $sql.=' WHERE ShoutStat=1 AND ShoutStatTS>=NOW() - INTERVAL 48 HOUR';
    $columnnames=array('Shoutout','ShoutedBy','Branch','ApprovedTS');
    $title='Shoutout Today';

    include('../backendphp/layout/displayastablenosort.php');

    break;

	
}
?>
