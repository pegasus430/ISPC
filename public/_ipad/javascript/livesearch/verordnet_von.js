var s_loading = function(){

var dlist = '<div class="DropDwnTable" style="margin-top:5px;margin-left:35px; height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntbles"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
 document.getElementById('sapvverordnungdropdown').style.display = "";
 document.getElementById('sapvverordnungdropdown').innerHTML=dlist;
	 
}

function verdropDownchange()
{
  ajaxCallserver({url:'familydoctor/getsapvverordnung',
				callLoading:s_loading,
				 method:'POST',
				 data:{ltr:$("#verordnet_von").val()}
				});
} 


var doctors;
var verordiv = function(params){

var dlist = '<div class="DropDwnTable" id="verdropdowninner" style="height:175px; overflow:auto;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntbles">';
	doctors = params.doctors;
	
	//alert(dv);
	if(doctors.length<1)
	{
		 $('#sapvverordnungdropdown').hide('slow');
		
	}
	
	if(doctors.length>0){dlist+='<tr class="bluerow"><td class="first"><strong>'+translate('lastname')+'</strong></td><td class="first"><strong>'+translate('firstname')+'</strong></td><td class="first"><strong>'+translate('city')+'</strong></td><td>&nbsp;</td></tr>';}
	else
	{
	 dlist+='<tr><td colspan="2" class="BlueBox">'+translate('noresultsfoundpleaseaddnewfamilydoctor')+'</td></tr>';
	 document.getElementById('hidd_verordnet_von').value = "";
	}
	
	for(i=0;i<params.doctors.length;i++)
	{
		dlist+='<tr class="bluerow" onmousedown="selectverDoctor('+i+')"><td class="first">'+params.doctors[i].last_name +'</td><td class="first">'+params.doctors[i].first_name +'</td><td class="first">'+params.doctors[i].city +'</td><td>'+translate('select')+'</td></tr>';
	}
	 dlist+= "</table></div>";
	 
	
	if(fdocblur==false)
	{
		document.getElementById('sapvverordnungdropdown').style.display = "";
	}
	
	 document.getElementById('sapvverordnungdropdown').innerHTML=dlist;
	// document.getElementById('msg').innerHTML = "If doctor is not found in list. please add new doctor from here";
	 
}

function selectverDoctor(i)
{
	document.getElementById('verordnet_von').value = doctors[i].last_name+" , "+doctors[i].first_name;
	document.getElementById('sapvverordnungdropdown').style.display = "none";
	document.getElementById('hidd_verordnet_von').value = doctors[i].id;
	
	var t = setTimeout(function(){$("#verordnet_von").trigger('blur');},3);
}

var liveverorsearchkeup = function( e,ele ){
		//alert($(ele).attr('id'));
		id = $(ele).attr('id');
		fdocblur = false;
		//id = id.substring(t.length);
		
		if( e.keyCode == 40 ) {
			diagsearch = false;
			if( $("#sapvverordnungdropdown").find('.focused' ).length < 1 ) {
				//alert($("#diagnodropdown"+id+'tr:first'));
				//$("#diagnodropdown"+id+' tr:first').removeClass('bluerow');
				
				$("#sapvverordnungdropdown tr:first").addClass('focused');
				//alert($("#diagnodropdown"+dv+'tr:first'));
				//alert('$("#diagnodropdown"+dv+'tr:first')');
				//$('.list_name p:first' ).addClass( 'highlight' );
				
			} else {
				if($("#sapvverordnungdropdown").find( '.focused' ).next().length < 1 ) {
					$("#sapvverordnungdropdown").find( '.focused' ).removeClass( 'focused' );
					$("#sapvverordnungdropdown tr:first").addClass('focused');
				} else {
					var itsNext = $("#sapvverordnungdropdown").find('.focused' ).removeClass( 'focused' ).next().addClass( 'focused' );
					if( itsNext.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 40 });
					} else {
						
						
						
						var activeItem = $("#sapvverordnungdropdown").find( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#verdropdowninner');
							
						  upperBound = container.scrollTop();
						  lowerBound = upperBound + 175 - 35;
						  if (offsetTop < upperBound) {
							container.scrollTop(offsetTop);
						  } else if (offsetTop > lowerBound) {
							container.scrollTop(offsetTop - 175 + 35);
						  }
						
					}
				}
				
				
			}
			//e.stopPropagation();
			return;
		}
		if( e.keyCode == 38 ) {
			
			diagsearch = false;
			if( $("#sapvverordnungdropdown").find('.focused' ).length < 1 ) {
				$("#sapvverordnungdropdown tr:last").addClass('focused');
			} else {
				if( $("#sapvverordnungdropdown").find( '.focused' ).prev().length < 1 ) {
					$("#sapvverordnungdropdown").find( '.focused' ).removeClass( 'focused' );
					$("#sapvverordnungdropdown tr:last").addClass('focused');
				} else {
					var itsPrev = $("#sapvverordnungdropdown").find( '.focused' ).removeClass( 'focused' ).prev().addClass( 'focused' );
					if( itsPrev.is( ':hidden' ) ) {
						$( this ).trigger( { type : 'keyup', keyCode : 38 });
					} else {
						var activeItem = $("#sapvverordnungdropdown").find( '.focused' )[0];
						offsetTop = activeItem.offsetTop;
						var container = $('#verdropdowninner');
							
						  upperBound = container.scrollTop();
						  lowerBound = upperBound + 175 - 35;
						  if (offsetTop < upperBound) {
							container.scrollTop(offsetTop);
						  } else if (offsetTop > lowerBound) {
							container.scrollTop(offsetTop - 175 + 35);
						  }
					}
				}
			}
			
			e.preventDefault();
			return;
		}
		
		if( e.keyCode == 13 ) {
			if(!diagsearch)
			{
				$("#sapvverordnungdropdown").find( '.focused' ).trigger( 'mousedown' );
				
				
				diagsearch = true;
			}
			e.preventDefault();
			//alert(e.target);
			return false;
		}
		
		if(e.keyCode == 9)
		{
			$("#sapvverordnungdropdown"+id).hide();
			return;	
		}
		
		setTimeout(function(){verdropDownchange()},400);
		
	}


