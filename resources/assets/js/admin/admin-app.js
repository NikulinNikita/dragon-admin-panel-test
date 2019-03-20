Vue.component('vue-notifications', require('./components/Notifications'));
Vue.component('notification', require('./components/ParentNotification'));
Vue.component('mc-chats', require('./components/McChats/McChats'));
Vue.component('pagination', require('./components/Pagination/Pagination'));

import axios from 'axios';

if (process.env.NODE_ENV === 'development') {
    Vue.config.devtools = true;
}

$(document).on('click', '#admin-logout', (e) => {
    let token = localStorage.getItem('token');

    if (token !== null) {
        e.stopPropagation();

        axios.post(`/api/jwt/${Admin.User.id}/logout`).then((response) => {
            let data = response.data;

            if (data.success) {
                localStorage.removeItem('token');
                $('#admin-logout').click();
            }
        })
            .catch((error) => {
                Admin.Messages.error('Ошибка!', error.message);
            });
    }
});