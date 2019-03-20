import { Module } from 'vuex';
import { BaccaratState } from './BaccaratTypes';

import { actions } from './BaccaratActions';
import { getters } from './BaccaratGetters';
import { mutations } from './BaccaratMutations';
import { RootState } from '../../types';
import BaccaratCard from "../../../src/card/BaccaratCard";
import BaccaratGameState from "../../../src/gameState/BaccaratGameState";
import GameStateStorage from "../../../src/gameState/GameStateStorage";

export const state: BaccaratState = {
    cards: [
        new BaccaratCard({id: 1, additional: false, side: 'banker', got: false, code: null, revealed: false}),
        new BaccaratCard({id: 2, additional: false, side: 'banker', got: false, code: null, revealed: false}),
        new BaccaratCard({id: 3, additional: true, side: 'banker', got: false, code: null, revealed: false}),
        new BaccaratCard({id: 1, additional: false, side: 'player', got: false, code: null, revealed: false}),
        new BaccaratCard({id: 2, additional: false, side: 'player', got: false, code: null, revealed: false}),
        new BaccaratCard({id: 3, additional: true, side: 'player', got: false, code: null, revealed: false})
    ],
    gameState: new BaccaratGameState({name: 'pending', displayName: 'Идет получение текущей стадии игры'}),
    waitingFor: null,
    gameStateStorage: new GameStateStorage(),
    bettingTick: null,
    shuffleTick: null,
    playersWaitingTick: null,
    winners: [],
    paused: false,
    states: [
        {name: 'GamePlay.WaitingPlayersStart', displayName: 'Ожидание'},
        {name: 'GamePlay.WaitingPlayersTick', displayName: 'Идёт ожидание'},
        {name: 'GamePlay.WaitingPlayersStop', displayName: 'Окончание ожидания'},
        {name: 'GamePlay.RoundStart', displayName: 'Начало раунда'},
        {name: 'GamePlay.InterruptionTick', displayName: 'Игра приостановлена'},
        {name: 'GamePlay.Baccarat.BetAcceptionStart', displayName: 'Начало раздачи'},
        {name: 'GamePlay.Baccarat.BetAcceptionTick', displayName: 'До начала раздачи'},
        {name: 'GamePlay.Baccarat.BetAcceptionStop', displayName: 'Окончание раздачи'},
        {name: 'GamePlay.Baccarat.CardPullStart', displayName: 'Начало приема карт'},
        {name: 'GamePlay.Baccarat.CardPullRequest', displayName: 'Запрос на прием карты'},
        {name: 'GamePlay.Baccarat.CardPullCapture', displayName: 'Прием карты'},
        {name: 'GamePlay.Baccarat.CardPullFinish', displayName: 'Окончание приема карт'},
        {name: 'GamePlay.Baccarat.CardRevealStart', displayName: 'Начало вскрытия карт'},
        {name: 'GamePlay.Baccarat.CardRevealDeclaration', displayName: 'Начало вскрытия карты'},
        {name: 'GamePlay.Baccarat.CardRevealBroadcast', displayName: 'Вскрытие карты'},
        {name: 'GamePlay.Baccarat.CardRevealFinish', displayName: 'Окончание вскрытия карт'},
        {name: 'GamePlay.Baccarat.RoundResult', displayName: 'Результаты раунда'},
        {name: 'GamePlay.RoundFinish', displayName: 'Окончание раунда'},
        {name: 'GamePlay.Baccarat.ShuffleStart', displayName: 'Начало перемешивания'},
        {name: 'GamePlay.Baccarat.ShuffleCardRequest', displayName: 'Достаньте карту из shoe'},
        {name: 'GamePlay.Baccarat.ShuffleStop', displayName: 'Окончание перемешивания'},
        {name: 'GamePlay.SessionStop', displayName: 'Окончание игровой сессии'},
        {name: 'GamePlay.SessionInvalidInput', displayName: 'Некорректная сессия'},
        {name: 'GamePlay.SessionLogOutToggle', displayName: 'В конце раунда текущая сессия будет завершена'}
    ],
    playerScore: null,
    bankerScore: null
};

const namespaced: boolean = true;

export const Baccarat: Module<BaccaratState, RootState> = {
    namespaced,
    state,
    getters,
    actions,
    mutations,
};