<form method='post' action='#' style='display:inline;'>
    Sort By  <input type='text' name='sortfield' list='sortlist'> &nbsp <input type='radio' name='sortarrange' value='ASC' checked=TRUE>Ascending &nbsp &nbsp
    <input type='radio' name='sortarrange' value='DESC'>Descending
    <input type='submit' name='submit' value='Sort'>
</form>
<?php 
$sortby='';
foreach ($columnsub as $list){
    $sortby=$sortby.'<option value="'.$list.'"></option>';
}
$sortby='<datalist id="sortlist" style="height: 150px; width: 150px; overflow: auto">'.$sortby.'</datalist id="sortlist">';
echo $sortby;
?>