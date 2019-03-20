<template>
    <div class="text-center text-monospace text-primary my-5">
        <h2 class="my-0 text-uppercase text-bold" style="font-size: 60px"><span v-if="systemNotification === null" style="opacity: 0">goldendragon</span><span v-else-if="systemNotification !== null">{{systemNotification.message}}</span></h2>
    </div>
</template>

<script lang="ts">
    import Component from "vue-class-component";
    import Vue from 'vue';
    import {createNamespacedHelpers} from "vuex";

    export const coreHelpers = createNamespacedHelpers('Core');

    @Component<SystemNotifications>({
        computed: {
            ...coreHelpers.mapState(['systemNotification'])
        }
    })
    export default class SystemNotifications extends Vue {

    }
</script>

<style scoped>

</style>