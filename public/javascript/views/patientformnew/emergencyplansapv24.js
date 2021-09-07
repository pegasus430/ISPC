/**
 * @date 17.09.2019
 * @author @Lore
 * ISPC-2359
 * 
 */


$(document).ready(function() {
	table_actual = $('#medication_block').DataTable({
		// ADD language
		"language": {
			"emptyTable" : "Keine Medikamente",
		},
		"lengthMenu": [[10, 25, 50], [10, 25, 50]],
        "dom": 't',
		processing: true,
		info: false,
		filter: true,
		paginate: false,
		 "orderCellsTop": true,
		serverSide: false,
		"stateSave": false,
		"scrollX": false,
		"autoWidth": false,
		"scrollCollapse":true,
		columnDefs: [ 
			
				{ "targets": 0, "searchable": true, "orderable": true, "name":"medication_indication" , "data": "indication "}, // medication_concentration
		        { "targets": 1, "searchable": true, "orderable": true, "name":"medication_name" ,       "data": "medication"}, // medication_concentration
				{ "targets": 2, "searchable": true, "orderable": true, "name":"medication_dosage" ,     "data": "dosage"}, // medication_concentration
				{ "targets": 3, "searchable": true, "orderable": true, "name":"medication_nursing_measures" ,  "data": "nursing_measures"}, // medication_concentration
				{ "targets": 4, "searchable": false, "orderable": false  }, // medication_concentration

			
				],				
 		order: [[1,'ASC']],
 		
		initComplete: function()
		{ 
			
			 
		}
	});
	
	
});





