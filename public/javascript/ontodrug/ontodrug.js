//ISPC-2589 Ancuta 28.05.2020 [migration from clinic CISPC]
$(document).ready(function() {
    /* $('#medisync_drop').load('<?php echo APP_BASE;?>ajax/medisyncwidget?id=' + idpd)*/

    $('#ontodrug').click(function(event){
        show_ontodrug_dialog();
    });

});

function show_ontodrug_dialog(){

    check_onto_drug();

    $('#medisync_drop').dialog({resizable:true, width:1000, height:700, title:'OntoDrugCheck Arzneimitteltherapiesicherheit',
        buttons:{"Medikamente speichern":function(){
                $.get('ontodrug/buttonlog?button=save');
                $('#form_medicationedit').find('.btnsubmit[name="save"]').click();
            },

            "Medikamente speichern & weiter bearbeiten":function(){
                $.get('ontodrug/buttonlog?button=save2');
                $('#form_medicationedit').find('.btnsubmit[name="save_and_continue"]').click();
            },

            "Check beenden":function () {
                $.get('ontodrug/buttonlog?button=close');
                $(this).dialog("close");
            },
            "Check erneut durchführen":function () {
                $.get('ontodrug/buttonlog?button=again');
                // $(this).dialog("close");
                check_onto_drug();
            }
        }
    });
}

function check_onto_drug(){
    $.get('ontodrug/buttonlog?button=check');
    var medis = [];
    $('#actual_med_table').find('td.name').each(function () {
        var name = $(this).find('[data-medication_type="actual"]').val();
        //var name = $(this).find('.name').val();
        var wirkstoff = $(this).find('.medication_drug').val();
        var pzn = $(this).find('.medication_pzn').val();
        if (name.length > 0) {
            medis.push([name, wirkstoff, pzn]);
        }
    });

    var bedarfsmed =[];
    $('#isbedarfs_med_table').find('td.name').each(function () {
        var name = $(this).find('[data-medication_type="isbedarfs"]').val();
        var wirkstoff = $(this).find('.medication_drug').val();
        var pzn = $(this).find('.medication_pzn').val();
        if (name.length > 0) {
            bedarfsmed.push([name, wirkstoff, pzn]);
        }
    });

    var notfallmed = [];

    $('#iscrisis_med_table').find('td.name').each(function () {
        var name = $(this).find('[data-medication_type="iscrisis"]').val();
        var wirkstoff = $(this).find('.medication_drug').val();
        var pzn = $(this).find('.medication_pzn').val();
        if (name.length > 0) {
            notfallmed.push([name, wirkstoff, pzn]);
        }
    });

    var isivmed = [];

    $('#isivmed_med_table').find('td.name').each(function () {
        var name = $(this).find('[data-medication_type="isivmed"]').val();
        var wirkstoff = $(this).find('.medication_drug').val();
        var pzn = $(this).find('.medication_pzn').val();
        if (name.length > 0) {
            isivmed.push([name, wirkstoff, pzn]);
        }
    });

    var pumpenmed=[];

    $('#isschmerzpumpe_pumpeblock1').find('td.name').each(function () {
        var name = $(this).find('[data-medication_type="isschmerzpumpe"]').val();
        var wirkstoff = $(this).find('.medication_drug').val();
        var pzn = $(this).find('.medication_pzn').val();
        if (name.length > 0) {
            pumpenmed.push([name, wirkstoff, pzn]);
        }
    });

    var intervallmed=[];

    $('#scheduled_med_table').find('td.name').each(function () {
        var name = $(this).find('[data-medication_type="scheduled"]').val();
        var wirkstoff = $(this).find('.medication_drug').val();
        var pzn = $(this).find('.medication_pzn').val();
        if (name.length > 0) {
            intervallmed.push([name, wirkstoff, pzn]);
        }
    });

    var vals=[];
    $('.ontodrug-meds-cols-container .chnewcheck:checked').each(
        function(){
            vals.push($(this).val());
        });

    var riskgroup= [];
    $('.ontodrug-risk-cols-container .riskgroup:checked').each(
        function(){
            riskgroup.push({
                id: this.id,
                val: $(this).val()
            });
        });

    $('#medisync_drop').load(appbase + 'ontodrug/patientonto?id='+idpd, {medi: medis,bedarf:bedarfsmed,notfall: notfallmed,ivmed: isivmed,pump: pumpenmed,intervall: intervallmed, checked: vals,riskg:riskgroup},
        function(){

        }
    );
}
