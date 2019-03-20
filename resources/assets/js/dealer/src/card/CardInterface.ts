export interface ICard {
    id: number;
    side: string;
    additional: boolean;
    code: string | null;
    got: boolean;
    revealed: boolean;
}