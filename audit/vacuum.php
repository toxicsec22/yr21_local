<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(653,'1rtc')) { echo 'No permission'; exit;}
$showbranches=false; include_once('../switchboard/contents.php');  
 
include_once('../backendphp/functions/editok.php');
include_once('../generalinfo/lists.inc');



$method='POST';
$nopost=1;
$title='Encode/Edit Vacuum';
$fieldname='VacuumMonth';
//include_once('../backendphp/layout/clickontabletoedithead.php');
$main='
<form method="post" action="vacuum.php">
   Choose month (1-12): <input type="text" name="'.$fieldname.'" size=5 value="'.date("m").'" autocomplete=off>
   <input type="submit" name="lookup" value="Lookup">
</form>';
echo $main;
if (!isset($_POST[$fieldname])){
goto noform;
} else {
include ('unionserialnoswithlastyr.php');
$stmt=$link->prepare($sql0);
$stmt->execute();
$sql0='create temporary table itemnos(
ItemCode smallint(6) not null,
SerialNo varchar(100) null
)
Select ItemCode, left(ss.SerialNo,100) as SerialNo from `invty_2sale` sm join `invty_2salesub` ss on sm.TxnID=ss.TxnID where ItemCode in (Select ItemCode from `invty_1items` where CatNo=90)
union Select ItemCode, left(ss.SerialNo,100) as SerialNo from `'.$currentyr.'_1rtc`.`invty_2sale` sm join `'.$currentyr.'_1rtc`.`invty_2salesub` ss on sm.TxnID=ss.TxnID where ItemCode in (Select ItemCode from `invty_1items` where CatNo=90) ;
';
$stmt=$link->prepare($sql0);
$stmt->execute();
$sql0='create temporary table vacuumpermonth (
CountID int(11) not null,
Date date not null,
SerialNo varchar(50)  null,
Vacuum double  null,
VacuumedBy varchar(20) null,
AuditedBy varchar(20) null,
TotalSoldPerTank double  null,
TotalAccounted double  null,
Posted tinyint(1)  null,
PostedByNo smallint(6) null,
BranchNo smallint(6) null,
ItemCode smallint(6) null
)
SELECT c.CountID, c.Date, c.SerialNo, c.Vacuum, c.TotalSoldPerTank, c.Posted, c.PostedByNo, e.Nickname as AuditedBy, e1.Nickname as VacuumedBy, TotalSoldPerTank+ifnull(Vacuum,0) as TotalAccounted, (Select BranchNo from serialnos ss where ss.SerialNo=c.SerialNo limit 1) as BranchNo, (Select ItemCode from itemnos sn where sn.SerialNo=c.SerialNo limit 1) as ItemCode FROM audit_3vacuum c 
right join `1employees` e on e.IDNo=c.EncodedByNo
right join `1employees` e1 on e1.IDNo=c.VacuumedBy where Month(c.Date)='.$_POST[$fieldname].'
order by `Date`, `SerialNo`';
$stmt=$link->prepare($sql0);
$stmt->execute();
$sql='SELECT v.*, b.Branch, i.ItemDesc as Refrigerant FROM vacuumpermonth v 
left join `1branches` b on b.BranchNo=v.BranchNo
left join `invty_1items` i on i.ItemCode=v.ItemCode
order by `Date`, `SerialNo`';
$txnid='CountID';
}

$stmt=$link->query($sql);
$result=$stmt->fetchAll();

$columnnames=array('Date','SerialNo','Branch','Refrigerant','Vacuum','VacuumedBy','AuditedBy','TotalSoldPerTank','TotalAccounted','Posted','PostedByNo');
    
    $sub='';$cols='';
    foreach ($columnnames as $col){
        $cols=$cols.'<td><font face="arial" size="2">'.$col.'</font></td>';
    }
    
    //to make alternating rows have different colors
        $colorcount=0;
        $rcolor[0]="lightgray";
        $rcolor[1]="FFFFFF";
        
    foreach($result as $row){
        $sub=$sub.'<tr bgcolor='. $rcolor[$colorcount%2].'>';
        foreach ($columnnames as $col){
            $sub=$sub.'<td>'.$row[$col].'</td>';
        }
        $sub=$sub.(editOk('audit_3vacuum',$row[$txnid],$link,'vacuum')?'<td><a href="prvacuum.php?w=TotalSold&action_token='.$_SESSION['action_token'].'&CountID='.$row[$txnid].'">Update Total Sold</a>'.str_repeat('&nbsp',8).'</td><td><a href=../backendphp/layout/postunpost.php?Table=audit_3vacuum&Link=audit&txntype=vacuum&Post=1&TxnID='.$row[$txnid].'>Post</a>'.str_repeat('&nbsp',8).'<a href="editauditspecifics.php?edit=2&w=VacuumEdit&CountID='.$row[$txnid].'">Edit</a>'.str_repeat('&nbsp',8).'<a href=prvacuum.php?CountID='.$row[$txnid].'&action_token='.$_SESSION['action_token'].'&w=VacuumDel OnClick="return confirm(\'Really delete this?\');">Delete</a></td>':'<td><a href=../backendphp/layout/postunpost.php?Table=audit_3vacuum&Link=audit&txntype=vacuum&Post=0&TxnID='.$row[$txnid].'>Unpost</a></td>').'</tr>';
     
        $colorcount++;
    }
    $editsub=true; $main='';
   $sub='<table><tr>'.$cols.'<td>Edit?</td></tr><tbody>'.$sub.'</tbody></table>';
   
    $columnnames=array(
                    array('field'=>'Date', 'caption'=>'Date of Vacuum', 'type'=>'date','size'=>12,'required'=>true, 'autofocus'=>true,'value'=>date('Y-m-d')),
                    array('field'=>'SerialNo', 'caption'=>'Serial Number', 'type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Vacuum', 'caption'=>'Vacuum (kg)','type'=>'text','size'=>5, 'required'=>true, 'value'=>0),
                    array('field'=>'VacuumedBy', 'caption'=>'Vacuumed By', 'type'=>'text','required'=>true,'size'=>5, 'list'=>'employeeid')
                    );
   $liststoshow=array('employeeid');
    $action='prvacuum.php?w=AddVacuum';
    
$left='90%'; $leftmargin='91%'; $right='9%';
 include('../backendphp/layout/inputsubform.php');

noform:
      $link=null; $stmt=null;
?>