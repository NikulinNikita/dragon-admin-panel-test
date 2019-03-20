export default class Helpers {
    static localStorageAvailable() {
        let test = 'test';
        try {
            localStorage.setItem(test, test);
            localStorage.removeItem(test);
            return true;
        } catch(e) {
            return false;
        }
    }

    static extractHostname(url) {
        let hostname;
        //find & remove protocol (http, ftp, etc.) and get hostname

        if (url.indexOf("//") > -1) {
            hostname = url.split('/')[2];
        }
        else {
            hostname = url.split('/')[0];
        }

        //find & remove "?"
        hostname = hostname.split('?')[0];

        return hostname;
    }

    static extractRootDomain(url) {
        let domain = Helpers.extractHostname(url),
            splitArr = domain.split('.'),
            arrLen = splitArr.length;

        //extracting the root domain here
        //if there is a subdomain
        if (arrLen > 2) {
            domain = splitArr[arrLen - 2] + '.' + splitArr[arrLen - 1];

            //check to see if it's using a Country Code Top Level Domain (ccTLD) (i.e. ".me.uk")
            if (splitArr.length > 3) {
                //this is using a ccTLD
                domain = splitArr[arrLen - 3] + '.' + domain;
            }
        }

        return domain;
    }

    static getRootHost(host) {
        let root = null;

        if(!SOCKET_ORIGIN) {
            root = this.extractRootDomain(host);
        }
        else {
            root = SOCKET_ORIGIN;
        }

        return root;
    }
}