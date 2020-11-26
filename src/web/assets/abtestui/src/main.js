/* global Garnish */

import Vue from 'vue'
import App from './App'
import CraftUi from '@pixelandtonic/craftui'
Vue.use(CraftUi)

window.AbTest_EntrySidebar = Garnish.Base.extend({
    init: function(settings) {
        this.setSettings(settings, window.AbTest_EntrySidebar.defaults);

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
        section: {}
    }
});
