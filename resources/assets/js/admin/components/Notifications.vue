<!-- src/components/Notifications.vue -->

<template>
    <li class="dropdown messages-menu">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <i class="fa" :class="[icon]"></i>
            <span v-if="length > 0" class="label" :class="['label-'+type]">{{length}}</span>
        </a>
        <ul class="dropdown-menu">
            <li class="header">You have {{length}} messages of this type</li>
            <li>
                <ul class="menu">
                    <li v-for="(notification, index) in notifications.slice(0, 5)">
                        <a @click.prevent="read(notification, index)" href="#">
                            <h4>
                                {{notification.title}}
                                <small><i class="fa fa-clock-o"></i> {{notification.time}}</small>
                            </h4>
                            <p>{{notification.message}}</p>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="footer"><a href="/admin_panel/notifications">See All Messages</a></li>
            <li class="footer"><a href="/admin_panel/user/notifications/readAll" style="background-color:#f9b69d;">Read All Messages</a></li>
        </ul>
    </li>
</template>

<script>
    import axios from 'axios';

    export default {
        props: {
            type: {
                type: String,
                default: 'success'
            },
            icon: {
                type: String,
                default: 'fa-envelope-o'
            },
            notifications: {
                type: Array,
                default: []
            }
        },

        data() {
            return {
                number: null
            }
        },

        computed: {
            length() {
                return this.notifications.length;
            }
        },

        created() {
            this.userId = Admin.User.id;
        },

        methods: {
            read(notification, index) {
                if (notification.hasOwnProperty('id')) {
                    axios.patch(`/admin_panel/user/notifications/${this.userId}/read/${notification.id}`)
                        .then((response) => {
                            if (response.data.success === true) {
                                this.notifications.splice(index, 1);

                                if (notification.link !== '#') {
                                    window.location.href = notification.link;
                                }
                            }
                        })
                        .catch((error) => {
                            Admin.Messages.message('Error!', error.message, 'danger');
                        });
                }
                else {
                    this.notifications.splice(index, 1);

                    if (notification.link !== '#') {
                        window.location.href = notification.link;
                    }
                }
            },
            link(notification) {
                return notification.hasOwnProperty('link') ? notification.link : '#';
            }
        }
    };
</script>

<style scoped>
    .navbar-nav > .messages-menu > .dropdown-menu > li .menu > li > a > h4 {
        margin-left: 0;
    }

    .navbar-nav > .messages-menu > .dropdown-menu > li .menu > li > a > p {
        margin-left: 0;
        white-space: normal;
    }

    .navbar-nav > .messages-menu > .dropdown-menu > li .menu > li > a > h4 > small {
        top: -9px;
    }
</style>