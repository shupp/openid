function billShupp() {
    var popup_url = '';
    $.ajax({
        'url' : './bar.php',
        'type' : 'post',
        'data' : $("#rp_form").serialize(),
        'success' : function(text) {
            popup_url = text;
            var win = open(text,
                      'rp_popup',
                      'toolbar=no,location=yes,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=450,height=500,top=20,left=50');
        }
    });

    return false;
}
