webpackJsonp([8],{Vdt2:function(e,t,a){"use strict";Object.defineProperty(t,"__esModule",{value:!0});a("cpGA");var i={data:function(){return{page:0,count:10,total:0,datalist:[],tableCol:[{name:"id",width:50,label:"id",add_disable:!0,edit_disable:!0,type:"input"},{name:"version",width:100,label:"版本号",add_disable:!1,edit_disable:!1,type:"input"},{name:"name",width:150,label:"名称",add_disable:!1,edit_disable:!1,type:"input"},{name:"platform",width:90,label:"平台",add_disable:!1,edit_disable:!1,type:"select",options:""},{name:"device_type",width:150,label:"设备类型",add_disable:!1,edit_disable:!1,type:"select",options:""},{name:"work_type",width:150,label:"工种类型",add_disable:!1,edit_disable:!1,type:"select",options:""},{name:"type",width:150,label:"版本类型",add_disable:!1,edit_disable:!1,type:"select",options:""},{name:"is_force",width:150,label:"强制更新",add_disable:!1,edit_disable:!1,type:"select",options:""},{name:"launch_activity",width:150,label:"Activity",add_disable:!1,edit_disable:!1,type:"select",options:""},{name:"url",width:130,label:"链接",add_disable:!1,edit_disable:!1,type:"upload"},{name:"profile",width:130,label:"简介",add_disable:!1,edit_disable:!1,type:"input"},{name:"created_at",width:200,label:"创建时间",add_disable:!0,edit_disable:!0,type:"input"},{name:"updated_at",width:200,label:"更新时间",add_disable:!0,edit_disable:!0,type:"input"}],editFormVisible:!1,addFormVisible:!1,editForm:{},editFormRules:{name:[{required:!0,message:"请输入姓名",trigger:"blur"}]},editLoading:!1,addForm:{name:"",platform:"",device_type:"",type:"",is_force:"",url:"",profile:"",launch_activity:"",work_type:""},addFormRules:{},addLoading:!1,options:{platform:{ios:{value:"ios",label:"ios"},Android:{value:"Android",label:"Android"}},device_type:{1:{value:"1",label:"手机"},2:{value:"2",label:"平板"},3:{value:"3",label:"电视机"}},type:{0:{value:"0",label:"测试版本"},1:{value:"1",label:"正式版本"}},is_force:{1:{value:"1",label:"强制更新"},0:{value:"0",label:"非强制更新"}},work_type:{1:{value:"1",label:"员工端APP"},2:{value:"2",label:"管理端APP"}},launch_activity:{}},filter:{search:"",platform:"",device_type:"",type:"",is_force:"",work_type:""},ListLoading:!1,LaunchActivity:[]}},mounted:function(){this.initOptions(),this.getActivityList(),this.getList()},directives:{},methods:{UploadComplete:function(e,t){this.addForm[t]=e},initOptions:function(){for(var e in this.tableCol)"select"==this.tableCol[e].type&&(this.tableCol[e].options=this.options[this.tableCol[e].name])},getActivityList:function(){var e=this;this.$http.postDataEx("version","getActivityList",{},function(t){0==t.code&&(e.LaunchActivity=t.data.list)})},getList:function(e){var t=this;e&&(this.page=0),this.ListLoading=!0,this.$http.postDataEx("version","getList",{page:this.page,length:this.count,search:this.filter.search,platform:this.filter.platform,device_type:this.filter.device_type,type:this.filter.type,is_force:this.filter.is_force,work_type:this.filter.work_type},function(e){0==e.code&&(t.datalist=e.data.list,t.total=Number(e.data.total)),t.ListLoading=!1})},createSubmit:function(){var e=this;this.addLoading=!0,this.$http.postDataEx("version","create",this.addForm,function(t){0==t.code&&(e.$notify({title:"成功",message:"新增成功",type:"success"}),e.getList(),e.addFormVisible=!1,e.initAddForm()),e.addLoading=!1})},initAddForm:function(){for(var e in this.addForm)this.addForm[e]=""},editSubmit:function(){},del:function(e){var t=this,a=e.id;this.$confirm("确认删除该APP版本？","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){t.$http.postDataEx("version","deleted",{id:a},function(e){0==e.code&&(t.$notify({title:"成功",message:"新增成功",type:"success"}),t.getList())})}).catch(function(){t.$message({type:"info",message:"已取消删除"})})},selectItem:function(){this.page=0,this.getList()},selectChange:function(){},selsChange:function(){},handleCurrentChange:function(e){this.page=e-1,this.getList()},update:function(e){}},components:{nvPublish:a("k8oq").a},watch:{LaunchActivity:function(e,t){for(var a in this.LaunchActivity)this.options.launch_activity[this.LaunchActivity[a]]={value:this.LaunchActivity[a],label:this.LaunchActivity[a]}}}},l={render:function(){var e=this,t=e.$createElement,a=e._self._c||t;return a("div",{staticClass:"padApp",staticStyle:{height:"100%"}},[a("el-col",{staticClass:"toolbar",staticStyle:{"padding-bottom":"0px"},attrs:{span:24}},[a("el-form",{attrs:{inline:!0}},[a("el-form-item",[a("el-input",{attrs:{placeholder:"请输入关键字",clearable:""},model:{value:e.filter.search,callback:function(t){e.$set(e.filter,"search",t)},expression:"filter['search']"}})],1),e._v(" "),e._l(e.options,function(t,i){return"launch_activity"!=i?a("el-select",{key:i,staticStyle:{width:"120px","margin-left":"10px"},attrs:{placeholder:"请选择",clearable:""},model:{value:e.filter[i],callback:function(t){e.$set(e.filter,i,t)},expression:"filter[option_key]"}},e._l(t,function(e,t){return a("el-option",{key:t,attrs:{label:e.label,value:e.value}})})):e._e()}),e._v(" "),a("el-form-item",[a("el-button",{staticClass:"filter-item",attrs:{type:"primary",icon:"el-icon-search"},on:{click:function(t){e.getList(!0)}}},[e._v("查询")])],1),e._v(" "),a("el-form-item",[a("el-button",{staticClass:"filter-item",attrs:{type:"primary",icon:"el-icon-download"}},[e._v("导出")])],1),e._v(" "),a("el-form-item",[a("el-button",{staticClass:"filter-item",attrs:{type:"primary",icon:"el-icon-edit"},on:{click:function(t){e.addFormVisible=!0}}},[e._v("新增")])],1),e._v(" "),a("el-form-item",{staticStyle:{float:"right"},attrs:{label:"显示"}},[a("el-select",{ref:"count",staticStyle:{width:"70px"},attrs:{placeholder:"请选择"},on:{change:e.selectItem},model:{value:e.count,callback:function(t){e.count=t},expression:"count"}},[a("el-option",{attrs:{label:5,value:5}}),e._v(" "),a("el-option",{attrs:{label:10,value:10}}),e._v(" "),a("el-option",{attrs:{label:20,value:20}}),e._v(" "),a("el-option",{attrs:{label:50,value:50}})],1),e._v(" 条\n\t\t\t")],1),e._v(" "),a("el-form-item",{staticStyle:{float:"right","margin-right":"10px"}},[e._v("\n\t\t\t\t共 "),a("span",[e._v(e._s(e.total))]),e._v(" 条\n\t\t\t")])],2),e._v(" "),a("el-form",{attrs:{inline:!0}})],1),e._v(" "),a("el-table",{directives:[{name:"loading",rawName:"v-loading",value:e.ListLoading,expression:"ListLoading"}],staticStyle:{width:"100%",height:"100%"},attrs:{data:e.datalist},on:{"selection-change":e.selsChange}},[a("el-table-column",{attrs:{type:"selection",width:"55"}}),e._v(" "),e._l(e.tableCol,function(t){return a("el-table-column",{key:t.name,attrs:{prop:t.name,label:t.label,width:t.width},scopedSlots:e._u([{key:"default",fn:function(i){return["input"===t.type?a("span",{staticClass:"cell-edit-input"},[a("span",[e._v(e._s(i.row[t.name]?i.row[t.name]:"/"))])]):e._e(),e._v(" "),"upload"===t.type?a("span",{staticClass:"cell-edit-input"},[a("a",{staticClass:"el-icon-download",attrs:{target:"_blank",href:i.row[t.name]}})]):e._e(),e._v(" "),"select"===t.type?a("span",{staticClass:"cell-edit-input"},[a("div",{staticClass:"name-wrapper",attrs:{slot:"reference"},slot:"reference"},[e._v(e._s(e.options[t.name].hasOwnProperty([i.row[t.name]])?e.options[t.name][i.row[t.name]].label:"/"))])]):e._e()]}}])})}),e._v(" "),a("el-table-column",{attrs:{fixed:"right",label:"操作",width:"150"},scopedSlots:e._u([{key:"default",fn:function(t){return[a("el-button",{attrs:{type:"text",size:"small"},on:{click:function(a){e.del(t.row)}}},[e._v("删除")])]}}])})],2),e._v(" "),a("el-col",{staticClass:"toolbar",attrs:{span:24}},[a("el-pagination",{staticStyle:{float:"right"},attrs:{background:"",layout:"prev, pager, next","current-page":e.page,"page-size":e.count,total:e.total},on:{"current-change":e.handleCurrentChange,"update:currentPage":function(t){e.page=t}}})],1),e._v(" "),a("el-dialog",{attrs:{title:"编辑",visible:e.editFormVisible},on:{"update:visible":function(t){e.editFormVisible=t}}},[a("el-form",{ref:"editForm",attrs:{model:e.editForm,"label-width":"80px",rules:e.editFormRules}},e._l(e.tableCol,function(t){return t.edit_disable?e._e():a("el-form-item",{key:t.id,attrs:{label:t.label,prop:t.name}},["input"===t.type?a("el-input",{attrs:{"auto-complete":"off",disabled:t.edit_disable},model:{value:e.editForm[t.name],callback:function(a){e.$set(e.editForm,t.name,a)},expression:"editForm[item.name]"}}):e._e(),e._v(" "),"select"===t.type?a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(a){e.selectChange(t.name,a)}},model:{value:e.editForm[t.name],callback:function(a){e.$set(e.editForm,t.name,a)},expression:"editForm[item.name]"}},e._l(e.options[t.name],function(e){return a("el-option",{key:e.value,attrs:{label:e.label,value:e.value}})})):e._e()],1)})),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{nativeOn:{click:function(t){e.editFormVisible=!1}}},[e._v("取消")]),e._v(" "),a("el-button",{attrs:{type:"primary",loading:e.editLoading},nativeOn:{click:function(t){e.editSubmit(t)}}},[e._v("提交")])],1)],1),e._v(" "),a("el-dialog",{attrs:{title:"新增",visible:e.addFormVisible,"close-on-click-modal":!1},on:{"update:visible":function(t){e.addFormVisible=t}}},[a("el-form",{ref:"addForm",attrs:{model:e.addForm,"label-width":"80px",rules:e.addFormRules}},e._l(e.tableCol,function(t){return t.add_disable?e._e():a("el-form-item",{key:t.id,attrs:{label:t.label,prop:t.name}},["input"===t.type?a("el-input",{attrs:{"auto-complete":"off",disabled:t.add_disable},model:{value:e.addForm[t.name],callback:function(a){e.$set(e.addForm,t.name,a)},expression:"addForm[item.name]"}}):e._e(),e._v(" "),"select"===t.type&&"launch_activity"!=t.name&&"work_type"!=t.name?a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(a){e.selectChange(t.name,a)}},model:{value:e.addForm[t.name],callback:function(a){e.$set(e.addForm,t.name,a)},expression:"addForm[item.name]"}},e._l(e.options[t.name],function(e){return a("el-option",{key:e.value,attrs:{label:e.label,value:e.value}})})):e._e(),e._v(" "),"select"===t.type&&"launch_activity"==t.name&&"Android"==e.addForm.platform?a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(a){e.selectChange(t.name,a)}},model:{value:e.addForm[t.name],callback:function(a){e.$set(e.addForm,t.name,a)},expression:"addForm[item.name]"}},e._l(e.options[t.name],function(e){return a("el-option",{key:e.value,attrs:{label:e.label,value:e.value}})})):e._e(),e._v(" "),"select"===t.type&&"work_type"==t.name&&"1"==e.addForm.device_type?a("el-select",{attrs:{placeholder:"请选择"},on:{change:function(a){e.selectChange(t.name,a)}},model:{value:e.addForm[t.name],callback:function(a){e.$set(e.addForm,t.name,a)},expression:"addForm[item.name]"}},e._l(e.options[t.name],function(e){return a("el-option",{key:e.value,attrs:{label:e.label,value:e.value}})})):e._e(),e._v(" "),"upload"===t.type&&"Android"==e.addForm.platform?a("nv-publish",{attrs:{keyType:t.name,oriUrl:e.addForm[t.name]},on:{"update:keyType":function(a){e.$set(t,"name",a)},"update:oriUrl":function(a){e.$set(e.addForm,t.name,a)},"upload-complete":e.UploadComplete}}):e._e(),e._v(" "),"upload"===t.type&&"Android"!==e.addForm.platform?a("el-input",{attrs:{"auto-complete":"off"},model:{value:e.addForm[t.name],callback:function(a){e.$set(e.addForm,t.name,a)},expression:"addForm[item.name]"}}):e._e()],1)})),e._v(" "),a("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[a("el-button",{nativeOn:{click:function(t){e.addFormVisible=!1}}},[e._v("取消")]),e._v(" "),a("el-button",{attrs:{type:"primary",loading:e.addLoading},nativeOn:{click:function(t){e.createSubmit(t)}}},[e._v("提交")])],1)],1)],1)},staticRenderFns:[]};var n=a("VU/8")(i,l,!1,function(e){a("iRd7")},null,null);t.default=n.exports},iRd7:function(e,t,a){var i=a("jhnr");"string"==typeof i&&(i=[[e.i,i,""]]),i.locals&&(e.exports=i.locals);a("rjj0")("03a1d315",i,!0)},jhnr:function(e,t,a){(e.exports=a("FZ+f")(!1)).push([e.i,"",""])}});