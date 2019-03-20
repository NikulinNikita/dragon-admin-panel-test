import { Module } from 'vuex';
import {CoreState} from "./CoreTypes";
import {RootState} from "../../types";
import {mutations} from "./CoreMutations";
import {actions} from "./CoreActions";

export const state: CoreState = {
    notification: null,
    systemNotification: null
};

const namespaced: boolean = true;

export const Core: Module<CoreState, RootState> = {
    namespaced,
    state,
    actions,
    mutations
};