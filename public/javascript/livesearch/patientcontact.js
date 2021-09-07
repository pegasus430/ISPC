
function removeFromTemp(cid,rid)
{
  $("#PatieMasAdd_tr"+rid).remove();
   ajaxCallserver({url:'contactpersonmaster/delcontactfromtemp?cid='+cid});
}

var cntr=1;
function getContactPersonData(jdata)
{
   if(jdata)
   {
     
	 var row1 = document.createElement('tr');
	 row1.id = "PatieMasAdd_tr"+cntr;
     
	 var cell = document.createElement('td');
	 cell.id = "PatieMasAdd_tr"+cntr+"_td1";
	 cell.align = "left";
	 
	 var cell2 = document.createElement('td');
	 cell2.id = "PatieMasAdd_tr"+cntr+"_td2";
	 cell2.align = "left";
	 
	 var cell3 = document.createElement('td');
	 cell3.id = "PatieMasAdd_tr"+cntr+"_td3";
	 cell3.align = "left";
	 
	 var cell4 = document.createElement('td');
	 cell4.id = "PatieMasAdd_tr"+cntr+"_td4";
	 cell4.align = "left";
	 
	 var cell5 = document.createElement('td');
	 cell5.id = "PatieMasAdd_tr"+cntr+"_td5";
	 cell5.align = "left";
	 
	 
	 var ipelement = document.createElement('input');
	 ipelement.name = "hidd_cid[]";
	 ipelement.type = "hidden";
	 ipelement.value = jdata.id;
	 
//	 cell.innerHTML= jdata.cnt_first_name+",&nbsp;"+jdata.cnt_last_name;
	 cell.innerHTML= jdata.cnt_last_name+",&nbsp;"+jdata.cnt_first_name;
	 cell2.innerHTML= jdata.cnt_phone;
	 cell3.innerHTML= jdata.cnt_mobile;
	 cell4.innerHTML= jdata.cnt_street1;
	 
	 cell5.innerHTML = '<a href="javascript:void(0)" onclick="removeFromTemp('+jdata.id+','+cntr+')"><img src="images/action_delete.png" /></a>';
	
	 row1.appendChild(cell);
	 row1.appendChild(cell2);
	 cell3.appendChild(ipelement);
	 row1.appendChild(cell3);
	 row1.appendChild(cell4);
	 row1.appendChild(cell5);
	
	 $('#PatieContactMasAdd_table').append(row1);
    
	cntr++;
   }
}
