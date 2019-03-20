import { MutationTree } from 'vuex';
import {RouletteState} from './RouletteTypes';

export const mutations: MutationTree<RouletteState> = {
    SET_PAUSE(state, pause: boolean) {
        state.paused = pause;
    },
    SET_BETTING_TICK(state, tick: number | null) {
        state.bettingTick = tick;
    },
    SET_SPIN_TICK(state, tick: number | null) {
        state.spinTick = tick;
    },
    SET_PLAYERS_WAITING_TICK(state, tick: number | null) {
        state.playersWaitingTick = tick;
    },
    SET_WINNER_CELL(state, winnerCell: object | null) {
        state.winnerCell = winnerCell;
    }
};