var langurl = appbase + 'javascript/data_tables/de_language.json';
var ajaxurl =appbase + 'team/teameventhistorylist';

var left_menu_list;

$(document).ready(function() { /*------ Start $(document).ready --------------------*/
	window.left_menu_list = drawDatatable();
	
	$('.loading').hide();
	
	if(voluntary_event == 1){
		
		$('.attending_voluntary').show();
		$('.attending_users').hide();
		
	} else {
		
		$('.attending_users').show();
		$('.attending_voluntary').hide();
		
	}
	
	//datepicker and timepickers
	$('#event_date').datepicker({
		dateFormat: 'dd.mm.yy',
		showOn: "both",
		buttonImage: $('#calImg').attr('src'),
		buttonImageOnly: true,
		changeMonth: true,
		changeYear: true,
		nextText: '',
		prevText: ''
	}).mask("99.99.9999");
	
	$('#event_time_start, #event_time_end').timepicker({
		minutes: {
			interval: 5
		},
		showPeriodLabels: false,
		rows: 4,
		hourText: 'Stunde',
		minuteText: 'Minute'
	}).mask("99:99");
	
	
	
	

	/* ########################## GET USER DATA ON SELECT  ########################################  */
	$('#event_type').live('change',function(){
		$('.loading').show();
		
		var  is_voluntary = $(this).find('option:selected').attr('data');
		
		if(is_voluntary == 1){
			
			$('#voluntary_event').val('1');
			$('.attending_users').hide();
			$('.attending_voluntary').show();
			$('.loading').hide();
			
//				$.ajax({
//					type: 'POST',
//					url: 'ajax/attendingvoluntaryworkers',

//					success:function(data){
//						$('#load_voluntary_workers').html(data);
//						$('.loading').hide();
//					},
//					error:function(){
//						ajax_done = 1;
//					}
//				});
			
			
		} else{
			$('#voluntary_event').val('0');
			$('.attending_users').show();
			$('.attending_voluntary').hide();
			$('.loading').hide();
			$('#load_voluntary_workers').html("");
		}
		
	});
	
});/*-- END  $(document).ready ----------- --*/

// DATATABLE
function drawDatatable() {	
	var table = $('#table').DataTable({
		// ADD language
		 "language": {
                "url": langurl
         },
 
		"sDom": '<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lfr>'+
				't'+
				'<"fg-toolbar ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"<"#bottom_export">ip>',
		// la sDom - este sa zic - declarat tablelu(t) - cu header - search, paginare(p)

			
		"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
			
		"processing": true,

		"info": true,
		"filter": true,
		"paginate": true,

		"serverSide": true,
		"autoWidth": false,
		"stateSave": false,
		"scrollX": false,
		"scrollCollapse": true,
 		"columns": [
 		          { data: "tabname", className: "", "width": "10%", "visible": false, "searchable": false, "orderable": false},
 		          { data: "create_date", className: "", "width": "10%"},
		          { data: "title", className: "", "width": "40%"},
		          { data: "filetype", className: "", "width": "5%"},
		          { data: "create_user_name", className: "", "width": "20%"},
		          { data: "actions", className: "", "width": "5%"}
			],
			
		"columnDefs": [ 
		          { "targets": -1, "searchable": false, "orderable": false }     	
				],
		order: [],
		
		 rowCallback: function(row, data) {
			 //alert(data["tabname"]);
			 if(data["tabname"] == 'teamevent_custom')
			 {
				 $(row).addClass("custom_file");
				 $(row).find('td:eq(0)').addClass('event_date');
			 }
	    },
		
 		"ajax": {
			url:ajaxurl, // aici se iau detaliile prin ajax la incarcarea paginii
			type: 'POST',
			data: function(d) {
				d.eventid =  eventid;
			}
		}
 	
	});
	
	return table;
}

function changeEvent(data){
	var pre_url = window._finalJsUrl;
	turl = pre_url.replace("?new=1", "");
	turl = pre_url.replace("?", "");
	var url = turl +"?eventid="+data;
	$(location).attr('href',url);
}
function check_group(that)
{
	var group = $(that).attr('alt');

	if($(that).is(':checked')){
		$('.group-'+group).attr('checked', true);
	} else {
		$('.group-'+group).attr('checked', false);
		$('#checkall').attr('checked',false);
	}
}

function checkedall(allid)
{
	checkbox = document.getElementsByName('attending_users[]');

	if(allid.checked==true)
	{
		for(i=0; i<checkbox.length;i++)
		{
			checkbox[i].checked = true;
			$('.group-chks').attr('checked', true);
		}
	}else{
		for(i=0; i<checkbox.length;i++)
		{
			checkbox[i].checked = false;
			$('.group-chks').attr('checked', false);
		}
	}
}


function checkedallvw(allid)
{
	checkbox = document.getElementsByName('attending_vw[]');

	if(allid.checked==true)
	{
		for(i=0; i<checkbox.length;i++)
		{
			checkbox[i].checked = true;
		}
	}else{
		for(i=0; i<checkbox.length;i++)
		{
			checkbox[i].checked = false;
		}
	}
}