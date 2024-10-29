 /****************************************************
 * Function: InsertElement
 * Desricption: Insert the banner image  after the given Parent as first child
 * only works with prototype library
 * Parameters:  ID			# can be tag or class or id
 *		parentName, 		# parent element
 *		childName		# childName normaly banner
 * Returnvalues: none
 ****************************************************/
 function InsertElement (ID,parentName,childName)
 {
	  var banner = $(childName);
     
     if (ID == "tag")
     {
			var parents = $A(document.getElementsByTagName(parentName));
			var parent = parents.first();	
     }
     if (ID == "class") 
     {
		var parent = $$(parentName);
				 
     }
     if (ID == "id")
     {
		var parent = $(parentName);
		
     }
     /*
	 if (parent == null) {
	 	return;
	 }
	 */
     var childs = parent.childElements();
    		
   	if (childs.length > 0)
	{
	  	var child = parent.firstChild
	   parent.insertBefore(banner,child);
			  	
	}else{
		parentName.appendChild(banner);
	}		 

 }
 
  /****************************************************
 * Function: BannerClick
 * Desricption: Ajax function. Insert banner clicks into the database
 * It depends on the wp prototype Ajax library
 * Returnvalues: none
 ****************************************************/
 function BannerClick(banner_id)
 {
    var url = "wp-content/plugins/banner-ads/banner_clicks.php";
    new Ajax.Request(url, { 
    method : 'post',  
    postBody: 'banner_id='+banner_id   
    });
 }
 
