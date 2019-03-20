<template>
    <div>
        <baccarat-field></baccarat-field>
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

    import BaccaratField from '../baccarat/BaccaratField.vue';

    const core = namespace('Core');

    const baccarat = namespace('Baccarat');

    @Component({
        components: { BaccaratField },
    })
    export default class BaccaratPage extends Vue {
        @user.State token: string | null;

        @core.Action sendNotification: Function;

        @baccarat.Action stateListener: Function;

        @baccarat.Action resetState: Function;

        @Action loadConfig: Function;

        async created() {
            await this.resetState();
            await this.loadConfig();
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