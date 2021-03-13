<?php
$sqluser='Select * from `1employees` where IDNo='.$_SESSION['(ak0)'];
$stmt=$link->query($sqluser);
$resultuser=$stmt->fetch();
