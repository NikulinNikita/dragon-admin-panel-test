import Vue from 'vue';
import Vuex from 'vuex';
import {Baccarat} from './modules/baccarat';
import {User} from './modules/user';
import {RootState} from './types';
import {Echo} from "laravel-echo-sc";
import axios, {AxiosInstance} from 'axios';
import {Core} from "./modules/core";
import {Roulette} from "./modules/roulette";

Vue.use(Vuex);

declare var ENV: string;
declare var DEBUG: boolean;
declare var SOCKET_ORIGIN: boolean;

export default new Vuex.Store<RootState>({
    state: {
        socket: null,
        axios: axios.create(),
        tableId: null,
        game: null,
        roundId: null,
        logoutOn: false
    },
    modules: {
        Baccarat,
        Roulette,
        User,
        Core
    },
    mutations: {
        CONNECT_SOCKET(state, connection: Echo) {
            state.socket = connection;
        },
        CONFIGURE_AXIOS(state, instance: AxiosInstance) {
            state.axios = instance;
        },
        AXIOS_SET_HEADER(state, header: any) {
            state.axios.defaults.headers.common[header.name] = header.value;
        },
        SET_TABLE_ID(state, id: number | null) {
            state.tableId = id;
        },
        SET_ROUND_ID(state, id: number | null) {
            state.roundId = id;
        },
        SET_GAME(state, game: string | null) {
            state.game = game;
        },
        SET_LOGOUT(state, logout: boolean) {
            state.logoutOn = logout;
        }
    },
    actions: {
        async loadConfig({dispatch, rootState, state}) {
            await dispatch('loadTable');

            await dispatch('configureAxios');

            let session: Object | null = await state.axios.get('/dealer/getActiveSession/'+state.tableId).then((response) => response.data.activeSession).catch((response) => {
                console.error(response);
                return null;
            });

            if(session !== null) {
                let game = session['table']['game']['slug'];

                let rounds: object[] = session[`${game}_rounds`];

                if (rounds.length > 0) {
                    let lastRound: object = rounds[rounds.length - 1];

                    if (lastRound['status'] !== 'finished') {
                        dispatch('setRoundId', lastRound['id']);
                    }
                }
            }

            await dispatch('loadJwtToken', session);

            await dispatch('connectSocket');

            if(rootState['User'].id === null) {
                if(!session) {
                    await dispatch('User/listenToDealerLogin', {root: true});
                }
                else {
                    await dispatch('User/dealerLogin', {dealer: {id: session['staff_id']}, table: session['table']});
                }
            }
        },
        async setGame({commit}, game: string | null) {
            commit('SET_GAME', game);
        },
        async loadTable({commit}) {
            let uriArr: Array<string> = location.pathname.substr(1).split('/');

            if (uriArr.length === 3 && uriArr.includes('table')) {
                let tableNumber: number = parseInt(uriArr[uriArr.length - 1]);

                commit('SET_TABLE_ID', tableNumber);

                return tableNumber;
            }

            return 0;
        },
        async loadJwtToken({commit, state, dispatch, rootState}, session: Object | null) {
            if(!session) {
                localStorage.removeItem('token');
            }

            let jwtToken: string | null = localStorage.getItem('token');
            console.log(`jwtToken = ${jwtToken} User id: ${rootState['User'].id} Session: ${session}`)
            if(jwtToken !== null && rootState['User'].id === null && !session) {
                commit('AXIOS_SET_HEADER', {name: 'Authorization', value: `Bearer ${jwtToken}`});

                let data = await dispatch('User/load', null, {root: true});

                if(data.hasOwnProperty('user')) {
                    dispatch('User/setAuthToken', jwtToken, {root: true});
                }
                else {
                    dispatch('User/setAuthToken', null, {root: true});
                }
            }
            return jwtToken;
        },
        async configureAxios({commit, dispatch}) {
            let headers: Object = {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            };

            let axiosApiInstance: AxiosInstance = axios.create({
                baseURL: `${location.origin}/api/`,
                timeout: 10000,
                headers
            });

            commit('CONFIGURE_AXIOS', axiosApiInstance);
        },
        async connectSocket({commit, rootState, state}) {
            if(state.socket !== undefined && state.socket !== null && rootState['User'].token === null) await state.socket.disconnect();

            let token: any = document.head.querySelector('meta[name="csrf-token"]');
            let options: Object = {
                location: window.location.origin,
                csrfToken: token.content,
                debug: DEBUG,
                port: 26001,
                autoReconnect: true,
                broadcaster: 'socketcluster',
                auth:
                    {
                        headers:
                            {
                                'Accept': 'application/json',
                                'Authorization': 'Bearer ' + rootState['User'].token
                            }
                    }
            };

            let mergeOptions: Object;

            if (ENV === 'local') {
                mergeOptions = {secure: false, hostname: 'localhost'};
            }
            else {
                mergeOptions = {secure: true, hostname: SOCKET_ORIGIN};
            }

            options = Object.assign(options, mergeOptions);

            let connection: Echo = new Echo(options);
            connection.connector.onConnectAbort(error => {
                window.location.reload(false);
            });
            connection.connector.onClose(error => {
                console.log(error);
            });
            commit('CONNECT_SOCKET', connection);
        },
        async setRoundId({commit}, roundId: number | null) {
            commit('SET_ROUND_ID', roundId);
        },
        async setLogout({commit}, logout: boolean) {
            commit('SET_LOGOUT', logout);
        }
    }
});