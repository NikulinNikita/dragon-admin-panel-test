import { GetterTree } from 'vuex';
import { BaccaratState } from './BaccaratTypes';
import { RootState } from '../../types';
import BaccaratCard from "../../../src/card/BaccaratCard";

export const getters: GetterTree<BaccaratState, RootState> = {
    cardLink: (state) => (type: string): string => {
        return `/img/cards/${type}.png`;
    },
    isWinner: (state) => (member: string): boolean => {
        return state.winners.indexOf(member) !== -1;
    },
    playerCards: (state): Array<BaccaratCard> => {
        return state.cards.filter((card: BaccaratCard) => card.side === 'player' && !card.additional).reverse();
    },
    bankerCards: (state): Array<BaccaratCard> => {
        return state.cards.filter((card: BaccaratCard) => card.side === 'banker' && !card.additional).reverse();
    },
    mainCards: (state): Array<BaccaratCard> => {
        return state.cards.filter((card: BaccaratCard) => !card.additional);
    },
    additionalCards: (state): Array<BaccaratCard> => {
        return state.cards.filter((card: BaccaratCard) => card.additional);
    },
    capturedCards: (state): Array<BaccaratCard> => {
        return state.cards.filter((card: BaccaratCard) => card.got && !card.revealed);
    },
    revealedCards: (state): Array<BaccaratCard> => {
        return state.cards.filter((card: BaccaratCard) => card.got && card.revealed);
    }
};