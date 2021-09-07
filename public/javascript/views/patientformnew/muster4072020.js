/* ISPC-2370 */
/* Maria:: Migration ISPC to CISPC 02.09.2020 */
var formular_button_action = window.formular_button_action;

function form_submit_validate() {

    return true;
}


$(document).ready(function() {
    //disable enter key
    $('input[type="text"]').keydown(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });

    $( ".date" ).datepicker({
        dateFormat: 'dd.mm.yy',
        showOn: "both",
        buttonImage: $('#calImg').attr('src'),
        buttonImageOnly: true,
        changeMonth: true,
        changeYear: true,
        nextText: '',
        prevText: ''
    });


    $('.stamp_alert').hide();

    /*----------------------------------------------------------------------------------------------------------*/
    /*---------------------------------------- Stamp Info ------------------------------------------------------*/
    /*----------------------------------------------------------------------------------------------------------*/
    $('#stampusers_doct').live('change',function(){
        $('#user_stamp_block span').replaceWith('');
        $('#user_stamp_block textarea').replaceWith('');

        $.get(appbase+ 'ajax/userstampinfo?stamp-info=' + $(this).val(), function(result) {

            if (result != 0){
                var resultx = jQuery.parseJSON(result);

                var user_lanr = resultx.lanr;
                var user_bsnr = resultx.bsnr;

                $('#stamp_user_bsnr').val(user_bsnr);
                $('#stamp_user_lanr').val(user_lanr);

                $('#user_stamp_block span').replaceWith('');
                $('#user_stamp_block textarea').replaceWith('');

                var row1 = resultx.row1;
                var row2 = resultx.row2;
                var row3 = resultx.row3;
                var row4 = resultx.row4;
                var row5 = resultx.row5;
                var row6 = resultx.row6;
                var row7 = resultx.row7;

                var user_stamp = '<span>'+ row1 +'<br/>'+row2+'<br/>'+row3+'<br/>'+row4+'<br/>'+row5+'<br/>'+row6+'<br/>'+row7+'</span>';
                var user_stamp_hidden = '<textarea name="stamp_block" style="display: none">'+ row1 +'<br/>'+row2+'<br/>'+row3+'<br/>'+row4+'<br/>'+row5+'<br/>'+row6+'<br/>'+row7+'</textarea>';

                $('#user_stamp_block').append(user_stamp+user_stamp_hidden);


            } else{
                $('.stamp_alert').show('fast').delay(1000).hide('slow');
            }

        });
        return false;
    });



    $('.fees_type').each(function(){
        var dummyel = $($(this).parent().parent().find('.fees_type_dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
            $('#'+dummyel).addClass('rcb_img');
        }
        else {
            $('#'+dummyel).addClass('no_img');
        }
    });

    $(document).on('click','.fees_type_dummy',function(){
        var that = $($(this).parent().find('.fees_type')).attr('id');

        if($('#'+that).is(':checked')) {
            $('#'+that).removeAttr('checked');
            $(this).removeClass('rcb_img no_img').addClass('no_img');
        } else{
            $('#'+that).attr('checked','checked');
            $('.fees_type').not('#'+that).removeAttr('checked');
            $('.fees_type_dummy').not($(this)).removeClass('rcb_img no_img').addClass('no_img');
            $(this).removeClass('no_img rcb_img').addClass('rcb_img');
        }
    });

    $('.sick_reason').each(function(){
        var dummyel = $($(this).parent().parent().find('.sick_reason_dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
            $('#'+dummyel).addClass('rcb_img');
        }
        else {
            $('#'+dummyel).addClass('no_img');
        }
    });

    $(document).on('click','.sick_reason_dummy',function(){
        var that = $($(this).parent().find('.sick_reason')).attr('id');

        if($('#'+that).is(':checked')) {
            $('#'+that).removeAttr('checked');
            $(this).removeClass('rcb_img no_img').addClass('no_img');
        } else{
            $('#'+that).attr('checked','checked');
            $(this).removeClass('no_img rcb_img').addClass('rcb_img');
        }
    });

    $('.travel').each(function(){
        var dummyel = $($(this).parent().parent().find('.travel_dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
            $('#'+dummyel).addClass('rcb_img');
        }
        else {
            $('#'+dummyel).addClass('no_img');
        }
    });

    $(document).on('click','.travel_dummy',function(){
        var that = $($(this).parent().find('.travel')).attr('id');

        if($('#'+that).is(':checked')) {
            $('#'+that).removeAttr('checked');
            $(this).removeClass('rcb_img no_img').addClass('no_img');
        } else{
            $('#'+that).attr('checked','checked');
            $(this).removeClass('no_img rcb_img').addClass('rcb_img');
        }
    });

    $('.treatmentinclinic').each(function(){
        var dummyel = $($(this).parent().parent().find('.treatmentinclinic_dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
            $('#'+dummyel).addClass('rcb_img');
        }
        else {
            $('#'+dummyel).addClass('no_img');
        }
    });

    $(document).on('click','.treatmentinclinic_dummy',function(){
        var that = $($(this).parent().find('.treatmentinclinic')).attr('id');

        if($('#'+that).is(':checked')) {
            $('#'+that).removeAttr('checked');
            $(this).removeClass('rcb_img no_img').addClass('no_img');
        } else{
            $('#'+that).attr('checked','checked');
            $(this).removeClass('no_img rcb_img').addClass('rcb_img');
        }
    });

    $('#otherreason_text').attr('readonly', true);
    $('.otherreason').each(function(){
        var dummyel = $($(this).parent().parent().find('.otherreason_dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
            $('#'+dummyel).addClass('rcb_img');
        }
        else {
            $('#'+dummyel).addClass('no_img');
        }
    });

    $(document).on('click','.otherreason_dummy',function(){
        var that = $($(this).parent().find('.otherreason')).attr('id');

        if($('#'+that).is(':checked')) {
            $('#'+that).removeAttr('checked');
            $('#otherreason_text').attr('readonly', true);
            $('#otherreason_text').val('');
            $(this).removeClass('rcb_img no_img').addClass('no_img');
        } else{
            $('#'+that).attr('checked','checked');
            $('#otherreason_text').attr('readonly', false);
            $(this).removeClass('no_img rcb_img').addClass('rcb_img');
        }
    });

    $('#reason_needed_for_transfer_text').attr('readonly', true);
    $('.high_frequency_treatment').each(function(){
        var dummyel = $($(this).parent().parent().find('.high_frequency_treatment_dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
            $('#'+dummyel).addClass('rcb_img');
        }
        else {
            $('#'+dummyel).addClass('no_img');
        }
    });

    $(document).on('click','.high_frequency_treatment_dummy',function(){
        var that = $($(this).parent().find('.high_frequency_treatment')).attr('id');

        if(that == 'high_frequency_treatment2')
        {
            if($('#'+that).is(':checked')) {
                $('#'+that).removeAttr('checked');
                $('#reason_needed_for_transfer_text').attr('readonly', true);
                $('#reason_needed_for_transfer_text').val('');
                $(this).removeClass('rcb_img no_img').addClass('no_img');
            } else{
                $('#'+that).attr('checked','checked');
                $('#reason_needed_for_transfer_text').attr('readonly', false);
                $(this).removeClass('no_img rcb_img').addClass('rcb_img');
            }
        }
        else
        {

            if($('#'+that).is(':checked')) {
                $('#'+that).removeAttr('checked');
                $(this).removeClass('rcb_img no_img').addClass('no_img');
            } else{
                $('#'+that).attr('checked','checked');
                $(this).removeClass('no_img rcb_img').addClass('rcb_img');
            }
        }
    });

    $('.durable_limited_mobility').each(function(){
        var dummyel = $($(this).parent().parent().find('.durable_limited_mobility_dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
            $('#'+dummyel).addClass('rcb_img');
        }
        else {
            $('#'+dummyel).addClass('no_img');
        }
    });

    $(document).on('click','.durable_limited_mobility_dummy',function(){
        var that = $($(this).parent().find('.durable_limited_mobility')).attr('id');

        if(that == 'durable_limited_mobility2')
        {
            if($('#'+that).is(':checked')) {
                $('#'+that).removeAttr('checked');
                $('#reason_needed_for_transfer_text').attr('readonly', true);
                $('#reason_needed_for_transfer_text').val('');
                $(this).removeClass('rcb_img no_img').addClass('no_img');
            } else{
                $('#'+that).attr('checked','checked');
                $('#reason_needed_for_transfer_text').attr('readonly', false);
                $(this).removeClass('no_img rcb_img').addClass('rcb_img');
            }
        }
        else
        {

            if($('#'+that).is(':checked')) {
                $('#'+that).removeAttr('checked');
                $(this).removeClass('rcb_img no_img').addClass('no_img');
            } else{
                $('#'+that).attr('checked','checked');
                $(this).removeClass('no_img rcb_img').addClass('rcb_img');
            }
        }
    });

    $('.otherreason_needing_approval').each(function(){
        var dummyel = $($(this).parent().parent().find('.otherreason_needing_approval_dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
            $('#'+dummyel).addClass('rcb_img');
        }
        else {
            $('#'+dummyel).addClass('no_img');
        }
    });

    $(document).on('click','.otherreason_needing_approval_dummy',function(){
        var that = $($(this).parent().find('.otherreason_needing_approval')).attr('id');

        if($('#'+that).is(':checked')) {
            $('#'+that).removeAttr('checked');
            $(this).removeClass('rcb_img no_img').addClass('no_img');
        } else{
            $('#'+that).attr('checked','checked');
            $(this).removeClass('no_img rcb_img').addClass('rcb_img');
        }
    });

    $('.transport_type').each(function(){
        var dummyel = $($(this).parent().parent().find('.transport_type_dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
            $('#'+dummyel).addClass('rcb_img');
        }
        else {
            $('#'+dummyel).addClass('no_img');
        }
    });

    $(document).on('click','.transport_type_dummy',function(){
        var that = $($(this).parent().find('.transport_type')).attr('id');

        if($('#'+that).is(':checked')) {
            $('#'+that).removeAttr('checked');
            $(this).removeClass('rcb_img no_img').addClass('no_img');
        } else{
            $('#'+that).attr('checked','checked');
            $(this).removeClass('no_img rcb_img').addClass('rcb_img');
        }
    });

    $('#special_transport_type_text').attr('readonly', true);
    $('.special_transport_type').each(function(){
        var dummyel = $($(this).parent().parent().find('.special_transport_type_dummy')).attr('id');
        if($(this).attr('checked') == 'checked'){
            $('#'+dummyel).addClass('rcb_img');
        }
        else {
            $('#'+dummyel).addClass('no_img');
        }
    });

    $(document).on('click','.special_transport_type_dummy',function(){
        var that = $($(this).parent().find('.special_transport_type')).attr('id');

        if($('#'+that).is(':checked')) {
            $('#'+that).removeAttr('checked');
            $('#special_transport_type_text').attr('readonly', true);
            $('#special_transport_type_text').val('');
            $(this).removeClass('rcb_img no_img').addClass('no_img');
        } else{
            $('#'+that).attr('checked','checked');
            $('#special_transport_type_text').attr('readonly', false);
            $(this).removeClass('no_img rcb_img').addClass('rcb_img');
        }
    });


});