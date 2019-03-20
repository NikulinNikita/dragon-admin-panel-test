import {GameStateInterface} from "./GameStateInterface";

export default abstract class GameState implements GameStateInterface {
    displayName: string;
    name: string;
    data?: any;

    constructor(state: GameStateInterface) {
        this.name = state.name;
        this.displayName = state.displayName;

        if(state.hasOwnProperty('data')) this.data = state.data;
    }

    created() {
        if(typeof this['onCreate'] === "function") { //additional hook
            this['onCreate']();
        }
    }

    getFunctionName() {
        let name: string = this.name.charAt(0).toUpperCase() + this.name.slice(1);

        return `on${name}State`;
    }

    async change(state: GameStateInterface) {
        this.name = state.name;
        this.displayName = state.displayName;

        if(state.hasOwnProperty('data')) this.data = state.data;

        let functionName: string = this.getFunctionName();

        if(typeof this[functionName] === "function") {
            await this[functionName]();
        }
        else {
            throw new Error(`You should declare a function called "${functionName}" first!`);
        }
    }
}