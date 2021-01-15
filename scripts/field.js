$(document).ready( function() {
    $("#fl_inp").change(function(){
        let filename = $(this).val().replace(/.*\\/, "");
        $("#fl_nm").html(filename);
    });
});