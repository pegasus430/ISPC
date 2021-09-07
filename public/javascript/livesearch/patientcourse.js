
function insertsession(rcnt,pid,totalcnt)
{
	var ctp = "";
	var ctt = "";

	for(i=1;i<=totalcnt;i++)
	{
		//escape is deprecated and kills umlauts, used encodeURIComponent instead
		ctp +='&ctp[]='+ encodeURIComponent(document.getElementById('course_type'+i).value);
		ctt +='&ctt[]='+ encodeURIComponent(document.getElementById('course_title'+i).value);
	}
	ajaxCallserver({
		url:'patient/coursesession?pid='+pid+ctp+ctt
		});
}

var callback = function(stackframes) {
	  var stringifiedStack = stackframes.map(function(sf) {
	    return sf.toString();
	  }).join('\n');
	  console.log(stringifiedStack);
	};

var errback = function(err) { console.log(err.message); };



function addNewShortcutActionRow(_this) {
	//add new row only if the last one is allteady selected
	
	var _pid = idpd; //idpd is assigned in layout
	
	var _ownerDiv = $(_this).parents("div.RightContent01");
	var _rowsCounter = _ownerDiv.find('div.shortcutRow').length; 
	// this _rowsCounter used to increment the id's .... 
	// remove this _rowsCounter when you change fn changeinput() to use normal post and not a string concat
	
	var _lastRow = _ownerDiv.find('div.shortcutRow :last');
	
	if ($("selec.shortcutRow", _lastRow).val() != '') {
		//last row has the shortcut selected
		//count the rows and append a new one
		
		var _newCnt = _rowsCounter+1;
		
		$("<div class='ListOuter01 shortcutRow' id='listcoursesession_content_div" + _newCnt + "'></div>");
		$("<div class='LeftList01 left'></div>");
		$("<div class='RightList01 left' id='listcoursesession_course_title"+ _newCnt + "'></div>");
		
		var outerdiv = document.createElement('div');
//		outerdiv.className = "ListOuter01 shortcutRow";
//		outerdiv.id = "listcoursesession_content_div" + newcnt;

		var div1 = document.createElement('div');
//		div1.className="LeftList01 left";

		var div2 = document.createElement('div');
//		div2.className="RightList01 left";
//		div2.id = "listcoursesession_course_title"+newcnt;
		
		
		/* cloned_selectShortcut replaces ip1 */
		var cloned_selectShortcut= $(_ownerDiv).find('select.selectShortcut:first').clone(false).off();
		$(cloned_selectShortcut)
		.attr('id', "course_type"+newcnt)
		.css('display','block')
		.removeAttr('onchange')
//		.chosen("destroy") 
		.change(function(){ 
			keyupdelay(this, newcnt); 
			selectdignosis(pid,newcnt+1);
		})
		.find(':selected').removeAttr('selected').end();
		
		
		var lbl = document.createElement('label');

		var _textarea = $("<textarea name='course_title[]' class='defaultTextarea' id='course_title"+ _newCnt + "'></textarea>");
		_textarea.onblur = function(){
			insertsession(newcnt,pid,newcnt);
		};
		
		var ip2 = document.createElement('textarea');
		ip2.name = "course_title[]";
		ip2.id = "course_title"+newcnt;
		ip2.className = 'defaultTextarea';
		/*
		ip2.onfocus=function(){
			selectdignosis(pid,newcnt+1);
		};
		*/
		
		ip2.onblur = function(){
			insertsession(newcnt, pid, newcnt);
		};
		ip2.disabled = true;
		ip2.placeholder = translate("Please select action first");
		
		var ip1 = document.createElement('select');
		ip1.className = 'selectShortcut';

		lbl.appendChild(ip2);
//		div1.appendChild(ip1);
		div1.appendChild($(cloned_selectShortcut).get(0));
		div2.appendChild(lbl);

		var clr = document.createElement('div');
		clr.className = "ClrBoth";

		outerdiv.appendChild(div1);
		outerdiv.appendChild(div2);
		outerdiv.appendChild(clr);

		$('#cfileds').append($(outerdiv));
		$('textarea').elastic();

//		$(outerdiv).find('select.selectShortcut')
//		.change(function(){
//			keyupdelay(this, newcnt);
//		});
		
		chosenizeSelectShortcut($(outerdiv).find('select.selectShortcut'));
		
	}
	
	
}

function selectdignosis(pid,newcnt)
{
	
	var createrow = 0;
	if(newcnt==2)
	{
		createrow = '1';
	}
	else
	{

//		alert(document.getElementById('medications-'+(newcnt-1)).value.length);

		if(document.getElementById('course_title'+(newcnt-2))){ //avoid error when element is not defined!!
			if(document.getElementById('course_title'+(newcnt-2)).value.length>0) //if it has more than 1 character create new row
			{
				createrow = '1';
			}
		}


		if(document.getElementById('medications-'+(newcnt-2))){
			if(document.getElementById('medications-'+(newcnt-2)).value.length>0)
			{
				createrow = '1';
			}
		}

		if(document.getElementById('symptom-'+(newcnt-2))){
			if(document.getElementById('symptom-'+(newcnt-2)).value.length>0)
			{
				createrow = '1';
			}
		}
	}

	if(createrow==1)
	{
		if($('#course_type'+newcnt).length == "0"){
			var outerdiv = document.createElement('div');
			outerdiv.className = "ListOuter01 shortcutRow";
			outerdiv.id = "listcoursesession_content_div" + newcnt;

			var div1 = document.createElement('div');
			div1.className="LeftList01 left";

			var div2 = document.createElement('div');
			div2.className="RightList01 left";
			div2.id = "listcoursesession_course_title"+newcnt;
			
/*
			var ip1 = document.createElement('input');
			ip1.name = "course_type[]";
			ip1.id = "course_type"+newcnt;
			ip1.type = "text";
			
//			 ip1.onkeyup= function(){upper(newcnt);chkmask(this.value,newcnt);changeinput('this.value',newcnt);};
//changed when added the 2 letter shortcuts
//             ip1.setAttribute('onkeyup', 'upper('+newcnt+');chkmask(this.value,'+newcnt+');changeinput(this.value,'+newcnt+');');
//			ip1.setAttribute('onkeyup', "javascript:keyupdelay(this,"+newcnt+");");
//
//			 ip1.setAttribute('ochange', 'changeinput(this.value,'+newcnt+');');
			ip1.onkeyup = function(){
				keyupdelay(this,newcnt);
			};
//			ip1.onchange = function(){
//				changeinput(this.value,newcnt);
//			};
//			ip1.onblur = function(){
//				changeinput(this.value,newcnt);
//			};
//		 ip1.setAttribute('onkeyup', 'changeinput(this.value,'+newcnt+');');
*/
			/* cloned_selectShortcut replaces ip1 */
			var cloned_selectShortcut= $('#cfileds').find('select.selectShortcut:first').clone(false).off();
			$(cloned_selectShortcut)
			.attr('id', "course_type"+newcnt)
			.css('display','block')
			.removeAttr('onchange')
//			.chosen("destroy") 
			.change(function(){ 
				keyupdelay(this, newcnt); 
				selectdignosis(pid,newcnt+1);
			})
			.find(':selected').removeAttr('selected').end();
			
			
			var lbl = document.createElement('label');

			var ip2 = document.createElement('textarea');
			ip2.name = "course_title[]";
			ip2.id = "course_title"+newcnt;
			ip2.className = 'defaultTextarea';
			/*
			ip2.onfocus=function(){
				selectdignosis(pid,newcnt+1);
			};
			*/
			
			ip2.onblur = function(){
				insertsession(newcnt,pid,newcnt);
			};
			ip2.disabled = true;
			ip2.placeholder = translate("Please select action first");
			
			var ip1 = document.createElement('select');
			ip1.className = 'selectShortcut';

			lbl.appendChild(ip2);
//			div1.appendChild(ip1);
			div1.appendChild($(cloned_selectShortcut).get(0));
			div2.appendChild(lbl);

			var clr = document.createElement('div');
			clr.className = "ClrBoth";

			outerdiv.appendChild(div1);
			outerdiv.appendChild(div2);
			outerdiv.appendChild(clr);

			$('#cfileds').append($(outerdiv));
			$('textarea').elastic();

//			$(outerdiv).find('select.selectShortcut')
//			.change(function(){
//				keyupdelay(this, newcnt);
//			});
			
			chosenizeSelectShortcut($(outerdiv).find('select.selectShortcut'));
			
			
			
			
//		document.getElementById('course_title'+(newcnt-1)).onfocus="";
		}

	}
//alert(newcnt);
//document.getElementById('course_title'+(cnt-1)).onblur="";

}

/*
 * added on 07.02.2018
 */
var chosenSelectShortcutTimeoutId; 
var chosenSelectShortcutTimeoutdelay = 2000;

function clearTimeoutSelectShortcut(){
	clearTimeout(chosenSelectShortcutTimeoutId);
}

function chosenizeSelectShortcut(_this) {
	
	if (typeof _this =='undefined' || _this == null ) {
		return;
	}
	
	$(_this)
	.chosen({
		
        placeholder_text_single     : ' ',
        inherit_select_classes      : true,
        allow_single_deselect       : false,
        display_selected_options    : false,
        width   : '50px',
        //multiple: false,
        "search_contains": false,
        no_results_text: translate('noresultfound'),
        
        
        "data-choice_vsprintf"  : '<span class=\"choice\" style=\"%3%\">%1%</style>' ,
        "data-row_vsprintf"     : '<span class=\"colLeft\" style=\"%3%\">%1%</span><span class=\"colRight\" style=\"%3%\">%2%</span>' ,
        "data-search_vsprintf"  : '%1% %2%',
        
    })
    .on("chosen:update_results_content", function(evt, data) {
    	var _searchText = $(data.chosen.search_field).val().toUpperCase();
		if (_searchText != '') {
			if ($('option[value="' +_searchText+ '"]', this).length == 1) {

				clearTimeoutSelectShortcut();
				
		    	var _that = this;
		    	chosenSelectShortcutTimeoutId = setTimeout(function(){
		    		
		    		clearTimeoutSelectShortcut();
		    		
		    		$(data.chosen.search_field).val('');
		    		
				    $(_that).val(_searchText)
				    .change()
				    .trigger("chosen:updated")
				    .trigger("chosen:close")
				    ;
				    
				    //why you no close? mouse_on_container?
				    $($(_that).data("chosen").container[0])
				    .removeClass('chosen-with-drop')
				    .removeClass('chosen-container-active');
				    				    
				} , chosenSelectShortcutTimeoutdelay);
		    }	
		}
	 })
	 .on("chosen:container_mousedown_open", function (evt, data) {
		 clearTimeoutSelectShortcut();
	 });
	
}


