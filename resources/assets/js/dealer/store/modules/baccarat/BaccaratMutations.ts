import { MutationTree } from 'vuex';
import {BaccaratState} from './BaccaratTypes';
import BaccaratGameState from "../../../src/gameState/BaccaratGameState";
import BaccaratCard from "../../../src/card/BaccaratCard";

export const mutations: MutationTree<BaccaratState> = {
    LOAD_CARD(state, card: BaccaratCard) {
        state.cards.push(card);
    },
    SET_CARDS(state, cards: BaccaratCard[]) {
        state.cards = cards;
    },
    SET_PAUSE(state, pause: boolean) {
        state.paused = pause;
    },
    GOT_CARD(state, card: BaccaratCard) {
        let index: number | undefined = state.cards.findIndex((currentCard: BaccaratCard) => { return currentCard.id === card.id && currentCard.side === card.side; });
        if(index !== undefined && index !== -1) {
            state.cards.splice(index, 1, card);
        }
    },
    UPDATE_CARDS(state, options: Object) {
        let option: string;

        let tempCards: BaccaratCard[] = [];

        state.cards.forEach((card: BaccaratCard) => {
            for(option in options) {
                if(card.hasOwnProperty(option)) {
                    if(card.additional) {
                        if(card.got && card.revealed) {
                            card.got = false;
                            card.revealed = false;
                            card.code = null;
                        }
                    }
                    else {
                        card[option] = options[option];
                    }
                }
            }

            tempCards.push(card);
        });

        state.cards = tempCards;
    },
    UPDATE_CARD(state, card: BaccaratCard) {
        let index: number = state.cards.findIndex(stateCard => stateCard.id === card.id && stateCard.side === card.side);

        if(index > -1) {
            state.cards.splice(index, 1, card);
        }
    },
    WAIT_FOR_CARD(state, card: BaccaratCard | null) {
        state.waitingFor = card;
    },
    SET_BETTING_TICK(state, tick: number | null) {
        state.bettingTick = tick;
    },
    SET_SHUFFLE_TICK(state, tick: number | null) {
        state.shuffleTick = tick;
    },
    SET_PLAYERS_WAITING_TICK(state, tick: number | null) {
        state.playersWaitingTick = tick;
    },
    SET_WINNERS(state, winners: Array<string>) {
        state.winners = winners;
    },
    SET_PLAYER_SCORE(state, score: number | null) {
        state.playerScore = score;
    },
    SET_BANKER_SCORE(state, score: number | null) {
        state.bankerScore = score;
    }
};