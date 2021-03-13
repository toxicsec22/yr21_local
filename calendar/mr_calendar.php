<?php
date_default_timezone_set('Asia/Manila');
$path=$_SERVER['DOCUMENT_ROOT']; 
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
if (!allowedToOpen(5637,'1rtc')) { echo 'No permission'; exit();}
$showbranches=false;
include_once('../switchboard/contents.php');
include_once $path.'/acrossyrs/js/bootstrapCALENDAR/includecalendar.php';

//1 - conference , 2 - interview
$roomno=(!isset($_GET['roomno'])?1:$_GET['roomno']); 

if($roomno==1){
	$roomname='<b>10</b>th Floor Conference Room Schedule';
} else if ($roomno==2){
	$roomname='<b>10</b>th Floor Interview Room Schedule';
} else if ($roomno==3){
	$roomname='<b>17</b>th Floor Conference Room Schedule';
}
echo ' <div style="background-color:#cccccc; width:60%; border: 1px solid black; padding:10px;font-size:10pt;" >
		<b>Instructions:</b><br>
To RESERVE: Click on the time, and drag to cover the full reservation time. Put details in the dialog box that appears.<br>
To DELETE Reservation: Click on the reservation. Only the original encoder can delete.<br><br><i style="font-size:9.5pt;">* Reservations in the past cannot be edited.</i><br></div><br>';
include_once('../backendphp/layout/linkstyle.php');
	echo '<a id=\'link\' href="mr_calendar.php?roomno=1"><b>10</b>th Floor Conference Room</a> ';
    echo '<a id=\'link\' href="mr_calendar.php?roomno=2"><b>10</b>th Floor Interview Room</a> ';
    echo '<a id=\'link\' href="mr_calendar.php?roomno=3"><b>17</b>th Floor Conference Room</a> <br><br>';
 // echo '<br><form action=""><input type="submit" value="Update"></form>';
 
?>
<!DOCTYPE html>
<html>
 <head>
 <title><?php echo str_replace("</b>","",str_replace("<b>","",$roomname)); ?>
 </title>
 
  <script>
   
  $(document).ready(function() {
   var calendar = $('#calendar').fullCalendar({
    editable:true,
    header:{
     left:'prev,next today',
     center:'title',
     right:'month,agendaWeek,agendaDay,listWeek'
    },
	defaultView: 'agendaWeek',
	<?php if($roomno==1) {?>
    events: 'mr_load.php?roomno=1',
    <?php } elseif($roomno==2) {?>
    events: 'mr_load.php?roomno=2',
	<?php } else {?>
	events: 'mr_load.php?roomno=3',
	<?php }?>
    selectable:true,
    selectHelper:true,
	minTime: "07:00:00",
	maxTime: "18:00:00",
	height: 'auto',
	
	// themeSystem : "standard",
	
    select: function(start, end, allDay)
    {
     var title = prompt("Enter Event Title");
     if(title)
     {
      var start = $.fullCalendar.formatDate(start, "Y-MM-DD HH:mm:ss");
      var end = $.fullCalendar.formatDate(end, "Y-MM-DD HH:mm:ss");
      $.ajax({
		  <?php if($roomno==1) {?>
		url:"mr_insert.php?roomno=1",
		<?php } elseif($roomno==2) {?>
		url:"mr_insert.php?roomno=2",
		<?php } else {?>
		url:"mr_insert.php?roomno=3",
		<?php }?>
       
       type:"POST",
       data:{title:title, start:start, end:end},
       success:function()
       {
        calendar.fullCalendar('refetchEvents');
        alert("Added Successfully");
       }
      })
     }
    },
    editable:true,
    eventResize:function(event)
    {
     var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
     var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
     var title = event.title;
     var id = event.id;
     $.ajax({
		url:"mr_update.php",
      type:"POST",
      data:{title:title, start:start, end:end, id:id},
      success:function(){
       calendar.fullCalendar('refetchEvents');
       alert('Event Update');
      }
     })
    },

    eventDrop:function(event)
    {
     var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD HH:mm:ss");
     var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD HH:mm:ss");
     var title = event.title;
     var id = event.id;
     $.ajax({
      url:"mr_update.php",
      type:"POST",
      data:{title:title, start:start, end:end, id:id},
      success:function()
      {
       calendar.fullCalendar('refetchEvents');
       alert("Event Updated");
      }
     });
    },

    eventClick:function(event)
    {
     if(confirm("Are you sure you want to remove it?"))
     {
      var id = event.id;
      $.ajax({
		url:"mr_delete.php",
       type:"POST",
       data:{id:id},
       success:function()
       {
        calendar.fullCalendar('refetchEvents');
        alert("Event Removed");
       }
      })
     }
    },

   });
  });
   
  </script>
 </head>
 <body>

	  
  <br />
  <h3 align="center"><?php echo $roomname; ?></h3>
  <br />
  <div class="container" style="background-color:#<?php echo ($roomno==1?"FFFDFD":($roomno==2?"fafad2":"f4f1fa"));?>;"><br>
   <div id="calendar"></div>
  </div>
 </body>
</html>