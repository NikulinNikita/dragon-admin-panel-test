import { ActionTree } from 'vuex';
import axios from 'axios';
import {BaccaratState} from './BaccaratTypes';
import { RootState } from '../../types';

import BaccaratGameState from "../../../src/gameState/BaccaratGameState";
import BaccaratCard from "../../../src/card/BaccaratCard";

export const actions: ActionTree<BaccaratState, RootState> = {
    async fetch({ commit }) {
        await axios.get('/cards').then((response: any) => {
            return response.data;
        }, (error: any) => {
            return error;
        });
    },
    async setPause({commit}, value: boolean) {
        commit('SET_PAUSE', value);
    },
    async stateListener({state, rootState, dispatch, commit}) {
        let gameState: BaccaratGameState;

        let connection = rootState.socket.private('dealer-table.'+rootState.tableId);

        state.states.forEach((state: any) => {
            connection.listen(state.name, async (response: any) => {
                let stateNameArray: Array<string> = state.name.split('.');
                let stateName: string = stateNameArray[stateNameArray.length-1];
                console.log(`Changing state to the: "${stateName}"`);
                gameState = new BaccaratGameState({name: stateName, displayName: state.displayName, data: response});
                await dispatch('setGameState', gameState);
            });
        });
    },
    async stopTableListener({state, rootState}) {
        rootState.socket.leave('dealer-table.'+rootState.tableId);
    },
    async cardListener({dispatch}) {
        await dispatch('currentCards');
    },
    async setGameState({state}, gameState: BaccaratGameState) {
        await state.gameState.change(gameState);
    },
    resetCards({commit}) {
        commit('UPDATE_CARDS', {got: false, code: null, revealed: false});
    },
    updateCard({commit}, card: BaccaratCard) {
        commit('UPDATE_CARD', card);
    },
    gotCard({commit}, card: BaccaratCard) {
        commit('GOT_CARD', card);
    },
    waitForCard({commit}, card: BaccaratCard | null) {
        commit('WAIT_FOR_CARD', card);
    },
    setBettingTick({commit}, tick: number | null) {
        commit('SET_BETTING_TICK', tick);
    },
    setShuffleTick({commit}, tick: number | null) {
        commit('SET_SHUFFLE_TICK', tick);
    },
    setPlayersWaitingTick({commit}, tick: number | null) {
        commit('SET_PLAYERS_WAITING_TICK', tick);
    },
    setWinners({commit}, winners: Array<string>) {
        commit('SET_WINNERS', winners);
    },
    setPlayerScore({commit}, score: number | null) {
        commit('SET_PLAYER_SCORE', score);
    },
    setBankerScore({commit}, score: number | null) {
        commit('SET_BANKER_SCORE', score);
    },
    async resetState({dispatch}) {
        await dispatch('setGameState', new BaccaratGameState({name: 'pending', displayName: 'Идет получение текущей стадии игры'}));
        await dispatch('setBettingTick', null);
        await dispatch('setShuffleTick', null);
        await dispatch('setPlayersWaitingTick', null);
        await dispatch('setPlayerScore', null);
        await dispatch('setBankerScore', null);
        await dispatch('setWinners', []);
        await dispatch('waitForCard', null);
    }
};