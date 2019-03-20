import GameState from "./GameState";
import store from '../../store';
import {state as userState} from "../../store/modules/user";
import BaccaratCard from "../card/BaccaratCard";

//TODO: may be i should create single-file class for each state
export default class RouletteGameState extends GameState {

    readonly CODE_UID_IS_UNKNOWN = 1;
    readonly CODE_DEALER_IS_INACTIVE = 2;
    readonly CODE_OTHER_UID_IS_EXPECTED = 3;

    notification(customMessage: string | null = null) {
        store.dispatch('Core/sendNotification', {
            title: 'Стадия: '+this.name,
            message: customMessage !== null ? customMessage : this.displayName,
            type: 'success'
        });
    }

    systemNotification(customMessage: string | null = null) {
        store.dispatch('Core/sendSystemNotification', {
            title: 'Стадия: '+this.name,
            message: customMessage !== null ? customMessage : this.displayName,
            type: 'danger'
        });
    }

    hideSystemNotification() {
        store.dispatch('Core/sendSystemNotification', null);
    }

    hideNotification() {
        store.dispatch('Core/sendNotification', null);
    }

    onCreate() {
        this.notification();
    }

    async onPendingState() {
        this.notification();
    }

    async onWaitingPlayersStartState() {
        this.hideNotification();
    }

    async onWaitingPlayersTickState() {
        let elapsed: number = this.data.elapsed;

        store.dispatch('Roulette/setPlayersWaitingTick', elapsed);
    }

    async onWaitingPlayersStopState() {
        store.dispatch('Roulette/setPlayersWaitingTick', null);
    }

    async onRoundStartState() {
        this.hideNotification();

        await store.dispatch('setRoundId', this.data.round.id);

        await store.dispatch('Roulette/setPause', false);
        await store.dispatch('Roulette/setWinnerCell', null);
        await store.dispatch('Roulette/setBettingTick', null);
        await store.dispatch('Roulette/setSpinTick', null);
    }

    async onInterruptionTickState() {
        this.notification();

        await store.dispatch('Roulette/setPause', true);
    }

    async onBetAcceptionStartState() {
        this.hideNotification();

        await store.dispatch('Roulette/setBettingTick', this.data.totalSeconds);
    }

    async onBetAcceptionTickState() {
        this.hideNotification();
        let seconds: number = this.data.remainingSeconds;

        store.dispatch('Roulette/setBettingTick', seconds);
    }

    async onBetAcceptionStopState() {
        this.hideNotification();

        await store.dispatch('Roulette/setBettingTick', null);
    }

    async onSpinStartState() {
        this.hideNotification();
    }

    async onSpinTickState() {
        this.hideNotification();
        let seconds: number = this.data.elapsedSeconds;

        store.dispatch('Roulette/setSpinTick', seconds);
    }

    async onRoundResultState() {
        this.hideNotification();

        store.dispatch('Roulette/setSpinTick', null);
        await store.dispatch('Roulette/setWinnerCell', this.data.cell);
    }
    async onRoundFinishState() {
        this.hideNotification();
    }

    async onSessionLogOutToggleState() {
        store.dispatch('setLogout', this.data.logout, {root: true});
    }

    async onSessionInvalidInputState() {
        let code: number = this.data.code;

        let message: string | undefined = undefined;

        switch (code) {
            case this.CODE_UID_IS_UNKNOWN: message = 'Приложите карту ещё раз'; break;
            case this.CODE_DEALER_IS_INACTIVE: message = 'Данная карта не активирована. Обратитесь к своему менеджеру'; break;
            case this.CODE_OTHER_UID_IS_EXPECTED: message = 'Приложите карту, которая принадлежит '+store.state['User'].name+'. Чтобы выйти из системы.'; break;
            default: break;
        }

        if(message) {
            this.systemNotification(message);

            setTimeout(() => {
                this.hideSystemNotification();
            }, 3000)
        }
    }

    async onSessionStopState() {
        this.hideNotification();

        let data = await store.state.axios.post(`/jwt/${userState.id}/logout`).then( (response) => {
            return response.data;
        })
        .catch(() => {
            return 'error';
        });

        if(data === 'error' || data.success) {
            store.dispatch('User/setAuthToken', null);
            store.dispatch('User/setId', null);
            store.dispatch('User/setName', null);
            store.dispatch('User/setAvatar', null);
            store.dispatch('Roulette/stopTableListener');
            store.dispatch('Roulette/setWinnerCell', null);
            store.dispatch('setGame', null, {root: true});
            store.dispatch('setLogout', false, {root: true});

            if(data === 'error') {
                localStorage.removeItem('token');
            }
        }
    }
}