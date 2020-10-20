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
                      v-model="experimentId">
            </dropdown>

            <span v-else class="error">You don’t have any experiments set up,<br><a :href="Craft.getCpUrl('ab-test/experiments')">set one up now</a>.</span>
        </div>

        <template v-if="hasExperiments">
            <div v-for="draft in drafts" class="field draft-field" :key="draft.id">
                <checkbox :label="draft.title" :value="draft.draftId" v-model="draftIds" />

                <div v-if="draft.note" class="instructions">
                    <p>{{ draft.note }}</p>
                </div>
            </div>

            <div class="footer">
                <div class="spinner" v-if="loading"></div>

                <div class="buttons right">
                    <a :href="experimentEditUrl" class="btn" v-if="section.id">View</a>
                    <button class="btn submit" @click.prevent="actionUpdate">Update</button>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    /* global axios, Craft */

    import _map from 'lodash/map';

    export default {
        props: {
            experimentOptions: {
                type: Array,
                default: () => { return [] },
            },
            drafts: {
                type: Array,
                default: () => { return [] },
            },
            section: {
                type: Object,
                default: () => { return {} },
            }
        },

        data() {
            // Find the selected experiment if there is one
            let experimentId = null;

            for (let i = 0; i < this.experimentOptions.length; i++) {
                if (this.experimentOptions[i].checked) {
                    experimentId = this.experimentOptions[i].value;
                }
            }

            // Default to the first one in the list
            if (!experimentId && this.experimentOptions.length >= 1) {
                experimentId = this.experimentOptions[0].value;
            }

            return {
                experimentId: experimentId,
                draftIds: this.section.drafts.length >= 1 ? _map(this.section.drafts, 'draftId') : [],
                loading: false
            }
        },

        methods: {
            actionUpdate() {
                if (this.loading) {
                    return;
                }

                this.loading = true;

                axios.post(Craft.getActionUrl('ab-test/sections/save'), {
                    sectionId: this.section.id,
                    sourceId: this.section.sourceId,
                    experimentId: this.experimentId,
                    draftIds: this.draftIds,
                }, {
                    headers: {
                        'X-CSRF-Token':  Craft.csrfTokenValue,
                    }
                })
                    .then( (response) => {
                        this.loading = false;
                        if (response.data.section) {
                            this.section = response.data.section;
                        } else {
                            this.section = {
                                'id' : null,
                                'sourceId' : this.section.sourceId,
                                'drafts' : []
                            };
                        }
                    })
                    .catch( (error) => {
                        this.loading = false;
                        console.log(error);
                    });

            }
        },

        computed: {
            hasExperiments () {
                return this.experimentOptions.length >= 1;
            },
            experimentEditUrl () {
                if (!this.experimentId) {
                    return null;
                }
                return Craft.getCpUrl('ab-test/experiments/'+this.experimentId);
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
