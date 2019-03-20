import { MutationTree } from 'vuex';
import {CoreState, Notification} from "./CoreTypes";

export const mutations: MutationTree<CoreState> = {
    SET_NOTIFICATION(state, notification: Notification) {
        state.notification = notification;
    },
    SET_SYSTEM_NOTIFICATION(state, notification: Notification) {
        state.systemNotification = notification;
    }
};