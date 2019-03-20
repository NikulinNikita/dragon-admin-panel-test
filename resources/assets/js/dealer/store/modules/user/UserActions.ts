import {ActionTree} from 'vuex';
import {RootState} from "../../types";
import {UserState} from "./UserTypes";
import store from "../../index";

export const actions: ActionTree<UserState, RootState> = {
    async load({commit, dispatch, state, rootState}) {
        let data: any = await rootState.axios.get('/user')
            .then((response: any) => {
                let data = response.data;
                dispatch('setId', data.user.id);
                dispatch('setName', data.user.name);
                dispatch('setAvatar', data.avatar);
                return data;
            })
            .catch((error: any) => {
                return error.response;
            });

        return data;
    },

    setId({commit}, id: number | null) {
        commit('SET_ID', id);
    },

    setName({commit}, name: string | null) {
        commit('SET_NAME', name);
    },

    setAvatar({commit}, avatar: string | null) {
        commit('SET_AVATAR', avatar);
    },

    async listenToDealerLogin({state, rootState, dispatch}) {
        const CODE_UID_IS_UNKNOWN = 1;
        const CODE_DEALER_IS_INACTIVE = 2;

        rootState.socket.channel('table.' + rootState.tableId)
            .listen('GamePlay.SessionStart', async (data: any) => {
                if (data.table.id === rootState.tableId) {
                    await dispatch('dealerLogin', data);
                }
            })
            .listen('GamePlay.SessionInvalidInput', async (data: any) => {
                if (data.table.id === rootState.tableId) {
                    let code: number = data.code;

                    let message: string | undefined = undefined;

                    switch (code) {
                        case CODE_UID_IS_UNKNOWN: message = 'Приложите карту ещё раз'; break;
                        case CODE_DEALER_IS_INACTIVE: message = 'Данная карта не активирована. Обратитесь к своему менеджеру'; break;
                        default: break;
                    }

                    if(message) {
                        dispatch('Core/sendSystemNotification', {
                            title: 'Ошибка!',
                            message,
                            type: 'danger'
                        }, {root: true});

                        setTimeout(() => {
                            dispatch('Core/sendSystemNotification', null, {root: true});
                        }, 3000)
                    }
                }
            });
    },

    async dealerLogin({state, rootState, dispatch}, data: any) {
        console.log(data);
        if(state.id === null) {
            rootState.axios.get(`/jwt/${data.dealer.id}/login`).then(async (response: any) => {
                response = response.data;

                if (response.success) {
                    rootState.axios.defaults.headers.common['Authorization'] = `Bearer ${response.token}`;

                    await dispatch('setAuthToken', response.token);

                    await dispatch('load');

                    if(data.hasOwnProperty('table') && data.table.hasOwnProperty('game')) {
                        dispatch('setGame', data.table.game.slug, {root: true});
                    }
                }
            });
        }
        else {
            console.log('Already logged in. Ignoring...');
        }
    },

    async setAuthToken({commit, dispatch}, token: string | null) {
        commit('SET_TOKEN', token);
    }
};