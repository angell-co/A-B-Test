(function(t){function e(e){for(var r,s,o=e[0],u=e[1],l=e[2],f=0,d=[];f<o.length;f++)s=o[f],Object.prototype.hasOwnProperty.call(a,s)&&a[s]&&d.push(a[s][0]),a[s]=0;for(r in u)Object.prototype.hasOwnProperty.call(u,r)&&(t[r]=u[r]);c&&c(e);while(d.length)d.shift()();return i.push.apply(i,l||[]),n()}function n(){for(var t,e=0;e<i.length;e++){for(var n=i[e],r=!0,o=1;o<n.length;o++){var u=n[o];0!==a[u]&&(r=!1)}r&&(i.splice(e--,1),t=s(s.s=n[0]))}return t}var r={},a={app:0},i=[];function s(e){if(r[e])return r[e].exports;var n=r[e]={i:e,l:!1,exports:{}};return t[e].call(n.exports,n,n.exports,s),n.l=!0,n.exports}s.m=t,s.c=r,s.d=function(t,e,n){s.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},s.r=function(t){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},s.t=function(t,e){if(1&e&&(t=s(t)),8&e)return t;if(4&e&&"object"===typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(s.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var r in t)s.d(n,r,function(e){return t[e]}.bind(null,r));return n},s.n=function(t){var e=t&&t.__esModule?function(){return t["default"]}:function(){return t};return s.d(e,"a",e),e},s.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},s.p="/";var o=window["webpackJsonp"]=window["webpackJsonp"]||[],u=o.push.bind(o);o.push=e,o=o.slice();for(var l=0;l<o.length;l++)e(o[l]);var c=u;i.push([0,"chunk-vendors"]),n()})({0:function(t,e,n){t.exports=n("56d7")},"56d7":function(t,e,n){"use strict";n.r(e);var r=n("7618"),a=(n("cadf"),n("551c"),n("f751"),n("097d"),n("8bbf")),i=n.n(a),s=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"meta"},[t._m(0),n("div",{staticClass:"field draft-field"},[t.hasExperiments?n("dropdown",{staticClass:"field",attrs:{label:"Experiment",instructions:"Choose an experiment and then switch on the drafts you want it to include.",options:t.experimentOptions},model:{value:t.experimentId,callback:function(e){t.experimentId=e},expression:"experimentId"}}):n("span",{staticClass:"error"},[t._v("You don’t have any experiments set up,"),n("br"),n("a",{attrs:{href:t.Craft.getCpUrl("ab-test/experiments")}},[t._v("set one up now")]),t._v(".")])],1),t.hasExperiments?[t._l(t.drafts,(function(e){return n("div",{key:e.id,staticClass:"field draft-field"},[n("checkbox",{attrs:{label:e.title,value:e.draftId},model:{value:t.draftIds,callback:function(e){t.draftIds=e},expression:"draftIds"}}),e.note?n("div",{staticClass:"instructions"},[n("p",[t._v(t._s(e.note))])]):t._e()],1)})),n("div",{staticClass:"footer"},[t.loading?n("div",{staticClass:"spinner"}):t._e(),n("button",{staticClass:"btn submit",on:{click:function(e){return e.preventDefault(),t.actionUpdate(e)}}},[t._v("Update")])])]:t._e()],2)},o=[function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"header"},[n("h2",[t._v("A/B Test")])])}],u={components:{},props:{experimentOptions:{type:Array,default:function(){return[]}},drafts:{type:Array,default:function(){return[]}}},data:function(){return{experimentId:this.experimentOptions.length>=1?this.experimentOptions[0].value:null,draftIds:[],loading:!1}},mounted:function(){console.log(this.drafts)},methods:{actionUpdate:function(){var t=this;this.loading||(this.loading=!0,axios.post(Craft.getActionUrl("ab-test/experiment-drafts/save"),{experimentId:this.experimentId,draftIds:this.draftIds},{headers:{"X-CSRF-Token":Craft.csrfTokenValue}}).then((function(e){t.loading=!1,console.log(e)})).catch((function(e){t.loading=!1,console.log(e)})))}},computed:{hasExperiments:function(){return this.experimentOptions.length>=1}}},l=u,c=(n("a0b1"),n("2877")),f=Object(c["a"])(l,s,o,!1,null,"852730a2",null),d=f.exports,p=n("76f0");i.a.use(p["a"]),"undefined"===Object(r["a"])(Craft.AbTest)&&(Craft.AbTest={}),Craft.AbTest.EntrySidebar=Garnish.Base.extend({init:function(t){this.setSettings(t,Craft.AbTest.EntrySidebar.defaults);var e=this.settings;return new i.a({components:{App:d},data:function(){return{}},render:function(t){return t(d,{props:e})}}).$mount("#abtest-entry-sidebar")}},{defaults:{experimentOptions:[],drafts:[]}})},"7fc5":function(t,e,n){},"8bbf":function(t,e){t.exports=Vue},a0b1:function(t,e,n){"use strict";var r=n("7fc5"),a=n.n(r);a.a}});
//# sourceMappingURL=app.js.map