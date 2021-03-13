<?php
$path=$_SERVER['DOCUMENT_ROOT']; include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
$link=!isset($link)?connect_db(''.$currentyr.'_1rtc',0):$link;

$quotedate=$_POST['quotedate'];
$clientname=$_POST['clientname'];
$contactperson=$_POST['contactperson'];
$position=$_POST['position'];
$sirmaam=$_POST['sirmaam'];
$warranty=addslashes($_POST['warranty']);
$payment=addslashes($_POST['payment']);
$note3=addslashes($_POST['note3']);
$encodedbyno=($_SESSION['(00)!']);
$note2=addslashes($_POST['note2']);

  $action=$_REQUEST['action']; // since unsure if action is POST (add or edit) or GET (delete)
    
    //if ($ok=="Y" or $action=="delete") {
        
    if ($action=="add") {
        $course_id=uniqid("co");
        $course=addslashes($_POST['course']);
        $tuition=$_POST['tuition'];
        
        $sqlco="INSERT INTO course_tb (course_id,person_id,course,tuition) values ('$course_id','$person_id','$course',$tuition)";
        mysql_query($sqlco);
            }
      //   }
        
      
     elseif ($action=="edit") {
        
        $course_id=$_POST['course_id'];
        $course=addslashes($_POST['course']);
        $tuition=$_POST['tuition'];
        $sql="Update course_tb SET course='$course', tuition=$tuition WHERE course_id='$course_id'";
        mysql_query($sql);
                
        
        $msg="Record updated";
     }
    elseif ($action=="delete") {
        $course_id=$_GET['course_id'];
        $sql="Delete from course_tb where person_id='$person_id' and course_id='$course_id'";
        mysql_query($sql);
        $msg="Record deleted";
        
    }
?>