<?php
$path = $_SERVER['DOCUMENT_ROOT'];
include_once $path . '/'.$url_folder.'/generalinfo/lists.inc';
function showCompanyBox($link, $target){
    ?>
    <form action="<?php echo $target ?>" method="POST" style="display: inline-block;">
        <label for="company">Choose a company</label> <input type="text" name="companyNumber" list="companynumbers" size=20 onchange="this.form.submit()" onkeydown="ignoreEnter()" placeholder="Choose company" style='font-style: italic;'>
    </form>
    <?php
    renderlist('companynumbers');
}
