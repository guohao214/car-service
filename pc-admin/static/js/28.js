webpackJsonp([28],{HWbk:function(t,e,i){(t.exports=i("FZ+f")(!1)).push([t.i,"\n.el-transfer-panel {\n  width: 280px;\n}\n",""])},TikY:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});i("cpGA");var a={data:function(){return{count:10,total:0,ListLoading:!1,deviceTypeSettingData:[],deviceTypeSettingTableCol:[{name:"type_name",width:100,label:"类型名",type:"input",options:""},{name:"pre_num",width:120,label:"类型编号(旧)",type:"input",options:""},{name:"num",width:120,label:"类型编号(新)",type:"input",options:""},{name:"is_battery_name",width:100,label:"电池供电",type:"select",options:""},{name:"manufacturer",width:100,label:"来源厂家",type:"input",options:""},{name:"category",width:100,label:"所属类别",type:"input",options:""},{name:"numbering_rules_name",width:100,label:"编号规则",type:"select",options:""},{name:"lora_name",width:120,label:"lora-sever",type:"select",options:""},{name:"iot_name",width:100,label:"IOT设备",type:"select",options:""},{name:"detect_interval",width:100,label:"检测周期",type:"select",options:""},{name:"heart_interval",width:100,label:"心跳周期",type:"select",options:""}],typeEditForm:{},typeEditFormRules:{},typeEditLoading:!1,typeEditFormVisible:!1,typeAddForm:{type_name:"",num:"",is_battery_name:"",manufacturer:"",category:"",numbering_rules_name:"",lora_name:"",iot_name:"",detect_interval:"",heart_interval:"",mod:1},typeAddFormRules:{is_battery:[{required:!0,message:"请选择",trigger:"change"}],numbering_rules:[{required:!0,message:"请选择",trigger:"change"}],lora:[{required:!0,message:"请选择",trigger:"change"}],num:[{required:!0,message:"编号不能为空",trigger:"blur"}],type_name:[{required:!0,message:"类型名不能为空",trigger:"blur"}]},typeAddLoading:!1,typeAddFormVisible:!1,nodeInitSettingData1:[],nodeInitSettingData2:[],nodeInitSettingTableCol1:[{name:"type_name",width:120,label:"设备类型",options:""},{name:"hardware_ver",width:100,label:"硬件版本",options:""},{name:"software_ver",width:100,label:"软件版本",options:""},{name:"v_create_warning",width:100,label:"触发阈值",options:""},{name:"v_close_warning",width:120,label:"关闭触发阈值",options:""},{name:"detect_interval",width:100,label:"检测周期",options:""},{name:"heart_interval",width:100,label:"心跳周期",options:""},{name:"heart_interval_offset",width:130,label:"心跳周期偏移量",options:""},{name:"awake_interval",width:100,label:"唤醒周期",options:""}],nodeInitSettingTableCol2:[{name:"type_name",width:120,label:"设备类型",options:""},{name:"node_id",width:160,label:"设备ID",options:""},{name:"hardware_ver",width:100,label:"硬件版本",options:""},{name:"software_ver",width:100,label:"软件版本",options:""},{name:"v_create_warning",width:100,label:"触发阈值",options:""},{name:"v_close_warning",width:120,label:"关闭触发阈值",options:""},{name:"detect_interval",width:100,label:"检测周期",options:""},{name:"heart_interval",width:100,label:"心跳周期",options:""},{name:"heart_interval_offset",width:130,label:"心跳周期偏移量",options:""},{name:"awake_interval",width:100,label:"唤醒周期",options:""}],nodeInitSettingVisible:!1}},mounted:function(){this.getList()},directives:{},methods:{getList:function(){var t=this;t.ListLoading=!0,this.$http.postDataEx("device","getDeviceType",{num:0},function(e){if(0==e.code){var i=e.data;t.deviceTypeSettingData=i}t.ListLoading=!1})},initAddForm:function(t){for(var e in t)t[e]=""},resetForm:function(t){this.$refs[t].resetFields()},UploadComplete:function(t,e){this.addForm[e]=t},selsChange:function(){},getDeviceTypeSetting:function(){this.getTypeList()},getTypeList:function(){var t=this;this.$http.postDataEx("device","getDeviceType",{num:0},function(e){if(0==e.code){var i=e.data;t.deviceTypeSettingData=i}})},typeCreateSubmit:function(t){var e=this;this.typeAddForm.mod=1,this.typeAddLoading=!0,this.$refs[t].validate(function(t){t?e.$http.postDataEx("device","updateDeviceType",e.typeAddForm,function(t){0==t.code&&(e.$notify({title:"成功",message:"新增成功",type:"success"}),e.getTypeList(),e.typeAddFormVisible=!1,e.initAddForm(e.typeAddForm))}):e.$notify({title:"警告",message:"请先确认信息是否填写完整",type:"warning"}),e.typeAddLoading=!1})},typeEdit:function(t){var e=this;this.typeEditFormVisible=!0,this.$http.postDataEx("device","getDeviceType",{num:t.id},function(t){if(0==t.code){var i=t.data;e.typeEditForm=i[0]}})},typeEditSubmit:function(){var t=this;this.$http.postDataEx("device","updateDeviceType",{id:this.typeEditForm.id,type_name:this.typeEditForm.type_name,num:this.typeEditForm.num,is_battery:this.typeEditForm.is_battery,category:this.typeEditForm.category,numbering_rules:this.typeEditForm.numbering_rules,lora:this.typeEditForm.lora,iot:this.typeEditForm.iot,detect_interval:this.typeEditForm.detect_interval,heart_interval:this.typeEditForm.heart_interval,mod:2},function(e){0==e.code?(t.$notify({title:"成功",message:"编辑成功",type:"success"}),t.typeEditFormVisible=!1,t.getTypeList()):(t.$notify({title:"警告",message:"编辑失败",type:"warning"}),t.typeEditFormVisible=!1)})},typeDel:function(t){var e=this,i=t.id;this.$confirm("确认删除该类型？","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){e.$http.postDataEx("device","updateDeviceType",{id:i,type_name:"",num:"",is_battery:"",manufacturer:"",category:"",numbering_rules:"",iot:"",lora:"",mod:3},function(t){0==t.code&&(e.$notify({title:"成功",message:"删除成功",type:"success"}),e.getTypeList())})}).catch(function(){e.$message({type:"info",message:"已取消删除"})})},getNodeInitSetting:function(){this.nodeInitSettingVisible=!0;var t=this;this.$http.postDataEx("device","getNodeInitSetting",{},function(e){if(0==e.code){var i=e.data;t.nodeInitSettingData1=i.list1,t.nodeInitSettingData2=i.list2}})}},components:{nvPublish:i("k8oq").a},watch:{}},l={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"inbound_manager",staticStyle:{height:"100%"}},[i("el-col",{staticClass:"toolbar",staticStyle:{"padding-bottom":"0px"},attrs:{span:24}},[i("el-form",{attrs:{inline:!0}},[i("el-form-item",[i("el-button",{staticClass:"filter-item",attrs:{type:"warning",icon:"el-icon-view",round:""},on:{click:t.getNodeInitSetting}},[t._v("阈值查询")])],1),t._v(" "),i("el-form-item",{staticStyle:{float:"right"}},[i("el-button",{staticClass:"filter-item",attrs:{type:"primary",icon:"el-icon-circle-plus-outline"},on:{click:function(e){t.typeAddFormVisible=!0}}},[t._v("新增")])],1)],1)],1),t._v(" "),i("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.ListLoading,expression:"ListLoading"}],staticStyle:{width:"100%",height:"100%"},attrs:{data:t.deviceTypeSettingData},on:{"selection-change":t.selsChange}},[i("el-table-column",{attrs:{type:"selection",width:"55"}}),t._v(" "),t._l(t.deviceTypeSettingTableCol,function(e){return i("el-table-column",{key:e.name,attrs:{prop:e.name,label:e.label,width:e.width},scopedSlots:t._u([{key:"default",fn:function(a){return["input"===e.type||"none"===e.type||"select"===e.type?i("span",{staticClass:"cell-edit-input"},[i("span",[t._v(t._s(a.row[e.name]?a.row[e.name]:"/"))])]):t._e(),t._v(" "),"upload"===e.type?i("span",{staticClass:"cell-edit-input"},[i("a",{staticClass:"el-icon-download",attrs:{target:"_blank",href:a.row[e.name]}})]):t._e()]}}])})}),t._v(" "),i("el-table-column",{attrs:{fixed:"right",label:"操作",width:"150"},scopedSlots:t._u([{key:"default",fn:function(e){return[i("el-button",{attrs:{type:"text",size:"small"},on:{click:function(i){t.typeEdit(e.row)}}},[t._v("编辑")]),t._v(" "),i("el-button",{attrs:{type:"text",size:"small"},on:{click:function(i){t.typeDel(e.row)}}},[t._v("删除")])]}}])})],2),t._v(" "),i("el-dialog",{attrs:{title:"新增",visible:t.typeAddFormVisible,"close-on-click-modal":!1},on:{"update:visible":function(e){t.typeAddFormVisible=e}}},[i("el-form",{ref:"typeAddForm",staticClass:"demo-ruleForm",attrs:{model:t.typeAddForm,"label-width":"80px",rules:t.typeAddFormRules}},[i("el-form-item",{attrs:{label:"类型名",prop:"type_name"}},[i("el-input",{model:{value:t.typeAddForm.type_name,callback:function(e){t.$set(t.typeAddForm,"type_name",e)},expression:"typeAddForm['type_name']"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"编号",prop:"num"}},[i("el-input",{model:{value:t.typeAddForm.num,callback:function(e){t.$set(t.typeAddForm,"num",t._n(e))},expression:"typeAddForm['num']"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"电池供电",prop:"is_battery"}},[i("el-select",{attrs:{placeholder:"请选择"},model:{value:t.typeAddForm.is_battery,callback:function(e){t.$set(t.typeAddForm,"is_battery",e)},expression:"typeAddForm['is_battery']"}},[i("el-option",{attrs:{label:"是",value:"1"}}),t._v(" "),i("el-option",{attrs:{label:"否",value:"0"}})],1)],1),t._v(" "),i("el-form-item",{attrs:{label:"来源厂家",prop:"manufacturer"}},[i("el-input",{model:{value:t.typeAddForm.manufacturer,callback:function(e){t.$set(t.typeAddForm,"manufacturer",e)},expression:"typeAddForm['manufacturer']"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"类别",prop:"category"}},[i("el-input",{model:{value:t.typeAddForm.category,callback:function(e){t.$set(t.typeAddForm,"category",e)},expression:"typeAddForm['category']"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"编号规则",prop:"numbering_rules"}},[i("el-select",{attrs:{placeholder:"请选择"},model:{value:t.typeAddForm.numbering_rules,callback:function(e){t.$set(t.typeAddForm,"numbering_rules",e)},expression:"typeAddForm['numbering_rules']"}},[i("el-option",{attrs:{label:"规律",value:"1"}}),t._v(" "),i("el-option",{attrs:{label:"无规律",value:"0"}})],1)],1),t._v(" "),i("el-form-item",{attrs:{label:"lora",prop:"lora"}},[i("el-select",{attrs:{placeholder:"请选择"},model:{value:t.typeAddForm.lora,callback:function(e){t.$set(t.typeAddForm,"lora",e)},expression:"typeAddForm['lora']"}},[i("el-option",{attrs:{label:"添加",value:"1"}}),t._v(" "),i("el-option",{attrs:{label:"不添加",value:"0"}})],1)],1),t._v(" "),i("el-form-item",{attrs:{label:"IOT",prop:"iot"}},[i("el-select",{attrs:{placeholder:"请选择"},model:{value:t.typeAddForm.iot,callback:function(e){t.$set(t.typeAddForm,"iot",e)},expression:"typeAddForm['iot']"}},[i("el-option",{attrs:{label:"是",value:"1"}}),t._v(" "),i("el-option",{attrs:{label:"否",value:"0"}})],1)],1),t._v(" "),i("el-form-item",{attrs:{label:"检测周期",prop:"detect_interval"}},[i("el-input",{model:{value:t.typeAddForm.detect_interval,callback:function(e){t.$set(t.typeAddForm,"detect_interval",e)},expression:"typeAddForm['detect_interval']"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"心跳周期",prop:"heart_interval"}},[i("el-input",{model:{value:t.typeAddForm.heart_interval,callback:function(e){t.$set(t.typeAddForm,"heart_interval",e)},expression:"typeAddForm['heart_interval']"}})],1)],1),t._v(" "),i("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[i("el-button",{nativeOn:{click:function(e){t.typeAddFormVisible=!1}}},[t._v("取消")]),t._v(" "),i("el-button",{on:{click:function(e){t.resetForm("typeAddForm")}}},[t._v("重置")]),t._v(" "),i("el-button",{attrs:{type:"primary",loading:t.typeAddLoading},on:{click:function(e){t.typeCreateSubmit("typeAddForm")}}},[t._v("确定")])],1)],1),t._v(" "),i("el-dialog",{attrs:{title:"类型编辑",visible:t.typeEditFormVisible},on:{"update:visible":function(e){t.typeEditFormVisible=e}}},[i("el-form",{ref:"typeEditForm",attrs:{model:t.typeEditForm,"label-width":"100px",rules:t.typeAddFormRules}},[i("el-form-item",{attrs:{label:"编号",prop:"num"}},[i("el-input",{model:{value:t.typeEditForm.num,callback:function(e){t.$set(t.typeEditForm,"num",e)},expression:"typeEditForm['num']"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"类型名",prop:"type_name"}},[i("el-input",{model:{value:t.typeEditForm.type_name,callback:function(e){t.$set(t.typeEditForm,"type_name",e)},expression:"typeEditForm['type_name']"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"电池供电",prop:"is_battery"}},[i("el-select",{attrs:{placeholder:"请选择"},model:{value:t.typeEditForm.is_battery,callback:function(e){t.$set(t.typeEditForm,"is_battery",e)},expression:"typeEditForm['is_battery']"}},[i("el-option",{attrs:{label:"是",value:"1"}}),t._v(" "),i("el-option",{attrs:{label:"否",value:"0"}})],1)],1),t._v(" "),i("el-form-item",{attrs:{label:"来源厂家",prop:"manufacturer"}},[i("el-input",{model:{value:t.typeEditForm.manufacturer,callback:function(e){t.$set(t.typeEditForm,"manufacturer",e)},expression:"typeEditForm['manufacturer']"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"类别",prop:"category"}},[i("el-input",{model:{value:t.typeEditForm.category,callback:function(e){t.$set(t.typeEditForm,"category",e)},expression:"typeEditForm['category']"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"编号规则",prop:"numbering_rules"}},[i("el-select",{attrs:{placeholder:"请选择"},model:{value:t.typeEditForm.numbering_rules,callback:function(e){t.$set(t.typeEditForm,"numbering_rules",e)},expression:"typeEditForm['numbering_rules']"}},[i("el-option",{attrs:{label:"规律",value:"1"}}),t._v(" "),i("el-option",{attrs:{label:"无规律",value:"0"}})],1)],1),t._v(" "),i("el-form-item",{attrs:{label:"lora",prop:"lora"}},[i("el-select",{attrs:{placeholder:"请选择"},model:{value:t.typeEditForm.lora,callback:function(e){t.$set(t.typeEditForm,"lora",e)},expression:"typeEditForm['lora']"}},[i("el-option",{attrs:{label:"添加",value:"1"}}),t._v(" "),i("el-option",{attrs:{label:"不添加",value:"0"}})],1)],1),t._v(" "),i("el-form-item",{attrs:{label:"IOT",prop:"iot"}},[i("el-select",{attrs:{placeholder:"请选择"},model:{value:t.typeEditForm.iot,callback:function(e){t.$set(t.typeEditForm,"iot",e)},expression:"typeEditForm['iot']"}},[i("el-option",{attrs:{label:"是",value:"1"}}),t._v(" "),i("el-option",{attrs:{label:"否",value:"0"}})],1)],1),t._v(" "),i("el-form-item",{attrs:{label:"检测周期",prop:"detect_interval"}},[i("el-input",{model:{value:t.typeEditForm.detect_interval,callback:function(e){t.$set(t.typeEditForm,"detect_interval",e)},expression:"typeEditForm['detect_interval']"}})],1),t._v(" "),i("el-form-item",{attrs:{label:"心跳周期",prop:"heart_interval"}},[i("el-input",{model:{value:t.typeEditForm.heart_interval,callback:function(e){t.$set(t.typeEditForm,"heart_interval",e)},expression:"typeEditForm['heart_interval']"}})],1)],1),t._v(" "),i("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[i("el-button",{nativeOn:{click:function(e){t.typeEditFormVisible=!1}}},[t._v("取消")]),t._v(" "),i("el-button",{attrs:{type:"primary",loading:t.typeEditLoading},nativeOn:{click:function(e){t.typeEditSubmit(e)}}},[t._v("确定")])],1)],1),t._v(" "),i("el-dialog",{attrs:{title:"阈值查询",width:"70%",visible:t.nodeInitSettingVisible,"show-close":!1},on:{"update:visible":function(e){t.nodeInitSettingVisible=e}}},[i("el-card",{staticClass:"box-card"},[i("div",{staticClass:"clearfix",attrs:{slot:"header"},slot:"header"},[i("span",[t._v("默认阈值")])]),t._v(" "),i("el-table",{staticStyle:{width:"100%",height:"100%"},attrs:{data:t.nodeInitSettingData1}},t._l(t.nodeInitSettingTableCol1,function(e){return i("el-table-column",{key:e.name,attrs:{prop:e.name,label:e.label,width:e.width},scopedSlots:t._u([{key:"default",fn:function(a){return[i("span",{staticClass:"cell-edit-input"},[i("span",[t._v(t._s(a.row[e.name]?a.row[e.name]:"/"))])])]}}])})}))],1),t._v(" "),i("el-card",{staticClass:"box-card"},[i("div",{staticClass:"clearfix",attrs:{slot:"header"},slot:"header"},[i("span",[t._v("特殊阈值")])]),t._v(" "),i("el-table",{staticStyle:{width:"100%"},attrs:{data:t.nodeInitSettingData2,height:"280"}},t._l(t.nodeInitSettingTableCol2,function(e){return i("el-table-column",{key:e.name,attrs:{prop:e.name,label:e.label,width:e.width},scopedSlots:t._u([{key:"default",fn:function(a){return[i("span",{staticClass:"cell-edit-input"},[i("span",[t._v(t._s(a.row[e.name]?a.row[e.name]:"/"))])])]}}])})}))],1),t._v(" "),i("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[i("el-button",{attrs:{type:"primary"},nativeOn:{click:function(e){t.nodeInitSettingVisible=!1}}},[t._v("返回")])],1)],1)],1)},staticRenderFns:[]};var o=i("VU/8")(a,l,!1,function(t){i("tily")},null,null);e.default=o.exports},tily:function(t,e,i){var a=i("HWbk");"string"==typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);i("rjj0")("736975c4",a,!0)}});