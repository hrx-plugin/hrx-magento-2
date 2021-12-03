require(['jquery'],function($){
    $('document').ready(function(){
        var omniva_url = "";
        if ($('#call_omniva').attr('onclick')) {
            omniva_url = $('#call_omniva').attr('onclick').replace("location.href = ", "");
            omniva_url = omniva_url.replace(';','');
            omniva_url = omniva_url.replace('\'','');
            $('#call_omniva').removeAttr('onclick');
        }
        $('#call_omniva').on('click',function(e){
            e.preventDefault();
            console.log(omniva_url);
            if (confirm('Svarbu! Vėliausias galimas kurjerio iškvietimas yra iki 15val. Vėliau iškvietus kurjerį negarantuojame, jog siunta bus paimta.')) {
                location.href = omniva_url;
            }
            return false;
        });
    });
});