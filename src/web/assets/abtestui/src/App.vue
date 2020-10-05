<template>
    <div class="meta">
        <div class="field">
            <h2>A/B Test</h2>

            <dropdown label="Experiment"
                      instructions="Choose an experiment and then switch on the drafts you want it to include."
                      v-if="hasExperiments"
                      :options="experimentOptions"
                      v-model="experimentSelect">
            </dropdown>

            <span v-else class="error">You don’t have any experiments set up,<br><a href="/admin/ab-test/experiments">set one up now</a>.</span>
        </div>

        <template v-if="hasExperiments">
            <lightswitch v-for="draft in drafts"
                         :label="draft.title"
                         :key="draft.id"
                         class="field"
                         :checked="false" />
        </template>
    </div>
</template>

<script>
    /* global Craft, Vue */

    export default {
        components: {

        },

        props: {
            experimentOptions: {
                type: Array,
                default: () => { return [] },
            },
            drafts: {
                type: Array,
                default: () => { return [] },
            }
        },

        data() {
            return {
                experimentSelect: this.experimentOptions.length >= 1 ? this.experimentOptions[0].value : null
            }
        },

        mounted() {
            console.log(this.drafts)
        },

        methods: {
        },

        computed: {
            hasExperiments () {
                return this.experimentOptions.length >= 1;
            }
        }
    }
</script>

<style lang="scss">
    @import "~@pixelandtonic/craftui/dist/craftui.css";

    .meta > .field:first-of-type {
        padding-top: 20px;
        padding-bottom: 20px;

        h2 {
            margin-bottom: 20px;
        }
    }
</style>
