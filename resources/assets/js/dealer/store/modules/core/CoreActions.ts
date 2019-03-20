import {ActionTree} from 'vuex';
import {RootState} from "../../types";
import {CoreState} from "./CoreTypes";

export const actions: ActionTree<CoreState, RootState> = {
    sendNotification({commit, state}, notification: Notification) {
        commit('SET_NOTIFICATION', notification);
    },
    sendSystemNotification({commit, state}, notification: Notification) {
        commit('SET_SYSTEM_NOTIFICATION', notification);
    }
};