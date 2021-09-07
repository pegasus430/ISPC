
$(function(){
	 var gc = detailscookies;
	 
	 $('.dragboxpatient')
	.each(function(){
			
			var blockId=$(this).find('.patientdragbox-content').attr('id');
		
		if(gc!=undefined){
		
			if(gc.indexOf(blockId)!=-1){
				
				$(this).find('.patientdragbox-content').show();
			}else{
				
				$(this).find('.patientdragbox-content').hide();	
			}
			
		}
		
		
		$(this).hover(function(){
			$(this).find('h2').addClass('collapse');
		}, function(){
			$(this).find('h2').removeClass('collapse');
		})
		.find('h2').hover(function(){
			$(this).find('.configure').css('visibility', 'visible');
		}, function(){
			$(this).find('.configure').css('visibility', 'hidden');
		})
		.click(function(){
						
			
			
           // var gc = getCookie('patientInfoCook');
			if($(this).siblings('.patientdragbox-content').is(":hidden")){
			
				if(gc)
				{
					blockId = gc + blockId+',';
					///setCookie('patientInfoCook',blockId+';Expires=Thu Jul 08 2050 14:45:34');
				}
				else
				{
					///setCookie('patientInfoCook',blockId+',;Expires=Thu Jul 08 2050 14:45:34');
				}
			 
			}
			else
			{
			 	
				gc = gc.replace(blockId+',','');
				//setCookie('patientInfoCook',gc);
			}
			$(this).siblings('.patientdragbox-content').toggle();
		})
		.end()
		.find('.configure').css('visibility', 'hidden');
	});
	$('.patientcolumn').sortable({
		connectWith: '.patientcolumn',
		handle: 'h2',
		cursor: 'move',
		placeholder: 'placeholder',
		forcePlaceholderSize: true,
		opacity: 0.4,
		stop: function(event, ui){
			$(ui.item).find('h2').click();
			var sortorder='';
			var sep = "";
			$('.patientcolumn').each(function(){
				var itemorder=$(this).sortable('toArray');
				var patientcolumnId=$(this).attr('id');
				sortorder+=sep+patientcolumnId+'='+itemorder.toString();
				sep = '&';
			});
			//alert(sortorder);
			cookieSet('BlockSort',sortorder+';Expires=Thu Jul 08 2050 14:45:34'); 
			/*Pass sortorder variable to server using ajax to save state*/
		}
	})
	.disableSelection();
});

$(function(){
		   
		   var gc = detailscookies;
		
		 var gcarr = gc.split(',');

	$('.dragboxpatientdetails')
	.each(function(){
		
		
		var blockId=$(this).find('.patientdragbox-content').attr('id');
		
		if(gc){
			
			if(jQuery.inArray(blockId,gcarr)!=-1){
				
				$(this).find('.patientdragbox-content').show();
			}else{
				
				$(this).find('.patientdragbox-content').hide();	
			}
			
		}
			
		$(this).hover(function(){
			$(this).find('h2').addClass('collapse');
		}, function(){
			$(this).find('h2').removeClass('collapse');
		})
		.find('h2').hover(function(){
			$(this).find('.configure').css('visibility', 'visible');
		}, function(){
			$(this).find('.configure').css('visibility', 'hidden');
		})
		.click(function(){
						
	       // var gc = detailscookies;
			//var gcstr = "";
	      
			if($(this).siblings('.patientdragbox-content').is(":hidden")){
			
					gc = gc + blockId+',';

					//setCookie('patientDetailsCook',blockId);
			}
			else
			{
			 	
				gc = gc.replace(blockId+',','');
				//setCookie('patientDetailsCook',gc);
			}
			$(this).siblings('.patientdragbox-content').toggle();
		
           
			//ajaxCallserver({url:appbase+'patient/setoverviewcookie?ck='+gc});
			
		})
		.end()
		.find('.configure').css('visibility', 'hidden');
	});
	
	$('.bayern_dragvbox')
	.each(function(){
		
		
		var blockId=$(this).find('.bayern_dragvbox_content').attr('id');
		
		if(gc){
			
			if(jQuery.inArray(blockId,gcarr)!=-1){
				
				$(this).find('.bayern_dragvbox_content').show();
			}else{
				
				$(this).find('.bayern_dragvbox_content').hide();	
			}
			
		}
		
		$(this).hover(function(){
			$(this).find('h2').addClass('collapse');
		}, function(){
			$(this).find('h2').removeClass('collapse');
		})
		.find('h2').hover(function(){
			$(this).find('.configure').css('visibility', 'visible');
		}, function(){
			$(this).find('.configure').css('visibility', 'hidden');
		})
		.click(function(){
			
			if($(this).siblings('.bayern_dragvbox_content').is(":hidden")){
				
				gc = gc + blockId+',';
			}
			else
			{
				gc = gc.replace(blockId+',','');
			}
			$(this).siblings('.bayern_dragvbox_content').toggle();
			
		})
		.end()
		.find('.configure').css('visibility', 'hidden');
	});
	
	$('.contactform_dragvbox')
	.each(function(){
		
		
		var blockId=$(this).find('.contactform_dragvbox_content').attr('id');
		
		if(gc){
			
			if(jQuery.inArray(blockId,gcarr)!=-1){
				
				$(this).find('.contactform_dragvbox_content').show();
			}else{
				
				$(this).find('.contactform_dragvbox_content').hide();	
			}
			
		}
		
		$(this).hover(function(){
			$(this).find('h2').addClass('collapse');
		}, function(){
			$(this).find('h2').removeClass('collapse');
		})
		.find('h2').hover(function(){
			$(this).find('.configure').css('visibility', 'visible');
		}, function(){
			$(this).find('.configure').css('visibility', 'hidden');
		})
		.click(function(){
			
			if($(this).siblings('.contactform_dragvbox_content').is(":hidden")){
				
				gc = gc + blockId+',';
			}
			else
			{
				gc = gc.replace(blockId+',','');
			}
			$(this).siblings('.contactform_dragvbox_content').toggle();
			
		})
		.end()
		.find('.configure').css('visibility', 'hidden')
		.end()
		.find('.groupHeader').click(function(){
			$(this).toggleClass('expanded collapsed').siblings('.contactform_dragvbox_content').toggle().toggleClass('expanded collapsed');
		})
		;
	});
});

