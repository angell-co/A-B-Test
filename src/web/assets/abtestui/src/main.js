/* global Craft */
/* global Garnish */

import Vue from 'vue'
import App from './App'
import CraftUi from '@pixelandtonic/craftui'
Vue.use(CraftUi)

if (typeof Craft.AbTest === typeof undefined) {
    Craft.AbTest = {};
}

Craft.AbTest.EntrySidebar = Garnish.Base.extend({
    init: function(settings) {
        this.setSettings(settings, Craft.AbTest.EntrySidebar.defaults);

        const props = this.settings;

        return new Vue({
            components: {
                App
            },
            data() {
                return {};
            },
            render: (h) => {
                return h(App, {
                    props: props
                })
            },
        }).$mount('#abtest-entry-sidebar');
    },
},
{
    defaults: {
        experimentOptions: [],
        drafts: [],
        experimentDrafts: []
    }
});
