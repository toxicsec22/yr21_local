<?php
        
	$login=addslashes($_POST['login']); $pw=addslashes($_POST['pw']);
	$stmt=$link->prepare('SELECT u.IDNo, d.DefaultBranchAssignNo AS BranchNo, uphashmayasin,PositionID FROM ((1employees as e INNER JOIN `attend_30currentpositions` as p ON e.IDNo = p.IDNo) INNER JOIN attend_1defaultbranchassign as d ON e.IDNo = d.IDNo) INNER JOIN `1_gamit`.`1rtcusers` as u ON (e.IDNo = u.IDNo) AND (p.IDNo = u.IDNo) inner join `1branches` as b on b.BranchNo=d.DefaultBranchAssignNo
WHERE ((e.Resigned)=0) and e.IDNo=:UserID');
	$stmt->bindValue(':UserID', $login, PDO::PARAM_STR);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

?>