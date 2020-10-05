<template>
    <div class="meta">
        <div class="header">
            <h2>A/B Test</h2>
        </div>

        <div class="field draft-field">
            <dropdown label="Experiment"
                      instructions="Choose an experiment and then switch on the drafts you want it to include."
                      class="field"
                      v-if="hasExperiments"
                      :options="experimentOptions"
                      v-model="experimentSelect">
            </dropdown>

            <span v-else class="error">You don’t have any experiments set up,<br><a href="/admin/ab-test/experiments">set one up now</a>.</span>
        </div>

        <template v-if="hasExperiments">
            <div v-for="draft in drafts" class="field draft-field" :key="draft.id">
                <checkbox :label="draft.title" :checked="false" />

                <div v-if="draft.note" class="instructions">
                    <p>{{ draft.note }}</p>
                </div>
            </div>
        </template>

        <div class="footer">
            <div class="spinner" v-if="loading"></div>
            <button class="btn submit" @click.prevent="actionUpdate">Update</button>
        </div>
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
                experimentSelect: this.experimentOptions.length >= 1 ? this.experimentOptions[0].value : null,
                loading: false
            }
        },

        mounted() {
            console.log(this.drafts)
        },

        methods: {
            actionUpdate() {
                if (this.loading) {
                    return;
                }

                this.loading = true;

                // TODO Do the ajax
            }
        },

        computed: {
            hasExperiments () {
                return this.experimentOptions.length >= 1;
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "~@pixelandtonic/craftui/dist/craftui.css";

    .meta {
        overflow: hidden;

        > .field:first-of-type {
            padding-top: 20px;
            padding-bottom: 20px;
        }

        .btn {
            float: right;
        }

        > .field.draft-field {
            padding: 14px 24px 10px 24px;

            /deep/ .c-field {
                width: 100%;
                margin-bottom: 0;

                 label {
                    font-weight: bold;
                    color: #606d7b;
                    width: 100%;
                }
            }

            .instructions {
                margin-left: 24px;
            }
        }

        .header,
        .footer {
            margin: 0 -24px;
        }


        .header + .draft-field {
            border-top: 0;
        }
    }
</style>
