
<?php 

$title='ARWAN';
$path=$_SERVER['DOCUMENT_ROOT'];
include_once($path.'/acrossyrs/myswitch/generic/header.php');
include_once($path.'/acrossyrs/myswitch/generic/sidebar2.php');
include_once($path.'/acrossyrs/myswitch/generic/topbar.php');

$which=(isset($_GET['w'])?$_GET['w']:"Comments");



switch($which){



case "Comments":
    $sql_list = "SELECT TxnID,Comments FROM events_2syslayoutcomments WHERE EncodedByNo=".$_SESSION['(ak0)']."";
    $stmt_list = $link->query($sql_list);
    $res_list = $stmt_list->fetchAll();

?>

   

    <?php if(isset($_GET['done'])){ ?>
        <?php if($_GET['done']==1){ ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Successfully deleted.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
        <?php } ?>
        <?php if($_GET['done']==0){ ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error! Duplicate Entry.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
        <?php } ?>

        <?php if($_GET['done']==3){ ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Successfully added.
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            </div>
        <?php } ?>
    <?php } ?>
    


    <div class="container-fluid">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Comments and Suggestions</h6>
        </div>

       

    <div class="card-body">

    <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#addComment"><i class="fas fa-plus-square"></i> Add Comment</button><br><br>

    <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
       
            <thead>
                <tr>
                    <th>Comments/Suggestions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($res_list as $list): ?>
                    <tr>
                        <td><?php echo $list["Comments"]; ?></td>
                        <td style="width: 100px"><a href="comments.php?w=DeleteComment&TxnID=<?php echo $list["TxnID"]; ?>" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a></td>
                       

                            

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    </div>
    </div>
    </div>
    <!-- End Story Table -->
    
    <!-- View Story Modal -->
    <div class="modal fade " id="addComment" tabindex="-1" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Comments</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
        
            <form action="comments.php?w=AddComment" method="POST" class="" autocomplete="off">
            <div class="modal-body">
                
                    <label>Comments/Suggestions</label> <textarea name="Comments" class="form-control" ></textarea>
                       
            </div>

            <div class="modal-footer">
                <button type="submit" name="btnAddComment" id ="btnAddComment" class="btn btn-primary "> Add new</button>
            </div>

                    </form>

                
            </div>
        </div>
    </div>
    <!-- END Modal-->

    
<?php   

break;

case "AddComment":
       $comments = addslashes($_POST["Comments"]);

      
       $isql_setcomment = "INSERT INTO 
       events_2syslayoutcomments 
       SET 
       Comments = :comments, EncodedByNo=".$_SESSION['(ak0)'].", TimeStamp=NOW()";
       $stmt_setcomment = $link->prepare($isql_setcomment);
       $stmt_setcomment->bindParam(":comments", $comments);
       $stmt_setcomment->execute();
       $done=3;


   header('Location: comments.php?w=Comments&done='.$done);

break;

case "DeleteComment":
    $txnid = addslashes($_GET["TxnID"]);

   
    $isql_delcomment = "DELETE FROM 
    events_2syslayoutcomments 
    WHERE 
    TxnID = :txnid AND EncodedByNo=".$_SESSION['(ak0)']."";
    
    $stmt_delcomment = $link->prepare($isql_delcomment);
    $stmt_delcomment->bindParam(":txnid", $txnid);
    $stmt_delcomment->execute();
    $done=1;
header('Location: comments.php?w=Comments&done='.$done);

break;

} ?>

    <?php
     include_once($path.'/acrossyrs/myswitch/generic/footer.php');
    ?>

    