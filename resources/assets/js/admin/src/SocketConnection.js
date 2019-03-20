import {Echo} from 'laravel-echo-sc'
import Helpers from './Helpers';

export function initSocketConnection(componentData) {
    let token = document.head.querySelector('meta[name="csrf-token"]');

    let options = {
        location: window.location.origin,
        debug: DEBUG,
        csrfToken: token.content,
        port: 26001,
        autoReconnect: true,
        broadcaster: 'socketcluster',
        auth:
            {
                headers:
                    {
                        'Accept': 'application/json'
                    }
            }
    };

    let mergeOptions;

    if(ENV === 'local') {
        mergeOptions = {secure: false, hostname: 'localhost'};
    }
    else {
        let hostname = 'ws.' + Helpers.getRootHost(location.host);

        mergeOptions = {secure: true, hostname};
    }

    options = Object.assign(options, mergeOptions);

    if(BROADCAST_DRIVER === 'amqp') {
        componentData.connection = new Echo(options);
    }
    else {
        console.error('You do need to set your "BROADCAST_DRIVER" to the "AQMP"');
    }
}

