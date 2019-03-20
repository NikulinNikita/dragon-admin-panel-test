<template>
    <div>
        <roulette-field></roulette-field>
    </div>
</template>

<script lang="ts">
    import Vue from "vue";
    import Component from "vue-class-component";

    import {Watch} from 'vue-property-decorator'

    import {
        Action,
        namespace
    } from 'vuex-class';

    const user = namespace('User');

    import RouletteField from '../roulette/RouletteField.vue';

    const core = namespace('Core');

    const roulette = namespace('Roulette');

    @Component({
        components: { RouletteField },
    })
    export default class RoulettePage extends Vue {
        @user.State token: string | null;

        @core.Action sendNotification: Function;

        @roulette.Action stateListener: Function;

        @roulette.Action resetState: Function;

        @Action loadConfig: Function;

        async created() {
            await this.resetState();
            await this.loadConfig(null);
            await this.stateListener();
        }

        @Watch('token')
        async onTokenChanged(value: string | null, oldValue: string | null) {
            if (value === null) {
                this.$router.push({name: 'DealerLogin'});
            }
        }
    }
</script>

<style scoped>

</style>