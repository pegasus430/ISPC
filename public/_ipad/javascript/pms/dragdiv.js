
$(function(){
	var gc = detailscookies;	   

	$('.dragbox')
	.each(function(){
			
			var blockId=$(this).find('.dragbox-content').attr('id');
		
		if(gc!=undefined){
		
			if(gc.indexOf(blockId+',')!=-1){
				
				$(this).find('.dragbox-content').show();
			}else{
				
				$(this).find('.dragbox-content').hide();	
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
			$(this).siblings('.dragbox-content').toggle();
		})
		.end()
		.find('.configure').css('visibility', 'hidden');
	});
	$('.column').sortable({
		connectWith: '.column',
		handle: 'h2',
		cursor: 'move',
		placeholder: 'placeholder',
		forcePlaceholderSize: true,
		opacity: 0.4,
		stop: function(event, ui){
			$(ui.item).find('h2').click();
			var sortorder='';
			var sep = "";
			$('.column').each(function(){
				var itemorder=$(this).sortable('toArray');
				var columnId=$(this).attr('id');
				sortorder+=sep+columnId+'='+itemorder.toString();
				sep = '&';
			});
			//alert(sortorder);
			cookieSet('SortOrder',sortorder+';Expires=Thu Jul 08 2050 14:45:34'); 
			/*Pass sortorder variable to server using ajax to save state*/
		}
	})
	.disableSelection();
});

