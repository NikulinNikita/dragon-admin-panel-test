$(function () {
    window.body = $('body');

    Admin.Events.on('datatables::ajax::data', function (data) {
        localStorage.setItem('adminQueryWithFilters', JSON.stringify(data));
    });

    let unlockedSelect = $("select[unlocked='1']");
    if (unlockedSelect.length > 0) {
        unlockedSelect.removeAttr('disabled');
    }

    // disable form submit after click
    let submitForm = $("form.panel-default");
    if (submitForm.length > 0) {
        body.on('submit', 'form.panel-default', function (e) {
            let submitButton = $(this).find("button[type='submit']");

            submitButton.attr('disabled', true);
            setTimeout(function () {
                submitButton.removeAttr('disabled')
            }, 2000);
        });

    }

    if ($("#select_date_period").length > 0) {
        body.on('change', '.select_date_period', function (e) {
            e.preventDefault();
            var dateFormat = "YYYY-MM-DD";
            var period = $(this).val();
            var date_from = moment().format(dateFormat);
            var date_to = moment().format(dateFormat);

            if (period === 'today') {
                date_from = moment().format(dateFormat);
                date_to = moment().add(1, 'days').format(dateFormat);
            } else if (period === 'yesterday') {
                date_from = moment().subtract(1, 'days').format(dateFormat);
            } else if (period === 'last_3_days') {
                date_from = moment().subtract(3, 'days').format(dateFormat);
            } else if (period === 'last_week') {
                date_from = moment().subtract(7, 'days').format(dateFormat);
            } else if (period === 'last_month') {
                date_from = moment().subtract(30, 'days').format(dateFormat);
            } else {
                date_from = '';
                date_to = '';
            }

            if ($(".global_search").length > 0) {
                $('#date_from').attr('disabled', period !== 'custom').val(date_from);
                $('#date_to').attr('disabled', period !== 'custom').val(date_to);
            } else {
                $(this).closest('.column-filter').find('input[placeholder="From Date"]').val(date_from).trigger('change');
                $(this).closest('.column-filter').find('input[placeholder="To Date"]').val(date_to).trigger('change');
            }
        });
    }

    if ($(".global-filter-button").length > 0) {
        body.on('click', '.global-filter-button', function (e) {
            $('#date_from').attr('disabled', false);
            $('#date_to').attr('disabled', false);
        });
    }

    let filterButton = $("button#filters-exec.column-filter");
    let cancelButton = $("button#filters-cancel");
    if (filterButton.length > 0) {
        filterButton.text('');
        filterButton.append('<i class="fa fa-filter"></i>');

        // cancelFilters();
        // cancelButton.off("click");
        cancelButton.on("click", function () {
            cancelFilters($(this));
        })
    }

    function cancelFilters(obj) {
        localStorage.setItem('adminQueryWithFilters', null);
        $(".display-filters td[data-index] input").val(null).trigger("change");
        var e = $(".display-filters td[data-index] select");
        e.val(null), e.trigger("change");
        if (obj)
            obj.closest('.datatables').DataTable().draw();
        else
            $('.datatables').DataTable().draw();
    }

    // if ($(".b-colored_rows").length > 0) {
    //     console.log($(".b-colored_rows").find('div:contains("succeed")').closest('tr').addClass('b-bg_color_light_green'));
    // }
    if ($(".b-remove_header_and_pagination").length > 0) {
        $(".b-remove_header_and_pagination").closest('.panel-default').addClass('b-remove_included_table_header_and_pagination');
    }
    if ($(".b-remove_header").length > 0) {
        $(".b-remove_header").closest('.panel-default').addClass('b-remove_included_table_header');
    }
    if ($(".b-remove_pagination").length > 0) {
        $(".b-remove_pagination").closest('.panel-default').addClass('b-remove_included_pagination');
    }
    if ($(".b-remove_tabs").length > 0) {
        $(".b-remove_tabs").closest('.nav-tabs-custom ').addClass('b-remove_included_tabs');
    }

    if ($(".ajax_append").length > 0) {
        $(".ajax_append").on("click", function () {
            let self = $(this);
            let link = self.attr('data-type');
            let id = self.attr('data-id');
            let container = $('#' + link);
            let type = link.replace('a-', '');

            $.ajax({method: 'GET', url: '/admin_panel/ajax/appendData', data: {id: id, type: type}}).done(function (resp) {
                self.removeClass('ajax_append');
                self.off("click");
                container.empty();
                container.append(resp);
            })
        })
    }

    body.on('click', '.b-exportStaticReport', function (e) {
        let exportReport = $(this);
        if (exportReport.attr('disabled')) {
            return true;
        }

        exportReport.attr('disabled', true);
        setTimeout(function () {
            exportReport.removeAttr('disabled')
        }, 1000);
    });

    let multiselectDisabled = $('input.multiselect--disabled');
    if (multiselectDisabled.length > 0) {
        multiselectDisabled.prev('div.multiselect').addClass('multiselect--disabled');
    }

    body.on('click', '.b-stopRound', function (e) {
        e.preventDefault();
        swal({
            title: "Do you want to stop the round?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.value) {
                window.location = $(this).attr('data-link');
            }
        })
    });
    body.on('click', '.b-restartRound', function (e) {
        e.preventDefault();
        swal({
            title: "Do you want to restart the round?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.value) {
                window.location = $(this).attr('data-link');
            }
        })
    });
    body.on('click', '.b-manipulateRound', function (e) {
        e.preventDefault();
        swal({
            title: "Update users bets with new round results?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Update!'
        }).then((result) => {
            if (result.value) {
                window.location = $(this).attr('data-link');
            }
        })
    });
    body.on('click', '.b-refundRoundBets', function (e) {
        e.preventDefault();
        swal({
            title: "Refund users bets?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes!'
        }).then((result) => {
            if (result.value) {
                window.location = $(this).attr('data-link');
            }
        })
    });

    body.on('click', '.b-resourceDown', function (e) {
        e.preventDefault();
        swal({
            title: "Are you sure you want to STOP resource until?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Apply!',

            html: '<input id="swal-datepicker">',
            customClass: 'swal2-overflow',
            onOpen: function () {
                let format = 'YYYY-MM-DD HH:mm';
                let down_until = $('.b-resourceDown').attr('data-down-until');
                down_until = moment(down_until) > moment() ? down_until : '';

                $("#swal-datepicker").val(moment(down_until).format(format)).datetimepicker({
                    format: format,
                });
            },
            allowOutsideClick: () => !swal.isLoading()
        }).then((result) => {
            let link = $(this).attr('data-link');
            let date = $('#swal-datepicker').val().replace(' ', '_');
            let url = `${link}?down_until=${date}`;

            if (result.value) {
                window.location = url;
            }
        })
    });
    body.on('click', '.b-resourceUp', function (e) {
        e.preventDefault();
        swal({
            title: "Are you sure you want to START resource?",
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Apply!'
        }).then((result) => {
            if (result.value) {
                window.location = $(this).attr('data-link');
            }
        })
    });

    window.BrowserNotification = {
        'access': 'denied',
        'iconDefault': 'img/notification/logo-192x192.png',

        'notify': function (title, message, icon) {
            if (this.isGranted()) {
                new Notification(title, {
                    'body': message,
                    'requireInteraction': true,
                    'icon': icon || this.iconDefault
                });
            }
        },

        'isGranted': function () {
            return this.access == 'granted';
        }
    };

    if ('Notification' in window) {
        Notification.requestPermission().then((result) => {
            BrowserNotification.access = result;
        });
    }

    initAgentReportsEvents();

    $('#assigned_status').select2();
});

function initAgentReportsEvents() {
    $('.expand').each(function (idx, el) {
        el.addEventListener('click', epxandSubagents);
    });
    $('.agent-title').each(function (idx, el) {
        el.addEventListener('click', showAgentInfoTable);
    });
}

function epxandSubagents(e) {

    if (!Admin.loadingSubAgents) {
        Admin.loadingSubAgents = true;

        const parent_id = $(e.target).data('parent-id');
        const queryString = new URLSearchParams(window.location.search)
        const url = `agent/${parent_id}/subagents?` + queryString.toString();
        const $parent = $(e.target).parent();
        const $spin = $('.dataTables_processing');

        $(e.target).toggleClass('closed opened');

        $spin.css('display', 'block');

        if ($parent.children('.agent-list').length) {
            $parent.children('.agent-list').toggle();
            $spin.css('display', 'none');
            //crutch
            Admin.loadingSubAgents = false;
        } else {
            $.get(url, function (response) {
                if (response.html) {
                    $parent.append(response.html);

                    initAgentReportsEvents();

                    $spin.css('display', 'none');

                    Admin.loadingSubAgents = false;
                }
            });
        }
    }
}

function showAgentInfoTable(e) {
    $('.agent-title').removeClass('show-agent-info');
    $parent = $(e.target).parent();

    $(e.target).addClass('show-agent-info');

    $('.agent-info').html($parent.children('.table-agent-info').html());
}

