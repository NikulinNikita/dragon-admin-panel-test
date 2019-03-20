$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

window.body = $('body');
window.ajaxModal = $('#ajaxModal');

body.on('submit', '.ajax_form', function(e) {
    e.preventDefault();
    sendAjaxWIthValidation($(this));
});

body.on('click', '.ajax_replace', function(e) {
    e.preventDefault();
    sendAjaxWIthValidation($(this));
});

body.on('input', '.ajax_input', function(e) {
    e.preventDefault();
    if($(this).val().length >= 3)
        sendAjaxWIthValidation($(this), {query: $(this).val()});
    else
        $('.hide_window').hide();
});

body.on('click', '.ajax_append', function(e) {
    e.preventDefault();
    var currentUrl, url, urlObj, hashObj, getParams, hash, data;
    currentUrl = window.location.href;
    url = $(this).attr('href');
    urlObj = currentUrl.includes("?") ? $.deparam.querystring(currentUrl) : {};
    hashObj = currentUrl.includes("#") ? $.deparam.fragment(currentUrl) : {};
    urlObj = url.includes("?") ? Object.assign(urlObj, $.deparam.querystring(url)) : urlObj;
    hashObj = url.includes("#") ? Object.assign(hashObj, $.deparam.fragment(url)) : hashObj;
    data = Object.assign({}, urlObj, hashObj);
    data.ajax = true;
    getParams = urlObj && Object.keys(urlObj).length ? '?' + $.param(urlObj) : '';
    hash = hashObj && Object.keys(hashObj).length ? '#' + $.param(hashObj) : '';
    window.history.pushState('page2', 'Title', getParams + hash);
    sendAjaxWIthValidation($(this), data);
});

// MODALS
$(function () {
    body.on('click', '.ajax_modal', function(e) {
        e.preventDefault();
        getAjaxModal($(this));
    });
});

// Get info from object and send to modal window
function getAjaxModal(obj, params) {
    if(params)
        history.pushState('', 'Title', '/');
    var action = params ? params.action : obj.attr('data-action');
    var type = params ? params.type : obj.attr('data-type');
    var item_id = obj.attr('data-id') ? obj.attr('data-id') : 0;
    var forItem = obj.attr('data-forItem') ? obj.attr('data-forItem') : 0;
    var forItem_id = obj.attr('data-forItem-id') ? obj.attr('data-forItem-id') : 0;
    var parameters = params ? params.parameters : (obj.attr('data-parameters') ? obj.attr('data-parameters') : 0);

    ajaxModal.remove();
    $('.modal-backdrop').remove();
    ajaxModal.load('/ajax/modal/getModalContent', {action: action, type: type, item_id: item_id, forItem: forItem,
        forItem_id: forItem_id, parameters: parameters}, function (data) {
        ajaxModal.modal('toggle');
        blockButton(ajaxModal);
    });

    ajaxModal.on('shown.bs.modal', function (e) {
        $(this).find('form').find('.form-group input.form-control').first().focus();

        //PRELOADER
        $('.spinner').fadeOut();
        $('#page-preloader').delay(350).fadeOut('slow');
    });

    ajaxModal.on('hidden.bs.modal', function (e) {
        ajaxModal.find('.modal-dialog').remove();
    });
}

function sendAjaxWIthValidation(self, data, method, url, callbackFunction) {
    if(!method)
        method = self.prop("tagName") === 'FORM' ? 'POST' : 'GET';
    if(self && method === 'POST') {
        url = url ? url : self.attr('action');
        data = data ? data : self.serialize();
        self.find('.form-group').removeClass('has-error');
        blockButton($('.ajax_form'));
    } else
        url = url ? url : self.attr('href');

    $.ajax({method: method, url: url, data: data }).done(function(resp) {
        var status, viewsAmount, viewName, content, action, paramsAmount;
        if($.type(resp) !== 'object') {
            status = parseJsonFromHtml(resp, "#status");
            viewsAmount = parseJsonFromHtml(resp, "#viewsAmount");
            paramsAmount = parseJsonFromHtml(resp, "#paramsAmount");
        } else
            status = resp;
        if(self && method === 'POST') {
            self.trigger('reset');
        }

        if (status.success === 1) {
            if(ajaxModal && status.modalWindow !== 'doNotHide')
                ajaxModal.modal('hide');
            if(status.text)
                alertify.success(status.text);
            if(viewsAmount)
                for (var i = 0; i < viewsAmount; i++) {
                    viewName = parseJsonFromHtml(resp, "#viewName-" + i);
                    content = parseJsonFromHtml(resp, "#content-" + i, 'notParsed');
                    action = parseJsonFromHtml(resp, "#viewName-" + i, 'getAction');
                    replaceAjaxContent(viewName, content, action, url);
                }
            if(paramsAmount) {
                implementParams(resp, ["#toggleClass", '#setValue', '#setAttr']);
            }
            if(status.location || status.history)
                setTimeout(function () {
                    if(status.location && window.location.pathname !== status.location)
                        window.location = status.location;
                    else if(status.history)
                        window.history.pushState('page2', 'Title', status.history);
                }, status.timeOut ? status.timeOut : 0);
            if(callbackFunction)
                callbackFunction(resp);
        } else
            alertify.error(status.text);
    }).error(function (resp) {
        blockButton($('.ajax_form'), false);
        var errors = $.parseJSON(resp.responseText);
        self.find('*[name=' + Object.keys(errors)[0] + ']').closest('.form-group').addClass('has-error');
        self.find('*[name=' + Object.keys(errors)[0] + ']').focus();
        alertify.error(errors[Object.keys(errors)[0]][0]);
    });
}

function implementParams(resp, arrOfParams) {
    $.each(arrOfParams, function (k, param) {
        var action = parseJsonFromHtml(resp, param);
        if(action) {
            if(param === "#toggleClass")
                $.each(action, function (selector, className) {
                    $(selector).toggleClass(className);
                });
            if(param === "#setValue")
                $.each(action, function (selector, className) {
                    if($(selector).prop("tagName") === 'INPUT')
                        $(selector).val(className);
                    else
                        $(selector).text(className);
                });
            if(param === "#setAttr")
                $.each(action, function (selector, value) {
                    $(selector).attr(value[0], value[1]);
                });
        }
    });
}

function parseJsonFromHtml(html, id, custom) {
    if(!$($.parseHTML(html)).filter(id).length)
        return null;
    var parsed = custom === 'getAction' ? $($.parseHTML(html)).filter(id).attr('data-action') : $($.parseHTML(html)).filter(id).text();

    return custom === 'getAction' || custom === 'notParsed' ? parsed : $.parseJSON(parsed);
}

function replaceAjaxContent(view, content, action) {
    var view_arr = view.split('._');
    var prefix = view_arr[view_arr.length - 1];
    var replaced_block = $('.b-replaced_block_' + prefix);

    if(action === 'append') {
        replaced_block.append(content);
    } else {
        replaced_block.empty();
        replaced_block.html(content);
    }
}

// Block/unblock buttons
function blockButton(obj, status) {
    if(status !== false)
        obj.find('.m-changed_opacity').attr('disabled', 'disabled').css('opacity', 0.5).css('pointer-events', 'none');
    else
        obj.find('.m-changed_opacity').attr('disabled', false).css('opacity', 1).css('pointer-events', 'auto');
}

// clone a JavaScript object
function clone(obj) {
    if (null === obj || "object" !== typeof obj) return obj;
    var copy = obj.constructor();
    for (var attr in obj) {
        if (obj.hasOwnProperty(attr)) copy[attr] = obj[attr];
    }
    return copy;
}

// Hide modal and other windows by outside click
body.click(function() {
    $('.hide_window').hide();
});