<?php 	
	//Desc:		Creates a table with checkboxes on the first column, to serve as a selection
	//Usage: 	$headers - array of headers
	//			$data - 2 dimensional array of data to be put in (or table data)
	//			$formAction - Were the form will go on click submit
	//			$formMethod - POST or GET
	//			$dataRowKey - The primary key of the table data
	//			$valueKey - The name of the values of the form
	//			$id - Id of the table
	//			$class - display of table
	//			$style - inline style of the table

	function createTableWithCheckbox($headers, $data, $formAction, $formMethod="POST", 
		$title = null, $formDesc = null,
		$dataRowKey = "TxnID", $valueKey = "tablevalue[]",
		$id = "table", $class = "display"){

		global $editProcessLabel;
		$label = 'Process';

		if(isset($editProcessLabel))
			$label = $editProcessLabel;

		if(!is_null($title))
			echo "<br><h3>{$title}</h3>";
		if(!is_null($formDesc))
			echo "<br><i>{$formDesc}</i><br><br>";

		$numcols = 0;
		//Fields to cleanup fields later
		//Start of the table text
		$formHead = '<form method="'.$formMethod.'" action="'.$formAction.'">';
		$tableHead = '<table id="'.$id.'" class="'.$class.'"><thead><tr>';
		//For the column with the edit
		$tableHead = $tableHead . '<th>Mark</th>';
		foreach($headers as $tableHeader) {
	    	$tableHead=$tableHead . '<th>'.$tableHeader.'</th>';
	    	$numcols=$numcols+1;
	    	$fields[$numcols]=$tableHeader;
		}
		$tableHead = $tableHead . '</tr></thead>';
		//End of setting of headers
		$lastrecord=end($data);

		$tablebody="<tbody>";
        foreach ($data as $row) {
        	$tablebody = $tablebody.'<tr>';
        	//First tabledata is for marking fields
        	$tablebody = $tablebody . '<td> <input type="checkbox" name="'.$valueKey.'" value="'.$row[$dataRowKey].'"> </td>';

        	foreach ($row as $field) {
        		$tablebody=$tablebody.'<td>'. htmlspecialchars(nl2br(addslashes($field))) . '</td>';	
        	}

        	$tablebody = $tablebody.'</tr>';
        }
		$tablebody = $tablebody . '</tbody></table><input id="button-'.$id.'" type="Submit" value='.$label.'></form>';
        echo $formHead.$tableHead.$tablebody;
        createTableWithCheckboxJS($id);
	}

	function createTableWithCheckBoxOnlyWithMatchingHeaders($headers, $data, $formAction, $formMethod="POST",
		$title = null, $formDesc = null,
		$dataRowKey = "CVNo", $valueKey = "tablevalue[]",
		$id = "table", $class = "display"){

		global $editProcessLabel;
		$label = 'Process';

		if(isset($editProcessLabel))
			$label = $editProcessLabel;
		if(!is_null($title))
			echo "<br><h3>{$title}</h3>";
		if(!is_null($formDesc))
			echo "<br><i>{$formDesc}</i><br><br>";
		
		$numcols = 0;
		//Fields to cleanup fields later
		//Start of the table text
		$formHead = '<form method="'.$formMethod.'" action="'.$formAction.'">';
		$tableHead = '<table id="'.$id.'" class="'.$class.'"><thead><tr>';
		//For the column with the edit
		$tableHead = $tableHead . '<th>Mark</th>';
		foreach($headers as $tableHeader) {
	    	$tableHead=$tableHead . '<th>'.$tableHeader.'</th>';
	    	$numcols=$numcols+1;
	    	$fields[$numcols]=$tableHeader;
		}
		$tableHead = $tableHead . '</tr></thead>';

		$tablebody="<tbody>";
        foreach ($data as $row) {
			
        	$tablebody = $tablebody.'<tr>';
        	//First tabledata is for marking fields
        	$tablebody = $tablebody . '<td> <input type="checkbox" name="'.$valueKey.'" value="'.$row[$dataRowKey].'"> </td>';

        	foreach ($fields as $field) {
        		$tablebody=$tablebody.'<td>'. htmlspecialchars(nl2br(addslashes($row[$field]))) . '</td>';	
        	}

        	$tablebody = $tablebody.'</tr>';
        }
		$tablebody = $tablebody . '</tbody></table><input id="button-'.$id.'" type="Submit" value='.$label.'></form>';
        echo $formHead.$tableHead.$tablebody;
        createTableWithCheckboxJS($id);
	}
	
	function createTableWithCheckboxJS($id){
	    ?>
			<script type="text/javascript">
				document.addEventListener('DOMContentLoaded', (event) => {
					var button = document.getElementById("<?php echo 'button-'.$id ?>")
					button.addEventListener("click", function(){
						button.value = "Processing, please wait for page refresh"
						setTimeout(() => {
							var cur = window.location.href;
							if(cur.search("&")){
								if(cur.search("#"))
									window.location.href = window.location.href.split("#")[0]
								else
									window.location.href = window.location.href.split("&")[0]
							}
							else
								window.location.reload(true)
						}, 5000);
					})
				})
			</script>
		<?php
	}

?>