webpackJsonp([27],{"5wHv":function(t,e,l){var i=l("TLGR");"string"==typeof i&&(i=[[t.i,i,""]]),i.locals&&(t.exports=i.locals);l("rjj0")("60c01dd8",i,!0)},TLGR:function(t,e,l){(t.exports=l("FZ+f")(!1)).push([t.i,"\n.box-class {\n  -webkit-box-shadow: 2px 0px 6px 0px #545554;\n          box-shadow: 2px 0px 6px 0px #545554;\n}\n",""])},tPCa:function(t,e,l){"use strict";Object.defineProperty(e,"__esModule",{value:!0});l("Icdr");l("GbHy"),l("4UDB"),l("Oq2I"),l("miEh");var i={computed:{rules:function(){return{}}},data:function(){return{listFilter:{page:1,start:0,length:10,user:"",type:"",result:""},handleFilterLoading:!1,handleExportLoading:!1,listLoading:!1,addFormVisible:!1,list:[],total:0}},mounted:function(){this.getList(),this.getProductFromStock(),this.getProjectFromStock()},methods:{handleFilter:function(){this.listFilter.page=1,this.getList()},getList:function(){var t=this;this.$refs.filterForm.validate(function(e){e&&(t.listLoading=!0,t.$http.postDataEx("device","searchStockInfo",{product:t.listFilter.product,supplier:t.listFilter.supplier,project_name:t.listFilter.project_name,page:t.listFilter.page-1,length:t.listFilter.length},function(e){0==e.code&&(t.list=e.data.list,t.total=Number(e.data.count)),t.listLoading=!1}))})},mergeList:function(t){t.edit=!1,this.list.push(t)},handleSizeChange:function(t){this.listFilter.length=t,this.listFilter.page=1,this.listFilter.start=0,this.getList()},handleCurrentChange:function(t){this.listFilter.page=t,this.listFilter.start=this.listFilter.length*(t-1),this.getList()},handleExport:function(){var t=this;this.$refs.filterForm.validate(function(e){e&&(t.handleExportLoading=!0,t.$http.postDataEx("device","exportDeviceStockInfo",{product:t.listFilter.product,supplier:t.listFilter.supplier,project_name:t.listFilter.project_name},function(e){0==e.code&&window.open(e.data.info),t.handleExportLoading=!1}))})},changeRangeType:function(){this.listFilter.date=null},initAddForm:function(t){for(var e in t)t[e]=""},resetForm:function(t){this.$refs[t].resetFields()}}},r={render:function(){var t=this,e=t.$createElement,l=t._self._c||e;return l("div",{staticClass:"log_manager"},[l("el-row",{attrs:{gutter:12}},[l("div",{staticClass:"filter-container"},[l("el-form",{ref:"filterForm",attrs:{inline:!0,model:t.listFilter,rules:t.rules}},[l("el-form-item",{attrs:{label:"用户",prop:"product"}},[l("el-input",{attrs:{placeholder:"请输入用户",clearable:""},model:{value:t.listFilter.user,callback:function(e){t.$set(t.listFilter,"user",e)},expression:"listFilter.user"}})],1),t._v(" "),l("el-form-item",{attrs:{label:"日志类型",prop:"project_name"}},[l("el-select",{attrs:{clearable:"",placeholder:"请选择日志类型"},model:{value:t.listFilter.type,callback:function(e){t.$set(t.listFilter,"type",e)},expression:"listFilter.type"}},[l("el-option",{attrs:{label:"操作日志",value:"操作日志"}})],1)],1),t._v(" "),l("el-form-item",{attrs:{label:"操作结果",prop:"supplier"}},[l("el-select",{attrs:{clearable:"",placeholder:"请选择操作结果"},model:{value:t.listFilter.result,callback:function(e){t.$set(t.listFilter,"result",e)},expression:"listFilter.result"}},[l("el-option",{attrs:{label:"成功",value:"成功"}}),t._v(" "),l("el-option",{attrs:{label:"失败",value:"失败"}})],1)],1),t._v(" "),l("el-form-item",[l("el-button",{staticClass:"filter-item",attrs:{type:"primary",icon:"el-icon-search",loading:t.handleFilterLoading},on:{click:t.handleFilter}},[t._v(t._s(t.$t("common.search")))]),t._v(" "),l("el-button",{staticClass:"filter-item",attrs:{type:"info",icon:"el-icon-download",disabled:t.handleExportLoading,loading:t.handleExportLoading},on:{click:t.handleExport}},[t._v(t._s(t.$t("common.export")))])],1)],1)],1),t._v(" "),l("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.listLoading,expression:"listLoading"}],ref:"dataTable",attrs:{data:t.list,height:"500",fit:"","highlight-current-row":"",stripe:""}},[l("el-table-column",{attrs:{prop:"user",label:"用户"}}),t._v(" "),l("el-table-column",{attrs:{prop:"action",label:"操作行为"}}),t._v(" "),l("el-table-column",{attrs:{prop:"target",label:"操作对象"}}),t._v(" "),l("el-table-column",{attrs:{prop:"type",label:"操作类型"}}),t._v(" "),l("el-table-column",{attrs:{prop:"result",label:"操作结果"}}),t._v(" "),l("el-table-column",{attrs:{prop:"time",label:"时间"}})],1),t._v(" "),l("div",{staticClass:"pagination-container"},[l("el-pagination",{attrs:{background:"","current-page":t.listFilter.page,"page-sizes":[10,20,30,50],"page-size":t.listFilter.length,layout:"total, sizes, prev, pager, next, jumper",total:t.total},on:{"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange}})],1)],1)],1)},staticRenderFns:[]};var a=l("VU/8")(i,r,!1,function(t){l("5wHv")},null,null);e.default=a.exports}});