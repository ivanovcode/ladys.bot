$( document ).ready(function() {

    $("span[data-mode='add']").click(function(e) {
        e.preventDefault();
        $.ajax({
            url: "api/tills/link",
            type: 'POST',
            data:{
                till_id: $(this).closest("tr").find("td[data-key='TillId']").text()
            },
            success: function (response) {
                location.reload();
            },
            error: function () {
                location.reload();
            }
        });
    });

});