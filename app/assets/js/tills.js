$( document ).ready(function() {

    $("span[data-mode='add']").click(function(e) {
        e.preventDefault();
        $.ajax({
            url: "api/tills/addlink",
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

    $("span[data-mode='delete']").click(function(e) {
        e.preventDefault();
        $.ajax({
            url: "api/tills/deletelink",
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