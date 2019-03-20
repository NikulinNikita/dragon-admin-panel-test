import {Echo} from "laravel-echo-sc";
import {AxiosInstance} from 'axios';

export interface RootState {
    socket: Echo | null,
    axios: AxiosInstance,
    tableId: number | null,
    game: string | null,
    roundId: number | null,
    logoutOn: boolean
}