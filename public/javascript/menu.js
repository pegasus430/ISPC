function initMenu() {

 $('#menu ul').hide();

 // $('#menu ul:first').show();
// alert(getCookie('openmenu'));
  $('#'+getCookie('openmenu')).show();
	$('#li_'+getCookie('openmenu')).attr('class','open');//TODO-3958 Ancuta 15.03.2021 
	$('#'+getCookie('selectedmenu')).attr('class','selected');


  $('#menu li a.menutitle').click(
    function() {
	//alert($(this).attr('id'));
    	//ISPC-2782 CRISTI.C 19.01.2021
      $(this).parent().toggleClass('open').siblings().removeClass('open');      
      //
      var checkElement = $(this).next();
	  
      if((checkElement.is('ul')) && (checkElement.is(':visible'))) {
		  cookieSet('openmenu',$(this).attr('id')+"_menu"); 
		  
		 
		  
        return false;
        }else if((checkElement.is('ul')) && (!checkElement.is(':visible'))) {
		
		  cookieSet('openmenu',$(this).attr('id')+"_menu"); 
          $('#menu ul:visible').slideUp('normal');
          checkElement.slideDown('normal');
		  
          return false;
        }else{
			 cookieSet('openmenu',$(this).attr('id')+"_menu"); 
			 $path = $(this).attr('rel');
		 
		  if($path!=undefined)
		  {
		  	window.location.href = appbase+$path 
		  }
			
		}
      }
    );
  }

/*var getCookie = function(Name){ 
		var re=new RegExp(Name+"=[^;]", "i") //construct RE to search for target name/value pair
		if (document.cookie.match(re))
		{//if cookie found
			alert('aa');
			alert(document.cookie.match(re));
			return document.cookie.match(re)[0].split("=")[1] //return its value
		}
		return null
	}*/
	
function getCookie(c_name)
{
	if (document.cookie.length>0)
  	{
		c_start=document.cookie.indexOf(c_name + "=");
		if (c_start!=-1)
   		{
			c_start=c_start + c_name.length+1;
			c_end=document.cookie.indexOf(";",c_start);
			if (c_end==-1) c_end=document.cookie.length;
			return unescape(document.cookie.substring(c_start,c_end));
		}
	}
	
	return null;
	
}

var	setCookie = function(name, value){
		document.cookie = name + "=" + value + "; path=/"
	}

function cookieSet(cookieName,cookieText) {
if (document.cookie != document.cookie) {
index = document.cookie.indexOf(cookieName);
} else {
index = -1;
}
if (index == -1) {
document.cookie=cookieName+"="+cookieText + "; path=/";
}
} 
