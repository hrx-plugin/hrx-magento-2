require(['jquery', 'mage/translate'],function($, t){
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
            if (confirm($.mage.__('Important! Latest request for courier is until 15:00. If requested later, where are no guarantees that the courier will come.'))){
            //if (confirm('Svarbu! Vėliausias galimas kurjerio iškvietimas yra iki 15val. Vėliau iškvietus kurjerį negarantuojame, jog siunta bus paimta.')) {
                if (omniva_url) {
                    location.href = omniva_url;
                }
                return true;
            }
            location.href = "";
            return false;
        });
    });
});