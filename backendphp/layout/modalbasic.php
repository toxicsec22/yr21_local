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
        font-family: sans-serif;
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
        font-family: Calibri, Arial, Georgia, sans-serif;
        margin-top: 15px;
        margin-bottom: 15px;
    }

    .modal-header > .close {
        float: right;
        margin-top: 15px;
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