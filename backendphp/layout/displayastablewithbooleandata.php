<?php
	/***
	 * Creates a table where it can edit boolean data.
	 * 
	 * Note: In order for a specific column to be read as a column of booleans, 
	 * the column's values must be only either 'TRUE' or 'FALSE' as strings because there is no way
	 * for PHP to know if a column type is TINYINT(1) or any other numeric for that matter
	 * 
	 * When getting a data from this table, to know the name of the array that will be passed
	 * on the form. please var_dump the $_POST
	 * 
	 * @param array $headers Array of headers
	 * @param 2d-array $data 2 dimensional array of data to be put in the table
	 * @param string $formAction Where will the form will go when submit was clicked
	 * @param string $formMethod POST or GET
	 * @param string $title Title of the table
	 * @param string $formDesc Description of the table
	 * @param string $dataRowKey The primary key of the table data
	 * @param string $id Id of the table
	 * @param string $class inline style of the table
	 * @param bool $strict Only displays data if they have the same column names
	 */
	function createTableWithBooleanData($headers, $data, $formAction, $formMethod="POST",
                                    	$title = null, $formDesc = null, $editProcessLabel = "Process",
                                    	$dataRowKey = "TxnID", $id = "table", 
                                    	$class = "display", bool $strict = true){
        if(!is_null($title))
            echo '<br><h3>'.$title.'</h3>';
        if(!is_null($formDesc))
            echo '<br><i>'.$formDesc.'</i><br><br>';
           
        //tagging of boolean columns
        $boolColumns = array();
        //Iterate over the table per column, just to know if they're booleans or not
        $columns = array_keys($data[0]);
        
        //Initialize tags as false
        foreach ($columns as $columnName){
            $boolColumns[$columnName] = false;
        }
        
        //iterates on all rows tagging them as either booleans or not
        foreach ($columns as $columnName){
            foreach ($data as $row){
                if(($row[$columnName] == 'TRUE' or $row[$columnName] == 'FALSE')){
                    //once is false, skips the checking of that particular column
                    $boolColumns[$columnName] = true;
                }
                else{
                    $boolColumns[$columnName] = false;
                    continue;
                }
            }
        }
        
        //Start of the table text
        $formHead = '<form method="'.$formMethod.'" action="'.$formAction.'">';
        $tableHead = '<table id="'.$id.'" class="'.$class.'"><thead><tr>';
        
        //For the column with the edit
        for ($i = 0; $i < count($headers); $i++) {
            $tableHead=$tableHead . '<th>'.$headers[$i].'</th>';
        }
        
        $tableHead = $tableHead . '</tr></sthead>';
        //End of setting of headers
        
        $tablebody="<tbody>";
        foreach ($data as $row) {
            $tablebody = $tablebody.'<tr>';
            
            if($strict){
                $columnCount = count($headers);
                for($i = 0; $i < $columnCount; $i++){
                    //if column is tagged as a boolean column
                    if(isset($boolColumns[$headers[$i]]) and $boolColumns[$headers[$i]]){
                        $checkedString = $row[$headers[$i]] == 'TRUE' ? 'checked' : '';
                        $valueKey = $headers[$i] . '[]';
                        $tablebody=$tablebody.'<td><input type="checkbox" name="'.$valueKey.'"'.
                        'value="'.$row[$dataRowKey].'"'.$checkedString.'></td>';
                    }
                    else{
                        $tablebody=$tablebody.'<td>'. htmlspecialchars(nl2br(addslashes($row[$headers[$i]]))) . '</td>';
                    }
                }
            }
            else{
                $columnCount = count($columns);
                for($i = 0; $i < $columnCount; $i++){
                    if(isset($boolColumns[$columns[$i]]) and $boolColumns[$columns[$i]]){
                        $checkedString = $row[$columns[$i]] == 'TRUE' ? 'checked' : '';
                        $valueKey = $columns[$i] . '[]';
                        $tablebody=$tablebody.'<td><input type="checkbox" name="'.$valueKey.'"'.
                            'value="'.$row[$dataRowKey].'"'.$checkedString.'></td>';
                    }
                    else{
                        $tablebody=$tablebody.'<td>'. htmlspecialchars(nl2br(addslashes($row[$columns[$i]]))) . '</td>';
                    }
                }
            }
            $tablebody = $tablebody.'</tr>';
        }
        $tablebody = $tablebody . '</tbody></table><input id="button-'.$id.'" type="Submit" value="'.$editProcessLabel.'"></form>';
        echo $formHead.$tableHead.$tablebody;
	}