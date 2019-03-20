import BaccaratCard from "../../../src/card/BaccaratCard";
import BaccaratGameState from "../../../src/gameState/BaccaratGameState";
import GameStateStorage from "../../../src/gameState/GameStateStorage";

export interface BaccaratState {
    cards: BaccaratCard[];
    gameState: BaccaratGameState;
    waitingFor: BaccaratCard | null;
    gameStateStorage: GameStateStorage;
    bettingTick: number | null;
    shuffleTick: number | null;
    playersWaitingTick: number | null;
    winners: Array<string>;
    states: Array<Object>;
    playerScore: number | null;
    bankerScore: number | null;
    paused: boolean;
}