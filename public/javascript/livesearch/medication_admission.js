var cntr = jsbmedcount;
  function selectbmedication()
{
	var row = document.createElement('tr');
	row.id = "tab"+cntr;
	var cell1 = document.createElement('td');
	var cell2 = document.createElement('td');
	var cell3 = document.createElement('td');
	var cell4 = document.createElement('td');
	
	var ip1 = document.createElement('input');
	ip1.name = "add[medication]["+cntr+"]";
	ip1.id = "medication"+cntr;
	ip1.type = "text";
	// ip1.size = "45";
	ip1.className = "livesearchmedinp med";
	//ip1.tempid = cn
	// ip1.onkeyup=function(){medicationDropDown(this.tempid);};
	ip1.value = "";
	 
	 
	 
	var ipelement = document.createElement('input');
	ipelement.name = "add[hidd_medication]["+cntr+"]";
	ipelement.id = "hidd_medication"+cntr;
	ipelement.type = "hidden";
	 
	var divs = document.createElement('div');
	divs.id = "medidropdown"+cntr;
	divs.style.position = "absolute";
	divs.className = "livesearchdropdown";
	
	var ip2 = document.createElement('input');
	ip2.name = "add[dosage]["+cntr+"]";
	ip2.id = "dosage"+cntr;
	ip2.type = "text";
	ip2.className = "dosage";
	ip2.value = "";
	 
	var ip3 = document.createElement('input');
	ip3.name = "add[comments]["+cntr+"]";
	ip3.id = "comments"+cntr;
	ip3.type = "text";
	ip3.className = "med";
	ip3.value = "";
	 
	 
	 
	 
	cell4.innerHTML = '<a href="javascript:void(0)" onclick="removeElem(\'#tab'+cntr+'\')"><img src="images/action_delete.png" border="0" /></a>';
	
	cell1.appendChild(ip1);
	cell2.appendChild(ip2);
	cell3.appendChild(ip3);
	cell1.appendChild(ipelement);
	cell1.appendChild(divs);
	 
	row.appendChild(cell1);
	row.appendChild(cell2);
	row.appendChild(cell3);
	row.appendChild(cell4);
	
	$('#medicationbgrid_contenttable').append(row);
    
	
	$('#medication'+cntr).bind('keyup',function(e){
		livemedicationsearchkeup(e,$(this))
		});
	medblur['medication'+cntr] = true;
	
	$('#medication'+cntr).bind('blur',function(){
	
		var t = setTimeout(function(){
			
			$('#medidropdown'+cntr).hide();
			
		},5);
											
	});
	
	$("#medidropdown"+cntr).bind('mousedown',function(){
	
		
		var t = setTimeout(function(){
			$("#medidropdown"+cntr).show();
			$('#medication'+cntr).focus();
		},10);
		
	});
	
	cntr++;	
}