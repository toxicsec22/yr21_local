<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php'; 
if (!allowedToOpen(512,'1rtc')) { echo 'No permission'; exit; }
include_once('../switchboard/contents.php');

  
include_once $path.'/acrossyrs/commonfunctions/listoptions.php';
?><br><div id="section" style="display: block;"><?php
$which=(!isset($_GET['w'])?'List':$_GET['w']);

if (allowedToOpen(5121,'1rtc')){ $columnstoadd=array('GroupID', 'AccountGroup', 'OrderNo','Level');}
if (in_array($which,array('List','EditSpecifics'))){
   echo comboBox($link,'SELECT GroupID, AccountGroup FROM `acctg_1accountgroup` ORDER BY AccountGroup','GroupID','AccountGroup','accountgroups');
   echo comboBox($link,'SELECT 1 AS Level UNION SELECT 2 UNION SELECT 3 ORDER BY Level','Level','Level','levels');
   $sql='SELECT a.*, 2a.AccountGroup AS Sublevel_Of, Nickname AS EncodedBy FROM `acctg_1accountgroup` a 
       JOIN `acctg_1accountgroup` 2a ON 2a.GroupID=a.SublevelOf
        LEFT JOIN `1employees` e ON e.IDNo=a.EncodedByNo ';
   $columnnameslist=array('GroupID', 'AccountGroup', 'Level', 'SublevelOf', 'Sublevel_Of', 'OrderNo');
   
} 

if (in_array($which,array('Add','Edit'))){
        if($_POST['Sublevel_Of']<>'No Group') {
        $sublevel=comboBoxValue($link,'`acctg_1accountgroup`','AccountGroup',addslashes($_POST['Sublevel_Of']),'GroupID');
        } else { $sublevel=0;}
    }

switch ($which){
   case 'List':
       if (!allowedToOpen(512,'1rtc')) { echo 'No permission'; exit; } 
         $title='Account Groups'; $method='post';
         $columnnames=array(
                    array('field'=>'GroupID', 'type'=>'text','size'=>5,'required'=>true),
                    array('field'=>'AccountGroup','type'=>'text','size'=>10,'required'=>true),
                    array('field'=>'Level','type'=>'text','size'=>5,'required'=>true, 'value'=>0, 'list'=>'levels'),
                    array('field'=>'Sublevel_Of','type'=>'text','size'=>10,'required'=>true, 'list'=>'accountgroups', 'value'=>'"No Group"'),
                    array('field'=>'OrderNo','type'=>'text','size'=>5,'required'=>false));
                     
      $action='accountgroups.php?w=Add'; $fieldsinrow=4; $liststoshow=array();
      if (allowedToOpen(5121,'1rtc')){
	 include('../backendphp/layout/inputmainform.php');
	 $delprocess='accountgroups.php?w=Delete&GroupID=';
         $columnstoedit=array('GroupID', 'AccountGroup', 'Level', 'OrderNo');
	 } else { $columnstoedit=array();}
      
      $title=''; $formdesc='';$txnid='GroupID'; $txnidname='GroupID';
      $columnnames=$columnnameslist;
        $sortfield=(isset($_POST['sortfield'])?$_POST['sortfield']:' AccountGroup,OrderNo'); $columnsub=$columnnames;
        $sql=$sql.' ORDER BY '.$sortfield.(isset($_POST['sortarrange'])?' '.$_POST['sortarrange']:' ASC');         
        $editprocess='accountgroups.php?w=EditSpecifics&GroupID='; $editprocesslabel='Edit'; 
        echo '<div style="width:100%;">	<div style="float:left;   width:50%;">';
      include('../backendphp/layout/displayastable.php');
      
      $sql1='SELECT GroupID, AccountGroup, SublevelOf FROM `acctg_1accountgroup` WHERE SublevelOf=0 ORDER BY OrderNo;';
        $stmt1=$link->query($sql1); $res1=$stmt1->fetchAll(); 
        $display='</div><div style="background:FFFFFF; float:right; width:50%;"><h4>Grouping of Accounts</h4>';
      foreach ($res1 as $level1){
          $display.=$level1['GroupID'].' - '.$level1['AccountGroup'].'<br>';
          $sql2='SELECT GroupID, AccountGroup, SublevelOf FROM `acctg_1accountgroup` WHERE SublevelOf<>0 AND SublevelOf='.$level1['GroupID'].';';
          $stmt2=$link->query($sql2); $res2=$stmt2->fetchAll();
          foreach ($res2 as $level2){ 
                $display.=str_repeat('&nbsp;', 10).$level2['GroupID'].' - '.$level2['AccountGroup'].'<br>';
                $sql3='SELECT GroupID, AccountGroup, SublevelOf FROM `acctg_1accountgroup` WHERE SublevelOf<>0 AND SublevelOf='.$level2['GroupID'].';';
                $stmt3=$link->query($sql3); $res3=$stmt3->fetchAll();
                foreach ($res3 as $level3){ $display.=str_repeat('&nbsp;', 20).$level3['GroupID'].' - '.$level3['AccountGroup'].'<br>';
                $sql4='SELECT AccountID, ShortAcctID, AccountDescription FROM `acctg_1chartofaccounts` WHERE GroupID<>0 AND GroupID='.$level3['GroupID'].';';
                $stmt4=$link->query($sql4); $res4=$stmt4->fetchAll();
                foreach ($res4 as $level4){ $display.=str_repeat('&nbsp;', 30).$level4['AccountID'].' - '.$level4['ShortAcctID'].' : '.$level4['AccountDescription'].'<br>';}
                }
          
                $sql4='SELECT AccountID, ShortAcctID, AccountDescription FROM `acctg_1chartofaccounts` WHERE GroupID<>0 AND GroupID='.$level2['GroupID'].';';
                $stmt4=$link->query($sql4); $res4=$stmt4->fetchAll();
                foreach ($res4 as $level4){ $display.=str_repeat('&nbsp;', 20).$level4['AccountID'].' - '.$level4['ShortAcctID'].' : '.$level4['AccountDescription'].'<br>';}
          }
          
          $sql4='SELECT AccountID, ShortAcctID, AccountDescription FROM `acctg_1chartofaccounts` WHERE GroupID='.$level1['GroupID'].';';
          $stmt4=$link->query($sql4); $res4=$stmt4->fetchAll();
          foreach ($res4 as $level4){ $display.=str_repeat('&nbsp;', 10).$level4['AccountID'].' - '.$level4['ShortAcctID'].' : '.$level4['AccountDescription'].'<br>';}          
          }
      
      echo $display.'</div></div>';
        break;
    case 'Add':
        if (allowedToOpen(5121,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_POST[$field]).'\', '; }
        $sql='INSERT INTO `acctg_1accountgroup` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' SublevelOf='.$sublevel.', TimeStamp=Now()';
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;
    case 'Delete':
        if (allowedToOpen(5122,'1rtc')){
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	$sql='DELETE FROM `acctg_1accountgroup` WHERE GroupID='.$_GET['GroupID'];
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:".$_SERVER['HTTP_REFERER']);
        break;   
    case 'EditSpecifics':
         $title='Edit Specifics';
	 $txnid=$_GET['GroupID']; 
	 $sql=$sql.'WHERE a.GroupID='.$txnid;
	 array_push($columnstoadd,'Sublevel_Of');
         $columnstoedit=$columnstoadd;
	 $columnnames=$columnnameslist+array('EncodedBy','TimeStamp');
	 $columnswithlists=array('Sublevel_Of','Level');$listsname=array('Sublevel_Of'=>'accountgroups','Level'=>'levels');
	 $editprocess='accountgroups.php?w=Edit&GroupID='.$txnid; 
         include('../backendphp/layout/editspecificsforlists.php');
         break;
    case 'Edit':
        require_once $path.'/acrossyrs/logincodes/confirmtoken.php';
	if (allowedToOpen(5121,'1rtc')){
        $sql='';
        foreach ($columnstoadd as $field) {$sql=$sql.' `' . $field. '`=\''.addslashes($_REQUEST[$field]).'\', '; }
        $sql='UPDATE `acctg_1accountgroup` SET EncodedByNo='.$_SESSION['(ak0)'].', '.$sql.' SublevelOf='.$sublevel.', TimeStamp=Now() WHERE GroupID='.$_GET['GroupID']; 
        $stmt=$link->prepare($sql); $stmt->execute();
	}
        header("Location:accountgroups.php");
        break;
    
}
  $link=null; $stmt=null;
?>
</div> <!-- end section -->
</body></html>