webpackJsonp([7],{E4GW:function(t,e,i){var a=i("XQ4n");"string"==typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);i("rjj0")("5c15ccc9",a,!0)},LY8U:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});i("cpGA");var a=i("k8oq"),n=(i("NYxO"),i("9/zi"),i("qqHy")),l=i.n(n),o={data:function(){return{page:0,count:10,total:0,files:[],datalist:[],tableCol:[{name:"name",width:200,label:"客户名称",add_disable:!0,edit_disable:!0,type:"input"},{name:"mobile",width:200,label:"手机号",add_disable:!1,edit_disable:!1,type:"input"},{name:"city",width:250,label:"城市",add_disable:!1,edit_disable:!1,type:"input"},{name:"brand_name",width:250,label:"品牌名称",add_disable:!1,edit_disable:!1,type:"input"},{name:"scan_info",width:250,label:"浏览历史",add_disable:!1,edit_disable:!1,type:"input"}],editFormVisible:!1,addFormVisible:!1,editForm:{},editFormRules:{name:[{required:!0,message:"请输入姓名",trigger:"blur"}]},editLoading:!1,addForm:{name:"",cn_name:"",logo:""},addFormRules:{},addLoading:!1,options:{},filter:{search:"",platform:"",device_type:"",type:"",is_force:"",work_type:""},ListLoading:!1,LaunchActivity:[],imgs:[]}},computed:{},mounted:function(){this.initOptions(),this.getActivityList(),this.getList()},directives:{},methods:{UploadComplete:function(t,e){this.addForm[e]=t},initOptions:function(){for(var t in this.tableCol)"select"==this.tableCol[t].type&&(this.tableCol[t].options=this.options[this.tableCol[t].name])},getActivityList:function(){},getList:function(t){var e=this;t&&(this.page=0),this.ListLoading=!0,this.$http.postDataEx("member","webMemberList",{page:this.page,length:this.count,search:this.filter.search},function(t){if(0==t.code){var i=t.data.list;e.datalist=i,e.total=Number(t.data.total)}e.ListLoading=!1})},createSubmit:function(){var t=this;if(this.addLoading=!0,0==this.files.length)return this.$notify({title:"失败",message:"图片为空",type:"error"}),void(this.addFormVisible=!1);this.addForm.logo=this.files[0].url,this.$http.postDataEx("brand","webAddBrand",this.addForm,function(e){0==e.code&&(t.$notify({title:"成功",message:"新增成功",type:"success"}),t.files=[],t.getList(),t.addFormVisible=!1,t.initAddForm()),t.addLoading=!1})},initAddForm:function(){for(var t in this.addForm)this.addForm[t]=""},edit:function(t){this.editLoading=!1,this.editForm=t;var e=t.logo;if(null==e)this.files=[];else{var i=e.lastIndexOf("/"),a=e.substring(i+1);this.files=[{name:a,url:e}]}this.editFormVisible=!0,this.showImagePreview()},editSubmit:function(t){var e=this;if(this.editLoading=!0,0==this.files.length)return this.$notify({title:"失败",message:"图片为空",type:"error"}),void(this.editLoading=!1);this.editForm.logo=this.files[0].url,this.$http.postDataEx("brand","webEditBrand",this.editForm,function(t){0==t.code&&(e.$notify({title:"成功",message:"编辑成功",type:"success"}),e.files=[],e.getList(),e.editFormVisible=!1,e.initEditForm()),e.editLoading=!1})},del:function(t){var e=this,i=t.id;this.$confirm("确认删除？","提示",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then(function(){e.$http.postDataEx("member","webDeleteMember",{id:i},function(t){0==t.code&&(e.$notify({title:"成功",message:"删除成功",type:"success"}),e.getList())})}).catch(function(){e.$message({type:"info",message:"已取消删除"})})},selectItem:function(){this.page=0,this.getList()},selectChange:function(){},selsChange:function(){},handleCurrentChange:function(t){this.page=t-1,this.getList()},update:function(t){},imageChange:function(t){(new FormData).append("file",t)},beforeUpload:function(t){},uploadSuccess:function(t){var e=t.data,i=e.lastIndexOf("/"),a=e.substring(i+1);l()("ul.el-upload-list li:last-child").append(l()("<img class='preview-img' src='"+e+"'/>")),this.files.push({name:a,url:e}),console.log(this.fileList)},imageRemove:function(t){this.files=this.files.filter(function(e){return e.url!==t.url})},newSubmitForm:function(){this.$refs.newupload.submit()},showAddForm:function(){this.addLoading=!1,this.addFormVisible=!0,this.showImagePreview()},showImagePreview:function(){var t=this;setTimeout(function(){l()("li.el-upload-list__item").each(function(){var e=l()(this).attr("tabindex"),i=t.files[e].url;0==l()(this).find("img").length&&l()(this).append(l()("<img class='preview-img' src='"+i+"'/>"))})},1e3)}},components:{nvPublish:a.a},watch:{LaunchActivity:function(t,e){for(var i in this.LaunchActivity)this.options.launch_activity[this.LaunchActivity[i]]={value:this.LaunchActivity[i],label:this.LaunchActivity[i]}}},destoryed:function(){this.imgs=[]}},s={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"brand",staticStyle:{height:"100%"}},[i("el-col",{staticClass:"toolbar",staticStyle:{"padding-bottom":"0px"},attrs:{span:24}},[i("el-form",{attrs:{inline:!0}},[i("el-form-item",{staticStyle:{float:"right"},attrs:{label:"显示"}},[i("el-select",{ref:"count",staticStyle:{width:"70px"},attrs:{placeholder:"请选择"},on:{change:t.selectItem},model:{value:t.count,callback:function(e){t.count=e},expression:"count"}},[i("el-option",{attrs:{label:5,value:5}}),t._v(" "),i("el-option",{attrs:{label:10,value:10}}),t._v(" "),i("el-option",{attrs:{label:20,value:20}}),t._v(" "),i("el-option",{attrs:{label:50,value:50}})],1),t._v(" 条\n\t\t\t")],1),t._v(" "),i("el-form-item",{staticStyle:{float:"right","margin-right":"10px"}},[t._v("\n\t\t\t\t共 "),i("span",[t._v(t._s(t.total))]),t._v(" 条\n\t\t\t")])],1),t._v(" "),i("el-form",{attrs:{inline:!0}})],1),t._v(" "),i("el-table",{directives:[{name:"loading",rawName:"v-loading",value:t.ListLoading,expression:"ListLoading"}],staticStyle:{width:"100%",height:"100%"},attrs:{data:t.datalist},on:{"selection-change":t.selsChange}},[i("el-table-column",{attrs:{type:"selection",width:"55"}}),t._v(" "),t._l(t.tableCol,function(e){return i("el-table-column",{key:e.name,attrs:{prop:e.name,label:e.label,width:e.width},scopedSlots:t._u([{key:"default",fn:function(a){return["input"===e.type?i("span",{staticClass:"cell-edit-input"},[i("span",[t._v(t._s(a.row[e.name]?a.row[e.name]:"/"))])]):t._e(),t._v(" "),"upload"===e.type?i("span",{staticClass:"cell-edit-input"},[i("a",{staticClass:"el-icon-download",attrs:{target:"_blank",href:a.row[e.name]}})]):t._e(),t._v(" "),"select"===e.type?i("span",{staticClass:"cell-edit-input"},[i("div",{staticClass:"name-wrapper",attrs:{slot:"reference"},slot:"reference"},[t._v(t._s(t.options[e.name].hasOwnProperty([a.row[e.name]])?t.options[e.name][a.row[e.name]].label:"/"))])]):t._e()]}}])})}),t._v(" "),i("el-table-column",{attrs:{fixed:"right",label:"操作",width:"150"},scopedSlots:t._u([{key:"default",fn:function(e){return[i("el-button",{attrs:{type:"text",size:"small"},on:{click:function(i){t.edit(e.row)}}},[t._v("查看")]),t._v(" "),i("el-button",{attrs:{type:"text",size:"small"},on:{click:function(i){t.del(e.row)}}},[t._v("删除")])]}}])})],2),t._v(" "),i("el-col",{staticClass:"toolbar",attrs:{span:24}},[i("el-pagination",{staticStyle:{float:"right"},attrs:{background:"",layout:"prev, pager, next","current-page":t.page,"page-size":t.count,total:t.total},on:{"current-change":t.handleCurrentChange,"update:currentPage":function(e){t.page=e}}})],1),t._v(" "),i("el-dialog",{attrs:{title:"编辑",visible:t.editFormVisible},on:{"update:visible":function(e){t.editFormVisible=e}}},[i("el-form",{ref:"editForm",attrs:{model:t.editForm,"label-width":"80px",rules:t.editFormRules}},t._l(t.tableCol,function(e){return e.edit_disable?t._e():i("el-form-item",{key:e.id,attrs:{label:e.label,prop:e.name}},["input"===e.type?i("el-input",{attrs:{"auto-complete":"off",disabled:e.edit_disable},model:{value:t.editForm[e.name],callback:function(i){t.$set(t.editForm,e.name,i)},expression:"editForm[item.name]"}}):t._e(),t._v(" "),"select"===e.type?i("el-select",{attrs:{placeholder:"请选择"},on:{change:function(i){t.selectChange(e.name,i)}},model:{value:t.editForm[e.name],callback:function(i){t.$set(t.editForm,e.name,i)},expression:"editForm[item.name]"}},t._l(t.options[e.name],function(t){return i("el-option",{key:t.value,attrs:{label:t.label,value:t.value}})})):t._e(),t._v(" "),t._v(' \'index.php?m=home&c=brand&a=webUploadImage\'\n\t\t\t\t\taccept="image/jpeg,image/png,image/jpg"\n\t\t\t\t\t:before-upload="beforeUpload"\n\t\t\t\t\t:on-success="uploadSuccess"\n\t\t\t\t\t:on-change="imageChange"\n\t\t\t\t\t:on-remove="imageRemove"\n\t\t\t\t\tref="newupload"\n\t\t\t\t\t:limit="1"\n\t\t\t\t\t:file-list="files"\n\t\t\t\t\t:auto-upload="true">\n\t\t\t\t\t\t'),i("i",{staticClass:"el-icon-upload"})],1)})),t._v(" "),i("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[i("el-button",{nativeOn:{click:function(e){t.editFormVisible=!1}}},[t._v("取消")]),t._v(" "),i("el-button",{attrs:{type:"primary",loading:t.editLoading},nativeOn:{click:function(e){t.editSubmit(e)}}},[t._v("提交")])],1)],1),t._v(" "),i("el-dialog",{attrs:{title:"新增",visible:t.addFormVisible,"close-on-click-modal":!1},on:{"update:visible":function(e){t.addFormVisible=e}}},[i("el-form",{ref:"addForm",attrs:{model:t.addForm,"label-width":"80px",rules:t.addFormRules}},t._l(t.tableCol,function(e){return e.add_disable?t._e():i("el-form-item",{key:e.id,attrs:{label:e.label,prop:e.name}},["input"===e.type?i("el-input",{attrs:{"auto-complete":"off",disabled:e.add_disable},model:{value:t.addForm[e.name],callback:function(i){t.$set(t.addForm,e.name,i)},expression:"addForm[item.name]"}}):t._e(),t._v(" "),"select"===e.type&&"launch_activity"!=e.name&&"work_type"!=e.name?i("el-select",{attrs:{placeholder:"请选择"},on:{change:function(i){t.selectChange(e.name,i)}},model:{value:t.addForm[e.name],callback:function(i){t.$set(t.addForm,e.name,i)},expression:"addForm[item.name]"}},t._l(t.options[e.name],function(t){return i("el-option",{key:t.value,attrs:{label:t.label,value:t.value}})})):t._e(),t._v(" "),t._v(' \'index.php?m=home&c=brand&a=webUploadImage\'\n\t\t\t\t\taccept="image/jpeg,image/png,image/jpg"\n\t\t\t\t\t:before-upload="beforeUpload"\n\t\t\t\t\t:on-success="uploadSuccess"\n\t\t\t\t\t:on-change="imageChange"\n\t\t\t\t\t:on-remove="imageRemove"\n\t\t\t\t\tref="newupload"\n\t\t\t\t\t:limit="1"\n\t\t\t\t\t:file-list="files"\n\t\t\t\t\t:auto-upload="true">\n\t\t\t\t\t\t'),i("i",{staticClass:"el-icon-upload"}),t._v(" "),"upload"===e.type&&"Android"!==t.addForm.platform?i("el-input",{attrs:{"auto-complete":"off"},model:{value:t.addForm[e.name],callback:function(i){t.$set(t.addForm,e.name,i)},expression:"addForm[item.name]"}}):t._e()],1)})),t._v(" "),i("div",{staticClass:"dialog-footer",attrs:{slot:"footer"},slot:"footer"},[i("el-button",{nativeOn:{click:function(e){t.addFormVisible=!1}}},[t._v("取消")]),t._v(" "),i("el-button",{attrs:{type:"primary",loading:t.addLoading},nativeOn:{click:function(e){t.createSubmit(e)}}},[t._v("提交")])],1)],1)],1)},staticRenderFns:[]};var r=i("VU/8")(o,s,!1,function(t){i("E4GW")},null,null);e.default=r.exports},XQ4n:function(t,e,i){(t.exports=i("FZ+f")(!1)).push([t.i,"\n.btnUploader {\n  width: 100%;\n  height: 3em;\n  background-color: green;\n  color: #fff;\n  display: -webkit-box;\n  display: -ms-flexbox;\n  display: flex;\n  -webkit-box-pack: center;\n      -ms-flex-pack: center;\n          justify-content: center;\n  -webkit-box-align: center;\n      -ms-flex-align: center;\n          align-items: center;\n}\n.preview-img {\n  width: 100px;\n}\n",""])}});