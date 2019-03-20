export function generateVueUri(uri, params) {
    let query;

    if (params && Object.keys(params).length) {
        let esc = encodeURIComponent;
        query = Object.keys(params).map((k) =>
            params[k] || params[k] === '' ? esc(k) + '=' + esc(params[k]) : null
        ).join('&');
    }
    query = query ? `?${query}` : ``;
    uri = uri.replace('/undefined', '/');
    uri = uri.replace('/null', '/');
    uri = `/admin_panel/vue/${uri}${query}`;

    return uri;
}

export function scrollToContainer(componentData, con) {
    let container = componentData.$el.querySelector(con ? con : "#scrollToContainer");
    container.scrollTop = container.scrollHeight;
}

export function isset(base, variable, setIfNotIsset = 'null', showConsoleLog = false) {
    let isset = true, path;
    if (typeof base !== 'undefined' && typeof variable !== 'undefined') {
        path = variable.split('.');
        if (path.length) {
            for (let i in path) {
                if (path.hasOwnProperty(i) && typeof base[path[i]] === 'undefined') {
                    if (showConsoleLog)
                        console.log('undefined variable: "' + path[i] + '"');
                    isset = false;
                    break
                }
                else {
                    base = base[path[i]];
                }
            }
        }
    }

    return isset && setIfNotIsset !== 'null' ? base : (isset ? isset : (setIfNotIsset !== 'null' ? setIfNotIsset : false));
}