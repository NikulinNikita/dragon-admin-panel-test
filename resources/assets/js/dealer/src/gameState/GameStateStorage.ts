import GameState from "./GameState";

export default class GameStateStorage {

    states: GameState[];

    constructor(states: GameState[] = []) {
        this.states = states;
    }

    add(state: GameState) {
        this.states.push(state);
    }

    remove(state?: GameState) {
        if(state !== undefined) {
            let index: number | undefined = this.states.reverse().findIndex((current: GameState) => {return current.name === state.name;});

            if(index) {
                this.states.slice(index, 1);
            }
        }
        else {
            this.states.pop();
        }
    }

    first(): GameState | null {
        if(this.states.length > 0)
            return this.states[this.states.length-1];
        return null;
    }

    last(): GameState | null {
        if(this.states.length > 0)
            return this.states[0];
        return null;
    }
}