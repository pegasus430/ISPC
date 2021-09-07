//ISPC-2615 Ancuta 13.07.2020
    (function ($) {
    	

    	$.fn.patientWrongClient = function (options) {

    		$( "#patientWrongClient" ).dialog({
    			autoOpen: false,
    			resizable: false,
    			title: translate('patientWrongClient'),
    			height: 450,
    			width: 650,
    			modal: true,
    			open: function() {
    				var buttonsData = $(this).data('buttons');
    				//console.log(buttonsData);
    				var url = appbase + 'connections/checkwrongclient?id='+idpd+'&action=showconn';
   			   		
    		   		$.ajax({
    					type: 'POST',
    					url: url,
    					data: buttonsData,
    					success:function(data){
    					
    						$('#patientWrongClient').html(data);
    					},
    					error:function(){
    						
    					}
    				});
    			},
    				    
    			
    			close: function() {
    				
    			}
    			
    		});
    		

   		if(typeof pstatus !== 'undefined' && pstatus == 1) 
   		{
    			check_saved_patientWrongSession();
    	}
   		
   		$(document).on('click', ('.changepatient'), function(){
   			var pencid = $(this).data('pid');
   			var clid = {value : $(this).data('pclid')};

   			$.post(appbase + 'connections/checkwrongclient?action=changeconn', {pencid: pencid})
   			.done(function(data) {
   				$('#patientWrongClient').dialog('close');
   				getClientUservalues(clid, data);
   			});
   		});
   		
   		$(document).on('click', ('.currentpatient'), function(){
   			$.post(appbase + 'connections/checkwrongclient?id='+idpd)
   			.done(function() {
   				$('#patientWrongClient').dialog('close');
   			});
   		});
    };

	    //window.idcidpd holds the cleintid from the pageload
	    function get_idcidpd() {
	    	var arr;	    	
	    	if (typeof(window.idcidpd) !== 'undefined' ) {
		    	arr = {"idcidpd": window.idcidpd};
		    } else {
		    	arr = {};
		    }
	    	return arr;
	    }
	    
 
 var check_saved_patientWrongSession = function (behaviour, callback) {
 
	checkSavedstarted = $.ajax({
		type: 'POST',
	    dataType: "json",
	    url: appbase + 'connections/checkwrongclient?id='+idpd+'&action=findconn',
	    cache: false,
	    data : get_idcidpd(),
	    
	}).done(function (result) {
	    
	/*}).done(function (check) {
		//console.log(check);
		if(check && check.result) {
			result = check.result;
		    } else {
			result = 'POTATO';
		    }*/
		
		// IF
		if(!$.isEmptyObject(result)){
			console.log(result);
			
			$('#patientWrongClient')
			.data('buttons', result)
			.dialog('open');
		}
		
			
	});
 };

 this.check_saved_patientWrongSession = check_saved_patientWrongSession; //make this callable directly

    	
    })(jQuery);