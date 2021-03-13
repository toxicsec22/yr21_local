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
			width: 1800px; /*position: fixed;*/
		}
		
		ul#navmenu, ul.sub1, ul.sub2 {
                        list-style: none;
                        font: normal 12px/14px 'Helvetica', Arial, sans-serif; 
                        z-index: 1000;
                }
                
                ul#navmenu li  { 
                        width:125px;
                        text-align: center;
                        position: relative;
                        float: left; 
                        border: 1px solid #000;/*#44ACCF;*/
                        border-radius: 0px; 
                }
                
                ul#navmenu a {
                        text-decoration: none;
                        display: block;
                        width:125px;
                        height: 35px;
                        line-height: 35px; background: #cccccc/*003366*/;border-radius: 0px;color:#000;
                        
                }
                
                               
                ul#navmenu li:hover > a, ul#navmenu .sub1 li:hover > a{ 
                        background: #999999; font-weight: 800;
                        color: #000;
                        /*border: 1px solid #FFF;*/
                        border-radius: 0px;
                }
                
                ul#navmenu li:hover a:hover { 
                        background: #FFFF99;
                        color: #003366;
                }
                
                ul#navmenu ul.sub1 {
                        display: none;
                        position: absolute;
                        top:36px; border-bottom: 1px solid;
                }
                
                ul#navmenu ul.sub2 {
                        display: none;
                        position: absolute;
                        left:204px;
                        top:0px;
                }
                
                ul#navmenu ul.sub1 li, ul.sub2 li, ul#navmenu ul.sub1 a, ul#navmenu ul.sub2 a {
                        background: #999999;
                        width:202px; color:#FFFF99;
                }
                
                ul#navmenu ul.sub1 li:hover, ul.sub2 li:hover { 
                        background: #FFFF99;
                        color: #003366;
                        width:202px;
                }
                
                ul#navmenu li:hover .sub1  { 
			display: block;
                        width:202px;
                }
                
                ul#navmenu .sub1 li:hover .sub2  { 
			display: block;
                        width:202px;
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
			/*background: #f36283;*/
		}
		
		
	</style>