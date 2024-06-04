$(document).ready(function() {
			
			 $('#execBtn').prop('disabled',true);
			 var sysenableFlag = false;
			 var nodeenableFlag = false;
			 var mnuctgyenableFlag = false;
			 var mnunamenableFlag = false;
			 //setButtonStatus (sysenableFlag,nodeenableFlag,mnuctgyenableFlag,mnunamenableFlag);
			 $('#Sysname').on('change',() => {
				
				 if($('#Sysname').val() !== 'def')
				 {   sysenableFlag = true;
				 } else{
					sysenableFlag = false; 
				 }
				  $('#Nodename').on('change',() => {
					
					  if($('#Nodename').val() !== 'def')
				{  
					 nodeenableFlag = true;
					
				 } else{
					nodeenableFlag = false; 
				 }
				    $('.Menucategory').on('change',() => {
						var Menucategory = $('.Menucategory').find('option:selected').text();
						 Menucategory = Menucategory.replace(/-/g,"");
						 
						if(Menucategory !== "")
				 {   
					 mnuctgyenableFlag = true;
					
				 } else{
					 
					mnuctgyenableFlag = false; 
				 }
						$('.Menuname').on('change',() => {
							 
						 var Menuname = $('.Menuname').find('option:selected').text(); 
						 Menuname = Menuname.replace(/-/g,"");
						 
						 if(Menuname !== "")
				 {   
					mnunamenableFlag = true;
					
				 }  else{
					 
					mnunamenableFlag = false; 
				 }
				 
				setButtonStatus (sysenableFlag,nodeenableFlag,mnuctgyenableFlag,mnunamenableFlag);
			 });
				setButtonStatus (sysenableFlag,nodeenableFlag,mnuctgyenableFlag,mnunamenableFlag);
			 });
				setButtonStatus (sysenableFlag,nodeenableFlag,mnuctgyenableFlag,mnunamenableFlag);
	});
				setButtonStatus (sysenableFlag,nodeenableFlag,mnuctgyenableFlag,mnunamenableFlag);
	 });

	
			
		 $('.Nodename').change(function() {
			 
			var nodenameselectedValue = $(this).val();
			setButtonStatus (sysenableFlag,nodeenableFlag,mnuctgyenableFlag,mnunamenableFlag);
                $.ajax({
                    type: 'POST',
                    url: 'jobexecution.php',
                    data: { jobnameValue: nodenameselectedValue },
                    success: function(response) {
						
                        $('.Menuname').html('<option value="" selected >-</option>' + response);
						$('.Joboutput').empty();
						
				
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred: ' + error);
                    }
                });
            });
			
		
	
           
            $('.Sysname').change(function() {
                var sysnameselectedValue = $(this).val();
				setButtonStatus (sysenableFlag,nodeenableFlag,mnuctgyenableFlag,mnunamenableFlag);
                $.ajax({
                    type: 'POST',
                    url: 'jobexecution.php',
                    data: { sysnameValue: sysnameselectedValue },
                    success: function(response) {
                        $('.Nodename').html('<option value="def" selected >-</option>' + response);
                        $('.Menuname').html('<option value="" selected >-</option>'); // Reset Menuname
						$('.Joboutput').empty();
						
					},
                    error: function(xhr, status, error) {
                        console.error('Error occurred: ' + error);
                    }
                });
            });
		
		});
       
	      function displayExecResult(jobExecResult) {
            document.getElementById('Joboutput').innerHTML = jobExecResult;
        }
		
		function showLoading() {
            document.getElementById('loading').style.display = 'flex';
        }
		
		function setButtonStatus (sysenableFlag,nodeenableFlag,mnuctgyenableFlag,mnunamenableFlag){
			 //alert ('system flag >>'+ sysenableFlag + ' node flag >>' + nodeenableFlag + ' menu category flag >>'+ mnuctgyenableFlag + ' menu name flag >>'+ mnunamenableFlag); 
				if(sysenableFlag === true && nodeenableFlag === true && mnuctgyenableFlag === true && mnunamenableFlag === true){
					//alert('button should be enable');
				//document.getElementById('execBtn').disabled = false; 
				$('.execBtn').prop('disabled',false);
				 }else{
					 //alert('button should be disable');
				 //document.getElementById('execBtn').disabled = true; 
				  $('.execBtn').prop('disabled',true);
				 }
			}
			
			