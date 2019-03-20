import {ICard} from "./CardInterface";

export default abstract class Card implements ICard {
    id: number;
    side: string;
    additional: boolean;
    got: boolean;
    code: string | null;
    revealed: boolean;

    constructor(card: ICard) {
        this.id = card.id;
        this.additional = card.additional;
        this.got = card.got;
        this.side = card.side;
        this.code = card.code;
        this.revealed = card.revealed;
    }
}