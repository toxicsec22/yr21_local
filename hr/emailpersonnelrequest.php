<?php
$path=$_SERVER['DOCUMENT_ROOT']; 
// ini_set("include_path", ".:/usr/share/php/PHPMailer");
require($path."/acrossyrs/downloadedphp/PHPMailer/class.phpmailer.php");

include_once('../backendphp/layout/standardprintsettings.php');

$which=(!isset($_REQUEST['which'])?'SendForApproval':$_REQUEST['which']);
$emails=array();
switch ($which){
    case 'SendForApproval':
        $sql='SELECT pr.*, Entity, Position, TargetDate, e.Nickname as RequestedBy, pr.TimeStamp as RequestTS, e1.Nickname as ApprovedBy, pr.ApproveTS
        FROM hr_2personnelrequest pr
        JOIN attend_0positions p ON p.PositionID=pr.PositionID        
        JOIN `1employees` e ON e.IDNo=pr.EncodedByNo
        LEFT JOIN `1employees` e1 ON e1.IDNo=pr.ApprovedByNo
        JOIN `acctg_1budgetentities` be ON be.EntityID=pr.EntityID
        WHERE Approved=0';
        $stmt=$link->query($sql); $res=$stmt->fetchAll();
        $msg='';
        if ($stmt->rowCount()==0){goto end;}
        foreach ($res as $req){
           $msg=$msg.'<tr><td>'.$req['Entity'].'</td><td>'.$req['Position'].'</td><td>'.$req['Remarks'].'</td><td>'.$req['TargetDate'].'</td>
           <td>'.$req['RequestedBy'].'</td>';
           if(isset($_GET['Online'])){$msg.='<td>
           <form method=post action="https://www.1rtc.biz/'.$url_folder.'/hr/personnelrequest.php?which=Approve&TxnID='.$req['TxnID'].'">
           Comment<input type="text" size=20 name="ApproveComment" placeholder="blank if no comment">
           <input type="submit" name="Approve" value="Approve">  <input type="submit" name="Approve" value="Deny">
           </form></td>';}
           $msg.='</tr>'; 
        }
        $msg='<table border=1, collapsed><th>Entity</th><th>Position</th><th>Remarks</th><th>TargetDate</th><th>RequestedBy</th><th>Approve?</th>'.$msg.'</table>';
        if (isset($_GET['Online'])){ echo $msg; goto end;}
        else {
        //Send to JYE
            $msg='<a href="https://www.1rtc.biz/'.$url_folder.'/hr/emailpersonnelrequest.php?which=SendForApproval&Online=true">Go to approval page</a>'.$msg;
        $emails[]='jyeusebio@1rotary.com.ph';
        $subject='Request for Personnel';
        // Check which need to go to JOR -- REMOVED SINCE BRANCHES ARE NOW DIFF DEPT
//        $sqlsales='SELECT pr.* FROM hr_2personnelrequest pr WHERE (EntityID BETWEEN 1 AND 94) AND Approved=0;';
//        $stmtsales=$link->query($sqlsales); 
//        if ($stmtsales->rowCount()>0){ $emails[]='jackie.ramos@1rotary.com.ph';}
        }
    break;

    case 'SendToHR':
        include_once $path.'/acrossyrs/dbinit/emailpassword.php';
	 // rtciconpass()
        $sql='SELECT pr.*, Entity, Position, TargetDate, e.Nickname as RequestedBy, pr.TimeStamp as RequestTS, e1.Nickname as ApprovedBy, pr.ApproveTS, IF(Approved=1,"Approved","Denied") AS `Approved?`
        FROM hr_2personnelrequest pr
        JOIN attend_0positions p ON p.PositionID=pr.PositionID        
        JOIN `1employees` e ON e.IDNo=pr.EncodedByNo
        LEFT JOIN `1employees` e1 ON e1.IDNo=pr.ApprovedByNo
        JOIN `acctg_1budgetentities` be ON be.EntityID=pr.EntityID
        WHERE (PersonHired IS NULL OR PersonHired="") AND DATE(ApproveTS)=CURDATE()';
        //WHERE TxnID='.//$_GET['TxnID'];
        $stmt=$link->query($sql); $res=$stmt->fetchAll();
        $columnnames=array('Entity', 'Position', 'Remarks', 'TargetDate', 'RequestedBy', 'RequestTS', 'ApproveComment', 'ApprovedBy', 'ApproveTS');
        $msg=''; $requester='';
        if ($stmt->rowCount()==0){goto end;}        
        foreach ($res as $req){
           $msg=$msg.'<tr><td>'.$req['Entity'].'</td><td>'.$req['Position'].'</td><td>'.$req['Remarks'].'</td><td>'.$req['TargetDate'].'</td>
           <td>'.$req['RequestedBy'].'</td><td>'.$req['ApproveComment'].'</td><td>'.$req['Approved?'].'</td></tr>';
           $requester=$req['EncodedByNo'].',';
        }
        $msg='Approved today:<br><br><table border=1, collapsed><th>Entity</th><th>Position</th><th>Remarks</th><th>TargetDate</th><th>RequestedBy</th><th>ApproveComments</th><th>Approve?</th>'.$msg.'</table>';
        $msg=$msg.'<br><br><a href="https://www.1rtc.biz/'.$url_folder.'/hr/personnelrequest.php">Lookup</a>';
        //Send to HR, Controller (for budget), and requester
        $emails[]='hrd@1rotary.com.ph';$emails[]='jyeusebio@1rotary.com.ph';
        $sqlemail='SELECT Email FROM `1_gamit`.`1rtcusers` u join `attend_30currentpositions` p on u.IDNo=p.IDNo WHERE u.IDNo IN ('.rtrim($requester,",").')'; 
        $stmt=$link->query($sqlemail); $resemail=$stmt->fetchAll();
        foreach ($resemail as $reqemail){$emails[]=$reqemail['Email'];}
        $subject='Response to Request for Personnel';
    break;
} 
 
$mail = new PHPMailer();
$mail->IsSMTP();  // telling the class to use SMTP
$mail->SMTPDebug = 2; // debugging: 1 = errors and messages, 2 = messages only
$mail->Host = "smtp.gmail.com"; // SMTP server
$mail->Port = '587';//'465';
$mail->IsHTML(true);
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->SMTPSecure = 'tls';//'ssl';
$mail->Username = '1rtcicon@gmail.com';                            // SMTP username
$mail->Password = rtciconpass();                           // SMTP password

$mail->From = '1rtcicon@gmail.com';
$mail->FromName = '1Rotary - The Industry Icon';

$mail->Subject  = $subject;
$mail->WordWrap = 50;

//echo $msg; break;
foreach ($emails as $email){
    $mail->AddAddress($email);
    $mail->Body     = $msg;
    $mail->AltBody     = $msg;
}
header("Location:".$_SERVER['HTTP_REFERER']);
if(!$mail->Send()) {
echo 'Message was not sent.';
echo 'Mailer error: ' . $mail->ErrorInfo;
} else {
echo 'Message has been sent.'; $done='1';
}

 $mail->ClearAddresses();
end: 
     $link=null; $stmt=null;
?>