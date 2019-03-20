$(function () {
    $('.b-exportReport').on("click", function (e) {
        e.preventDefault();
        let exportReport = $(this);
        if (exportReport.attr('disabled')) {
            return true;
        }

        let model = $(this).attr('data-model');
        let params = $(this).attr('data-params');
        let route = `/admin_panel/${model}/async?`;
        let query = localStorage.getItem('adminQueryWithFilters') !== null ? JSON.parse(localStorage.getItem('adminQueryWithFilters')) : {};
        exportReport.attr('disabled', 'disabled');
        query.includeHiddenColumns = true;
        query.length = 10000;
        query = $.param(query);

        $.ajax({method: 'GET', url: route + query}).done(function (resp) {
            let exportRoute = `/admin_panel/reports/${model}/exportReport`;
            let encodedResp = JSON.stringify(resp);
            $.ajax({method: 'POST', url: exportRoute, data: {resp: encodedResp, params: params}}).done(function (response) {
                let a = document.createElement("a");
                a.href = response.file;
                a.download = response.name;
                document.body.appendChild(a);
                a.click();
                a.remove();
                exportReport.removeAttr('disabled');
            })
        })
    })
});