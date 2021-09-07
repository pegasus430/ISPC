$(document).ready(function(){
	//Maria:: Migration CISPC to ISPC 22.07.2020
    var listselector=$('#patientquicknavigation_lists');
    var patselector=$('#patientquicknavigation_pats');

    var upath = document.location.pathname.split('/');
    if(upath.length>1) {
        upath = upath[upath.length - 2] + "/" + upath[upath.length - 1];
    }

    var override=true;
    var default_override="patientcourse/patientcourse";

    var no_overrides =[
        'patientcourse/patientcourse',
        'patient/patientdetails',
        'patient/patientmedication',
        'patient/patientfileupload',
        'patient/doctorletter',
        'brief/createletter',
        'patient/patdiagnoedit',
        'patient/patientdischarge',
        'patient/patientorders',
        'patient/opsoverview2016',
        'patient/opsoverview2',
        'patient/opsoverview2016k',
        'patient/opsoverview',
        'patientpsysoz/index',
        'patient/documenttransfer',
        'patient/calendar',
        'patientform/lmuteammeeting',
        'patientnew/medication'
    ];

    if ('patientpflegedoku' == upath.split('/')[1]){
        override=false;
    }

    if (no_overrides.indexOf(upath)>-1){
        override=false;
    }

    if(override){
        $('#patientquicknavigation_form').attr('action',appbase + default_override);
    }

    function patientquicknavigation_lists(status, act_status){
        for (var i=0; i< status.length; i++){
            var myline=status[i];
            var selected=" ";
            if (act_status == myline.value) {
                selected = ' selected ';
            }
            var newel=$('<option value="'+myline.value+'" '+selected+' >' + myline.name + '</option>');
            $(listselector).append(newel);
        }

    }

    function patientquicknavigation_pats(pats, patient_epid ){
        for (var i=0; i< pats.length; i++){
            var myline=pats[i];
            var selected=" ";
            if (patient_epid == myline.enc_id) {
                selected = ' selected ';
            }
            var newel=$('<option value="'+myline.enc_id+'" '+selected+' >' + myline.nice_name + '</option>');
            $(patselector).append(newel);
        }
    }

    function patientquicknavigation_prev(){

        var prev=$('#patientquicknavigation_pats option:selected').prev();
        if(prev.length <1) prev=$('#patientquicknavigation_pats option').last();
        prev=$(prev).text();
        if(prev==undefined){
            prev="";
        }
        if (prev.length>15){
            prev=prev.substr(0,15) + "...";
        }
         $('#patientquicknavigation_prev span').text(prev);
    }

    function patientquicknavigation_next( ){

        var next=$('#patientquicknavigation_pats option:selected').next();
        if(next==undefined){
            return;
        }
        if(next.length <1) next=$('#patientquicknavigation_pats option').first();
        next=$(next).text();
        if(next==undefined){
            next="";
        }
        if (next.length>15){
            next=next.substr(0,15) + "...";
        }
        $('#patientquicknavigation_next span').text(next);
    }


    function qpn_loadpatlist(){
        var fallart=$(listselector).val();
        $(patselector).empty();
        $(listselector).empty();
        $.ajax({
            url: appbase+"patient/patientswitcherclinicheader?" + formVars + "&fallart=" + fallart,
            dataType : "json"
        })
            .done(function( data ) {
                patientquicknavigation_lists(data.stats, data.act_status)
                patientquicknavigation_pats(data.pats, data.act_pat_encid);
                patientquicknavigation_prev();
                patientquicknavigation_next();
            });
    }

    $(listselector).change(function(){
        var fallart=$(listselector).val();
        qpn_loadpatlist();
        $.ajax({
            url: appbase+"ajax/setuserdefaultpatientlist?plist="+fallart
        });
    });


    $(patselector).change(function(){
        $('#patientquicknavigation_form').submit();
    });


    $('#patientquicknavigation #patientquicknavigation_next').click(function(){
        if($('#patientquicknavigation_pats option').length<2){
            return;
        }
        var next=$('#patientquicknavigation_pats option:selected').next();
        if(next.length <1) next=$('#patientquicknavigation_pats option').first();
        $(next).attr('selected',true);
        $(patselector).change();
    });
    $('#patientquicknavigation #patientquicknavigation_prev').click(function(){
        if($('#patientquicknavigation_pats option').length<2){
            return;
        }
        var next=$('#patientquicknavigation_pats option:selected').prev();
        if(next.length <1) next=$('#patientquicknavigation_pats option').last();
        $(next).attr('selected',true);
        $(patselector).change();
    });

    qpn_loadpatlist(listselector);


});
