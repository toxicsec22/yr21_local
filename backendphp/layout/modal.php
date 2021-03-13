<style type="text/css">
    #biometric-box {
        margin: 10px 10px 10px 10px;
        align-content: center;
        text-align: center;
    }
    .modal {
        display: block;
        z-index: 100;
        position: fixed;
        width: 100vw;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.7);
        margin: 0;
        padding: 0;
        font-family: Calibri, Arial, Georgia, sans-serif;
        top: 0;
        left: 0;
        /*TODO: ADD TRANSITION*/
        transition: fade;
        transition-duration: 0.5s;
    }

    .modal-dialog {
        display: block;
        width: fit-content;
        margin-top: 50vh;
        transform: translateY(-50%);
        background: white;
        border-radius: 10px;
        margin-left: auto;
        margin-right: auto;
        padding-left: 20px;
        padding-right: 20px;
    }

    .modal-header > .modal-title {
        display: inline-block;
        font-size: 30px;
        line-height: 35px;
        align-content: middle;
        margin-top: 15px;
        margin-bottom: 15px;
    }

    .modal-header > .close {
        float: right;
        margin-bottom: 15px;
        line-height: 35px;
        font-size: 30px;
        background-color: white;        
        color: gray;
        font-weight: bold;
        border-style: none;
        border-radius: 10px;
    }

    .modal-body {
        min-width: 30vw;
        align-content: middle;
        text-align: center;
    }
    
    .modal-body > img{
        width: 150px;
        height: 150px;
    }
    
    .modal-body > p {
        margin-top: 10px;
    }
    
    .modal-footer {
        text-align: right;
    }

    .modal-footer > button {
        margin-top: 10px;
        margin-bottom: 15px;
        line-height: 35px;
        font-size: 20px;
        background-color: white;        
        color: gray;
        font-weight: bold;
        border-style: none;
        border-radius: 10px;
    }

    .fade{
        display: none;
    }
</style>
<?php 

    //Sets up a modal, 
    function setupModal($modalId, $modalClassList, $modalTitle, $modalInitialText){
        ?>

        <div id="<?php echo $modalId; ?>" class="<?php echo $modalClassList ?> fade"tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 id="modal-title" class="modal-title"><?php echo $modalTitle; ?></h4>
                    </div>
                    <div class="modal-body">
                        <img src="https://<?php echo $_SERVER['HTTP_HOST']; ?>/acrossyrs/js/fingerprint.gif">
                        <p class="status-text"><?php echo $modalInitialText; ?></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-dismiss="modal">Close</button>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
<?php }
/*
 To make fully functional modal, add this script to make it real
  
 <script type="text/javascript">
 
 document.addEventListener('DOMContentLoaded', (event) => {
         var buttonInit = document.getElementById("modal-id")
         buttonInit.addEventListener("click", (event) => {
         var subject = document.getElementById("subjectId").value
         showModal('modal-id', 'Enroll Fingerprint', employee)
     })
 })
 
 
 function showModal(id, title, subject){
 //show modal in bootstrap
 document.getElementById(id).classList.remove('fade')
 document.getElementById('modal-title').innerHTML = title + " - " + subject
 window.postMessage({source: "page", type: "START SESSION", action: title, userId: subject})
 
 }
 
 function closeModal(id){
 document.getElementById("modal-id").classList.add('fade')
 window.postMessage({source: "page", type: "END SESSION"})
 }
 </script>
 */
?>