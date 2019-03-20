import { Module } from 'vuex';

import {UserState} from "./UserTypes";
import {RootState} from "../../types";
import {actions} from "./UserActions";
import {mutations} from "./UserMutations";

export const state: UserState = {
    id: null,
    token: null,
    name: null,
    avatar: null
};

const namespaced: boolean = true;

export const User: Module<UserState, RootState> = {
    namespaced,
    state,
    actions,
    mutations,
};