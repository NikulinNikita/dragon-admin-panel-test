<template>
    <div class="dealer-component d-flex align-content-center justify-content-center h-100">
        <div class="w-100">
            <div class="row">
                <div v-if="systemNotification === null" class="col-12 text-center">
                    <div class="text-center text-primary my-5 text-monospace">
                        <h2 class="my-0 text-uppercase text-bold" style="font-size: 60px">Раунд<span style="width: 90px" v-if="roundId">: {{roundId}}</span></h2>
                    </div>
                </div>
                <div v-else class="col-12 text-center">
                    <system-notifications></system-notifications>
                </div>
            </div>
            <div class="row" v-if="playersWaitingTick === null">
                <div class="col-12 text-center">
                    <notifications></notifications>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <div class="text-center text-primary my-5 text-monospace" v-show="playersWaitingTick !== null">
                        <h2 class="my-0 text-uppercase text-bold" style="font-size: 60px">{{gameState.displayName}}: <span style="width: 90px">{{playersWaitingTick}}</span> сек.</h2>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-4">
                    <div class="row no-gutters">
                        <div class="col-12">
                            <button disabled type="button" class="btn opacity-100 text-white btn-lg btn-block" :class="{'btn-outline-success': bettingTick === null, 'btn-success': bettingTick}">
                                До начала спина
                                <br />
                                <span v-if="bettingTick !== null">{{bettingTick}} сек.</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="row no-gutters">
                        <div class="col-12">
                            <button disabled type="button" class="btn opacity-100 btn-lg btn-block" :class="{'btn-outline-danger': !spinTick, 'btn-danger': spinTick}">
                                Спин
                                <br />
                                <span v-if="spinTick !== null">{{spinTick}} сек.</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="row no-gutters">
                        <div class="col-12">
                            <button disabled type="button" class="btn opacity-100 btn-lg btn-block" :class="{'btn-outline-warning': winnerCell === null, 'btn-danger': winnerCell !== null && winnerCell.color === 'red', 'btn-dark': winnerCell !== null && winnerCell.color === 'black', 'btn-success': winnerCell !== null && winnerCell.color === 'green'}">
                                Результат
                                <br />
                                <span v-if="winnerCell !== null">{{winnerCell.value}}</span>
                                <span v-if="winnerCell !== null">
                                    <template v-if="winnerCell.value === 0">
                                        zero
                                    </template>
                                    <template v-else-if="winnerCell.value % 2 === 0">
                                        even
                                    </template>
                                    <template v-else>odd</template>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from 'vue';

    import Notifications from '../core/Notifications.vue';
    import SystemNotifications from "../core/SystemNotifications.vue";

    import {
        namespace, State
    } from 'vuex-class';

    import RouletteGameState from "../../src/gameState/RouletteGameState";

    const roulette = namespace('Roulette');
    const core = namespace('Core');

    @Component({
        components: {
            Notifications,
            SystemNotifications
        }
    })
    export default class BaccaratField extends Vue {
        loading: boolean = false;

        @core.State systemNotification: Notification | null;

        @roulette.State gameState: RouletteGameState;
        @roulette.State bettingTick: number | null;
        @roulette.State spinTick: number | null;
        @roulette.State winnerCell: object | null;
        @roulette.State playersWaitingTick: number | null;

        @State('roundId') roundId: number | null;

        async created() {
            this.gameState.created();
        }
    }
</script>

<style lang="scss" scoped>
    $border-radius: 0.31vw;

    button {
        font-size: 80px;
        height: 30vw;
    }
</style>