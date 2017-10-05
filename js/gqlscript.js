
window.addEventListener('load', 
  function() { 
     var newElem = document.createElement("div");
		newElem.innerHTML = '<div id="light" class="white_content"><a  id="lposts" href = "javascript:void(0)"  class="close-btn" onclick ="gqlClose()">X</a><a href = "javascript:void(0)" id="lfields"  class="close-btn" onclick ="fgqlClose()">X</a><div id="gql-message" class="sus-err-content"> </div></div>	<div id="fade" class="black_overlay"></div>';    
      document.body.appendChild(newElem);
  }, false);
function gqlClose(){	
	location.reload();		
}
function fgqlClose(){
	document.getElementById("fieldform").submit();
}
function gqlError(message){
	document.getElementById('gql-error').style.display ='block'; 
	document.getElementById('gql-error').innerHTML = message; 
	document.getElementById('light').style.display ='none';
	document.getElementById('gql-loader').style.display ='none';
	document.getElementById('fade').style.display = 'none';
	document.getElementById('light').style.display ='none';
}
function gqlErrorRemove(message){
	document.getElementById('gql-error-r').style.display ='block'; 
	document.getElementById('gql-error-r').innerHTML = message; 
	document.getElementById('light').style.display ='none';
	document.getElementById('gql-loader-r').style.display ='none';
	document.getElementById('fade').style.display = 'none';
	document.getElementById('light').style.display ='none';
}
function ajaxBtnClick(){
	document.getElementById('gql-loader').style.display ='inline-block';
	document.getElementById('fade').style.display = 'block';
}
function ajaxBtnFieldClick(){
	document.getElementById('gql-loader-r').style.display ='inline-block';
	document.getElementById('fade').style.display = 'block';
}
function gqlLoderHide(message){
	var  y = document.getElementById('gql-loader-r');	

	document.getElementById('light').style.display ='none';
	document.getElementById('gql-loader').style.display ='none';
	if(y != null){
		document.getElementById('gql-loader-r').style.display ='none'; 
	}
	document.getElementById('fade').style.display = 'none';
	document.getElementById('light').style.display ='block';
	document.getElementById('gql-message').innerHTML = message;	

}
function onlyText(e) {
	var letter = /^[a-zA-Z]+$/;  	
	if(e.target.value.length > 0){	
		e.target.classList.remove("clrred");
		if((e.target.value.match(letter))){  			
		
		} else{   
			e.target.value =  e.target.value.substring(0,  e.target.value.length - 1);
			return false;   
		} 
	}
	document.getElementById('gql-error').style.display ='none'; 

}
function pboxChange(e){
	var x = document.getElementsByClassName("clrred");
	var  y = document.getElementById('gql-loader-r');	
	if(x.length == 0){
		document.getElementById('gql-error').style.display ='none'; 
		if(y !== null){
		document.getElementById('gql-error-r').style.display ='none'; 
		}
	}
}
function selectOneItem(event){
			var checkboxes = document.getElementsByName("posttype[]");
			var chk = event.target.checked;			
			for (var i= 0; i<checkboxes.length;i++)		{
				checkboxes[i].checked = false;						
			}
			if(chk){
				event.target.checked=true;	
			}else{
				event.target.checked=false;	
			}		
}	
function saveGraphqlMutationFields(){	
	var error= false;
	var letter = /^[a-zA-Z]+$/;  
	var checkboxes = document.getElementsByName("mmetakeys[]");
	var posttype =  document.getElementById("fposttype").value;
	var addmutnonce =  document.getElementById("ad-mut-field").value;
	var filedArr = [];			
	var malias = '';
	var elem = document.getElementById('mfields').elements;
	for(var i = 0; i < elem.length; i++){
		if(elem[i].type == "text"){				
			var aliasValue = elem[i].value;					
			if(!(aliasValue.match(letter)) ){ 					
				error = true;
				elem[i].className += " clrred";						
				break;
			}if(aliasValue.length > 2){
					malias += "&"+elem[i].name + "="+elem[i].value;	
			}else{
				error = true;
				elem[i].className += " clrred";							
				break;
			}				
		}
	} 
	if (!error) {
		for (var i= 0; i<checkboxes.length;i++)		{
				if (checkboxes[i].checked === true)		{
					filedArr.push(checkboxes[i].value); 						
				}
		}
		if(filedArr.length > 0 ){
		var http = new XMLHttpRequest();
		var url = ajaxhandler.ajax_url;
		ajaxBtnClick();
		var params = "action=gql_support_mutation_fields&mfields="+filedArr+"&posttype="+posttype+malias+"&addmutnonce="+addmutnonce;;
		http.open("POST", url, true);
		//Send the proper header information along with the request
		http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http.onreadystatechange = function() {
			//Call a function when the state changes.
			if(http.readyState == 4 && http.status == 200) {
				var out = JSON.parse( http.responseText );	
				if(out.error == 0){								 
				document.getElementById('lposts').style.display ='none';
				document.getElementById('lfields').style.display ='block';
				gqlLoderHide(out.message);	
				} else if( out.error == 1){
				gqlError(out.message);
				}else{
				gqlLoderHide(out.message);	
				}	
			}
		}
		http.send(params);
	}else{
		gqlError('Please select the posttype to add');
	}
	}else{
		gqlError('Invalid post alias name. It should be at least 3 characters. Only allow letters');
	}
}
function removeGraphqlMutFields(){
	var checkboxes = document.getElementsByName("mrmetakeys[]");
	var posttype =  document.getElementById("fposttype").value;
	var remvmutnonce =  document.getElementById("rm-mut-fields").value;
	var delFieldArry = [];				
	for (var i= 0; i<checkboxes.length;i++)		{
			if (checkboxes[i].checked === true)		{
				delFieldArry.push(checkboxes[i].value); 						
			}
	}
	if(delFieldArry.length > 0 ){
		ajaxBtnFieldClick();
		var http = new XMLHttpRequest();
		var url = ajaxhandler.ajax_url;
		var params = "action=gql_support_remove_mut_fields&mrfields="+delFieldArry+"&posttype="+posttype+"&remvmutnonce="+remvmutnonce;
		http.open("POST", url, true);
		//Send the proper header information along with the request
		http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http.onreadystatechange = function() {
			//Call a function when the state changes.
			if(http.readyState == 4 && http.status == 200) {
				var out = JSON.parse( http.responseText );	
				if(out.error == 0){
					gqlLoderHide(out.message);	
				} else if( out.error == 1){
					gqlErrorRemove(out.message);
				}else{
					gqlLoderHide(out.message);	
				}		
			}
		}
		http.send(params);
	}else{
		gqlErrorRemove('Please select the filed to remove');
	}

}
function removeGraphqlFields(){	
	var checkboxes = document.getElementsByName("rmetakeys[]");
	var posttype =  document.getElementById("fposttype").value;
	var remvfieldnonce =  document.getElementById("rm-field-fields").value;
	var delFieldArry = [];				
	for (var i= 0; i<checkboxes.length;i++)		{
			if (checkboxes[i].checked === true)		{
				delFieldArry.push(checkboxes[i].value); 						
			}
	}
	if(delFieldArry.length > 0 ){
		ajaxBtnFieldClick();
		var http = new XMLHttpRequest();
		var url = ajaxhandler.ajax_url;
		var params = "action=gql_support_remove_fields&rfields="+delFieldArry+"&posttype="+posttype+"&remvfieldnonce="+remvfieldnonce;
		http.open("POST", url, true);
		//Send the proper header information along with the request
		http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http.onreadystatechange = function() {
			//Call a function when the state changes.
			if(http.readyState == 4 && http.status == 200) {
				var out = JSON.parse( http.responseText );	
					if(out.error == 0){
						gqlLoderHide(out.message);	
					} else if( out.error == 1){
						gqlErrorRemove(out.message);
					}else{
						gqlLoderHide(out.message);	
					}
			}
		}
		http.send(params);
	}else{
		 gqlErrorRemove('Please select the filed to remove');
	}
}
function saveGraphqlFields(){	
	var error= false;
	var letter = /^[a-zA-Z]+$/;  
	var checkboxes = document.getElementsByName("metakeys[]");
	var posttype =  document.getElementById("fposttype").value;
	var addfieldnonce =  document.getElementById("ad-field-field").value;			
	var filedArr = [];			
	var malias = '';
	var elem = document.getElementById('afields').elements;
	for(var i = 0; i < elem.length; i++){
		if(elem[i].type == "text"){						
			var aliasValue = elem[i].value;					
			if(!(aliasValue.match(letter)) ){ 					
				error = true;
				elem[i].className += " clrred";						
				break;
			}if(aliasValue.length > 2){
					malias += "&"+elem[i].name + "="+elem[i].value;
				}else{
					error = true;
					elem[i].className += " clrred";							
					break;
				}	
		}
	} 
	if (!error) {
		for (var i= 0; i<checkboxes.length;i++)		{
			if (checkboxes[i].checked === true)		{
				filedArr.push(checkboxes[i].value); 						
			}
		}
		if(filedArr.length > 0 ) {				
			var http = new XMLHttpRequest();
			var url = ajaxhandler.ajax_url;
			ajaxBtnClick();
			var params = "action=gql_support_add_fields&afields="+filedArr+"&posttype="+posttype+malias+"&addfieldnonce="+addfieldnonce;
			http.open("POST", url, true);
			//Send the proper header information along with the request
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http.onreadystatechange = function() {
				//Call a function when the state changes.
				if(http.readyState == 4 && http.status == 200) {
					var out = JSON.parse( http.responseText );	
					if(out.error == 0){
						 
						document.getElementById('lposts').style.display ='none';
						document.getElementById('lfields').style.display ='block';
						gqlLoderHide(out.message);	
					} else if( out.error == 1){
						gqlError(out.message);
					}else{
						gqlLoderHide(out.message);	
					}									
				}
			}
			http.send(params);
		}else{
			 gqlError('Please select the posttype to add');
		}
	}else{
		gqlError('Invalid post alias name. It should be at least 3 characters. Only allow letters');
	}
}
function gqlPostSupport(){
	var error= false;
	var letter = /^[a-zA-Z]+$/;  
	var checkboxes = document.getElementsByName("posttype[]");
	var addpostnonce =  document.getElementById("ad-post-nonce").value;
	var postArr = [];
	var palias = '';
	var elem = document.getElementById('gpostform').elements;
	for (var i= 0; i<checkboxes.length;i++)		{
		if (checkboxes[i].checked === true)		{
			postArr.push(checkboxes[i].value); 
			var aliasValue =document.getElementsByName(checkboxes[i].value)[0].value;
			if(!(aliasValue.match(letter)) ){ 					
				error = true;
				document.getElementsByName(checkboxes[i].value)[0].className += " clrred";						
				break;
			}else{
				if(aliasValue.length > 2){
					palias += "&"+checkboxes[i].value + "="+document.getElementsByName(checkboxes[i].value)[0].value;	
				}else{
					error = true;
					document.getElementsByName(checkboxes[i].value)[0].className += " clrred";						
					break;
				}							
			}					
		}
	}
	if (!error) {
		if(postArr.length > 0 ){
			ajaxBtnClick();
			var http = new XMLHttpRequest();
			var url = ajaxhandler.ajax_url;
			var params = "action=gql_support_add_posts&ptypes="+postArr+"&addpostnonce="+addpostnonce+palias;
			http.open("POST", url, true);
			//Send the proper header information along with the request
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http.onreadystatechange = function() {//Call a function when the state changes.
				if(http.readyState == 4 && http.status == 200) {
					var out = JSON.parse( http.responseText );	
					if(out.error == 0){
						gqlLoderHide(out.message);	
					} else if( out.error == 1){
						gqlError(out.message);
					}else{
						gqlLoderHide(out.message);	
					}
				}
			}
			http.send(params);
		}else{				 
			 gqlError('Please select the posttype to add');
		}
	} else{
		gqlError('Invalid post alias name. It should be at least 3 characters. Only allow letters');
	}
}	
//remove
function gqlRemovePostSupport(){
	var checkboxes = document.getElementsByName("rposttype[]");
	var rempostnonce =  document.getElementById("re-post-nonce").value;
	var postArr = [];
	for (var i= 0; i<checkboxes.length;i++)		{
		if (checkboxes[i].checked === true)	{
			postArr.push(checkboxes[i].value); 
		}
	}
	var http = new XMLHttpRequest();
	var url = ajaxhandler.ajax_url;
	var params = "action=gql_support_remove_posts&ptypes="+postArr+"&rempostnonce="+rempostnonce;
	http.open("POST", url, true);
	//Send the proper header information along with the request
	http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	http.onreadystatechange = function() {
	//Call a function when the state changes.
	if(http.readyState == 4 && http.status == 200) {
	alert(http.responseText);
	location.reload();
	console.log(http.responseText);
	}
	}
	http.send(params);
	
}