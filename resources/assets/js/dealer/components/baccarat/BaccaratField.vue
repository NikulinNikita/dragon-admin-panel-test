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
            <div class="row" v-if="bettingTick === null && shuffleTick === null && playersWaitingTick === null">
                <div class="col-12 text-center">
                    <notifications></notifications>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center">
                    <div class="text-center text-primary my-5 text-monospace" v-show="bettingTick !== null">
                        <h2 class="my-0 text-uppercase text-bold" style="font-size: 60px">{{gameState.displayName}}: <span style="width: 90px">{{bettingTick}}</span> сек.</h2>
                    </div>
                    <div class="text-center text-primary my-5 text-monospace" v-show="shuffleTick !== null">
                        <h2 class="my-0 text-uppercase text-bold" style="font-size: 60px">{{gameState.displayName}}: <span style="width: 90px">{{shuffleTick}}</span> сек.</h2>
                    </div>
                    <div class="text-center text-primary my-5 text-monospace" v-show="playersWaitingTick !== null">
                        <h2 class="my-0 text-uppercase text-bold" style="font-size: 60px">{{gameState.displayName}}: <span style="width: 90px">{{playersWaitingTick}}</span> сек.</h2>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <div id="additional__top--section" class="col-4">
                    <div class="row no-gutters">
                        <div class="col-12">
                            <button disabled type="button" class="btn opacity-100 btn-outline-success text-white btn-lg btn-block" :class="{'scores__tie--winner': isWinner('tie')}">Ничья</button>
                        </div>
                    </div>
                </div>
                <div id="banker__top--section" class="col-4">
                    <div class="row no-gutters">
                        <div class="col-12">
                            <button disabled type="button" class="btn opacity-100 btn-outline-danger btn-lg btn-block" :class="[{'scores__banker--winner': isWinner('banker')}]">Банкир<strong v-if="bankerScore !== null">: {{bankerScore}}</strong></button>
                        </div>
                    </div>
                </div>
                <div id="player__top--section" class="col-4">
                    <div class="row no-gutters">
                        <div class="col-12">
                            <button disabled type="button" class="btn opacity-100 btn-outline-info btn-lg btn-block" :class="[{'scores__player--winner': isWinner('player')}]">Игрок<strong v-if="playerScore !== null">: {{playerScore}}</strong></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="game-cards row no-gutters">
                <div id="additional-cards" class="col-4">
                    <div class="row no-gutters">
                        <div class="col-6" v-for="(card, index) in additionalCards">
                            <span class="game-card" :class="{'game-card--clear' : card.code !== null, 'game-card--waiting': isWaitingFor(card) && gameState.name === 'CardPullRequest', 'game-card--got': card.got && !card.revealed, 'game-card--reveal': isWaitingForReveal(card) && gameState.name === 'CardRevealDeclaration'}">
                                <span class="mx-auto">
                                    <img :src="[card.code === null ? cardLink('CARDBACK') : cardLink(card.code)]" alt="card">
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                <div id="dealer-cards" class="col-4">
                    <div class="row no-gutters">
                        <div class="col-6" v-for="(card, index) in bankerCards">
                            <span class="game-card" :class="{'game-card--clear' : card.code !== null, 'game-card--waiting': isWaitingFor(card) && gameState.name === 'CardPullRequest', 'game-card--got': card.got && !card.revealed, 'game-card--reveal': isWaitingForReveal(card) && gameState.name === 'CardRevealDeclaration'}">
                                <span class="mx-auto">
                                    <img :src="[card.code === null ? cardLink('CARDBACK') : cardLink(card.code)]" alt="card">
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                <div id="player-cards" class="col-4 mb-4">
                    <div class="row no-gutters">
                        <div class="col-6" v-for="(card, index) in playerCards">
                            <span class="game-card" :class="{'game-card--clear' : card.code !== null, 'game-card--waiting': isWaitingFor(card) && gameState.name === 'CardPullRequest', 'game-card--got': card.got && !card.revealed, 'game-card--reveal': isWaitingForReveal(card) && gameState.name === 'CardRevealDeclaration'}">
                                <span class="mx-auto">
                                    <img :src="[card.code === null ? cardLink('CARDBACK') : cardLink(card.code)]" alt="card">
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div v-for="(combination, index) in combinations" class="col-6 mb-4">
                    <button disabled type="button" :class="[{'text-dark bg-primary': isWinner(index)}]" class="btn opacity-100 btn-outline-warning text-primary text-bold border-primary btn-block" style="font-size: 30px">{{combination}}</button>
                </div>
            </div>
            <div class="row">
                <div v-for="(combination, index) in dragonCombinations" class="col-6">
                    <button disabled type="button" :class="[{'text-dark bg-primary': isWinner(index)}]" class="btn opacity-100 btn-outline-warning text-primary text-bold border-primary btn-block" style="font-size: 30px">{{combination}}</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from 'vue';
    import {Baccarat} from '../../store/modules/baccarat';
    import BaccaratCard from "../../src/card/BaccaratCard";

    import Notifications from '../core/Notifications.vue';
    import SystemNotifications from "../core/SystemNotifications.vue";

    import {
        namespace, State
    } from 'vuex-class';

    import BaccaratGameState from "../../src/gameState/BaccaratGameState";
    import {Notification} from "../../store/modules/core/CoreTypes";

    const baccarat = namespace('Baccarat');
    const core = namespace('Core');

    @Component({
        components: {
            SystemNotifications,
            Notifications
        }
    })
    export default class BaccaratField extends Vue {
        loading: boolean = false;

        @core.State systemNotification: Notification | null;

        @baccarat.State cards: BaccaratCard[];
        @baccarat.State gameState: BaccaratGameState;
        @baccarat.State waitingFor: BaccaratCard | null;
        @baccarat.State bettingTick: number | null;
        @baccarat.State shuffleTick: number | null;
        @baccarat.State playersWaitingTick: number | null;
        @baccarat.State playerScore: number | null;
        @baccarat.State bankerScore: number | null;

        @baccarat.Getter playerCards: BaccaratCard[];
        @baccarat.Getter bankerCards: BaccaratCard[];
        @baccarat.Getter additionalCards: BaccaratCard[];
        @baccarat.Getter mainCards: BaccaratCard[];

        @baccarat.Getter cardLink: (type: string) => string;
        @baccarat.Getter isWinner: (member: string) => boolean;

        @State('roundId') roundId: number | null;

        dragonCombinations: Object = {
            'banker-dragon': 'Банкир дракон',
            'player-dragon': 'Игрок дракон'
        };

        combinations: Object = {
            'banker-pair': 'Пара банкира',
            'player-pair': 'Пара игрока',
            'big': 'Большее',
            'small': 'Меньшее',
        };

        async created() {
            this.gameState.created();
        }

        isWaitingFor(card: BaccaratCard) {
            return this.waitingFor !== null && card.id === this.waitingFor.id && card.side === this.waitingFor.side && !card.got
        }

        isWaitingForReveal(card: BaccaratCard) {
            return this.waitingFor !== null && card.id === this.waitingFor.id && card.got && card.side === this.waitingFor.side && !card.revealed
        }
    }
</script>

<style lang="scss" scoped>
    $color-player: #737ed2;
    $color-banker: #f33b54;
    $color-tie:    #42b01c;

    $game-card-overlay: #382510;

    $border-radius: 0.31vw;

    .dealer-component {
        font-size: .84vw;
    }

    .scores {
        display: flex;
        justify-content: center;
        margin-bottom: 4.42vw;

        &__player,
        &__banker,
        &__tie {
            margin: 0 2vw;
            padding: 1vw 1.3vw;
            border: 0.15vw solid;
            font-size: 2.6vw;
            text-align: center;
            line-height: 1;
            border-radius: $border-radius;
        }

        &__player,
        &__banker {
            min-width: 19vw;
        }

        &__player {
            &--winner {
                color: #fff;
                background-color: $color-player;
            }
        }

        &__banker {
            &--winner {
                color: #fff;
                background-color: $color-banker;
            }
        }

        &__tie {
            &--winner {
                background-color: $color-tie;
            }
        }
    }

    .combinations {

        &__item {
            margin: 0 2.6vw;
            padding: 1.3vw;
            border-width: 0.15vw !important;
            font-size: 1.3vw;
            text-align: center;
            line-height: 1;

            &:first-child {
                margin-left: 0;
            }

            &:last-child {
                margin-right: 0;
            }
        }
    }

    .game-cards {
        > [class*="col-"]:nth-child(1) [class*="col-"]:nth-child(3) .game-card > span {
            margin-left: 0;
        }

        > [class*="col-"]:nth-child(2) [class*="col-"]:nth-child(1) .game-card > span {
            margin-right: 0;
        }
    }

    .game-card {
        display: block;
        background: transparent;

        > span {
            position: relative;
            display: block;
            width: 100%;
            max-width: 70%;

            &::before {
                position: absolute;
                content: '';
                width: 100%;
                height: 100%;
                top: 0;
                left: 0;
                border-radius: .63vw;
                background-color: rgba($game-card-overlay, .6);
                transition: all .5s ease;
                z-index: 1;
            }

            > img {
                position: relative;
                display: block;
                width: 100%;
                opacity: 1;
                transition: all .5s ease;
                z-index: 0;
            }
        }

        &--horizontal {
            transform: rotate(90deg);
        }

        &--clear,
        &--waiting {

            > span::before {
                background-color: rgba(#000, 0);
            }
        }

        &--reveal > span {
            &::before {
                background-color: rgba(#fff, 1) !important;
            }
        }

        &--got > span {

            &::before {
                background-color: rgba($game-card-overlay, 1);
            }

            > img {
                opacity: 0;
            }

            &::after {
                position: absolute;
                content: '';
                width: 4vw;
                height: 4vw;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-image: url('/img/icons/check-circle.svg');
                background-size: cover;
                z-index: 2;
            }
        }

        &--got--horizontal {
            & > span {
                &::after {
                    transform: rotate(-90deg) translate(50%, -50%);
                }
            }
        }
    }

    button {
        font-size: 50px;
    }
</style>