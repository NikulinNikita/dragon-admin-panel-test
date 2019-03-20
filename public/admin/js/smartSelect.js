Admin.Modules.register('form.elements.smartSelect', () => {
    $('.js-data-smartSelect').each((e, item) => {
        let options = {},
            $self = $(item);

        options = {
            placeholder: $self.attr('placeholderText'),
            minimumInputLength: $self.data('min-symbols'),
            ajax: {
                url: $self.attr('search_url'),
                dataType: 'json',
                method: 'POST',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term ? params.term : null, // search term
                        page: params.page,
                        model: $self.attr('model'),
                        field: $self.attr('field'),
                        search: $self.attr('search'),
                        relations: $self.attr('relations'),
                        isNullable: $self.attr('isNullable') !== undefined ? true : null,
                        queryFilters: $self.attr('queryFilters'),
                        with: $self.attr('with'),
                        orderBy: $self.attr('orderBy'),
                        limit: $self.attr('limit'),
                        joins: $self.attr('joins'),
                        selectRaws: $self.attr('selectRaws'),
                        distinct: $self.attr('distinct'),
                        cte: $self.attr('cte'),
                    };
                },
                processResults: function (data, params) {
                    return {
                        results: data,
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; },
            templateResult: $self.attr('templateResult') ? window[$self.attr('templateResult')] : formatRepo,
            templateSelection: $self.attr('templateSelection') ? window[$self.attr('templateSelection')] : formatRepoSelection
        };

        $self.select2($self.attr('isReadOnly') !== undefined || $self.attr('isStaticOptions') !== undefined ? {} : options);
    });

    function roundsTemplate (repo) {
        if (repo.custom_name) return repo.custom_name;

        if (repo.loading) {
            return repo.text;
        }

        let markup = "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-repository__meta'>" +
            "<div class='select2-result-repository__title' data-code='" + repo.code + "'>" + repo.tag_name + "</div>";

        return markup;
    }

    function formatRepo (repo) {
        if (repo.custom_name) return repo.custom_name;

        if (repo.loading) {
            return repo.text;
        }

        let markup = "<div class='select2-result-repository clearfix'>" +
            "<div class='select2-result-repository__meta'>" +
            "<div class='select2-result-repository__title'>" + repo.tag_name + "</div>";

        return markup;
    }

    function formatRepoSelection (repo) {
        return repo.custom_name || repo.tag_name || repo.text;
    }

}, 0, ['bootstrap::tab::shown']);