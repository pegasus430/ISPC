
function set_receipt_kv_blank(changetype)
	{
	    $('#receipt_type').val("kv_blank");

	    $('.receipt_background').css({
	    	backgroundImage: 'url(' + pi.imagePath + '/kv_blank.png)'
	    });
//	    $('#rceipt_form_NameGebInpt').css({
//		top: '38px'
//	    });

	    $('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();
	    $('#line3').val($('#custom_line_3').val());
	    
	    //ISPC 2306
	    if(!$('.receipt_background').hasClass('new_background'))
	    {
	    	$('.receipt_background').addClass('new_background');
	    }
	    
	    if(changetype === true)
     	{
	    	$('input[name="kassenno"]').val(ik);
	    	if($('#stampusers').val() != '' && ($('#rceipt_form_VertagsNrinpt').val() == '' || $('#rceipt_form_VkgulbisInpt').val() == ''))
	    	{
	    		$.get(appbase+ 'ajax/userstampinfo?stamp-info=' + $('#stampusers').val(), function(result) {
	    			if (result != 0){
	    				var resultx = jQuery.parseJSON(result);

	    				var user_lanr = resultx.lanr;
	    				var user_bsnr = resultx.bsnr;
					
	    				$('#rceipt_form_VertagsNrinpt').val(user_bsnr);
	    				$('#rceipt_form_VkgulbisInpt').val(user_lanr);
	    			} else{

	    			}

	    		});
	    	}	    
	    
	    	for (index = 1; index <= 10; index++)
	    	{
	    		if (index === 7) { continue; }
	    		if($("#rceipt_form_chek"+index).hasClass('norcb'))
	    		{
	    			$("#rceipt_form_chek"+index).toggleClass('rcb norcb');
	    		}
	    
	   		 	if(emptyform)
	   		 	{
	    			if(index != rezeptgebuhrenbefreiung)
	    			{
	    				$("#rceipt_form_chek"+index).removeAttr('checked');
	    			}
	    			else
	    			{
	    				if(!$("#rceipt_form_chek"+index).is(':checked'))
	   			     	{
	   		    		 	$("#rceipt_form_chek"+index).attr('checked','checked');
	   			     	}
	    			}
	    		}
	    		 else
	    		{
	    			 if(recipe_type_js == 'kv_blank')
	    			{
	    				 //alert(index+ ' ' +$.inArray(index.toString(), getiuval));
	    			 	if($.inArray(index.toString(), getiuval) != -1)
	    			 	{
	    			 		$("#rceipt_form_chek"+index).attr('checked','checked');
	    			 	}
	    			}
	    			 else
	    			{
	    				 if(index != rezeptgebuhrenbefreiung)
	 	    			{
	 	    				$("#rceipt_form_chek"+index).removeAttr('checked');
	 	    			}
	 	    			else
	 	    			{
	 	    				if(!$("#rceipt_form_chek"+index).is(':checked'))
	 	   			     	{
	 	   		    		 	$("#rceipt_form_chek"+index).attr('checked','checked');
	 	   			     	}
	 	    			}
	    			}
	    			 
	    		}
	    	}
	    
	    	if($("input[name='bvg']").hasClass('norcb'))
	    	{
	    		$("input[name='bvg']").toggleClass('rcb norcb');
	    	}
	    
	    	if($("input[name='bvg']").is(':checked'))
	    	{
	    		if(emptyform)
	    		{	
	    			$("input[name='bvg']").removeAttr('checked');
	    		}
	    		else
	    		{
	    			if(recipe_type_js == 'kv_blank')
	    			{
	    				if(bvg == 0)
	    				{
	    					$("input[name='bvg']").removeAttr('checked');
	    				}
	    			}
	    			else
	    			{
	    				$("input[name='bvg']").removeAttr('checked');
	    			}
	    		}
	    	}
	    	else
	    	{
	    		if(recipe_type_js == 'kv_blank' && bvg != 0)
	    		{
	    			$("input[name='bvg']").attr('checked','checked');
	    		}
	    	}
	    
	    	if($("input[name='mttel']").hasClass('norcb'))
	    	{
	    		$("input[name='mttel']").toggleClass('rcb norcb');
	    	}	    
	   
	    	if($("input[name='mttel']").is(':checked'))
	    	{
	    		if(emptyform)
	    		{	
	    			$("input[name='mttel']").removeAttr('checked');
	    		}
	    		else
	    		{
	    			if(recipe_type_js == 'kv_blank')
	    			{
	    				if(aid == 0)
	    				{
	    					$("input[name='mttel']").removeAttr('checked');
	    				}
	    			}
	    			else
	    			{
	    				$("input[name='mttel']").removeAttr('checked');
	    			}
	    		}
	    	}
	    	else
	    	{
	    		if(recipe_type_js == 'kv_blank' && aid != 0)
	    		{
	    			$("input[name='mttel']").attr('checked','checked');
	    		}
	    	}
	    
	    	if($("input[name='soff']").hasClass('norcb'))
	    	{
	    		$("input[name='soff']").toggleClass('rcb norcb');
	    	}
	    
	    	if($("input[name='soff']").is(':checked'))
	    	{
	    		if(emptyform)
	    		{	
	    			$("input[name='soff']").removeAttr('checked');
	    		}
	    		else
	    		{
	    			if(recipe_type_js == 'kv_blank')
	    			{
	    				if(vaccine == 0)
	    				{
	    					$("input[name='soff']").removeAttr('checked');
	    				}
	    			}
	    			else
	    			{
	    				$("input[name='soff']").removeAttr('checked');
	    			}
	    		}
	    	}
	    	else
	    	{
	    		if(recipe_type_js == 'kv_blank' && vaccine != 0)
	    		{
	    			$("input[name='soff']").attr('checked','checked');
	    		}
	    	}
	    
	    	if($("input[name='bedaf']").hasClass('norcb'))
	    	{
	    		$("input[name='bedaf']").toggleClass('rcb norcb');
	    	}
	    
	    	if($("input[name='bedaf']").is(':checked'))
	    	{
	    		if(emptyform)
	    		{	
	    			$("input[name='bedaf']").removeAttr('checked');
	    		}
	    		else
	    		{
	    			if(recipe_type_js == 'kv_blank')
	    			{
	    				if(bedarf == 0)
	    				{
	    					$("input[name='bedaf']").removeAttr('checked');
	    				}
	    			}
	    			else
	    			{
	    				$("input[name='bedaf']").removeAttr('checked');
	    			}
	    		}
	    	}
	    	else
	    	{
	    		if(recipe_type_js == 'kv_blank' && bedarf != 0)
	    		{
	    			$("input[name='bedaf']").attr('checked','checked');
	    		}
	    	}
	    
	    	if($("input[name='pricht']").hasClass('norcb'))
	    	{
	    		$("input[name='pricht']").toggleClass('rcb norcb');
	    	}
	    
	    	if($("input[name='pricht']").is(':checked'))
	    	{
	    		if(emptyform)
	    		{	
	    			$("input[name='pricht']").removeAttr('checked');
	    		}
	    		else
	    		{
	    			if(recipe_type_js == 'kv_blank')
	    			{
	    				if(price == 0)
	    				{
	    					$("input[name='pricht']").removeAttr('checked');
	    				}
	    			}
	    			else
	    			{
	    				$("input[name='pricht']").removeAttr('checked');
	    			}
	    		}
	    	}
	    	else
	    	{
	    		if(recipe_type_js == 'kv_blank' && price != 0)
	    		{
	    			$("input[name='pricht']").attr('checked','checked');
	    		}
	    	}

	    	$("span").remove(".rcb_dummy");
	    	$('.receipt_background .rcb').each(function(){
	    		var css=$(this).attr('style');
	    		//$(this).hide();
	    		var that=this;
	    		var newel=$("<span></span>");
	    		newel.attr('style',css);
	    		newel.addClass('rcb_dummy');
	    		$(this).parent().append(newel);
	    		newel.show();
            
	    		if($(this).attr('checked') == 'checked'){
	    			$(newel).css('background','url('+RES_FILE_PATH_js+'/images/pharmaindex/checkboxmark.png) no-repeat');
	    		}

	    		$(newel).click(function(){
	    			if($(that).attr('checked')){
	    				$(that).removeAttr('checked');
	    				$(this).css('background','none');
	    			} else{
	    				$(that).attr('checked','checked');
	    				$(this).css('background','url('+RES_FILE_PATH_js+'/images/pharmaindex/checkboxmark.png) no-repeat');
	    			}
	    		});
	    	});
     	}	    
	}
	    
		
	function set_receipt_kv_aid(changetype)
	{
		$('#receipt_type').val("kv_aid");
		
		$('.receipt_background').css({
			backgroundImage: 'url(' + pi.imagePath + '/kv_blank.png)'
		});
		
		$('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();
		if($('#custom_line_3').val() != ""){
			$('#line3').val($('#custom_line_3').val());	
		} else{
			$('#line3').val($('#main_diagnosis').val());
		}
		if(pi.mode == "recipe_switch_only") {
			$('#line3').val($('#main_diagnosis').val());
		}
		
		//ISPC 2306
	    if(!$('.receipt_background').hasClass('new_background'))
	    {
	    	$('.receipt_background').addClass('new_background');
	    }
		
		if(changetype === true)
		{
			$('input[name="kassenno"]').val(ik);
			if($('#stampusers').val() != '' && ($('#rceipt_form_VertagsNrinpt').val() == '' || $('#rceipt_form_VkgulbisInpt').val() == ''))
	    	{
	    		$.get(appbase+ 'ajax/userstampinfo?stamp-info=' + $('#stampusers').val(), function(result) {
	    			if (result != 0){
	    				var resultx = jQuery.parseJSON(result);

	    				var user_lanr = resultx.lanr;
	    				var user_bsnr = resultx.bsnr;
					
	    				$('#rceipt_form_VertagsNrinpt').val(user_bsnr);
	    				$('#rceipt_form_VkgulbisInpt').val(user_lanr);
	    			} else{

	    			}

	    		});
	    	}	    
	    
	    	for (index = 1; index <= 10; index++)
	    	{
	    		if (index === 7) { continue; }
	    		if($("#rceipt_form_chek"+index).hasClass('norcb'))
	    		{
	    			$("#rceipt_form_chek"+index).toggleClass('rcb norcb');
	    		}
	    
	    		 if(emptyform)
	    		{
	    			if(index != rezeptgebuhrenbefreiung)
	    			{
	    				$("#rceipt_form_chek"+index).removeAttr('checked');
	    			}
	    			else
	    			{
	    				if(!$("#rceipt_form_chek"+index).is(':checked'))
	   			     	{
	   		    		 	$("#rceipt_form_chek"+index).attr('checked','checked');
	   			     	}
	    			}
	    		}
	    		 else
	    		{
	    			 if(recipe_type_js == 'kv_aid')
	    			{
	    				 //alert(index+ ' ' +$.inArray(index.toString(), getiuval));
	    			 	if($.inArray(index.toString(), getiuval) != -1)
	    			 	{
	    			 		$("#rceipt_form_chek"+index).attr('checked','checked');
	    			 	}
	    			}
	    			 else
	    			{
	    				 if(index != rezeptgebuhrenbefreiung)
	 	    			{
	 	    				$("#rceipt_form_chek"+index).removeAttr('checked');
	 	    			}
	 	    			else
	 	    			{
	 	    				if(!$("#rceipt_form_chek"+index).is(':checked'))
	 	   			     	{
	 	   		    		 	$("#rceipt_form_chek"+index).attr('checked','checked');
	 	   			     	}
	 	    			}
	    			}
	    			 
	    		}
	    	}
	    
	    	if($("input[name='bvg']").hasClass('norcb'))
	    	{
	    		$("input[name='bvg']").toggleClass('rcb norcb');
	    	}
	    
	    	if($("input[name='bvg']").is(':checked'))
	    	{
	    		if(emptyform)
	    		{
	    			$("input[name='bvg']").removeAttr('checked');
	    		}
	    		else
	    		{
	    			if(recipe_type_js == 'kv_aid')
	    			{
	    				if(bvg == 0)
	    				{
	    					$("input[name='bvg']").removeAttr('checked');
	    				}
	    			}
	    			else
	    			{
	    				$("input[name='bvg']").removeAttr('checked');
	    			}
	    		}
	    	}
	    	else
	    	{
	    		if(recipe_type_js == 'kv_aid' && bvg != 0)
	    		{
	    			$("input[name='bvg']").attr('checked','checked');
	    		}
	    	}
	    
	    	if($("input[name='mttel']").hasClass('norcb'))
	    	{
	    		$("input[name='mttel']").toggleClass('rcb norcb');
	    	}	    
	   
	    	if($("input[name='mttel']").is(':checked'))
	    	{
	    		if(recipe_type_js == 'kv_aid')
	    		{
	    			if(aid == 0)
	    			{
	    				$("input[name='mttel']").removeAttr('checked');
	    			}
	    		}
	    		else
	    		{
	    			$("input[name='mttel']").attr('checked','checked');
	    		}
	    		
	    	}
	    	else
	    	{
	    		if(emptyform)
	    		{	
	    			$("input[name='mttel']").attr('checked','checked');
	    		}
	    		else
	    		{
	    			if(recipe_type_js == 'kv_aid')
	    			{
	    				if(aid == 0)
	    				{
	    					$("input[name='mttel']").removeAttr('checked');
	    				}
	    				else
	    				{
	    					$("input[name='mttel']").attr('checked','checked');
	    				}
	    			}
	    			else
	    			{
	    				$("input[name='mttel']").attr('checked','checked');
	    			}
	    		}	    	
	    	}  
	    
	    	if($("input[name='soff']").hasClass('norcb'))
	    	{
	    		$("input[name='soff']").toggleClass('rcb norcb');
	    	}
	    
	    	if($("input[name='soff']").is(':checked'))
	    	{
	    		if(emptyform)
	    		{	
	    			$("input[name='soff']").removeAttr('checked');
	    		}
	    		else
	    		{
	    			if(recipe_type_js == 'kv_aid')
	    			{
	    				if(vaccine == 0)
	    				{
	    					$("input[name='soff']").removeAttr('checked');
	    				}
	    			}
	    			else
	    			{
	    				$("input[name='soff']").removeAttr('checked');
	    			}
	    		}
	    	}
	    	else
	    	{
	    		if(recipe_type_js == 'kv_aid' && vaccine != 0)
    			{
	    			$("input[name='soff']").attr('checked','checked');
    			}
	    	}
	    
	    	if($("input[name='bedaf']").hasClass('norcb'))
	    	{
	    		$("input[name='bedaf']").toggleClass('rcb norcb');
	    	}
	    
	    	if($("input[name='bedaf']").is(':checked'))
	    	{
	    		if(emptyform)
	    		{	
	    			$("input[name='bedaf']").removeAttr('checked');
	    		}
	    		else
	    		{
	    			if(recipe_type_js == 'kv_aid')
	    			{
	    				if(bedarf == 0)
	    				{
	    					$("input[name='bedaf']").removeAttr('checked');
	    				}
	    			}
	    			else
	    			{
	    				$("input[name='bedaf']").removeAttr('checked');
	    			}
	    		}
	    	}
	    	else
	    	{
	    		if(recipe_type_js == 'kv_aid' && bedarf != 0)
	    		{
	    			$("input[name='bedaf']").attr('checked','checked');
	    		}
	    	}
	    
	    	if($("input[name='pricht']").hasClass('norcb'))
	    	{
	    		$("input[name='pricht']").toggleClass('rcb norcb');
	    	}
	    
	    	if($("input[name='pricht']").is(':checked'))
	    	{
	    		if(emptyform)
	    		{	
	    			$("input[name='pricht']").removeAttr('checked');
	    		}
	    		else
	    		{
	    			if(recipe_type_js == 'kv_aid')
	    			{
	    				if(price == 0)
	    				{
	    					$("input[name='pricht']").removeAttr('checked');
	    				}
	    			}
	    			else
	    			{
	    				$("input[name='pricht']").removeAttr('checked');
	    			}
	    		}
	    	}
	    	else
	    	{
	    		if(recipe_type_js == 'kv_aid' && price != 0)
	    		{
	    			$("input[name='pricht']").attr('checked','checked');
	    		}
	    	}

	    	$("span").remove(".rcb_dummy");
	    	$('.receipt_background .rcb').each(function(){
	    		var css=$(this).attr('style');
	    		//$(this).hide();
	    		var that=this;
	    		var newel=$("<span></span>");
            	newel.attr('style',css);
            	newel.addClass('rcb_dummy');
            	$(this).parent().append(newel);
            	newel.show();
            
            	if($(this).attr('checked') == 'checked'){
            		$(newel).css('background','url('+RES_FILE_PATH_js+'/images/pharmaindex/checkboxmark.png) no-repeat');
            	}

            	$(newel).click(function(){
            		if($(that).attr('checked')){
            			$(that).removeAttr('checked');
            			$(this).css('background','none');
            		} else{
            			$(that).attr('checked','checked');
            			$(this).css('background','url('+RES_FILE_PATH_js+'/images/pharmaindex/checkboxmark.png) no-repeat');
            		}
            	});
	    	});
		}		
	}

	function set_receipt_kv_green(changetype)
	{
	    $('.receipt_background').css({
		backgroundImage: 'url(' + pi.imagePath + '/kv_blank_green.png)'
	    });

	    //set hidden text to value "otcGreen - kv_green"
	    $('#receipt_type').val("kv_green");

	    $('#rceipt_form_NameGebInpt').css({
	    	top: '38px'
	    });

	    $('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').hide();
	    $('#line3').val($('#custom_line_3').val());
	    
	    //ISPC 2306
	    if($('.receipt_background').hasClass('new_background'))
	    {
	    	$('.receipt_background').removeClass('new_background');
	    }
	    
	    if(changetype === true)
		{
	    	$('input[name="kassenno"]').val(kassenno);
		}
	    
    	for (index = 1; index <= 10; index++)
    	{
    		if (index === 7) { continue; }
    		if($("#rceipt_form_chek"+index).hasClass('rcb'))
    		{
    			$("#rceipt_form_chek"+index).toggleClass('rcb norcb');
    			if($("#rceipt_form_chek"+index).attr('checked')){
    				$("#rceipt_form_chek"+index).removeAttr('checked');
    			}
    		}
    	}
    
    	if($("input[name='bvg']").hasClass('rcb'))
    	{
    		$("input[name='bvg']").toggleClass('rcb norcb');
    		if($("input[name='bvg']").attr('checked')){
    			$("input[name='bvg']").removeAttr('checked');
    		}
    	}
    
    	if($("input[name='mttel']").hasClass('rcb'))
    	{
    		$("input[name='mttel']").toggleClass('rcb norcb');
    		if($("input[name='mttel']").attr('checked')){
    			$("input[name='mttel']").removeAttr('checked');
    		}
    	}	    
    
    	if($("input[name='soff']").hasClass('rcb'))
    	{
    		$("input[name='soff']").toggleClass('rcb norcb');
    		if($("input[name='soff']").attr('checked')){
    			$("input[name='soff']").removeAttr('checked');
    		}
    	}	    
    
    	if($("input[name='bedaf']").hasClass('rcb'))
    	{
    		$("input[name='bedaf']").toggleClass('rcb norcb');
    		if($("input[name='bedaf']").attr('checked')){
    			$("input[name='bedaf']").removeAttr('checked');
    		}
    	}	    
    
    	if($("input[name='pricht']").hasClass('rcb'))
    	{
    		$("input[name='pricht']").toggleClass('rcb norcb');
    		if($("input[name='pricht']").attr('checked')){
    			$("input[name='pricht']").removeAttr('checked');
    		}
    	}	    
    
    	$('.receipt_background .norcb').each(function(){
    		$(this).hide();
    	});
	    
	    if(changetype === true)
	    {
	    	if($("#rceipt_form_VkgulbisInpt") != '')
	    	{
	    		$("#rceipt_form_VkgulbisInpt").val('');
	    	}
	    	if($("#rceipt_form_VertagsNrinpt") != ''){
	    		$("#rceipt_form_VertagsNrinpt").val('');
	    	}

		    $("span").remove(".rcb_dummy");
		    $('.receipt_background .rcb').each(function(){
	            var css=$(this).attr('style');
	            //$(this).hide();
	            var that=this;
	            var newel=$("<span></span>");
	            newel.attr('style',css);
	            newel.addClass('rcb_dummy');
	            $(this).parent().append(newel);
	            newel.show();
	            
	           if($(this).attr('checked') == 'checked'){
	                $(newel).css('background','url('+RES_FILE_PATH_js+'/images/pharmaindex/checkboxmark.png) no-repeat');
	            }

	            $(newel).click(function(){
	                if($(that).attr('checked')){
	                    $(that).removeAttr('checked');
	                    $(this).css('background','none');
	                } else{
	                    $(that).attr('checked','checked');
	                    $(this).css('background','url('+RES_FILE_PATH_js+'/images/pharmaindex/checkboxmark.png) no-repeat');
	                }
	            });
		    });
	    }
	}

	function set_receipt_kv_btm(changetype)
	{
	    $('#receipt_type').val("kv_btm");

	    $('.receipt_background').css({
	    	//Backgroung  changed on 27.06.2019 By Ancuta ISPC-2306
	    	backgroundImage: 'url(' + pi.imagePath + '/kv_blank_btm_190627.png)'
	    });
	    
//	    $('#rceipt_form_NameGebInpt').css({
//		top: '38px'
//	    });
	    $('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();
	    $('#line3').val($('#custom_line_3').val());
	    
	    //ISPC 2306
	    if(!$('.receipt_background').hasClass('new_background'))
	    {
	    	$('.receipt_background').addClass('new_background');
	    }
	    
	    if(changetype === true)
		{
	    	//$('input[name="kassenno"]').val(kassenno);
	    	$('input[name="kassenno"]').val(ik); // Changed to IK on 27.06.2019 By Ancuta ISPC-2306
		}
	    
	    if($("input[name='mttel']").hasClass('rcb'))
    	{
	    	$("input[name='mttel']").toggleClass('rcb norcb');
	    	if($("input[name='mttel']").attr('checked')){
		    	$("input[name='mttel']").removeAttr('checked');
		    }
    	}	    
	    
	    if($("input[name='soff']").hasClass('rcb'))
    	{
	    	$("input[name='soff']").toggleClass('rcb norcb');
	    	if($("input[name='soff']").attr('checked')){
		    	$("input[name='soff']").removeAttr('checked');
		    }
    	}
	    
	    if($("input[name='pricht']").hasClass('rcb'))
    	{
	    	$("input[name='pricht']").toggleClass('rcb norcb');
	    	if($("input[name='pricht']").attr('checked')){
		    	$("input[name='pricht']").removeAttr('checked');
		    }
    	}
	    
	    $('.receipt_background .norcb').each(function(){
            $(this).hide();
	    });
	    
	    if(changetype === true)
	    {
	    	if($('#stampusers').val() != '' && ($('#rceipt_form_VertagsNrinpt').val() == '' || $('#rceipt_form_VkgulbisInpt').val() == ''))
	    	{
	    		$.get(appbase+ 'ajax/userstampinfo?stamp-info=' + $('#stampusers').val(), function(result) {
	    			if (result != 0){
	    				var resultx = jQuery.parseJSON(result);

	    				var user_lanr = resultx.lanr;
	    				var user_bsnr = resultx.bsnr;
					
	    				$('#rceipt_form_VertagsNrinpt').val(user_bsnr);
	    				$('#rceipt_form_VkgulbisInpt').val(user_lanr);
	    			} else{

	    			}

	    		});
	    	}
	    
	    	for (index = 1; index <= 10; index++)
	    	{
	    		if (index === 7) { continue; }
	    		if($("#rceipt_form_chek"+index).hasClass('norcb'))
	    		{
	    			$("#rceipt_form_chek"+index).toggleClass('rcb norcb');
	    		}
	    
	    		 if(emptyform)
	    		{
	    			if(index != rezeptgebuhrenbefreiung)
	    			{
	    				$("#rceipt_form_chek"+index).removeAttr('checked');
	    			}
	    			else
	    			{
	    				if(!$("#rceipt_form_chek"+index).is(':checked'))
	   			     	{
	   		    		 	$("#rceipt_form_chek"+index).attr('checked','checked');
	   			     	}
	    			}
	    		}
	    		 else
	    		{
	    			 if(recipe_type_js == 'kv_btm')
	    			{
	    				 //alert(index+ ' ' +$.inArray(index.toString(), getiuval));
	    			 	if($.inArray(index.toString(), getiuval) != -1)
	    			 	{
	    			 		$("#rceipt_form_chek"+index).attr('checked','checked');
	    			 	}
	    			}
	    			 else
	    			{
	    				 if(index != rezeptgebuhrenbefreiung)
	 	    			{
	 	    				$("#rceipt_form_chek"+index).removeAttr('checked');
	 	    			}
	 	    			else
	 	    			{
	 	    				if(!$("#rceipt_form_chek"+index).is(':checked'))
	 	   			     	{
	 	   		    		 	$("#rceipt_form_chek"+index).attr('checked','checked');
	 	   			     	}
	 	    			}
	    			}
	    			 
	    		}	   	   
	    	}
	    
	        if($("input[name='bvg']").hasClass('norcb'))
	    	{
		    	$("input[name='bvg']").toggleClass('rcb norcb');
	    	}
		    
		    if($("input[name='bvg']").is(':checked'))
		    {
		    	if(emptyform)
		    	{	
		    		$("input[name='bvg']").removeAttr('checked');
		    	}
		    	else
		    	{
		    		if(recipe_type_js == 'kv_btm')
	    			{
		    			if(bvg == 0)
		    			{
		    				$("input[name='bvg']").removeAttr('checked');
		    			}
	    			}
		    		else
		    		{
		    			$("input[name='bvg']").removeAttr('checked');
		    		}
		    	}
		    }
		    else
		    {
		    	if(recipe_type_js == 'kv_btm' && bvg != 0)
	    		{
		    		$("input[name='bvg']").attr('checked','checked');
	    		}
		    }
		    
		    if($("input[name='bedaf']").hasClass('norcb'))
	    	{
		    	$("input[name='bedaf']").toggleClass('rcb norcb');
	    	}
		    
		    if($("input[name='bedaf']").is(':checked'))
		    {
		    	if(emptyform)
	    		{	
		    		$("input[name='bedaf']").removeAttr('checked');
	    		}
		    	else
		    	{
		    		if(recipe_type_js == 'kv_btm')
	    			{
		    			if(bedarf == 0)
		    			{
		    				$("input[name='bedaf']").removeAttr('checked');
		    			}
	    			}
		    		else
		    		{
		    			$("input[name='bedaf']").removeAttr('checked');
		    		}
		    	}
		    }
		    else
		    {
		    	if(recipe_type_js == 'kv_btm' && bedarf != 0)
	    		{
		    		$("input[name='bedaf']").attr('checked','checked');
	    		}
		    }
		    $("span").remove(".rcb_dummy");
		    $('.receipt_background .rcb').each(function(){
	            var css=$(this).attr('style');
	            //$(this).hide();
	            var that=this;
	            var newel=$("<span></span>");
	            newel.attr('style',css);
	            newel.addClass('rcb_dummy');
	            $(this).parent().append(newel);
	            newel.show();
	            
	           if($(this).attr('checked') == 'checked'){
	                $(newel).css('background','url('+RES_FILE_PATH_js+'/images/pharmaindex/checkboxmark.png) no-repeat');
	            }

	            $(newel).click(function(){
	                if($(that).attr('checked')){
	                    $(that).removeAttr('checked');
	                    $(this).css('background','none');
	                } else{
	                    $(that).attr('checked','checked');
	                    $(this).css('background','url('+RES_FILE_PATH_js+'/images/pharmaindex/checkboxmark.png) no-repeat');
	                }
	            });
		    });
	    }

             
        //ISPC-2711 Ancuta 08.03.2021
        if(user_use_btm_text){
        	$('#line3').val(user_btm_default_text);	
        }
        //--

	}

	function set_receipt_kv_blue(changetype)
	{
	    $('.receipt_background').css({
	    	backgroundImage: 'url(' + pi.imagePath + '/kv_blank_blue.png)'
	    });

//	    $('#rceipt_form_NameGebInpt').css({
//		top: '45px'
//	    });


	    /*$('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show();*/
	    $('#rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').show()
	    $('#rceipt_form_VertagsNrinpt').hide();
	   // $('#rceipt_form_VertagsNrinpt, #rceipt_form_VkgulbisInpt, #rceipt_form_VkGutBisinpt').hide();
	    
	    $('#line3').val($('#custom_line_3').val());
	    
	    //ISPC 2306
	    if($('.receipt_background').hasClass('new_background'))
	    {
	    	$('.receipt_background').removeClass('new_background');
	    }
	    
	    if(changetype === true)
		{
	    	$('input[name="kassenno"]').val(kassenno);
		}
	    
	    for (index = 1; index <= 10; index++)
	    {
	    	if (index === 5  || (index >=8 && index <= 10))
	    	{
	    		if(changetype === true)
	    		{
	    			if($("#rceipt_form_chek"+index).hasClass('norcb'))
	    			{
	    				$("#rceipt_form_chek"+index).toggleClass('rcb norcb');
	    			}
	    			if(emptyform)
		    		{
		    			if(index != rezeptgebuhrenbefreiung)
		    			{
		    				$("#rceipt_form_chek"+index).removeAttr('checked');
		    			}
		    			else
		    			{
		    				if(!$("#rceipt_form_chek"+index).is(':checked'))
		   			     	{
		   		    		 	$("#rceipt_form_chek"+index).attr('checked','checked');
		   			     	}
		    			}
		    		}
		    		 else
		    		{
		    			 if(recipe_type_js == 'kv_blue')
		    			{
		    				 //alert(index+ ' ' +$.inArray(index.toString(), getiuval));
		    			 	if($.inArray(index.toString(), getiuval) != -1)
		    			 	{
		    			 		$("#rceipt_form_chek"+index).attr('checked','checked');
		    			 	}
		    			}
		    			 else
		    			{
		    				 if(index != rezeptgebuhrenbefreiung)
		 	    			{
		 	    				$("#rceipt_form_chek"+index).removeAttr('checked');
		 	    			}
		 	    			else
		 	    			{
		 	    				if(!$("#rceipt_form_chek"+index).is(':checked'))
		 	   			     	{
		 	   		    		 	$("#rceipt_form_chek"+index).attr('checked','checked');
		 	   			     	}
		 	    			}
		    			}
		    			 
		    		}
	    		}
	    	}
	    	else
	    	{
	    		if($("#rceipt_form_chek"+index).hasClass('rcb'))
	    		{
	    			$("#rceipt_form_chek"+index).toggleClass('rcb norcb');
	    			if($("#rceipt_form_chek"+index).attr('checked')){
		    			$("#rceipt_form_chek"+index).removeAttr('checked');
		    		}
	    		}	    		
	    	}
	    }
	    
	    if($("input[name='bvg']").hasClass('rcb'))
    	{
	    	$("input[name='bvg']").toggleClass('rcb norcb');
	    	if($("input[name='bvg']").attr('checked')){
		    	$("input[name='bvg']").removeAttr('checked');
		    }
    	}	    
	    
	    if($("input[name='mttel']").hasClass('rcb'))
    	{
	    	$("input[name='mttel']").toggleClass('rcb norcb');
	    	if($("input[name='mttel']").attr('checked')){
		    	$("input[name='mttel']").removeAttr('checked');
		    }
    	}	    
	    
	    if($("input[name='soff']").hasClass('rcb'))
    	{
	    	$("input[name='soff']").toggleClass('rcb norcb');
	    	if($("input[name='soff']").attr('checked')){
		    	$("input[name='soff']").removeAttr('checked');
		    }
    	}	    
	    
	    if($("input[name='bedaf']").hasClass('rcb'))
    	{
	    	$("input[name='bedaf']").toggleClass('rcb norcb');
	    	if($("input[name='bedaf']").attr('checked')){
		    	$("input[name='bedaf']").removeAttr('checked');
		    }
    	}	    
	    
	    if($("input[name='pricht']").hasClass('rcb'))
    	{
	    	$("input[name='pricht']").toggleClass('rcb norcb');
	    	if($("input[name='pricht']").attr('checked')){
		    	$("input[name='pricht']").removeAttr('checked');
		    }
    	}
	    
	    $('.receipt_background .norcb').each(function(){
            $(this).hide();
	    });
	    
	    if(changetype === true)
    	{
	    	$("span").remove(".rcb_dummy");
	    	if($("#rceipt_form_VkgulbisInpt") != '')
	    	{
	    		if(recipe_type_js == "kv_blue")
	    		{
	    			$("#rceipt_form_VkgulbisInpt").val(oldlanr);
	    		}
	    		else 
	    		{
	    			$("#rceipt_form_VkgulbisInpt").val('');
	    		}
	    	}
	    	if($("#rceipt_form_VertagsNrinpt") != ''){
	    		$("#rceipt_form_VertagsNrinpt").val('');
	    	}
    	
	    	$('.receipt_background .rcb').each(function(){
	    		var css=$(this).attr('style');
	    		//$(this).hide();
	    		var that=this;
	    		var newel=$("<span></span>");
	    		newel.attr('style',css);
	    		newel.addClass('rcb_dummy');
	    		$(this).parent().append(newel);
	    		newel.show();
            
	    		if($(this).attr('checked') == 'checked'){
	    			$(newel).css('background','url('+RES_FILE_PATH_js+'/images/pharmaindex/checkboxmark.png) no-repeat');
	    		}

	    		$(newel).click(function(){
	    			if($(that).attr('checked')){
	    				$(that).removeAttr('checked');
	    				$(this).css('background','none');
	    			} else{
	    				$(that).attr('checked','checked');
	    				$(this).css('background','url('+RES_FILE_PATH_js+'/images/pharmaindex/checkboxmark.png) no-repeat');
	    			}
	    		});
	    	});
    	}
	}
	
	
	

	var default_maxlength = {};
	default_maxlength.maxlength_med1 = 46;
	default_maxlength.maxlength_line1 = 46;
	
	default_maxlength.maxlength_med4 = 46;
	default_maxlength.maxlength_line2 = 46;
	
	default_maxlength.maxlength_med7 = 46;
	default_maxlength.maxlength_line3 = 46;
	
	function maxlength(infobox_id , textlen, obj){
		
		var max = default_maxlength[infobox_id] - textlen;

		if(max < 0 )
		{
			 $('#'+infobox_id)
				 .html((-max )+' '+translate("characters over limit"))
				 .css("color", "red");
				 
			 		obj.addClass('limit_overflow');
		}
		else
		{
			 $('#'+infobox_id)
				 .html(max +' '+ translate("characters remaining"))
				 .css("color", "green");
			 		obj.removeClass('limit_overflow');
		}
	}
	
	
	function check_inputs_limit(){
	  	var disable_submit = 0; 
		var max_lenght = 46;
 			
		var curent_length = 0;
		$('.limit_content').each(function(key,value){
			
			var curent_length = $(this).val().length; 

			if(curent_length > max_lenght){
				$(this).addClass("limit_overflow");
				disable_submit++ ;
			} else{
				$(this).removeClass("limit_overflow");
			}
		});
		return disable_submit;
	}
	
$(document).ready(function(){
	
	
	
	var med_lines = ['1','4','7'];
	
	$.each(med_lines,function(key,i){

		var obj_med = 	$('input[name=med'+i+']');
		var obj_med_maxlenght_box = "maxlength_med"+i;
		//calulate lenght now
		
		var obj_med_text_input = 0 ; 
		if(obj_med.val() !=  'undefined' ){
			obj_med_text_input = obj_med.val().length; 
		}
		maxlength( obj_med_maxlenght_box , obj_med_text_input,obj_med);
		
		//attach event to re-calculate length
		obj_med.keyup(function(){
			maxlength( obj_med_maxlenght_box , $(this).val().length,obj_med );
		});
	});

	
	var other_lines = ['1','2','3'];
	
	$.each(other_lines,function(key,line_nr){
		var obj_line = 	$('input[name=line'+line_nr+']');
		var obj_line_maxlenght_box = "maxlength_line"+line_nr;
		//calulate lenght now
		
		var obj_line_text_input = 0 ; 
		if(obj_line.val() !=  'undefined' ){
			obj_line_text_input = obj_line.val().length; 
		}
		maxlength( obj_line_maxlenght_box , obj_line_text_input,obj_line);
		
		//attach event to re-calculate length
		obj_line.keyup(function(){
			maxlength( obj_line_maxlenght_box , $(this).val().length,obj_line );
		});
	});
	
 
	
	
	
	var changetype = false;
	if(show_pi_js)
	{
     // var pi = new pharmaindex();
	  pi.default_type = recipe_type_js;
      pi.input_medname = ".medication";
      pi.input_rowparent = "tr";
      pi.input_receipe_butt = "#mediplan_dialog .takeover_butt";
      pi.input_select_medi_butt = "#mediplan_dialog .select_medi";
      pi.input_to_recipe = ".to_recipe";
      pi.mode="recipe";
      pi.ikno="input[name=\"kassenno\"]";
		pi.use_suggestions = '0';
		pi.otcWarningSw = '0';

      //PATH FOR THE AJAX SCRIPTS
      pi.ajaxPath = "pharmaindex";

      //PATH FOR THE IMAGE FOLDER
      pi.imagePath="images/pharmaindex";
	 }
	 else
	{
		//load pharmaindex minimal only for switches
			//var pi = new pharmaindex();
			pi.default_type = recipe_type_js;
	        pi.mode="recipe_switch_only";
	//alert(pi.mode);
	        //PATH FOR THE IMAGE FOLDER
	        pi.imagePath="images/pharmaindex"; 
	}
	/*if(pi.mode == "recipe") {
		$('body').on('change', '#receipt_type', function () {
			var method_prefix = "set_receipt_";
			var receipt = $(this).val();

			if(receipt == "kv_blank" || receipt == "kv_btm" || receipt == "kv_green" || receipt == "kv_blue" || receipt == "kv_aid") {
				eval(method_prefix + receipt + "()");
			}
		});

		if(pi.default_type == "kv_blank" || pi.default_type == "kv_btm" || pi.default_type == "kv_green" || pi.default_type == "kv_blue" || pi.default_type == "kv_aid") {
			//init default type(the one loaded from db)
			eval("set_receipt_"+that.default_type+"()");
		}
    }
	
	if(pi.mode == "recipe_switch_only") {*/
	$('body').on('change', '#receipt_type', function () {
		var method_prefix = "set_receipt_";
		var receipt = $(this).val();
		changetype = true;

		if(receipt == "kv_blank" || receipt == "kv_btm" || receipt == "kv_green" || receipt == "kv_blue" || receipt == "kv_aid") {
			eval(method_prefix + receipt + "(changetype)");
		}
	});
		
	if(pi.default_type == "kv_blank" || pi.default_type == "kv_btm" || pi.default_type == "kv_green" || pi.default_type == "kv_blue" || pi.default_type == "kv_aid")
	{
		//init default type(the one loaded from db)
		eval("set_receipt_"+pi.default_type+"(changetype)");
	} 
	//}
});