var langurl = appbase + 'javascript/data_tables/de_language.json';
var delurl = appbase + 'news/deletenews';
var ajaxurl = appbase + 'news/newslist';

var left_menu_list; // this will be the datatable object

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	var confdel = translate('confirmdeleterecord');
	var conftitle = translate('confirmdeletetitle');
	window.left_menu_list = drawDatatable();
 
	// DELETE
	$(".delete").live('click', function() {
		var checklocs = document.getElementsByName('checkloc[]');
		
		jConfirm(confdel, conftitle, function(r) {
			if(r)
			{
				var confirmdeleteids = "";
				var delim = "";
				for(i=0; i<checklocs.length;i++)
				{					
					if(checklocs[i].value == '1')
					{
						confirmdeleteids += delim + checklocs[i].id;
						delim = "|";
					}
				}
				$("#delids").val(confirmdeleteids);
				$("#delloc").submit();
			}
			else
			{
				if($(".checkloc").is(':checked'))
				{
					$(".checkloc").removeAttr('checked');
				}
			}
		});	
	});
	
	$(".checkloc").live('click', function() {
		if($(this).val() == "")
		{
				$(this).val("1");
		}
		else
		{
			$(this).val("");
		}
	});

});/*-- END  $(document).ready ----------- --*/

// DATATABLE
function drawDatatable() {
	var deltext = translate('delete');
	var table = $('#table').DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		sDom: 
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
			't'+
			'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>',

		"lengthMenu": [[25, 50, 100], [25, 50, 100]],
			
		"processing": true,
		"info": true,
		"filter": true,
		"paginate": true,

		"serverSide": true,
		"autoWidth": false,
		"stateSave": true,
		"scrollX": false,
		"scrollCollapse": true,
//		"stateLoadCallback": function (settings)
//		{
//
//		},
	    "fnStateLoadParams": function (oSettings, oData) {
	    	if(reset_datatable == 1){
	    		return false;
	    	} else{
	    		 oData.search.search= "";
	    		return true;
	    	}
	      },
		columns: [
		          { data: "checkloc", className: "","width": "2%"},
		          { data: "news_title", className: "","width": "30%"},
		          { data: "news_date", className: "","width": "10%"},
		          { data: "actions", className: " ","width": "10%"},
		          { data: "status", className: "","width": "10%"},
			],
			
		columnDefs: [ { "targets": -1, "searchable": false, "orderable": true },
		              { "targets": 0, "searchable": false, "orderable": false },
		              { "targets": 3, "searchable": false, "orderable": false },
		            ],
 		//order: [[ 1, "asc" ]],
        order: [],
		
 		ajax: {
			url:ajaxurl,
			type: 'POST'		
		},
		initComplete: function(){
			var html = '<form id="delloc" action='+delurl+' method="post">';
			html += '<input type="hidden" id="delids" name="delids" value="" /><button type="button" class="delete">' + deltext + '</button></form>';
		      $("#bottom_export").html(html);           
		   }
 	
	});
	return table;
} 
