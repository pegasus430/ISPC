
	var Admin = {}; //Stripped from Admin System
	var tinyMCE = false; //Placeholder until tinyMCE is loaded at end of DOM.
	
	$(document).ready(function(){
		
		Admin.formbuilder.tinymce($('#form_builder_container'))
		//tinyMCE.execCommand('mceAddControl', false, 'header');
		 
		Admin.formbuilder.pages = noofpages;
		Admin.formbuilder.currentpage = 1;
		Admin.formbuilder.onemm = 3.779527559;
		Admin.formbuilder.dimensions = Array();
		Admin.formbuilder.dimensions[0] = '';
		Admin.formbuilder.dimensions[1] = '';
		$("#headerheight").keyup(function(){
			
			Admin.formbuilder.tinymceadjust.adjustHeight('header',$(this));
		
		});
		
		$("#footerheight").keyup(function(){
			
			Admin.formbuilder.tinymceadjust.adjustHeight('footer',$(this));
		
		});
		
		$("#formid").change(function(){
						
						
						//alert(appbase+'pdfdesigner/getformelements?frmid='+$(this).val());
						$(this).parent().addClass('loading');
						var e = $(this);
						$.getJSON(appbase+'pdfdesigner/getformelements?frmid='+$(this).val(),function(result){
							
							//alert(result.fieldlist);
							
							//$("#form_builder_panel fieldset.sml").remove();
							$(e).parent().removeClass('loading');
							for($i=0;$i<=Admin.formbuilder.pages;$i++)
							{
								$('#Pdfdesignpage_'+$i+' ol').html('');	
								
							}
							
							$("#Pdfdesignpage_0 ol").append(result.fieldlist);
							
							//$("#form_builder_panel fieldset.sml").append(result.fieldlist);
							//Admin.createDropable.initFormpanelDroppable();
							Admin.createDropable.initDraggableli();
							
							
							
							
					});
		});
		
		$("#dimensions").change(function(){
				
			dim  = $(this).val().split(',');
			
			
			
			pagex = Admin.formbuilder.onemm*dim[1];
			pagey = Admin.formbuilder.onemm*(dim[0]-Admin.formbuilder.headerheight-Admin.formbuilder.footerheight);
			
			for($i=1;$i<=Admin.formbuilder.pages;$i++)
			{
				//$('#Pdfdesignpage_'+$i).removeClass(Admin.formbuilder.dimensions);
				height = pagey;
				
				$('#Pdfdesignpage_'+$i).css({height:height+'px',width:pagex+'px'});
				//$('#Pdfdesignpage_'+$i).addClass($(this).val());
				$('#Pdfdesignpage_'+$i+' ol').css({height:pagey+'px',width:pagex+'px'});
				//$('#Pdfdesignpage_'+$i+' ol').addClass($(this).val());	
				
			}
			
			Admin.formbuilder.dimensions[0] = pagey;
			Admin.formbuilder.dimensions[1] = pagex;
			
			$("#altdimension_height").val(dim[0]);
			$("#altdimension_width").val(dim[1]);
			
			
			
			
		});
		
		$("#noofpages").change(function(){
										
										
									 
					if(Admin.formbuilder.pages<$(this).val())
					{
						var pg = Admin.formbuilder.pages;
						for($i=0;$i<$(this).val()-pg;$i++)
						{
							//("#form_builder_panel").append
						$('<fieldset  id="Pdfdesignpage_'+(Admin.formbuilder.pages+1)+'" style="position:relative;height:'+Admin.formbuilder.dimensions[0]+'px;width:'+Admin.formbuilder.dimensions[1]+'px" ><ol style="overflow:auto;position:relative;height:'+Admin.formbuilder.dimensions[0]+'px;width:'+Admin.formbuilder.dimensions[1]+'px" ></ol></fieldset>').insertAfter($('#Pdfdesignpage_'+(Admin.formbuilder.pages)));
							$('#Pdfdesignpage_'+(Admin.formbuilder.pages+1)).hide();
							Admin.formbuilder.pages++;
						}
						
						//Admin.createDropable.initFormpanelDroppable();
						//Admin.createDropable.initDraggableli();
						//Admin.formbuilder.pages+=$i;
						
					}else if(Admin.formbuilder.pages>$(this).val()){
						
						$j=0;
						for($i=parseInt($(this).val())+1;$i<=Admin.formbuilder.pages;$i++){
							
							$('#Pdfdesignpage_'+$i+' li').appendTo($('#Pdfdesignpage_'+$(this).val()));
							$('#Pdfdesignpage_'+$i).remove();
							$j++;
						}
						
						
						Admin.formbuilder.pages-=$j;
						
						
					}
									 
			});
		
		$('#pagenavigation a:first').click(function(){
		
			if(Admin.formbuilder.currentpage>1){
				Admin.formbuilder.currentpage--;	
			}
			
			for($i=1;$i<=Admin.formbuilder.pages;$i++)
			{
				if($i!=Admin.formbuilder.currentpage)
				{
					$('#Pdfdesignpage_'+$i).hide();	
				}else{
					
					$('#Pdfdesignpage_'+$i).show();	
				}
			}
			
			$('#pagenavigation span').html("Page "+Admin.formbuilder.currentpage);
													
		});
		
		$('#pagenavigation a:last').click(function(){
												   
			if(Admin.formbuilder.currentpage<Admin.formbuilder.pages){
				Admin.formbuilder.currentpage++;	
			}
			
			for($i=1;$i<=Admin.formbuilder.pages;$i++)
			{
				if($i!=Admin.formbuilder.currentpage)
				{
					$('#Pdfdesignpage_'+$i).hide();	
				}else{
					
					$('#Pdfdesignpage_'+$i).show();	
				}
			}
			
			$('#pagenavigation span').html("Page "+Admin.formbuilder.currentpage);
		
		});
		
		
		Admin.formbuilder.init();
		
	});
	
	
	
	Admin.createDropable={
		
		initFormpanelDroppable:function(){
			
				$("#form_builder_panel ol").droppable({
				activeClass: "ui-state-default",
				hoverClass: "ui-state-hover",
				//accept: ":not(.ui-sortable-helper)",
				drop: function(event, ui) {
							$(this).find(".placeholder").remove();
							//$("<li></li>").text(ui.draggable.text()).appendTo(this);
							//$(ui.draggable).appendTo(this);//.css({top:'1px',left:'5px'});
							
							$(this).sortable("refresh");
					}
			});
			
		},
		initDraggableli:function(){
			
			$("#form_builder_container li").draggable({
				containment: 'parent',
				stop:function(event,ui){
					
					
					var con = $(ui.helper).parent();
					var li = $(ui.helper)
					var id = $(ui.helper).find('label:first').attr('for');
					
					//$xratio =  Admin.formbuilder.dimensions[1]/
					
					
					
					var posy = (ui.offset.top-con.offset().top)/Admin.formbuilder.onemm;
					var posx = (ui.offset.left-con.offset().left)/Admin.formbuilder.onemm;
					var foundx = false;
					var foundy = false;
					$('div.attrs.'+id+' input').each(function(){
						if ($(this).attr('name') == "properties["+id+"][posx]")
						{
							$(this).val(posx);
							foundx = true;
						}
						
						if ($(this).attr('name') == "properties["+id+"][posy]")
						{
							$(this).val(posy);
							foundy = true;
						}
					});
					
					if(!foundx){
						
						$('div.attrs.'+id).append("<input type='hidden' class='new_property "+name+"' name='properties["+id+"][posx]'/>");
						$('.new_property').removeClass('new_property').val(posx);
					}
					
					if(!foundy){
						
						$('div.attrs.'+id).append("<input type='hidden' class='new_property "+name+"' name='properties["+id+"][posy]'/>");
						$('.new_property').removeClass('new_property').val(posy);
					}
					
					
					$('#form_builder_properties li.'+id+' input[name=posx]').val(posx);
					$('#form_builder_properties li.'+id+' input[name=posy]').val(posy);
					//alert($(ui.draggable).offset().top);
					//alert($(ui.draggable).offset().left);
				}
			
			});
			
			$("#form_builder_container li").each(function(){
												
				Admin.formbuilder.properties($(this));
														  
			});
			
			//$("#form_builder_panel li").resizable();
			
		},
		initDraggablesingleli:function(e){
			
			$(e).draggable({
				containment: 'parent',
				stop:function(event,ui){
					
					
					var con = $(ui.helper).parent();
					var li = $(ui.helper)
					var id = $(ui.helper).find('label:first').attr('for');
					
					//$xratio =  Admin.formbuilder.dimensions[1]/
					
					
					
					var posy = (ui.offset.top-con.offset().top)/Admin.formbuilder.onemm;
					var posx = (ui.offset.left-con.offset().left)/Admin.formbuilder.onemm;
					var foundx = false;
					var foundy = false;
					$('div.attrs.'+id+' input').each(function(){
						if ($(this).attr('name') == "properties["+id+"][posx]")
						{
							$(this).val(posx);
							foundx = true;
						}
						
						if ($(this).attr('name') == "properties["+id+"][posy]")
						{
							$(this).val(posy);
							foundy = true;
						}
					});
					
					if(!foundx){
						
						$('div.attrs.'+id).append("<input type='hidden' class='new_property "+name+"' name='properties["+id+"][posx]'/>");
						$('.new_property').removeClass('new_property').val(posx);
					}
					
					if(!foundy){
						
						$('div.attrs.'+id).append("<input type='hidden' class='new_property "+name+"' name='properties["+id+"][posy]'/>");
						$('.new_property').removeClass('new_property').val(posy);
					}
					
					
					$('#form_builder_properties li.'+id+' input[name=posx]').val(posx);
					$('#form_builder_properties li.'+id+' input[name=posy]').val(posy);
					//alert($(ui.draggable).offset().top);
					//alert($(ui.draggable).offset().left);
				}
			
			});
			
			/*$("#form_builder_panel li").each(function(){
												
				Admin.formbuilder.properties($(this));
														  
			});*/
			
			//$("#form_builder_panel li").resizable();
			
		}
		
	};
		
		
		
	
	Admin.formbuilder = {
		BASEURL: appbase+'pdfdesigner/createelements',
		PREVIEWURL: 'preview.php',
		init: function()
		{
			Admin.formbuilder.layout('body');
			Admin.formbuilder.tinymce();
		},
		layout: function(e)
		{
			var $active_layout = $(e);
			
			$active_layout.find('form[id=""]').each(function(){
				$(this).attr('id','f'+randomString(50)); //an ID for every form.
			});
			
			$active_layout.find('.last-child').removeClass('last-child'); //meh, safety dance
			
			$active_layout.find('ul,ol').each(function(){
				$(this).children('li:last').addClass('last-child');
			});
			
			$active_layout.children('li:last').addClass('last-child'); //incase the element itself is a ul or ol
			
			$active_layout.find('.tooltip').tooltip({track: true, delay: 0, showURL: false, fade: 0, showBody: " - "});
			
			$active_layout.find('.datepicker').datepicker({dateFormat: 'dd MM yy', duration: ''});
			
			$active_layout.find("#form_builder_toolbox li").click(function(){
					//var into = $("#form_builder_panel ol");
					var into = $('#Pdfdesignpage_'+Admin.formbuilder.currentpage+' ol');
					var type = $(this).attr('id');
					var e = this;
					$(this).addClass('loading');
					
					$.get(Admin.formbuilder.BASEURL+'?action=element&type='+type+'&cpage='+Admin.formbuilder.currentpage,function(result){
																						  
						$(e).removeClass('loading');
						$(into).prepend(result);
						var $newrow = $(into).find('li:first');
						//style
						$($newrow).find("hr").click(function(){
							
							$($newrow).find('a.properties').trigger('click');
						});
						Admin.formbuilder.editors();
						Admin.formbuilder.properties($newrow);
						Admin.formbuilder.layout($newrow);
						//show
						//$newrow.hide().slideDown('slow');
						
						
						$(into).sortable("refresh");
						Admin.createDropable.initDraggablesingleli($newrow);
						//$($newrow).draggable();

						delete result;
					});
			});
			
			
			
			$active_layout.find("#form_builder_panel ol").sortable({
				cursor: 'ns-resize',
				axis: 'y',
				handle: '.handle',
				start: function(e,ui) {
					$('.wysiwyg').each(function(){
						var name = $(this).attr('name');
						if (name) {
							if (tinyMCE.get(name)) {
								tinyMCE.execCommand('mceRemoveControl', false, name);
							}
						}
					});
				},
				stop: function(e,ui) {
					Admin.formbuilder.editors();
				}
			});
			
			
			
			
			
			
			
			
			$active_layout.find('div.dialog').each(function(){
				
				$.metadata.setType("class");
				var w = $(this).metadata().w;
				var h = $(this).metadata().h;
				
				$(this).dialog({
					modal: true,
					zIndex: 400000, /* TinyMCE grief. Their default is literally 300000... Fail*/
					autoOpen: false,
					shadow: false,
					width: (w?parseInt(w, 10):400),
					height: (h?parseInt(h, 10):'auto'),
					title: $(this).attr('title'),
					dragStart: function(event, ui) {
						$(this).find('iframe').hide();
					},
					dragStop: function(event, ui) {
						$(this).find('iframe').show();
					},
					resizeStart: function(event, ui) {
						$(this).find('iframe').hide();
					},
					resizeStop: function(event, ui) {
						$(this).find('iframe').show();
					}
				});
			});
			
			$active_layout = null; //destroy
		},
		tinymce: function(e)
		{
			
			if (!tinyMCE)
			{			
				
				tinyMCE_GZ.init({
					plugins : 'pagebreak,style,table,advlink,inlinepopups,media,contextmenu,paste,xhtmlxtras',
					themes : 'simple,advanced',
					disk_cache : true,
					languages : 'en',
					debug : false
				},function(){
					
					tinyMCE.init({
						mode : "textareas",
						theme : 'advanced',
						editor_selector : "fancysml",
						relative_urls : false,
						absolute_urls : true,
						
						file_browser_callback : "openSwampyBrowser",
						plugins : 'pagebreak,style,table,advlink,inlinepopups,media,contextmenu,paste,xhtmlxtras',
						
						theme_advanced_buttons1 : "justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect,|,save",
						
						theme_advanced_buttons2 :"undo,redo,|,link,unlink,anchor,image,cleanup,helptablecontrols,newdocument",
						
						theme_advanced_buttons3 : "bullist,numlist,outdent,indent,blockquote,attribs,pagebreak,|,bold,italic,underline,strikethrough,|,cut,copy,paste,pastetext,pasteword,search,replace,|,insertdate,inserttime,preview,code,",
						
						theme_advanced_buttons4 : "spellchecker,|,cite,abbr,acronym,del,ins,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage,|,hr,removeformat,visualaid,|,sub,sup,charmap,media,styleprops,|,forecolor,backcolor",
						
						theme_advanced_toolbar_location : "top",
						
						theme_advanced_toolbar_align : "left",
						
						onchange_callback : "tinymceOnChangeHandler",
						
						height:"100"

					});
					
					tinyMCE.get('header').contentAreaContainer.children[0].style.height = (Admin.formbuilder.onemm*25)+'px';
					tinyMCE.get('footer').contentAreaContainer.children[0].style.height = (Admin.formbuilder.onemm*10)+'px';
					
					Admin.formbuilder.headerheight = 25;
					Admin.formbuilder.footerheight = 10;
					
					
					
				});
			} else {
				$(e).find('textarea.fancy, textarea.fancysml').each(function(){
					
					var name = $(this).attr('name');
					if (!tinyMCE.get(name)) tinyMCE.execCommand('mceAddControl', false, name);
				});
			}
		},
		remove: function(e)
		{
			Admin.formbuilder.confirm("Really remove this element?",function(options){
				$('label[for='+options.rel+']').parents('li').slideUp('slow',function(){
					$(this).remove();
				});
			},{rel: $(e).attr('rel')});
		},
		editors: function()
		{
			$('.wysiwyg').each(function(){
				var name = $(this).attr('name');
				if (name) {
					if (!tinyMCE.get(name)) tinyMCE.execCommand('mceAddControl', false, name);
				}
			});
		},
		properties: function(e)
		{
			$(e).find('a.movement').click(function(){
				
				var li = $(this).parent().parent();
				li.draggable("destroy");
				
				li.appendTo($('#Pdfdesignpage_'+Admin.formbuilder.currentpage+' ol'));
				li.css({left:'',top:''});
				Admin.createDropable.initDraggablesingleli(li);
				
				var id = li.find("label:first").attr('for');
				var found = false;
				
				$('div.attrs.'+id+' input').each(function(){
					if ($(this).attr('name') == "properties["+id+"][pageno]")
					{
						$(this).val(Admin.formbuilder.currentpage);
						found = true;
					}
					
					if ($(this).attr('name') == "properties["+id+"][posx]")
					{
						$(this).val(0);
						foundx = true;
					}
					
					if ($(this).attr('name') == "properties["+id+"][posy]")
					{
						$(this).val(0);
						foundy = true;
					}
				});
				
				if (!found) {
					$('div.attrs.'+id).append("<input type='hidden' class='new_property "+name+"' name='properties["+id+"][pageno]'/>");
					$('.new_property').removeClass('new_property').val(value);
				}
				
				if (!foundx) {
					$('div.attrs.'+id).append("<input type='hidden' class='new_property "+name+"' name='properties["+id+"][posx]'/>");
					$('.new_property').removeClass('new_property').val('0');
				}
				
				if (!foundy) {
					$('div.attrs.'+id).append("<input type='hidden' class='new_property "+name+"' name='properties["+id+"][posy]'/>");
					$('.new_property').removeClass('new_property').val('0');
				}
				

				
				return false;
			});
			$(e).find('a.properties').click(function(){
					//	alert('adfadf');							
				$('#form_builder_properties').html('<span class="icon loading">Loading...</span>');
				var id = $(this).parents('label:first').attr('for');
				var isedit = $(this).parents().parents().find('label:last').attr('for');
				var li =  $(this).parent().parent();
				var qstr = '';
				$(li).find(":hidden").each(function(){
					
					if($(this).attr('name'))
					{
						qstr += '&'+$(this).attr('name')+'='+$(this).val();
					}
				});
				
				
				
				var title = $(this).attr('rel');
				
				
				
				$('#form_builder_panel li.on').removeClass('on');
				$(this).parents('li:first').addClass('on');
			
				$url = Admin.formbuilder.BASEURL+'?action=properties&type='+title+'&id='+id+'&noofpages='+$('#noofpages').val()+qstr;
				//alert($url);
				if(isedit=='edit'){$url+='&isedit=edit';}
				
				$.get($url,function(result){
					$('#form_builder_properties').html(result);
					Admin.formbuilder.attr.get(id);
					Admin.formbuilder.layout('#form_builder_properties');
					$('#form_builder_properties li *:input').keyup(function(){
																			
						
						Admin.formbuilder.attr.update(this);
					});
					
					$('#form_builder_properties li *:input:checkbox').click(function(){
																			
						
						Admin.formbuilder.attr.update(this);
					});
					
					$('#form_builder_properties li *:select').change(function(){
																			
						if($(this).attr('name'))
						{
							Admin.formbuilder.attr.update(this);
							if($(this).attr('name')=='linkedTables')
							{
								Admin.formbuilder.linkedFields(this);
							}
							
							if($(this).attr('name')=='pageno')
							{
								Admin.formbuilder.moveElement(this);
							}
						}
					});
					
					delete result;
				});
				
				return false;
			});
		},
		moveElement:function(e){
				var $element = $(e);
				
				var name = $element.attr('name');
				var id = $element.parents('li:not(.sub):first').attr('class');
				var rel = $element.attr('rel');
				var value = $element.val();
				var type = $element.attr('class');
				
				li = $('#form_builder_panel label[for='+id+']').parent();
				ol = $('#Pdfdesignpage_'+value+' ol');
				
				li.draggable("destroy");
				
				
				ol.append(li);
				
				li.css({left:'',top:''});
				
				
				$('div.attrs.'+id+' input').each(function(){
					
					
					if ($(this).attr('name') == "properties["+id+"][posx]")
					{
						$(this).val(0);
						foundx = true;
					}
					
					if ($(this).attr('name') == "properties["+id+"][posy]")
					{
						$(this).val(0);
						foundy = true;
					}
				});
				
				
				
				if (!foundx) {
					$('div.attrs.'+id).append("<input type='hidden' class='new_property "+name+"' name='properties["+id+"][posx]'/>");
					$('.new_property').removeClass('new_property').val('0');
				}
				
				if (!foundy) {
					$('div.attrs.'+id).append("<input type='hidden' class='new_property "+name+"' name='properties["+id+"][posy]'/>");
					$('.new_property').removeClass('new_property').val('0');
				}
				
				
				Admin.createDropable.initDraggablesingleli(li);
				
				
			
		},
		linkedFields:function(e)
		{
			var $element = $(e);
			var value = $element.val();
		
			$.getJSON(Admin.formbuilder.BASEURL+'?action=linkedfields&tablename='+value,function(result){
			
			$('#linkedFields').children().remove();
			for (key in result) {
				if (typeof(result[key] == 'string')) {
				$('#linkedFields').append('<option value="' + key + '">' + result[key] + '</option>');
				}
			}
			
			
			
		});
			
		},
		attr: {
			get: function(id)
			{
				$('.attrs.'+id+' input').each(function(){
					var val = $(this).val();
					var id = $(this).attr('class');
					if (val) {
						$('#form_builder_properties input[name='+id+']').val(val);
					}
				});
			},
			update: function(e)
			{
				var $element = $(e);
				
				var name = $element.attr('name');
				var id = $element.parents('li:not(.sub):first').attr('class');
				var rel = $element.attr('rel');
				var value = $element.val();
				var type = $element.attr('class');
				
				
				
				var found = false;
				
				$('div.attrs.'+id+' input').each(function(){
					if ($(this).attr('name') == "properties["+id+"]["+name+"]")
					{
						$(this).val(value);
						found = true;
					}
				});
				
				if (!found) {
					$('div.attrs.'+id).append("<input type='hidden' class='new_property "+name+"' name='properties["+id+"]["+name+"]'/>");
					$('.new_property').removeClass('new_property').val(value);
				}
				
				
				
				switch (type)
				{
					case 'dropdown':
						value = value.split(';');
					break;
					case 'checkbox':
						value = value.split(';');
					break;
					case 'checkboxmatrix':
						value = value.split(';');
					break;
					case 'radio':
						value = value.split(';');
					break;
					default: break;
				}
				
				if(type=='position')
				{
					Admin.formbuilder.align(id);
				}
				
				if(type=='dimension')
				{
					Admin.formbuilder.adjust(id);
				}
				
				if(type=='labeldimension')
				{
					Admin.formbuilder.label.labelwidth($element);
				}
				
				if(type=='labeldisable')
				{
					
					Admin.formbuilder.label.labeldisable($element);
				}
				
				if(type=='labelfont')
				{
					Admin.formbuilder.label.labelfont($element);
				}
				
				if(type=='labelfontsize')
				{
					Admin.formbuilder.label.labelfontsize($element);
				}
				
				if(type=='linethickness')
				{
					Admin.formbuilder.line.linethickness($element);
				}
				
				if(type=='linelength')
				{
					Admin.formbuilder.line.linelength($element);
				}
				
				if(type=='linecolor')
				{
					Admin.formbuilder.line.linecolor($element);
				}
				
				if (rel && value) {
					if (!$.isArray(value)) {
						var block = $(rel).not(':input').length;
						
						if (block == 0) $(rel).val(value);
						else $(rel).html(value);
					} else {
						//its an array, oh dear!
						switch (type)
						{
							case 'dropdown':
								var newc = '';
								for (i in value) newc += '<option>'+value[i]+'</option>';
								$(rel).html(newc);
								break;
							case 'radio':
								var newc = '';
								for (i in value) newc += '<input type="radio" name="temp['+name+'][]"> '+value[i]+'<br/>';
								$(rel).html(newc);
								break;
							case 'checkbox':
								var newc = '';
								
								for (i in value)
								{
										newc += '<input type="checkbox" name="temp['+name+'][]">'+value[i]+'<br/>';;
								}
								$(rel).html(newc);
								break;
							case 'checkboxmatrix':
							
								var newc = '';
								colval = $('.attrs.'+id+' input[name="properties['+id+'][columns]"]').val();
								if(!colval) break;
								colval = colval.split(';');
								
								rowval = $('.attrs.'+id+' input[name="properties['+id+'][values]"]').val();
								rowval = rowval.split(';');
								
								for (i in rowval)
								{
									newc+='<span style="width:200px;margin-left:10px;position:absolute">'+rowval[i]+'</span>';
									colhead='';
									for(j=0;j<colval.length;j++)
									{
										colhead +='<span style="width:200px;margin-left:60px">'+colval[j]+'</span>';
										newc += '<span style="width:200px;margin-left:50px"><input type="checkbox" name="temp['+name+'][]"></span>';
									}
									newc +='<br/>';
								}
								$(rel).html(colhead+'<br/>'+newc);
								break;
							default: break;
						}
					}
				}
			}
		},
		
		align:function(id){
			
				var left =  $('.attrs.'+id+' input[name="properties['+id+'][posx]"]').val();
				
				if(!left) left = 0;
				
				var top =  $('.attrs.'+id+' input[name="properties['+id+'][posy]"]').val();
				
				if(!top) top = 0;
				
				var con = $('#form_builder_panel label[for='+id+']').parent().parent();
				
				var li = $('#form_builder_panel label[for='+id+']').parent();
				
				var maxtop = con.height() - li.height();
				
				var maxleft = con.width() - li.width();
				
				if(maxtop>top)
				{
					li.css({top:top+'px'});
				}
				
				if(maxleft>left)
				{
					li.css({left:left+'px'});
				}
				
				
		},
		adjust:function(id){
			
			
			
			var height = $('.attrs.'+id+' input[name="properties['+id+'][dimheight]"]').val();
			
			//if(!height) height = 20;
			var width = $('.attrs.'+id+' input[name="properties['+id+'][dimwidth]"]').val();
			//if(!width) width = 450;
			
			
			
			var con = $('#form_builder_panel label[for='+id+']').parent().parent();
			
				
			var li = $('#form_builder_panel label[for='+id+']').parent();
			
			var maxheight = con.height();
				
			var maxwidth = con.width();
			
			
			
			if(height && maxheight>(height*Admin.formbuilder.onemm))
			{
				li.css({height:(height*Admin.formbuilder.onemm)+'px'});
			}
			
			if(width && maxwidth>(width*Admin.formbuilder.onemm))
			{
				li.css({width:(width*Admin.formbuilder.onemm)+'px'});
			}
			
			
		},
		/*preview: function()
		{
			$('textarea.wysiwyg').each(function(){
				var name = $(this).attr('name');
				if (name) {
					var contents = tinyMCE.get(name).getContent();
				}
				$(this).val(contents);
			});
			
			var data = $('#form_builder_panel form').serialize();
			
			$.post(Admin.formbuilder.PREVIEWURL,data,function(result){
				$('#form_builder_preview').html(result);
				Admin.formbuilder.dialog('form_builder_preview');
			});
		},*/
		dialog: function(rel,link)
		{
			var external = $("#"+rel).hasClass('external');
			if (external) {
				
				$("#"+rel).show().html("<iframe src='"+link+"' name='"+rel+"' width='100%' height='100%' frameborder='0' border='0'></iframe>").dialog('open');
				return;
			}
			if (link) {
				if (link.indexOf('http') >= 0) {
					$("#"+rel).html("");
					$.get(link,function(result){
						$("#"+rel).html(result).show().dialog('open');
						Admin.formbuilder.layout("#"+rel);
						delete result;
					});
					return;
				}
			}
			$("#"+rel).show().dialog('open');
		},
		confirm: function(msg,callback,options)
		{
			var id = 'confirm_'+Math.ceil(100*Math.random());
			$('body').append('<div id="'+id+'"><p></p></div>');
			$('#'+id+' p').html(msg).dialog({
				modal: true,
				overlay: { 
					opacity: 0.5, 
					background: "black" 
				},
				title: 'Confirm',
				buttons: { 
					"Confirm": function() { 
						if (callback) callback(options);
						$(this).dialog("close");
						$(this).parents('div:first').remove();
					}, 
					"Cancel": function() {
						$(this).dialog("close");
						$(this).parents('div:first').remove();
					} 
				} 
			});
		},
		preview:function(e){
		
			$(e).parent().addClass('loading');
			var e = $(e);
			
			$.ajax({
				  type: 'POST',
				  url: appbase+'pdfdesigner/preview',
				  data: $('#frmpdfbuilder').serialize(),
				  success: function(data,status){
						$(e).parent().removeClass('loading');
						window.open(data.previewpath, 'ISPC', "height=500,width=500");
						
				  },
				  dataType: 'json'
				});

			
		},
		pagesizeChange:function(){
			
			
			Admin.formbuilder.dimensions = Array();
			for($i=1;$i<=Admin.formbuilder.pages;$i++)
			{
				if($("#altdimension_height").val().length>0)
				{
					//height = (parseInt($("#altdimension_height").val()*Admin.formbuilder.onemm)) - Admin.formbuilder.footerheight - Admin.formbuilder.headerheight;
					height = ($("#altdimension_height").val()-Admin.formbuilder.footerheight - Admin.formbuilder.headerheight)*Admin.formbuilder.onemm;
					if(height<0){height = 0;}
					$('#Pdfdesignpage_'+$i).css({height:(parseInt(height))+'px'});
					$('#Pdfdesignpage_'+$i+' ol').css({height:height+'px'});
				}
				if($("#altdimension_width").val().length>0)
				{
					$('#Pdfdesignpage_'+$i).css({width:(parseInt($("#altdimension_width").val()*Admin.formbuilder.onemm))+'px'});
					//$('#Pdfdesignpage_'+$i+' ol').css({width:(parseInt($("#altdimension_width").val()))+'mm'});
				}
				
				
			}
			
			
			Admin.formbuilder.dimensions[0] =($("#altdimension_height").val()-Admin.formbuilder.footerheight - Admin.formbuilder.headerheight)*Admin.formbuilder.onemm;
			
			Admin.formbuilder.dimensions[1] =$("#altdimension_width").val()*Admin.formbuilder.onemm;
			
		}
		
	};
	
	
	Admin.formbuilder.label = {
		
		labelwidth:function(e){
			
			var $element = $(e);
				
			var name = $element.attr('name');
			var id = $element.parents('li:not(.sub):first').attr('class');
			var rel = $element.attr('rel');
			var value = $element.val();
			var type = $element.attr('class');
			
			if(value.length>0)
			{
			 	$('#form_builder_panel label[for='+id+']').css({width:(value*Admin.formbuilder.onemm)+'px'});
			}
			 
			 
		
		},
		labeldisable:function(e){
			
			var $element = $(e);
				
			var name = $element.attr('name');
			var id = $element.parents('li:not(.sub):first').attr('class');
			var rel = $element.attr('rel');
			var value = $element.val();
			var type = $element.attr('class');
			
			if($element.is(':checked')){
				$('#form_builder_panel label[for='+id+']').css({display:'none'});
				$('.attrs.'+id+' input[name="properties['+id+'][labelhide]"]').val('1');
			}else{
				
				$('#form_builder_panel label[for='+id+']').css({display:''});
				$('.attrs.'+id+' input[name="properties['+id+'][labelhide]"]').val('');
			}
		},
		labelfont:function(e){
			
			var $element = $(e);
				
			var name = $element.attr('name');
			var id = $element.parents('li:not(.sub):first').attr('class');
			var rel = $element.attr('rel');
			var value = $element.val();
			var type = $element.attr('class');
			
			if(value.length>0)
			{
				$('#form_builder_panel label[for='+id+'] a').css({'font-family':value});
			}
		},
		labelfontsize:function(e){
			
			var $element = $(e);
				
			var name = $element.attr('name');
			var id = $element.parents('li:not(.sub):first').attr('class');
			var rel = $element.attr('rel');
			var value = $element.val();
			var type = $element.attr('class');
			
			if(value.length>0)
			{
				$('#form_builder_panel label[for='+id+'] a').css({'font-size':value+'mm'});
			}
			
		}
		
	};
	
	Admin.formbuilder.line = {
		
		linethickness:function(e){
				var $element = $(e);
				
				var name = $element.attr('name');
				var id = $element.parents('li:not(.sub):first').attr('class');
				var rel = $element.attr('rel');
				var value = $element.val();
				var type = $element.attr('class');
				
				var hr = $('#form_builder_panel label[for='+id+']').parent().find('hr');
				if(value.length>0)
				{
					hr.css({'height':value*Admin.formbuilder.onemm+'px'});
				}
				
			
		},
		linelength:function(e){
			
			var $element = $(e);
				
				var name = $element.attr('name');
				var id = $element.parents('li:not(.sub):first').attr('class');
				var rel = $element.attr('rel');
				var value = $element.val();
				var type = $element.attr('class');
				
				var hr = $('#form_builder_panel label[for='+id+']').parent().find('hr');
				if(value.length>0)
				{
					hr.css({'width':value*Admin.formbuilder.onemm+'px'});
				}
			
			
		},
		linecolor:function(e){
			
			var $element = $(e);
				
				var name = $element.attr('name');
				var id = $element.parents('li:not(.sub):first').attr('class');
				var rel = $element.attr('rel');
				var value = $element.val();
				var type = $element.attr('class');
				
				var hr = $('#form_builder_panel label[for='+id+']').parent().find('hr');
				if(value.length>0)
				{
					hr.css({'background-color':'#'+value});
				}
		}
		
	
	};
	
	Admin.formbuilder.tinymceadjust={
	
		onchange:function(inst){
			
			var eh = $(inst.contentAreaContainer.children[0]).height()-10;
			ibody = $(inst.contentAreaContainer.children[0]).contents().find('html body');
			ibody.append('<div id="temp">'+ibody.html()+'</div>');
			var conh = ibody.find('#temp').height();
			
			//alert(conh);
			ibody.find('#temp').remove();
			if(conh>eh)
			{
				
				alert('Contet Height exceeded');
			}
			
		},
		
		adjustHeight:function(name,el){
			
			tinyMCE.get(name).contentAreaContainer.children[0].style.height = ($(el).val()*Admin.formbuilder.onemm)+'px';	
			if(name=='header')
			{
				Admin.formbuilder.headerheight = ($(el).val()*Admin.formbuilder.onemm);
			}
			if(name=='footer')
			{
				Admin.formbuilder.footerheight = ($(el).val()*Admin.formbuilder.onemm);
			}
			
			for($i=1;$i<=Admin.formbuilder.pages;$i++)
			{
				
					height = (Admin.formbuilder.dimensions[0]-Admin.formbuilder.footerheight - Admin.formbuilder.headerheight)*Admin.formbuilder.onemm;
					if(height<0){height = 0;}
					$('#Pdfdesignpage_'+$i).css({height:(parseInt(height))+'px'});
					$('#Pdfdesignpage_'+$i+' ol').css({height:height+'px'});
			}
		}
	
	};
	
	var tinymceOnChangeHandler = function(inst){
		
		Admin.formbuilder.tinymceadjust.onchange(inst);
	}
	
	
	function randomString(lengt)
	{
		var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
		var string_length = lengt;
		var randomstring = '';
		for (var i=0; i<string_length; i++) {
			var rnum = Math.floor(Math.random() * chars.length);
			randomstring += chars.substring(rnum,rnum+1);
		}
		return randomstring;
	}