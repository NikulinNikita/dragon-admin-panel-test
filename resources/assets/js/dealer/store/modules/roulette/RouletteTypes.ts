import GameStateStorage from "../../../src/gameState/GameStateStorage";
import RouletteGameState from "../../../src/gameState/RouletteGameState";

export interface RouletteState {
    gameState: RouletteGameState;
    gameStateStorage: GameStateStorage;
    bettingTick: number | null;
    spinTick: number | null;
    playersWaitingTick: number | null;
    winnerCell: object | null;
    states: Array<Object>;
    paused: boolean;
}