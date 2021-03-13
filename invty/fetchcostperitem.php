<?php

$path=$_SERVER['DOCUMENT_ROOT'];
include_once $path.'/acrossyrs/logincodes/checkifloggedon.php';
include_once $path.'/acrossyrs/dbinit/userinit.php';
$link=!isset($link)?connect_db($currentyr.'_1rtc',0):$link;
include_once "../generalinfo/lists.inc";
 
$output = '';
if(isset($_POST["query"]))
{
	
	$search = $_POST["query"];

	$itemcode=$search;
			
			 $sql1='SELECT Category, (CASE 
	WHEN i.MoveType = 0 THEN "Active"
	WHEN i.MoveType = 1 THEN "NonStock"
	WHEN i.MoveType = 3 THEN "NonMoving"
	ELSE "Obsolete"
	END) AS MoveType FROM invty_1items i JOIN invty_1category c ON i.CatNo=c.CatNo WHERE ItemCode='.$itemcode.';';
			$stmt1=$link->query($sql1); $res1=$stmt1->fetch();
		
		$table='invty_1items';
		   $itemdesc=getValue($link,$table,'ItemCode',$itemcode,'ItemDesc');
		   
		 
	     include('sqlphp/costlistperitem.php');
	  
	   $sql='Select lc.*,c.Category,i.ItemDesc as Description,i.Unit, i.MoveType from costlistperitem lc join invty_1items i on i.ItemCode=lc.ItemCode join invty_1category c on c.CatNo=i.CatNo WHERE lc.ItemCode='.$itemcode.' ORDER BY UnitCost ASC';
	
	
	$stmt = $link->prepare($sql);
	$stmt->execute();
	echo "<div style='background-color: #e6e6e6;
  width: 800px;
  border: 2px solid grey;
  padding: 20px;
  margin: 25px;'>";
  
  if($stmt->rowCount() > 0)	{
	  $output.='<h4>Suggested Unit Cost</h4>';
	  
		$output.='ItemCode: '.$itemcode.'<br>Category: '.$res1['Category'].'<br>Description: '.$itemdesc.'<br>MoveType: '.$res1['MoveType'].'<br><br>';
		$output .= '<table>
							<tr>
								<th>SupplierNo</th>
								<th>SupplierName</th>
								<th>UnitCost</th>
								<th>Unit</th>
								<th>PurchaseDate</th>
							</tr>';
			while($row = $stmt->fetch()){
				$output .= '
					<tr>
						<td>'.$row["SupplierNo"].'</td>
						<td>'.$row["SupplierName"].'</td>
						<td>'.$row["UnitCost"].'</td>
						<td>'.$row["Unit"].'</td>
						<td>'.$row["PurchaseDate"].'</td>
					</tr>
				';
			}
			$output.='</table>';
			echo $output;
		
	}
	else {
		echo 'Data Not Found';
	}
	
	
}

?>