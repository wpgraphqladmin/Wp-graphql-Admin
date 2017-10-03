function onlyText(e) {
			var letterNumber = /^[0-9a-zA-Z]+$/;  
			//console.log(e);
			//alert(e.target.value);
			if((e.target.value.match(letterNumber))){  				
			} else{   
				e.target.value =  e.target.value.substring(0,  e.target.value.length - 1);
				return false;   
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
			var checkboxes = document.getElementsByName("mmetakeys[]");
			var posttype =  document.getElementById("fposttype").value;
			var addmutnonce =  document.getElementById("ad-mut-field").value;
			var filedArr = [];			
			var malias = '';
			var elem = document.getElementById('mfields').elements;
			for(var i = 0; i < elem.length; i++){
				if(elem[i].type == "text"){						
					malias += "&"+elem[i].name + "="+elem[i].value;
				}
			} 
			for (var i= 0; i<checkboxes.length;i++)		{
					if (checkboxes[i].checked === true)		{
						filedArr.push(checkboxes[i].value); 						
					}
			}
			var http = new XMLHttpRequest();
			var url = ajaxhandler.ajax_url;
			var params = "action=gql_support_mutation_fields&mfields="+filedArr+"&posttype="+posttype+malias+"&addmutnonce="+addmutnonce;;
			http.open("POST", url, true);
			//Send the proper header information along with the request
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http.onreadystatechange = function() {
				//Call a function when the state changes.
				if(http.readyState == 4 && http.status == 200) {
					alert(http.responseText);
					document.getElementById("fieldform").submit();
				}
			}
			http.send(params);
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
			var http = new XMLHttpRequest();
			var url = ajaxhandler.ajax_url;
			var params = "action=gql_support_remove_mut_fields&mrfields="+delFieldArry+"&posttype="+posttype+"&remvmutnonce="+remvmutnonce;
			http.open("POST", url, true);
			//Send the proper header information along with the request
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http.onreadystatechange = function() {
				//Call a function when the state changes.
				if(http.readyState == 4 && http.status == 200) {
					alert(http.responseText);
					document.getElementById("fieldform").submit();			
				}
			}
			http.send(params);
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
			var http = new XMLHttpRequest();
			var url = ajaxhandler.ajax_url;
			var params = "action=gql_support_remove_fields&rfields="+delFieldArry+"&posttype="+posttype+"&remvfieldnonce="+remvfieldnonce;
			http.open("POST", url, true);
			//Send the proper header information along with the request
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http.onreadystatechange = function() {
				//Call a function when the state changes.
				if(http.readyState == 4 && http.status == 200) {
					alert(http.responseText);
					document.getElementById("fieldform").submit();			
				}
			}
			http.send(params);
		}
		function saveGraphqlFields(){	
			var checkboxes = document.getElementsByName("metakeys[]");
			var posttype =  document.getElementById("fposttype").value;
			var addfieldnonce =  document.getElementById("ad-field-field").value;
			
			var filedArr = [];			
			var malias = '';
			var elem = document.getElementById('afields').elements;
			for(var i = 0; i < elem.length; i++){
				if(elem[i].type == "text"){						
					malias += "&"+elem[i].name + "="+elem[i].value;
				}
			} 
			for (var i= 0; i<checkboxes.length;i++)		{
					if (checkboxes[i].checked === true)		{
						filedArr.push(checkboxes[i].value); 						
					}
			}
			var http = new XMLHttpRequest();
			var url = ajaxhandler.ajax_url;
			var params = "action=gql_support_add_fields&afields="+filedArr+"&posttype="+posttype+malias+"&addfieldnonce="+addfieldnonce;
			http.open("POST", url, true);
			//Send the proper header information along with the request
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http.onreadystatechange = function() {
				//Call a function when the state changes.
				if(http.readyState == 4 && http.status == 200) {
					alert(http.responseText);
					document.getElementById("fieldform").submit();				
				}
			}
			http.send(params);
		}
		function gqlPostSupport(){
			var checkboxes = document.getElementsByName("posttype[]");
			var addpostnonce =  document.getElementById("ad-post-nonce").value;
			var postArr = [];
			var palias = '';
			var elem = document.getElementById('gpostform').elements;
			for (var i= 0; i<checkboxes.length;i++)		{
				if (checkboxes[i].checked === true)		{
					postArr.push(checkboxes[i].value); 
					palias += "&"+checkboxes[i].value + "="+document.getElementsByName(checkboxes[i].value)[0].value;		
				}
			}
			var http = new XMLHttpRequest();
			var url = ajaxhandler.ajax_url;
			var params = "action=gql_support_add_posts&ptypes="+postArr+"&addpostnonce="+addpostnonce+palias;
			http.open("POST", url, true);
			//Send the proper header information along with the request
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http.onreadystatechange = function() {//Call a function when the state changes.
			if(http.readyState == 4 && http.status == 200) {
			alert(http.responseText);
			location.reload();
			console.log(http.responseText);
			}
			}
			http.send(params);
		}	
		///remove
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