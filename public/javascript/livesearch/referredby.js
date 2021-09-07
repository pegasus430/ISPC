var r_loading = function(){

var dlist = '<div class="DropDwnTable" style="margin-top:5px;margin-left:35px;"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble"><tr class="bluerow"><td><img src="images/loading.gif"></td></tr></table></div>';
 document.getElementById('refdropdown').style.display = "";
 document.getElementById('refdropdown').innerHTML=dlist;
	 
}

var refs;
var refdropdiv = function(params){

var dlist = '<div class="DropDwnTable"><table border="0" align="left" cellpadding="0" cellspacing="0" class="Dropdwntble">';
	refs = params.refs;
	if(refs.length<1)
	{
	    dlist+='<tr><td colspan="2" class="BlueBox">No Results Found</td></tr>';
	}
	
	for(i=0;i<params.refs.length;i++)
	{
		dlist+='<tr class="bluerow" onclick="selectRef('+i+')"><td class="first">'+params.refs[i].referred_name +'</td><td>Select</td></tr>';
	}
	 dlist+= "</table></div>";
	 
	 document.getElementById('refdropdown').style.display = "";
	 document.getElementById('refdropdown').innerHTML=dlist;
	// document.getElementById('msg').innerHTML = "If doctor is not found in list. please add new doctor from here";
	 
}

function selectRef(i)
{
	document.getElementById('referred_by').value =refs[i].referred_name;
	document.getElementById('refdropdown').style.display = "none";
	document.getElementById('hidd_referred_by').value =refs[i].id;
}
