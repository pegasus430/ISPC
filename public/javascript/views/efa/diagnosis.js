/** ISPC-2831 Dragos 15.03.2021 **/

if (typeof(DEBUG) !== 'undefined' && window.DEBUG === true) {
    console.info('custom view js included : '+document.currentScript.src);
}

$(document).ready(function(){
    $(".datetype").datepicker({
        dateFormat: 'dd.mm.yy',
        showOn: "both",
        buttonImage: $('#calImg').attr('src'),
        buttonImageOnly: true,
        changeMonth: true,
        changeYear: true,
        nextText: '',
        prevText: ''
    });
    // $('#diagnosis_and_findings_tabs').tabs();
    $('ul.tabs li').click(function(){
        var tab_id = $(this).attr('data-tab');
        $('ul.tabs li').removeClass('current');
        $('.tab_block').removeClass('current');
        $(this).addClass('current');
        $("#"+tab_id).addClass('current');
    })


});
