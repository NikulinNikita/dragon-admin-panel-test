<template>
    <li class="dropdown">
        <ul class="nav navbar-nav">
            <notifications v-for="link in links" :key="link.type" :notifications="notifications[link.type]" :type="link.type" :icon="link.icon"></notifications>
        </ul>
    </li>
</template>

<script>
    import Notifications from './Notifications';

    import {Echo} from 'laravel-echo-sc'

    import Helpers from '../src/Helpers';

    import {Howl} from 'howler';

    import Vue from 'vue';

    export default {
        data() {
            return {
                links: [
                    {
                        type: 'danger',
                        icon: 'fa-flag-o'
                    },
                    {
                        type: 'warning',
                        icon: 'fa-bell-o'
                    },
                    {
                        type: 'success',
                        icon: 'fa-envelope-o'
                    }
                ],
                connection: null,
                notifications: {
                    success: [],
                    danger: [],
                    warning: []
                }
            }
        },
        async created() {
            if(this.connection === null) await this.connect();

            this.fetch();
        },
        components: {Notifications},
        methods: {
            async connect() {
                let token = document.head.querySelector('meta[name="csrf-token"]');

                let jwtToken = localStorage.getItem('token');

                let userId = Admin.User.id;

                if(jwtToken === null) {
                    jwtToken = await axios.get(`/api/jwt/${userId}/login`).then((response) => {
                        let data = response.data;

                        if (data.success) {
                            localStorage.setItem('token', data.token);
                            return data.token;
                        }
                    })
                    .catch((error) => {
                        Admin.Messages.error('Ошибка!', error.message);
                        localStorage.removeItem('token');
                        return null;
                    });
                }
                
                if(token)
                    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;

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
                                    'Accept': 'application/json',
                                    'Authorization': 'Bearer ' + jwtToken
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

                const sound = new Howl({
                    src: ['/sounds/Notification.wav']
                });

                if(BROADCAST_DRIVER === 'amqp') {
                    this.connection = new Echo(options);

                    this.connection.private('App.Models.Staff.' + userId)
                        .notification((notification) => {
                            this.notifications[notification.style].push(notification);

                            if(notification.sound) {
                                sound.play();
                            }

                            BrowserNotification.notify(notification.title, notification.message);
                        });
                }
            },
            fetch() {
                axios.get('/admin_panel/user/notifications').then((data) => {
                    this.notifications = data.data.notifications;

                    if(!this.notifications.hasOwnProperty('success')) {
                        this.$set(this.notifications, 'success', []);
                    }
                    
                    if(!this.notifications.hasOwnProperty('warning')) {
                        this.$set(this.notifications, 'warning', []);
                    }

                    if(!this.notifications.hasOwnProperty('danger')) {
                        this.$set(this.notifications, 'danger', []);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
            },
        }
    }
</script>

<style scoped>

</style>