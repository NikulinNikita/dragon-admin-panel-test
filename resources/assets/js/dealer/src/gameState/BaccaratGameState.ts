import GameState from "./GameState";
import store from '../../store';
import {state as userState} from "../../store/modules/user";
import BaccaratCard from "../card/BaccaratCard";

//TODO: may be i should create single-file class for each state
export default class BaccaratGameState extends GameState {

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

        store.dispatch('Baccarat/setPlayersWaitingTick', elapsed);
    }

    async onWaitingPlayersStopState() {
        store.dispatch('Baccarat/setPlayersWaitingTick', null);
    }

    async onRoundStartState() {
        this.hideNotification();

        await store.dispatch('setRoundId', this.data.round.id);

        await store.dispatch('Baccarat/setPause', false);
        await store.dispatch('Baccarat/setPlayerScore', null);
        await store.dispatch('Baccarat/setBankerScore', null);
        await store.dispatch('Baccarat/setWinners', []);
        await store.dispatch('Baccarat/setBettingTick', null);
        await store.dispatch('Baccarat/setShuffleTick', null);
        store.dispatch('Baccarat/waitForCard', null);
        store.dispatch('Baccarat/resetCards');
    }

    async onInterruptionTickState() {
        this.notification();

        await store.dispatch('Baccarat/setPause', true);
    }

    async onBetAcceptionStartState() {
        this.hideNotification();
        this.notification();

        await store.dispatch('Baccarat/setBettingTick', this.data.totalSeconds);
    }

    async onBetAcceptionTickState() {
        let seconds: number = this.data.remainingSeconds;

        store.dispatch('Baccarat/setBettingTick', seconds);
    }

    async onBetAcceptionStopState() {
        this.hideNotification();
        this.notification();

        await store.dispatch('Baccarat/setBettingTick', null);
    }
    async onCardPullStartState() {
        this.hideNotification();
    }
    async onCardPullRequestState() {
        this.hideNotification();
        let number: number = this.data.number;
        let side: string = this.data.type;

        let got = false;

        if(number === 1) {
            let captured: BaccaratCard[] = store.getters['Baccarat/capturedCards'];

            let capturedNumbers: number[] = captured.map(card => card.id);

            if(capturedNumbers.length > 0 && capturedNumbers.includes(3)) {
                await store.dispatch('Baccarat/updateCard', new BaccaratCard({id: 3, additional: true, side: 'player', got: false, code: null, revealed: false}));
                await store.dispatch('Baccarat/updateCard', new BaccaratCard({id: 3, additional: true, side: 'banker', got: false, code: null, revealed: false}));

                await store.dispatch('Baccarat/updateCard', new BaccaratCard({id: 1, additional: false, side: 'player', got: true, code: null, revealed: false}));
            }
        }
        else if(number === 2) {
            let captured: BaccaratCard[] = store.getters['Baccarat/capturedCards'];

            let capturedNumbers: number[] = captured.map(card => card.id);

            if(capturedNumbers.length > 0 && capturedNumbers.includes(3)) {
                await store.dispatch('Baccarat/updateCard', new BaccaratCard({id: 3, additional: true, side: 'player', got: false, code: null, revealed: false}));
                await store.dispatch('Baccarat/updateCard', new BaccaratCard({id: 3, additional: true, side: 'banker', got: false, code: null, revealed: false}));

                await store.dispatch('Baccarat/updateCard', new BaccaratCard({id: 1, additional: false, side: 'player', got: true, code: null, revealed: false}));
                await store.dispatch('Baccarat/updateCard', new BaccaratCard({id: 1, additional: false, side: 'banker', got: true, code: null, revealed: false}));
            }
        }

        store.dispatch('Baccarat/waitForCard', new BaccaratCard({id: number, additional: number === 3, side, got, code: null, revealed: false}));
    }
    async onCardPullCaptureState() {
        this.hideNotification();

        let number: number = this.data.number;
        let type: string = this.data.type;

        store.dispatch('Baccarat/gotCard', new BaccaratCard({id: number, additional: number === 3, side: type, got: true, code: null, revealed: false}));
    }
    async onCardPullFinishState() {
        this.hideNotification();

        store.dispatch('Baccarat/waitForCard', null);
    }
    async onCardRevealStartState() {
        this.hideNotification();
    }
    async onCardRevealDeclarationState() {
        this.hideNotification();

        let number: number = this.data.number;
        let type: string = this.data.type;

        store.dispatch('Baccarat/waitForCard', new BaccaratCard({id: number, additional: number === 3, side: type, got: true, code: null, revealed: false}));
    }
    async onCardRevealBroadcastState() {
        this.hideNotification();

        let number: number = this.data.number;
        let type: string = this.data.type;

        let card: Array<any> = this.data.card;

        store.dispatch('Baccarat/gotCard', new BaccaratCard({id: number, additional: number === 3, side: type, got: true, code: card['code'], revealed: true}));
    }
    async onCardRevealFinishState() {
        this.hideNotification();
    }
    async onRoundResultState() {
        this.hideNotification();

        let winners: Array<string> = this.data.winners;
        await store.dispatch('Baccarat/setPlayerScore', this.data.playerScore);
        await store.dispatch('Baccarat/setBankerScore', this.data.bankerScore);

        store.dispatch('Baccarat/setWinners', winners);
    }
    async onRoundFinishState() {
        this.hideNotification();
    }

    async onShuffleStartState() {
        this.hideNotification();

        store.dispatch('Baccarat/resetCards');
        await store.dispatch('Baccarat/setPlayerScore', null);
        await store.dispatch('Baccarat/setBankerScore', null);
        await store.dispatch('Baccarat/setWinners', []);
    }

    async onShuffleCardRequestState() {
        console.log(this.data, this.data.type && this.data.type === 'shuffler');
        if(this.data.hasOwnProperty('remains') && this.data.remains !== null && this.data.remains > 0) {
            this.notification(`Осталось достать карт из shoe: ${this.data.remains}`);
        }
        else if(this.data.type && this.data.type === 'shuffler') {
            this.notification('Приложите bar-код');
        }
        else {
            this.notification();
        }
    }

    async onShuffleStopState() {
        this.hideNotification();

        await store.dispatch('Baccarat/setShuffleTick', null);
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
            await store.dispatch('User/setAuthToken', null);
            await store.dispatch('User/setId', null);
            await store.dispatch('User/setName', null);
            await store.dispatch('User/setAvatar', null);
            await store.dispatch('Baccarat/resetCards');
            await store.dispatch('Baccarat/stopTableListener');
            await store.dispatch('setGame', null, {root: true});
            await store.dispatch('setLogout', false, {root: true});

            if(data === 'error') {
                localStorage.removeItem('token');
            }
        }
    }
}