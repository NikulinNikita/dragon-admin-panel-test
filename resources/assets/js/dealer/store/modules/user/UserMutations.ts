import { MutationTree } from 'vuex';
import {UserState} from "./UserTypes";

export const mutations: MutationTree<UserState> = {
    SET_ID(state, id: number) {
        state.id = id;
    },
    SET_NAME(state, name: string) {
        state.name = name;
    },
    SET_AVATAR(state, avatar: string) {
        state.avatar = avatar;
    },
    SET_TOKEN(state, token: string | null) {
        state.token = token;

        if(typeof token === 'string') {
            localStorage.setItem('token', token);
        }
        else if(localStorage.getItem('token') !== null) {
            localStorage.removeItem('token');
        }
    }
};