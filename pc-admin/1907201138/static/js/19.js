webpackJsonp([19],{"4ejJ":function(t,e,o){var i=o("6g4o");"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);o("rjj0")("73e3c2db",i,!0)},"6g4o":function(t,e,o){(t.exports=o("FZ+f")(!1)).push([t.i,"\n.mod-demo-ueditor {\n  position: relative;\n  z-index: 510;\n}\n.mod-demo-ueditor > .el-alert {\n    margin-bottom: 10px;\n}\n",""])},t1fE:function(t,e,o){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=o("fZIC"),n=o.n(i),l={data:function(){return{ue:null,ueContent:"",dialogVisible:!1}},mounted:function(){this.ue=n.a.getEditor("J_ueditorBox",{zIndex:3e3})},methods:{getContent:function(){var t=this;this.dialogVisible=!0,this.ue.ready(function(){t.ueContent=t.ue.getContent()})}}},s={render:function(){var t=this,e=t.$createElement,o=t._self._c||e;return o("div",{staticClass:"mod-demo-ueditor"},[o("el-alert",{attrs:{title:"提示：",type:"warning",closable:!1},scopedSlots:t._u([{key:"default",fn:function(e){return o("div",{},[o("p",{staticClass:"el-alert__description"},[t._v("1. 此Demo只提供UEditor官方使用文档，入门部署和体验功能。具体使用请参考：http://fex.baidu.com/ueditor/")]),t._v(" "),o("p",{staticClass:"el-alert__description"},[t._v("2. 浏览器控制台报错“请求后台配置项http错误，上传功能将不能正常使用！”，此错需要后台提供上传接口方法（赋值给serverUrl属性）")])])}}])}),t._v(" "),o("script",{staticClass:"ueditor-box",staticStyle:{width:"100%",height:"260px"},attrs:{id:"J_ueditorBox",type:"text/plain"}},[t._v("hello world!")]),t._v(" "),o("p",[o("el-button",{on:{click:function(e){t.getContent()}}},[t._v("获得内容")])],1),t._v(" "),o("el-dialog",{attrs:{title:"内容",visible:t.dialogVisible,"append-to-body":!0},on:{"update:visible":function(e){t.dialogVisible=e}}},[t._v("\n    "+t._s(t.ueContent)+"\n    "),o("span",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[o("el-button",{attrs:{type:"primary"},on:{click:function(e){t.dialogVisible=!1}}},[t._v("确 定")])],1)])],1)},staticRenderFns:[]};var r=o("VU/8")(l,s,!1,function(t){o("4ejJ")},null,null);e.default=r.exports}});