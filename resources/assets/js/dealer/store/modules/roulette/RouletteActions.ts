import { ActionTree } from 'vuex';
import {RouletteState} from './RouletteTypes';
import { RootState } from '../../types';

import RouletteGameState from "../../../src/gameState/RouletteGameState";

export const actions: ActionTree<RouletteState, RootState> = {
    async setPause({commit}, value: boolean) {
        commit('SET_PAUSE', value);
    },
    async stateListener({state, rootState, dispatch}) {
        let gameState: RouletteGameState;

        let connection = rootState.socket.private('dealer-table.'+rootState.tableId);

        state.states.forEach((state: any) => {
            connection.listen(state.name, async (response: any) => {
                let stateNameArray: Array<string> = state.name.split('.');
                let stateName: string = stateNameArray[stateNameArray.length-1];
                console.log(`Changing state to the: "${stateName}"`);
                gameState = new RouletteGameState({name: stateName, displayName: state.displayName, data: response});
                await dispatch('setGameState', gameState);
            });
        });
    },
    async stopTableListener({state, rootState}) {
        rootState.socket.private('dealer-table.'+rootState.tableId).unwatch();
    },
    async cardListener({dispatch}) {
        await dispatch('currentCards');
    },
    async setGameState({state}, gameState: RouletteGameState) {
        await state.gameState.change(gameState);
    },
    setBettingTick({commit}, tick: number | null) {
        commit('SET_BETTING_TICK', tick);
    },
    setSpinTick({commit}, tick: number | null) {
        commit('SET_SPIN_TICK', tick);
    },
    setPlayersWaitingTick({commit}, tick: number | null) {
        commit('SET_PLAYERS_WAITING_TICK', tick);
    },
    setWinnerCell({commit}, winnerCell: object | null) {
        commit('SET_WINNER_CELL', winnerCell);
    },
    async resetState({dispatch}) {
        await dispatch('setGameState', new RouletteGameState({name: 'pending', displayName: 'Идет получение текущей стадии игры'}));
        await dispatch('setBettingTick', null);
        await dispatch('setSpinTick', null);
        await dispatch('setPlayersWaitingTick', null);
        await dispatch('setWinnerCell', null);
    }
};