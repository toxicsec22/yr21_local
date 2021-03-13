<style>
		body { 
			margin: 50;
			font: normal 14px/18px 'Helvetica', Arial, sans-serif;
			background: #ffe6cc; padding: 10px;
		}
		/* Positioning Rules */
		#container { 
			width: 900px;
			margin: 0 auto;
		}
		#content { 
			position: relative; 
			padding: 20px 250px 30px 20px;
			background: #fff;
		}
		/*#nav { 
			height: 50px;
			background: #b7d84b;
		}*/
		#nav { 
			position: absolute;
			top: 50px;
			bottom: 50px;
			right: 0px;
			width: 210px;
			padding: 0 20px;
			/*border-top: 5px solid #f95b34;*/
			background: #999999; /*#003366;*/
		}
		#footer { 
			position: fixed;
			bottom: 0; right: 0px;
			/*width: 860px;*/
			padding: 0 20px;
                        font: normal 10px/18px 'Helvetica', Arial, sans-serif;
			/*background: #f36283;*/
		}
		
		/* Stylistic Rules */
		#nav a {
/*			display: block; */
			float: left;
			color: #fff;
			text-decoration: none;
			/*padding: 0 20px;*/
			line-height: 30px;
                        width: 210px;
                        font: normal 11px/14px 'Helvetica', Arial, sans-serif;
			/*border-right: 1px solid #91ab3b;*/
		}

                #nav a.previous { font-style: italic; display: inline;}
                
                #nav a.next { font-style: italic; float: right; display: inline;}

                #nav a:hover { background: #FFFF99; color: #003366; font-weight: bold;}

		#nav p {
                        font: normal 12px/18px 'Helvetica', Arial, sans-serif;
			color: #fff;
		}
		
		img { width: 800px; }
                
		table,th,td
		{
		border:2px solid black;
		border-collapse:collapse;
		padding: 5px;
		background-color: #fff;
		font: normal 12px/14px 'Helvetica', Arial, sans-serif;
		}
	</style>