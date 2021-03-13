<style>
a:link { color: blue; }
a:visited { color: maroon; }
a:hover { color: orange; }
		* { 
			margin: 0;
                        padding: 0px;
                }
                
                body { 
			font: normal 14px/18px 'Helvetica', Arial, sans-serif;
			background-color: #afcecf;
                        padding: 10px;
		}
		
		#wrapper { 
			width: 1410px;
		}
		
		ul#navmenu, ul.sub1, ul.sub2 {
                        list-style: none;
                        font: normal 12px/14px 'Helvetica', Arial, sans-serif;
                        padding-right: 3px;
                }
                
                ul#navmenu li  { 
                        width:200px;
                        text-align: center;
                        position: relative;
                        float: left;
                        border-right: 1px solid #000; 
                        border-radius: 0px; 
                }
                
                ul#navmenu a {
                        text-decoration: none;
                        display: block;
                        width:200px;
/*                        height: 25px;*/
                        line-height: 25px; background: #b3b3b3;
                        border-radius: 0px;color:#000;
                        
                }
                
                               
                ul#navmenu ul.sub1 {
                        display: block;
                        position: relative; /*border: thick; */
                        left:0px;
                        top:0px; 
                        
                }
                
                ul#navmenu ul.sub2 {
                        display: block;
                        position: relative;
                        left:0px;
                        top:0px;
                }
                
                ul#navmenu ul.sub1 li,ul#navmenu ul.sub1 a {
                        background:#cccccc;
                        color:#4d4d4d; font-weight: 600;
                        width:200px;
                        
                }
                
                ul#navmenu ul.sub2 li, ul#navmenu ul.sub2 a {
                        background:#e6e6e6;
                        color:#595959;
                        width:200px; 
/*                        height: 25px; 
                        line-height: 16px;*/
                }
                
                
                
                ul#navmenu li:hover  a:hover { 
			display: block; background: #FFFF99;
                        width:200px;    
                        
                }
                
                               
                
                .clearFloat {
                    clear: both;
                    margin: 0px;
                    padding: 0px;
                }
                
		#footer { 
			position: fixed;
			bottom: 0; right: 0px;
			/*width: 860px;*/
			padding: 0 20px;
                        font: normal 10px/18px 'Helvetica', Arial, sans-serif;
		}
		
		
	</style>