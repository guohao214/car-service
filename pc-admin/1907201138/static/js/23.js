webpackJsonp([23],{"+emG":function(t,e,l){(t.exports=l("FZ+f")(!1)).push([t.i,"\n.box-class {\n  -webkit-box-shadow: 2px 0px 6px 0px #545554;\n          box-shadow: 2px 0px 6px 0px #545554;\n}\n",""])},UNgd:function(t,e,l){var r=l("+emG");"string"==typeof r&&(r=[[t.i,r,""]]),r.locals&&(t.exports=r.locals);l("rjj0")("f09b77ea",r,!0)},iiBv:function(t,e,l){"use strict";Object.defineProperty(e,"__esModule",{value:!0});l("Icdr");l("GbHy"),l("4UDB"),l("Oq2I"),l("miEh");var r={computed:{rules:function(){return{}}},data:function(){return{listFilter:{page:1,start:0,length:10,product:"",supplier:""},handleFilterLoading:!1,handleExportLoading:!1,listLoading:!1,addFormVisible:!1,list:[],total:0,product_array:[],project_array:[]}},mounted:function(){this.getList(),this.getProductFromStock(),this.getProjectFromStock()},methods:{handleFilter:function(){this.listFilter.page=1,this.getList()},getProductFromStock:function(){this.product_array=[];var t=this;this.$http.postDataEx("device","getProductFromStock",{},function(e){0==e.code&&e.data.forEach(function(e,l){var r=[];r.value=e.product,r.label=e.product,t.product_array.push(r)})})},getProjectFromStock:function(){this.project_array=[];var t=this;this.$http.postDataEx("device","getProjectFromStock",{},function(e){0==e.code&&e.data.forEach(function(e,l){var r=[];r.value=e.project_name,r.label=e.project_name,t.project_array.push(r)})})},getList:function(){var t=this;this.$refs.filterForm.validate(function(e){e&&(t.listLoading=!0,t.$http.postDataEx("device","searchStockInfo",{product:t.listFilter.product,supplier:t.listFilter.supplier,project_name:t.listFilter.project_name,page:t.listFilter.page-1,length:t.listFilter.length},function(e){0==e.code&&(t.list=e.data.list,t.total=Number(e.data.count)),t.listLoading=!1}))})},mergeList:function(t){t.edit=!1,this.list.push(t)},handleSizeChange:function(t){this.listFilter.length=t,this.listFilter.page=1,this.listFilter.start=0,this.getList()},handleCurrentChange:function(t){this.listFilter.page=t,this.listFilter.start=this.listFilter.length*(t-1),this.getList()},handleExport:function(){var t=this;this.$refs.filterForm.validate(function(e){e&&(t.handleExportLoading=!0,t.$http.postDataEx("device","exportDeviceStockInfo",{product:t.listFilter.product,supplier:t.listFilter.supplier,project_name:t.listFilter.project_name},function(e){0==e.code&&window.open(e.data.info),t.handleExportLoading=!1}))})},changeRangeType:function(){this.listFilter.date=null},initAddForm:function(t){for(var e in t)t[e]=""},resetForm:function(t){this.$refs[t].resetFields()}}},a={render:function(){var t=this,e=t.$createElement,l=t._self._c||e;return l("div",{staticClass:"stock_manager"},[l("el-row",{attrs:{gutter:12}},[l("div",{staticClass:"filter-container"},[l("el-form",{ref:"filterForm",attrs:{inline:!0,model:t.listFilter,rules:t.rules}},[l("el-form-item",{attrs:{label:"产品名",prop:"product"}},[l("el-select",{attrs:{clearable:"",placeholder:"请选择产品"},on:{change:t.getProductFromStock},model:{value:t.listFilter.product,callback:function(e){t.$set(t.listFilter,"product",e)},expression:"listFilter.product"}},t._l(t.product_array,function(t,e){return l("el-option",{attrs:{label:t.label,value:t.value}})}))],1),t._v(" "),l("el-form-item",{attrs:{label:"项目",prop:"project_name"}},[l("el-select",{attrs:{clearable:"",placeholder:"请选择项目"},on:{change:t.getProjectFromStock},model:{value:t.listFilter.project_name,callback:function(e){t.$set(t.listFilter,"project_name",e)},expression:"listFilter.project_name"}},t._l(t.project_array,function(t,e){return l("el-option",{attrs:{label:t.label,value:t.value}})}))],1),t._v(" "),l("el-form-item",{attrs:{label:"供应商",prop:"supplier"}},[l("el-input",{attrs:{placeholder:"请输入供应商",clearable:""},model:{value:t.listFilter.supplier,callback:function(e){t.$set(t.listFilter,"supplier",e)},expression:"listFilter.supplier"}})],1),t._v(" "),l("el-form-item",[l("el-button",{staticClass:"filter-item",attrs:{type:"primary",icon:"el-icon-search",loading:t.handleFilterLoading},on:{click:t.handleFilter}},[t._v(t._s(t.$t("common.search")))]),t._v(" "),l("el-button",{staticClass:"filter-item",attrs:{type:"info",icon:"el-icon-download",disabled:t.handleExportLoading,loading:t.handleExportLoading},on:{click:t.handleExport}},[t._v(t._s(t.$t("common.export")))])],1)],1)],1),t._v(" "),l("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.listLoading,expression:"listLoading"}],ref:"dataTable",attrs:{data:t.list,height:"500",fit:"","highlight-current-row":"",stripe:""}},[l("el-table-column",{attrs:{prop:"brand",label:"品牌"}}),t._v(" "),l("el-table-column",{attrs:{prop:"product",label:"产品"}}),t._v(" "),l("el-table-column",{attrs:{prop:"count",label:"数量"}}),t._v(" "),l("el-table-column",{attrs:{prop:"unit_price",label:"单价"}}),t._v(" "),l("el-table-column",{attrs:{prop:"total_price",label:"总价"}}),t._v(" "),l("el-table-column",{attrs:{prop:"purchase_num",label:"采购编号"}}),t._v(" "),l("el-table-column",{attrs:{prop:"project_num",label:"项目编号"}}),t._v(" "),l("el-table-column",{attrs:{prop:"project_name",label:"项目名称"}}),t._v(" "),l("el-table-column",{attrs:{prop:"supplier",label:"供应商名称"}})],1),t._v(" "),l("div",{staticClass:"pagination-container"},[l("el-pagination",{attrs:{background:"","current-page":t.listFilter.page,"page-sizes":[10,20,30,50],"page-size":t.listFilter.length,layout:"total, sizes, prev, pager, next, jumper",total:t.total},on:{"size-change":t.handleSizeChange,"current-change":t.handleCurrentChange}})],1)],1)],1)},staticRenderFns:[]};var i=l("VU/8")(r,a,!1,function(t){l("UNgd")},null,null);e.default=i.exports}});