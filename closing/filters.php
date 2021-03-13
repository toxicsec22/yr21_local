<?php
if(!isset($_REQUEST['f'])) { $filter=' HAVING DiffValue>1';}
elseif (isset($_REQUEST['f'])==='Show All') { $filter='';}
else {
    $filter='';
    $filter=($_REQUEST['Month']=='')?'':' WHERE Month='.$_REQUEST['Month'];
    $filter.=($_REQUEST['AccountID']=='')?'':(strpos($filter,'WHERE')>0?' AND ':' WHERE ').' AccountID='.$_REQUEST['AccountID'];
    $filter.=($_REQUEST['BranchNo']=='')?'':(strpos($filter,'WHERE')>0?' AND ':' WHERE ').' BranchNo='.$_REQUEST['BranchNo'];
}

//if($_SESSION['(ak0)']==1002){ echo $_REQUEST['f'].'<br>'.$filter;}
$formdesc='</i><br><br><form action="'.$action.'" method=post style="display:in-line; border: solid 1px; padding: 10px;" >Filter by: '
        . 'Month (1-12) <input type=text name=Month size=5 />'.str_repeat('&nbsp;', 10)
        . 'Account ID <input type=text name=AccountID size=5 />'.str_repeat('&nbsp;', 10)
        . 'Branch Number <input type=text name=BranchNo size=5 />'.str_repeat('&nbsp;', 10)
        . '<input type=submit name="f" value="Set filter">'.str_repeat('&nbsp;', 10).'<input type=submit name="f" value="Show All"></form><i>';
?>