<template>
    <div class="container h-100">
        <div class="row align-items-center h-100">

            <div class="col align-self-center">
                <div class="jumbotron jumbotron-fluid text-center">
                    <div class="container">
                        <h1 class="display-4">Добро пожаловать за стол №{{$store.state.tableId}}</h1>
                        <h2 v-if="game">Это стол <span v-if="game === 'baccarat'">баккары</span><span v-else-if="game === 'roulette'">рулетки</span></h2>
                        <p class="lead">Для авторизации приложите Вашу карту к NFC считывателю</p>
                        <i class="fas fa-id-card-alt fa-4x"></i>

                        <system-notifications></system-notifications>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
    import Vue from 'vue';

    import Component from "vue-class-component";

    import {Watch} from 'vue-property-decorator'

    import SystemNotifications from "../core/SystemNotifications.vue";

    import {
        Action,
        namespace, State
    } from 'vuex-class';

    const user = namespace('User');
    const core = namespace('Core');

    @Component({
        components: {
            SystemNotifications
        }
    })
    export default class Home extends Vue {
        @user.Action dealerLogin: Function;
        @user.State token: string | null;
        @user.State id: number | null;

        @core.State systemNotification: Notification | null;

        @State('game') game: string | null;
        @State('tableId') tableId: number | null;

        @Action loadConfig: Function;
        @Action setRoundId: Function;

        async created() {
            await this.loadConfig();
        }

        @Watch('game')
        onGameChanged(value: string | null) {
            if (value !== null) {
                if(value === 'baccarat') {
                    this.$router.push({name: 'BaccaratField'});
                }
                else if(value === 'roulette') {
                    this.$router.push({name: 'RouletteField'});
                }
            }
        }
    }
</script>

<style scoped>
    .jumbotron {
        background-color: rgba(0,0,0,.4)
    }
</style>