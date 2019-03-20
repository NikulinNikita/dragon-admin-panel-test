import { Module } from 'vuex';
import {RouletteState} from './RouletteTypes';

import { actions } from './RouletteActions';
import { getters } from './RouletteGetters';
import { mutations } from './RouletteMutations';
import { RootState } from '../../types';
import GameStateStorage from "../../../src/gameState/GameStateStorage";
import RouletteGameState from "../../../src/gameState/RouletteGameState";

export const state: RouletteState = {
    gameState: new RouletteGameState({name: 'pending', displayName: 'Идет получение текущей стадии игры'}),
    gameStateStorage: new GameStateStorage(),
    bettingTick: null,
    spinTick: null,
    playersWaitingTick: null,
    winnerCell: null,
    paused: false,
    states: [
        {name: 'GamePlay.WaitingPlayersStart', displayName: 'Ожидание'},
        {name: 'GamePlay.WaitingPlayersTick', displayName: 'Идёт ожидание'},
        {name: 'GamePlay.WaitingPlayersStop', displayName: 'Окончание ожидания'},
        {name: 'GamePlay.RoundStart', displayName: 'Начало раунда'},
        {name: 'GamePlay.InterruptionTick', displayName: 'Игра приостановлена'},
        {name: 'GamePlay.Roulette.BetAcceptionStart', displayName: 'Начало приема ставок'},
        {name: 'GamePlay.Roulette.BetAcceptionTick', displayName: 'До окончания приема ставок'},
        {name: 'GamePlay.Roulette.BetAcceptionStop', displayName: 'Окончание приема ставок'},
        {name: 'GamePlay.Roulette.SpinStart', displayName: 'Начало спина'},
        {name: 'GamePlay.Roulette.SpinTick', displayName: 'Длительность спина'},
        {name: 'GamePlay.Roulette.RoundResult', displayName: 'Результаты раунда'},
        {name: 'GamePlay.RoundFinish', displayName: 'Окончание раунда'},
        {name: 'GamePlay.SessionStop', displayName: 'Окончание игровой сессии'},
        {name: 'GamePlay.SessionInvalidInput', displayName: 'Некорректная сессия'},
        {name: 'GamePlay.SessionLogOutToggle', displayName: 'В конце раунда текущая сессия будет завершена'}
    ]
};

const namespaced: boolean = true;

export const Roulette: Module<RouletteState, RootState> = {
    namespaced,
    state,
    getters,
    actions,
    mutations,
};