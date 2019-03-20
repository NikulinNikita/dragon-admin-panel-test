$(function () {
    $('.b-ajax-select2').select2({
        ajax: {
            url: '/admin_panel/ajax/getCustomAjaxSelectOptions',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                let query = {
                    search: params.term,
                    model: $(this).attr('data-model'),
                    field: $(this).attr('data-field'),
                };

                return query;
            },
            processResults: function (data, params) {
                params.page = params.page || 1;

                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 10) < data.total_count
                    }
                };
            },
            cache: true
        },
        escapeMarkup: function (markup) {
            return markup;
        },
        minimumInputLength: 1,
        templateResult: formatRepo,
        templateSelection: formatRepoSelection
    });

    function formatRepo(repo) {
        if (repo.loading) {
            return repo.text;
        }
        var markup =
            "<div class='select2-result-repository clearfix'>" +
                "<div class='select2-result-repository__title'>" + repo.text + "</div>" +
            "</div>";

        return markup;
    }

    function formatRepoSelection(repo) {
        return repo.text;
    }
});