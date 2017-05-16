init = function (){ 
	$$('#tablekit-table-1 tr').each(function(element){
		if(element.hasAttribute("href")){
		    element.style.cursor="pointer";
		    element.observe('click', function up(event) {
				var ele = event.element().ancestors()[0];
				window.location=ele.getAttribute("href");
		    });
		}
	});
	
	Validation.add('validate-codeMm','Please use only letters (a-z or A-Z), numbers (0-9) or underscore(_) in this field, first character should be a letter.',function (v) {
                return Validation.get('IsEmpty').test(v) ||  /^[a-zA-Z]+[a-zA-Z0-9_]+$/.test(v)
            },{});
	$$('.chosen-select').each(function(element){
		new Chosen(element,{});
	});
} 

var ReportManager = Class.create();
ReportManager.prototype = {
  initialize: function(url,urlAjax,croneditor) {
	  this.url = url;
	  this.urlAjax = urlAjax;
	  this.useAjax = false;
	  this.reloadParams = false;
	  this.containerId = "comboLoadReport";
	  this.filterVar  = false;
	  this.lastSqlCode = "";
	  this.showContainerSql = false;
	  this.action="";
	  this.reportJson = "";
	  this.groupsNew = 0;
	  this.ifrElemName = "import_post_target_frame";
	  this.rolePermission = "";
	  this.croneditor = croneditor;
	  this.marketLoaded = false;
	  this.marketReports = new Array();
	  this.cronLogContent = new Array();
	  this.cronLogError = new Array();
  },
  addVarToUrl : function(varName, varValue){
      var re = new RegExp('\/('+varName+'\/.*?\/)');
      var parts = this.url.split(new RegExp('\\?'));
      this.url = parts[0].replace(re, '/');
      this.url+= varName+'/'+varValue+'/';
      if(parts.size()>1) {
          this.url+= '?' + parts[1];
      }
      //this.url = this.url.replace(/([^:])\/{2,}/g, '$1/');
      return this.url;
  },
  removeVarToUrl : function(varName){
	  var re = new RegExp('\/('+varName+'\/.*?\/)');
      var parts = this.url.split(new RegExp('\\?'));
      this.url = parts[0].replace(re, '/');
      return this.url;
	  
  },showMessageWSLog : function(response){
	  var message = response.responseText.evalJSON();
	  var tipo = "";
	  var mensaje="";
	  
	  try{
			tipo=message[0].type;
			mensaje=message[0].msg;
	  }catch (e){
		tipo="error-msg";
		try{
			mensaje=message.message; 
		}catch (e2){
			mensaje = message;
		}
	  }
	  
	  $('messagesLog').addClassName(tipo);
	  $('messagesLog').parentNode.style.display="inline";
	  $('messagesLogText').innerHTML=mensaje;
	  
	  if(tipo=="success-msg"){
		  $('messagesLog').removeClassName("error-msg");
		  setTimeout(function() {$('messagesLog').parentNode.style.display="none";},5000);
	  }else{
	      $('messagesLog').removeClassName("success-msg");
		  setTimeout(function() {$('messagesLog').parentNode.style.display="none";},10000);
	  }
	  
	  
  },
  reload : function(url){
      if (!this.reloadParams) {
          this.reloadParams = {form_key: FORM_KEY};
      }
      else {
          this.reloadParams.form_key = FORM_KEY;
      }
      url = url || this.url;
      if(this.useAjax){
          new Ajax.Request(url + (url.match(new RegExp('\\?')) ? '&ajax=true' : '?ajax=true' ), {
              loaderArea: this.containerId,
              parameters: this.reloadParams || {},
              evalScripts: true,
              onFailure: this._processFailure.bind(this),
              onComplete: this.initGridAjax.bind(this),
              onSuccess: function(transport) {
                  try {
                      var responseText = transport.responseText.replace(/>\s+</g, '><');

                      if (transport.responseText.isJSON()) {
                          var response = transport.responseText.evalJSON()
                          if (response.error) {
                              alert(response.message);
                          }
                          if(response.ajaxExpired && response.ajaxRedirect) {
                              setLocation(response.ajaxRedirect);
                          }
                      } else {
                          /**
                           * For IE <= 7.
                           * If there are two elements, and first has name, that equals id of second.
                           * In this case, IE will choose one that is above
                           *
                           * @see https://prototype.lighthouseapp.com/projects/8886/tickets/994-id-selector-finds-elements-by-name-attribute-in-ie7
                           */
                          var divId = $(this.containerId);
                          if (divId.id == this.containerId) {
                              divId.update(responseText);
                          } else {
                              $$('div[id="'+this.containerId+'"]')[0].update(responseText);
                          }
                      }
                  } catch (e) {
                      var divId = $(this.containerId);
                      if (divId.id == this.containerId) {
                          divId.update(responseText);
                      } else {
                          $$('div[id="'+this.containerId+'"]')[0].update(responseText);
                      }
                  }
              }.bind(this)
          });
          return;
      }
      else{
          if(this.reloadParams){
              $H(this.reloadParams).each(function(pair){
                  url = this.addVarToUrl(pair.key, pair.value);
              }.bind(this));
          }
          location.href = url;
      }
  },
  ws : function(action,params,functionSuccess){
	  var manager = this;
	  new Ajax.Request(this.urlAjax + (this.urlAjax.match(new RegExp('\\?')) ? '&action='+action+'' : '?action='+action+'' ), {
			method: 'post',
			parameters: params,
			onSuccess: functionSuccess,
			onFailure : function(response){
				alert(response.responseText);
			}
	});
  },
  loadReport: function() {
	  var codeReport = $('general_code').value;
	  this.url=urlViewReport;
	  this.removeVarToUrl("filter");
	  this.addVarToUrl("action","load");
	  this.removeVarToUrl("page");
	  this.removeVarToUrl("rowPerPage");
	  this.reload(this.addVarToUrl("id", codeReport));
	  
  },
  loadReportAdmin: function() {
	  var codeReport = $('comboLoadReport').options[$('comboLoadReport').selectedIndex].value;
	  var manager = this;
	  this.action="saveReport";
	  this.removeComboAll();
	  this.ws('loadReport',{report_id:codeReport},function(response){
			var report = response.responseText.evalJSON();
			if(report[0].type == 'error-msg'){
					manager.showMessageWSLog(response);
			}else{
				  $('general_code').value=report[0].id;
				  $('general_title').value=report[0].title;
				  $('general_group').value=report[0].group_code;
				  $('sqlReport').value=report[0].sql;
				  
				  manager.codeMirrorSql('sqlReport',false);
				  
				  $$('#butDelReport')[0].style.display="inline";
				  $$('#duplicateReport')[0].style.display="inline";
				  $$('#butSaveReport')[0].style.display="inline";
				  $$('#butAddReport')[0].style.display="inline";
				  $$('#butExportReport')[0].style.display="inline";
				  $$('#butSendReportMarket')[0].style.display="inline";

				  $$('#titlePageReport')[0].innerHTML="Manage Report: "+report[0].title;
				  
				  var comboList = report[0].combos;
				  for(var i =0;i<comboList.length;i++){
					  $('combo4report-'+comboList[i]).addClassName('selected');
				  }
				  manager.addComboSelected();
				  				  
				  manager.reportJson = report;
			}
			  
			  
	  	});
  },
  manageReport: function(report_code){
	  var manager = this;
	  this.action="saveReport";
	  this.removeComboAll();
	  
	  var validator  = new Validation('reportEditGeneral_form');
	  validator.reset();
	  validator  = new Validation('report_editChartValueList_form');
	  validator.reset();
	  validator  = new Validation('reportEditlinkListTD_form');
	  validator.reset();
	  validator  = new Validation('reportEditRowlinkSelected_form');
	  validator.reset();
	  validator  = new Validation('reportEditCron_form');
	  validator.reset();
	  
	  this.ws('loadReport',{report_id:report_code},function(response){
			var report = response.responseText.evalJSON();
			if(report[0].type == 'error-msg'){
					manager.showMessageWSLog(response);
			}else{
			  $('reportEditRowlinkList').show();
	          $('reportEditRowlinkSelected').hide(); 
			  $('reportEditRowlinkSelectedID').value = "";
			  $$('#reportEditlinkListTD td[name=column] input').each(function(element){
					element.value='';
			  });
			  $('general_code').value=report[0].report_id;
			  $('general_code').setAttribute('disabled','true');
			  $('general_title').value=report[0].title;
			  $('general_group').value=report[0].group_id;
			  $('sqlReport').value=report[0].report_sql;
			  
			  
			  manager.codeMirrorSql('sqlReport',false);

			  $('titlePageReport').innerHTML="Manage Report: "+report[0].title;
			  var comboList = report[0].combos;
			  for(var i =0;i<comboList.length;i++){
				  var comboId = "";
				  var comboValue = "";
				  if(comboList[i].indexOf("=")>0){
					  comboId=comboList[i].substr(0,comboList[i].indexOf("="));
					  comboValue = comboList[i].substr(comboList[i].indexOf("=")+1,comboList[i].length);
				  }else{
					  comboId=comboList[i];
				  }
				  $('combo4report-'+comboId).addClassName('selected');
				  manager.addComboSelected();
				  if($$('input[id=cronCombo-'+comboId+']').length>0){
					  $$('input[id=cronCombo-'+comboId+']')[0].value=comboValue;
				  }
				  if($$('select[id=cronCombo-'+comboId+']').length>0){
					  if(""!=comboValue){
						  var comboElement = $$('select[id=cronCombo-'+comboId+']')[0];
						  var descripcion = comboValue.substr(comboValue.indexOf("$")+1,comboValue.length);
						  var value = comboValue.substr(0,comboValue.indexOf("$"));
						  var option =new Element('option',{'value':value});
						  option.insert(descripcion);
						  comboElement.insert(option);
					  }
				  }
			  }
			  manager.reportJson = report;
			  
			  manager.showChartConfig(report[0].atributes["chart_type"]);
			  
			  if(report[0].atributes["chart_type"] != null && ""!=report[0].atributes["chart_type"]){
				  $('report_chart_charxvalue').value=report[0].atributes["chartXValue"];
				  var chart_series = report[0].chart_series;
				  $('report_chart_charyvalue1').value=chart_series[0];
				  for(var i=0;i<chart_series.length;i++){
					  if(i>0) manager.addChartYValue();
					  $$('#chartValueList tr td.value input')[$$('#chartValueList tr td.value input').length-1].value=chart_series[i];
				  }
				  
			  }
				  
			  
			  $('butReturnReportList').style.display="inline";
			  $('butDelReport').style.display="inline";
			  $('duplicateReport').style.display="inline";
			  $('butSaveReport').style.display="inline";
			  $('butAddReport').style.display="none";
			  $('butViewReport').style.display="inline";
			  $('butExportReport').style.display="inline";
			  $('butSendReportMarket').style.display="inline";
			  
			  
			  var listLinkTR = report[0].linkTR;
			  if(listLinkTR.length==1){
					for(var key in listLinkTR[0]){
						var valor = listLinkTR[0][key];
						manager.reportEditRowLinkSelected(key,report[0].linkTRVariables);
						
					}
				
			  }
			  
			  var listLinkTD = report[0].linkTD;
			  if(listLinkTD.length>0){
				for(var i=0;i<listLinkTD.length;i++){
					for(var key in listLinkTD[i]){
						var valor = listLinkTD[i][key];
						$('reportEditRowLinkInput-'+key).value = valor;
					}
				}
			  }
			  
			  // Attributes
			  if(0!=report[0].atributes.length){
				  for(var key in report[0].atributes){
					  var valor =  report[0].atributes[key];
					  
					  if("columnGroup"==key){
						  var valoresGroup = valor.split("@@");
						  for(var i=0;i<valoresGroup.length;i++){  
							
							  var setGroup = valoresGroup[i].split("::");
							  manager.addGroupReportVal(setGroup[0],setGroup[1]);
						  }
						  
					  }else if($$('#container-ReportEdit #'+key).size()>0){
						  var button = $$('#container-ReportEdit #'+key)[0];
						  if(key.substr(0,3)=="xls"||key.substr(0,3)=="pdf") button.addClassName("changed");
						  
						  if(button.type=="select-one"){
							  button.value=valor;
						  }else if(button.type=="text"){
							  button.value=valor;
							  if(key.substr(0,3)=="xls"||key.substr(0,3)=="pdf"){
								  if(null!=document.getElementById(key+"Icon")) document.getElementById(key+"Icon").style.backgroundColor = '#'+valor;
							  }
						  }else if(button.type=="textarea"){
							  button.value=valor;
						  }else if(button.type=="radio"){
							  $$('input[type=radio][name=cronFileSave][value='+valor+']')[0].checked=true;
						  }else if(button.type=="checkbox"){
							  if("true"==valor){
								  button.checked = true;
							  }else{
								  button.checked = false;
							  }
						  }else if(key.substr(0,3)=="xls"||key.substr(0,3)=="pdf"){
							  if("true"==valor){
								  button.removeClassName("uncheck");
								  button.addClassName("check");
							  }else{
								  button.removeClassName("check");
								  button.addClassName("uncheck");
							  }
						  }
					  }
					  
				  }
			  }
			  if(report[0].atributes["xlsDefault"] == null || ""==report[0].atributes["xlsDefault"]||"true"==report[0].atributes["xlsDefault"]){
				  $$("input[name='xlsDefault'][value='true']")[0].checked=true;				  
				  manager.xlsActiveCustomize(true);
			  }else{
				  $$("input[name='xlsDefault'][value='false']")[0].checked=true;
				  manager.xlsActiveCustomize(false);
			  }
			  
			  if(report[0].atributes["pdfDefault"] == null || ""==report[0].atributes["pdfDefault"]||"true"==report[0].atributes["pdfDefault"]){
				  $$("input[name='pdfDefault'][value='true']")[0].checked=true;				  
				  manager.pdfActiveCustomize(true);
			  }else{
				  $$("input[name='pdfDefault'][value='false']")[0].checked=true;
				  manager.pdfActiveCustomize(false);
			  }
			  
			  if(report[0].atributes["cronString"] != null && ""!=report[0].atributes["cronString"]){
				  croneditor.setValue(report[0].atributes["cronString"]);
			  }else{
				  croneditor.reset();
			  }
			  
			  // Cron Log
			  manager.addCronLog(report[0].cronLog);
			  manager.showExpPDFOptions();
			  
			  $('container-ReportList').style.display="none";
			  $('container-ReportEdit').style.display="inline";
			  
			  manager.collapseClose('report_editSQL_field');
			  manager.collapseClose('report_editFilters_field');
			  manager.collapseClose('report_editChart_field');
			  manager.collapseClose('report_editLink_field');
			}
	  	});
  },
  doFilter : function(){
	  var codeReport = $('general_code').value;
      var filters = $$('#comboList .value input', '#comboList .value select');
      var elements = [];
      for(var i in filters){
		if("checkbox"==filters[i].type || "radio"==filters[i].type){
			if(filters[i].checked) elements.push(filters[i]);
		}else{
          if(filters[i].value && filters[i].value.length) elements.push(filters[i]);
		}
      }
      var validator  = new Validation('filter_form');
      var dofilter = ($('filter_form') == null)?true:validator.validate();
      if (dofilter) {
    	  if($('page')!=null){
    		  var page = $('page').value;
    		  this.addVarToUrl("page",page);
    	  }else{
    		  this.removeVarToUrl("page");
    	  }
    	  
    	  if($('rowPerPage')!=null){
    		  var rowPerPage = $('rowPerPage').value;
    		  this.addVarToUrl("rowPerPage",rowPerPage);
    	  }else{
    		  this.removeVarToUrl("rowPerPage");
    	  }
		  
		  var filtersHeader = [];
		  $$('#tablekit-table-1 thead tr[class=filter] input').each(function (element){
				if(""!=element.value) filtersHeader.push(element);
			});
		  $$('#tablekit-table-1 thead tr[class=filter] select').each(function (element){
				if(""!=element.value) filtersHeader.push(element);
			});
		  
		  if($('butOriginalSQL') && $('butOriginalSQL').visible()){
			  if(cmText != undefined) cmText.save();
			  var sqlCustom = $('sqlReport_edit').value;
			  this.addVarToUrl("sqlCustom",encode_base64(sqlCustom));
		  }else{
    		  this.removeVarToUrl("sqlCustom");
    	  }
		  this.addVarToUrl("filterHeader",encode_base64(Form.serializeElements(filtersHeader)));
    	  this.reload(this.addVarToUrl("filter", encode_base64(Form.serializeElements(elements))));
      }
  },
  doResetFilter : function(){
	$$('#tablekit-table-1 thead tr[class=filter] input').each(function (element){
		element.value="";
	});
	
	$$('#tablekit-table-1 thead tr[class=filter] select').each(function (element){
		element.value="";
	});
	
	this.doFilter();

  }, showSQL : function (codigo,readonly){
	  
	  $$('.CodeMirror').each(function (element){
		  element.remove();
	  });
	  cmText = undefined;
	  
	  this.codeMirrorSql(codigo,readonly);

	  this.lastSqlCode = codigo;
	  $$('#butRefParams')[0].style.display="none";
	  $$('#butValueParams')[0].style.display="inline";
	  if("sqlReport"==codigo){
		  $$('#butEditSQL')[0].style.display="inline";
		  $$('#butOriginalSQL')[0].style.display="none";
	  }else{
		  $$('#butEditSQL')[0].style.display="none";
		  $$('#butOriginalSQL')[0].style.display="inline";
	  }
	  
  },
  showSQLRefParams : function (){
	  
	  $$('.CodeMirror').each(function (element){
		  element.remove();
	  });
	  cmText = undefined;
	  
	  this.codeMirrorSql(this.lastSqlCode,true);
	  
	  $$('#butRefParams')[0].style.display="none";
	  $$('#butValueParams')[0].style.display="inline";
  },
  showSQLValueParams : function (){
	  
	  $$('.CodeMirror').each(function (element){
		  element.remove();
	  });
	  cmText = undefined;
	  
	  this.codeMirrorSql(this.lastSqlCode+"_value",true);
	  $$('#butRefParams')[0].style.display="inline";
	  $$('#butValueParams')[0].style.display="none";
  },
  showSQLHide : function (){
	  $$('.CodeMirror').each(function (element){
		  element.remove();
	  });
	  cmText = undefined;
	  $$('#butRefParams')[0].style.display="none";
	  $$('#butValueParams')[0].style.display="none";
	  $$('#butHideSql')[0].style.display="none";
  },
  editSQLReportView : function(){
	  $$('.CodeMirror').each(function (element){
		  element.remove();
	  });
	  cmText = undefined;
	  this.lastSqlCode = "sqlReport_edit";
	  
	  this.codeMirrorSql("sqlReport_edit",false);
	  $$('#butOriginalSQL')[0].style.display="inline";
	  $$('#butRefParams')[0].style.display="none";
	  $$('#butValueParams')[0].style.display="inline";
	  $$('#butEditSQL')[0].style.display="none";
  },
  showOriginalSql : function(){
	  $$('.CodeMirror').each(function (element){
		  element.remove();
	  });
	  cmText = undefined;
	  
	  this.lastSqlCode = "sqlReport";
	  this.codeMirrorSql('sqlReport',true);
	  
	  $$('#butOriginalSQL')[0].style.display="none";
	  $$('#butRefParams')[0].style.display="none";
	  $$('#butValueParams')[0].style.display="inline";
	  $$('#butEditSQL')[0].style.display="inline";
	  
	  
  },
  doExport : function(){
	  var codeReport = $('general_code').value;
      var filters = $$('#comboList .value input', '#comboList .value select');
      var elements = [];
      var method = $('exportList').value;
      for(var i in filters){
          if(filters[i].value && filters[i].value.length) elements.push(filters[i]);
      }
      var validator  = new Validation('filter_form');
	  var validateOk = true;
	  if($$('#filter_form').length>0){
	 	if(!validator.validate() ){
		  validateOk = false;
	 	}
	  }
      if (validateOk) { 
    	  var lastUrl = this.url;
    	  this.url=urlExport;
    	  this.addVarToUrl("method", method);
    	  
    	  this.reload(this.addVarToUrl("filter", encode_base64(Form.serializeElements(elements))));
    	  this.url=lastUrl;
      }
  },
  showMenu : function(option){
	  
	  if($$('#container-ReportList').length > 0){
		  $$('#container-ReportList')[0].style.display="none";
		  $$('#container-ReportList-menu')[0].removeClassName('active');
	  }
	  if($$('#container-ReportEdit').length > 0){
		  $$('#container-ReportEdit')[0].style.display="none";
		  $$('#container-ReportEdit-menu')[0].removeClassName('active');
	  }
	  if($$('#container-General').length > 0){
		  $$('#container-General')[0].style.display="none";
		  $$('#container-General-menu')[0].removeClassName('active');
	  }
	  if($$('#container-Sql').length > 0){
		  $$('#container-Sql')[0].style.display="none";
		  $$('#container-Sql-menu')[0].removeClassName('active');
	  }
	  if($$('#container-Combo').length > 0){
		  $$('#container-Combo')[0].style.display="none";
		  $$('#container-Combo-menu')[0].removeClassName('active');
	  }
	  if($$('#container-ComboAdd').length > 0){
		  $$('#container-ComboAdd')[0].style.display="none";
		  $$('#container-ComboAdd-menu')[0].removeClassName('active');
	  }
	  
	  if($$('#container-Group').length > 0){
		  $$('#container-Group')[0].style.display="none";
		  $$('#container-Group-menu')[0].removeClassName('active');
	  }
	  
	  if($$('#container-link').length > 0){
		  $$('#container-link')[0].style.display="none";
		  $$('#container-link-menu')[0].removeClassName('active');
	  }
	  
	  
	  if($$('#container-System').length > 0){
		  $$('#container-System')[0].style.display="none";
		  $$('#container-System-menu')[0].removeClassName('active');
	  }
	  
	  if($$('#container-Permissions').length > 0){
		  $$('#container-Permissions')[0].style.display="none";
		  $$('#container-Permissions-menu')[0].removeClassName('active');
	  }
	  
	  if($$('#container-Import').length > 0){
		  $$('#container-Import')[0].style.display="none";
		  $$('#container-Import-menu')[0].removeClassName('active');
	  }
	  
	  if($$('#container-Export').length > 0){
		  $$('#container-Export')[0].style.display="none";
		  $$('#container-Export-menu')[0].removeClassName('active');
		  $('report_list_import_result_field').innerHTML="";
	  }
	  
	  if($$('#container-Market').length > 0){
		  $$('#container-Market')[0].style.display="none";
		  $$('#container-Market-menu')[0].removeClassName('active');
	  }
	  
	  
	  $$('#'+option)[0].style.display="inline";
	  $$('#'+option+'-menu')[0].addClassName('active');
	  
	  if('container-Market'==option){
		  this.loadMarket();
	  }
	  
	  /*if(!this.showContainerSql){
		  this.codeMirrorSql('sqlReport',false);
		  this.showContainerSql = true;
	  }*/
	  
  },
  delReport: function (action,id){
	  
	  var codeReport = id || $('general_code').value;
	  
	  var manager = this;
	  this.ws('delReport',{report_id:codeReport},function(response){
			  var report = response.responseText.evalJSON();
			  manager.showMessageWSLog(response);
			  if(report[0].type=="success-msg"){
				  manager.removeReport2List(codeReport);
				  manager.refreshReportList();
				  
				  if(action=="reportList"){
					  $('general_code').value="";
					  $('general_title').value="";
					  $('general_group').value="";
					  $('sqlReport').value="";
					  
					  $('butAddReport').style.display="none";
					  $('butDelReport').style.display="none";
					  $('duplicateReport').style.display="none";
					  $('butSaveReport').style.display="none";
					  $('butExportReport').style.display="none";
					  $('butSendReportMarket').style.display="none";
					  
					  $('titlePageReport').innerHTML="Add Report";
					  
					  manager.codeMirrorSql('sqlReport',false);
					  manager.showMenu('container-ReportList');
				  }else if(action == "market"){

					  var tdAction = $$('#marketAction'+manager.marketReports[id])[0];
					  tdAction.innerHTML="";
					  var butonAction= new Element('button',{'type':'button','class':'scalable add','onclick':"reportManager.addReportMarket('add','"+manager.marketReports[id]+"')",'style':'display: inline'});
					  butonAction.insert(new Element('span').insert("Install"));
					  tdAction.insert(butonAction);
				  }
				  
				  
				  
			  }
	  	});
  },
  saveReport: function(){
	  cmText.save();
	   // Comprobaci�n de input requeridos
	  var formularios=true;
	  var validator  = new Validation('reportEditGeneral_form');
	  if (!validator.validate())  formularios=false;
	  
	  var validator  = new Validation('report_editChartValueList_form');
	  if (!validator.validate())  formularios=false;
	  
	  var validator  = new Validation('reportEditlinkListTD_form');
	  if (!validator.validate())  formularios=false;
	  
	  if($('reportEditRowlinkSelected').visible()){
		  var validator  = new Validation('reportEditRowlinkSelected_form');
		  if (!validator.validate())  formularios=false;
	  }
	  
	  if(this.isTinymceInit('cronEmailText')) 	tinyMCE.get('cronEmailText').save();
	  
	  if($('cronActive').checked){
		  
		  var validator  = new Validation('reportEditCron_form');
		  if (!validator.validate())  formularios=false;
	  }
	  
	  // Groups
	  // Eliminamos los blancos
	  $$('#reportGroupTable tbody tr').each(function(ele){
		  if(ele.getElementsByTagName('input')[0].value==""){
			  ele.remove();
		  }
	  });
	  var validator  = new Validation('reportEditGroup_form');
	  if (!validator.validate())  formularios=false;

	  if(formularios){
		  var report_id = $('general_code').value;
		  var title = $('general_title').value;
		  var sql = $('sqlReport').value;
		  var group_id = $('general_group').value;
		  var group_description = $('general_group').selectedIndex >= 0 ? $('general_group').options[$('general_group').selectedIndex].innerHTML : undefined;
		  
		  var elements = "";
		  $$("#comboListReport tr td[name='code-combo']").each(function(ele){
			  if($$('select[name=cronCombo-'+ele.innerHTML+']').length>0){
				  if(""!=$$('select[name=cronCombo-'+ele.innerHTML+']')[0].value){
					  var selectCron = $$('select[name=cronCombo-'+ele.innerHTML+']')[0];
				      var descriptionValue = selectCron.options[selectCron.selectedIndex].innerHTML;
					  elements += "|"+ele.innerHTML+"="+selectCron.value+"$"+descriptionValue;
				  }else{
					  elements += "|"+ele.innerHTML;  
				  }
			  }else if($$('input[name=cronCombo-'+ele.innerHTML+']').length>0){
				  if(""!=$$('input[name=cronCombo-'+ele.innerHTML+']')[0].value){
					  elements += "|"+ele.innerHTML+"="+$$('input[name=cronCombo-'+ele.innerHTML+']')[0].value;
				  }else{
					  elements += "|"+ele.innerHTML;  
				  }
			  }else {
				  elements += "|"+ele.innerHTML;
			  }
			  
		  });
		  
		  var chartType = "";
		  var chartXValue = "";
		  var elementChartYValue = "";
		  
		  if($$('#report_editChart_field img.img-onoff.selected').size()>0){
			  chartType = $$('#report_editChart_field img.img-onoff.selected')[0].title;
			  chartXValue = $('report_chart_charxvalue').value;
			  $$('#chartValueList tr td.value input').each(function(ele){
				  if(ele.id!='report_chart_charxvalue'){
					  elementChartYValue += "|"+ele.value;
				  }
			  });
		  }
		  var linkTR = $('reportEditRowlinkSelectedID').value;
		  
		  var linkTRVariables ="";
		  if(linkTR!=""){
			  $$('#reportEditRowlinkSelectedVAR tr[class=variables] td[class=scope-label] input').each(function (element){
				var variable = element.getAttribute('variable');
				var value = element.value;
				linkTRVariables+="|"+variable+";"+value;
			  });
		  }
		  
		  var linkTDVariables ="";
		  $$('#reportEditlinkListTD tr td[name=column] input').each(function(element){
				if(element.value!=""){
					linkTDVariables+="|"+element.name+";"+element.value;
				}
		  });
		  
		  var atributes = "";
		  var def = $$("input[name='xlsDefault']:checked")[0].value;
		  if(def=="true"){
			  atributes += "|xlsDefault=true";
		  }else{
			  atributes += "|xlsDefault=false";
			  $$('#reportEditXls_form input.changed').each(function(element){
				  atributes += "|"+element.id+"="+element.value;
	  			});
			  $$('#reportEditXls_form select.changed').each(function(element){
				  atributes += "|"+element.id+"="+element.value;
	  			});
			  $$('#reportEditXls_form a.check.changed').each(function(element){
				  atributes += "|"+element.id+"=true";
		  		  });
			  $$('#reportEditXls_form a.uncheck.changed').each(function(element){
				  atributes += "|"+element.id+"=false";
		  		  });
		  }
		  
		  def = $$("input[name='pdfDefault']:checked")[0].value;
		  if(def=="true"){
			  atributes += "|pdfDefault=true";
		  }else{
			  atributes += "|pdfDefault=false";
			  $$('#reportEditPDF_form input.changed').each(function(element){
				  atributes += "|"+element.id+"="+element.value;
	  			});
			  $$('#reportEditPDF_form select.changed').each(function(element){
				  atributes += "|"+element.id+"="+element.value;
	  			});
			  $$('#reportEditPDF_form a.check.changed').each(function(element){
				  atributes += "|"+element.id+"=true";
		  		  });
			  $$('#reportEditPDF_form a.uncheck.changed').each(function(element){
				  atributes += "|"+element.id+"=false";
		  		  });
			  $$('#reportEditPDF_form textarea.changed').each(function(element){
				  atributes += "|"+element.id+"="+element.value;
	  			});
		  }
		  
		  if($('cronActive').checked){
			  atributes += "|cronActive=true";
		  }else{
			  atributes += "|cronActive=false";
		  }
		  
		  if($('cronNoRow').checked){
			  atributes += "|cronNoRow=true";
		  }else{
			  atributes += "|cronNoRow=false";
		  }
		  
		  if($('cronEmailPerRow').checked){
			  atributes += "|cronEmailPerRow=true";
		  }else{
			  atributes += "|cronEmailPerRow=false";
		  }
		  
		  $$('#reportEditCron_form #report_cron_div input.required-entry').each(function(element){
			  atributes += "|"+element.id+"="+element.value;
			});
		   
		  $$('#reportEditCron_form #report_cron_email input[type=text]').each(function(element){
			  atributes += "|"+element.id+"="+element.value;
			});
		  $$('#reportEditCron_form #report_cron_email input[type=radio]').each(function(element){
			  if(element.checked){
				  atributes += "|"+element.id+"="+element.value;
			  }
			});
		  $$('#reportEditCron_form #report_cron_email textarea').each(function(element){
			  atributes += "|"+element.id+"="+element.value;
			});
		  $$('#reportEditCron_form #report_cron_email select').each(function(element){
			  atributes += "|"+element.id+"="+element.value;
			});
		  
		  
		  atributes += "|reportDescription="+$('reportDescription').value;
		  var versions = $$('#reportEditGeneral_form #version');
		  if(versions.length==1){
			  atributes += "|version="+versions[0].value;
		  }
		  var grGroups="";
		  $$('#reportGroupTable tbody tr').each(function(element){
			  var grColumns = element.getElementsBySelector('[id="columns"]')[0].value;
			  var grDesciption = element.getElementsBySelector('[id="description"]')[0].value;
			  grGroups +=grColumns+"::"+grDesciption+"@@";
		  });
		  if(""!=grGroups){
			  grGroups=grGroups.substr(0,grGroups.length-2);
			  atributes += "|columnGroup="+grGroups;
		  }
		  
		  var params = {report_id : report_id,
						title:title,
						sql:sql,
						group_id:group_id,
						combo:elements,
						chartType:chartType,
						chartXValue:chartXValue,
						elementChartYValue:elementChartYValue,
						linkTR : linkTR,
						linkTRVariables:linkTRVariables,
						linkTDVariables:linkTDVariables,
						atributes:atributes};
		  var action = this.action;
		  var manager= this;
		  
		  this.ws(this.action,params,function(response){
			  var report = response.responseText.evalJSON();
			  manager.showMessageWSLog(response);
			  if(report[0].type=="success-msg"){
				  if(action=="addReport"){
					  report_id=report[0].id;
					  $('general_code').value = report_id;
					  
					  var opt = document.createElement('option');
					  opt.text = title;
					  opt.value = report_id;
					  
					  $('butReturnReportList').style.display="inline";
					  $('butDelReport').style.display="inline";
					  $('duplicateReport').style.display="inline";
					  $('butSaveReport').style.display="inline";
					  $('butViewReport').style.display="inline";
					  $('butExportReport').style.display="inline";
					  $('butSendReportMarket').style.display="inline";
					  
					  $('butAddReport').style.display="none";
					  $('container-ReportEdit-menu').removeClassName('active');
					  $('container-ReportList-menu').addClassName('active');
				  }
				  $('titlePageReport').innerHTML="Manage Report: "+title;
				  
				  manager.updateReport2List(report_id,title,group_description);
				  manager.refreshReportList();
				  manager.action="saveReport";
			  }
			  
		  });
	 }
	  
	  
  },
  addNewReport: function(){
	  
	  $$('#general_code')[0].value="";
	  $$('#general_title')[0].value="";
	  $$('#general_group')[0].value="";
	  $$('#sqlReport')[0].value="";
	  $$('#reportDescription')[0].value="";
	  
	  $$('#butSaveReport')[0].style.display="inline";
	  this.action="addReport";
	  $$('#general_code')[0].removeAttribute('disabled');
	  $$('#butAddReport')[0].style.display="none";
	  $$('#butDelReport')[0].style.display="none";
	  $$('#duplicateReport')[0].style.display="none";
	  $$('#butViewReport')[0].style.display="none";
	  $$('#titlePageReport')[0].innerHTML="Add new report";
	  $$('#butExportReport')[0].style.display="none";
	  $$('#butSendReportMarket')[0].style.display="none";
	  
	  this.codeMirrorSql('sqlReport',false);
  },
  duplicateReport: function(){
	  $('butSaveReport').style.display="none";
	  this.action="addReport";
	  $('general_code').removeAttribute('disabled');
	  $('butAddReport').style.display="inline";
	  $('butDelReport').style.display="none";
	  $('duplicateReport').style.display="none";
	  $('butExportReport').style.display="none";
	  $('butSendReportMarket').style.display="none";
	  
	  $('titlePageReport').innerHTML="Add new report";
  },
  resetReport: function(){
	  $$('#general_code')[0].value="";
	  $$('#general_title')[0].value="";
	  $$('#general_group')[0].value="";
	  $$('#sqlReport')[0].value="";
	  $$('#reportDescription')[0].value="";
  },
  codeMirrorSql: function(container,readOnly){
	  $$('.CodeMirror').each(function (element){
		  element.remove();
	  });
	  cmText = undefined;
	  
	  if(cmText == undefined){
		  cmText = CodeMirror.fromTextArea(document.getElementById(container), {
		        mode: 'text/x-mariadb',
		        indentWithTabs: true,
		        smartIndent: true,
		        lineNumbers: true,
		        matchBrackets : true,
		        autofocus: true,
		        readOnly : readOnly
		    });
	  }else{
		  cmText.setValue($(container).value);  
	  }
	  
  },
  addComboSelected: function (){
	  var manager = this;
	  $$('#comboList4Report tr').each(function (element){
			if(!element.hasClassName('headings') && element.hasClassName('selected')){
				var td=new Element('td');
				var imgUp = new Element('img', {'src':URL_TYPE_MEDIA+'serpini_sqlreport/arrow_up.png',
					  'class': 'v-middle',
					  'title':'up',
					  'alt':'up'
					  });
				imgUp.observe("click", function up(event) {
					var element = event.element().ancestors()[0];
					element = element.parentNode;
					var previous = element.previous();
					if (previous) {
						previous.remove();
						element.insert({after:previous});
					}
				});
				var imgDown = new Element('img', {'src':URL_TYPE_MEDIA+'serpini_sqlreport/arrow_down.png',
										  'class': 'v-middle',
										  'title':'down',
										  'alt':'down'
				});
				imgDown.observe("click", function down(event) {
					var element = event.element().ancestors()[0];
					element = element.parentNode;
					var next = element.next();
					if (next) {
						next.remove();
						element.insert({before:next});
					}
				});
				td.insert(imgUp);
				td.insert(" ");
				td.insert(imgDown);
				element.insert(td);
				$('comboListReport').insert(element);
				element.removeClassName('selected');
				
				var code = element.getElementsBySelector('td[name=code-combo]')[0].innerHTML;
				var combo = manager.getComboList(code);
				croneditor.addCombo(combo);
				
			}
	  });
	  
  },
  removeComboSelected: function (){
	  $$('#comboListReport tr').each(function (element){
			if(!element.hasClassName('headings') && element.hasClassName('selected')){
				element.childElements()[4].remove();
				//$('comboList4Report').insert(element);
				$$('#comboList4Report tbody')[0].insert(element);
				element.removeClassName('selected');
				var code = element.getElementsBySelector('td[name=code-combo]')[0].innerHTML;
				croneditor.removeCombo(code);
			}
	  });
	  
	  this.resetXLSReport();
	  this.resetPDFReport();
	  this.hideExpPDFOptions(false);
	  
	  $$('#reportEditCron_form #report_cron_email input[type=text]').each(function(element){
		  element.value="";
		});
	  $$('#reportEditCron_form #report_cron_email textarea').each(function(element){
		  element.value="";
		});
	  $$('#reportEditCron_form #report_cron_email select').each(function(element){
		  element.value=element.options[0].value;
		});
	  
	  $$('#reportEditCron_form #report_cron_email input[type=radio][value=false]')[0].checked=true;
	  
	  $$('#report_cronLog_table tbody input[type=checkbox]').each(function(element){
		  element.parentElement.parentElement.remove();
	  });
	  
	  $$('#reportDescription')[0].value="";
	  
	  $$('#reportGroupTable tbody tr').each(function(ele){
			 ele.remove();
	  });
  },
  removeComboAll: function(){
	  $$('#comboListReport tr').each(function (element){
			if(!element.hasClassName('headings')){
				element.childElements()[4].remove();
				$('comboList4Report').insert(element);
				element.removeClassName('selected');
				var code = element.getElementsBySelector('td[name=code-combo]')[0].innerHTML;
				croneditor.removeCombo(code);
			}
	  });
	  
	  this.resetXLSReport();
	  this.resetPDFReport();
	  this.hideExpPDFOptions(false);
	  
	  $$('#reportGroupTable tbody tr').each(function(ele){
		 ele.remove();
	  });
	  
	  if(this.isTinymceInit('cronEmailText'))  tinyMCE.execCommand('mceRemoveControl', false, 'cronEmailText');
	  
	  $$('#reportEditCron_form #report_cron_email input[type=text]').each(function(element){
		  element.value="";
		});
	  
	  $$('#reportEditCron_form #report_cron_email textarea').each(function(element){
		  element.value="";
		});
	  $$('#reportEditCron_form #report_cron_email select').each(function(element){
		  element.value=element.options[0].value;
		});
	  
	  $$('#reportEditCron_form #report_cron_email input[type=radio][value=false]')[0].checked=true;
	  $('cronActive').checked = false;
	  $('cronNoRow').checked = false;
	  $('cronEmailPerRow').checked = false;
	  $('cronLogActionSelect').value=""
	  $$('#report_cronLog_table tbody input[type=checkbox]').each(function(element){
		  element.parentElement.parentElement.remove();
	  });
	  $$('#reportDescription')[0].value="";
	  
	 
	  
  },
  resetXLSReport: function(){
	  $$('#reportEditXls_form input').each(function(element){
		  if(element.type=="text"){
			  element.removeClassName('changed');
			  element.value= $$('#container-System #'+element.id)[0].value;
			  if($$('#container-ReportEdit #'+element.id+"Icon").size()>0) $$('#container-ReportEdit #'+element.id+"Icon")[0].style.backgroundColor = '#'+element.value;			  
		  }
	  });
	  
	  $$('#reportEditXls_form select').each(function(element){
		  element.removeClassName('changed');
		  element.value=$$('#container-System #'+element.id)[0].value;
	  });
	  
	  $$('#reportEditXls_form textarea').each(function(element){
		  element.removeClassName('changed');
		  element.value=$$('#container-System #'+element.id)[0].value;
	  });
	  
	  $$('#reportEditXls_form a').each(function(element){
		  element.removeClassName('changed');
		  if($$('#container-System #'+element.id)[0].hasClassName("check")){
			  element.removeClassName('unchecked');
			  element.addClassName('check');
		  }else{
			  element.removeClassName('check');
		  	  element.addClassName('unchecked');
	  	  }
  	  });
	  
	  $$("input[name='xlsDefault'][value='true']")[0].checked=true;

	  this.xlsActiveCustomize(true);
  },
  resetPDFReport: function(){
	  $$('#reportEditPDF_form input').each(function(element){
		  if(element.type=="text"){
			  element.removeClassName('changed');
			  element.value= $$('#container-System #'+element.id)[0].value;
			  if($$('#container-ReportEdit #'+element.id+"Icon").size()>0) $$('#container-ReportEdit #'+element.id+"Icon")[0].style.backgroundColor = '#'+element.value;			  
		  }
	  });
	  
	  $$('#reportEditPDF_form select').each(function(element){
		  element.removeClassName('changed');
		  element.value=$$('#container-System #'+element.id)[0].value;
	  });
	  
	  $$('#reportEditPDF_form textarea').each(function(element){
		  element.removeClassName('changed');
		  element.value=$$('#container-System #'+element.id)[0].value;
	  });
	  
	  $$('#reportEditPDF_form a').each(function(element){
		  element.removeClassName('changed');
		  if($$('#container-System #'+element.id)[0].hasClassName("check")){
			  element.removeClassName('unchecked');
			  element.addClassName('check');
		  }else{
			  element.removeClassName('check');
		  	  element.addClassName('unchecked');
	  	  }
  	  });
	  
	  $$("input[name='pdfDefault'][value='true']")[0].checked=true;
	  this.pdfActiveCustomize(true);
  },
  /*editGroupTable: function(code){
	  var len = $$("#group-"+code+" img[title='edit']").length;
	  
	  if(len>0){
		  var elementImg= $$("#group-"+code+" img[title='edit']")[0];
		  elementImg.src=URL_TYPE_SKIN+"adminhtml/default/default/images/ico_success.gif";
		  elementImg.title="save";
		  elementImg.alt="save";
		  
		  var element =$$("#group-"+code+" td[name='description']")[0];
		  var value= element.innerHTML;
		  var c =new Element('input', {'type':'text', 
			  						   'id': 'description' , 
			  						   'style': 'width:100%',
			  						   	'value':value}); 
		  element.innerHTML = "";
		  element.insert(c);
	  }else{
		  var elementImg= $$("#group-"+code+" img[title='save']")[0];
		  elementImg.src=URL_TYPE_SKIN+"adminhtml/default/default/images/icon_edit_address.gif";
		  elementImg.title="edit";
		  elementImg.alt="edit";
		  
		  len = $$("#group-"+code+" td[name='code'] input").length;
		  if(len>0){
			  var value = $$("#group-"+code+" td[name='code'] input")[0].value;
			  var element =$$("#group-"+code+" td[name='code']")[0];
			  element.innerHTML=value;
		  }
		  var value = $$("#group-"+code+" td[name='description'] input")[0].value; 
		  var element =$$("#group-"+code+" td[name='description']")[0];
		  element.innerHTML=value;
	  }
	  
	  

  },*/
  addGroupTable: function(){
	  this.groupsNew ++;
	  var codeNew = "new"+this.groupsNew;
	  var tr =new Element('tr', {'id': 'group-'+codeNew});
	  
	  var tdDescription =new Element('td', {'name':'description'}); 
	  var inputDescription =new Element('input', {'type':'text', 
										   'id': 'description' , 
										   'style': 'width:100%',
										   	'value':''});
	  tdDescription.insert(inputDescription);
	  var tdActions =new Element('td', {'name':'actions'});
	  var imgUp = new Element('img', {'src':URL_TYPE_MEDIA+'serpini_sqlreport/arrow_up.png',
		  							  'class': 'v-middle',
		  							  'title':'up',
		  							  'alt':'up'
		  							  });
	  imgUp.observe("click", function up(event) {
			var element = event.element().ancestors()[0];
          element = element.parentNode;
          var previous = element.previous();
          if (previous) {
              previous.remove();
              element.insert({after:previous});
          }
      })
	  var imgDown = new Element('img', {'src':URL_TYPE_MEDIA+'serpini_sqlreport/arrow_down.png',
			  'class': 'v-middle',
			  'title':'down',
			  'alt':'down'
			  });
	  imgDown.observe("click", function down(event) {
          var element = event.element().ancestors()[0];
          element = element.parentNode;
          var next = element.next();
          if (next) {
              next.remove();
              element.insert({before:next});
          }
      })
	  var imgSave = new Element('img', {'src':URL_TYPE_MEDIA+'serpini_sqlreport/disk.png',
		  'class': 'v-middle',
		  'title':'save',
		  'alt':'save',
		  'onclick': 'reportManager.saveGroup(\''+codeNew+'\',true)'
		  });
	  var imgDelete = new Element('img', {'src':URL_TYPE_SKIN+'adminhtml/default/default/images/cancel_btn_icon.gif',
		  'class': 'v-middle',
		  'title':'delete',
		  'alt':'delete',
		  'onclick': 'reportManager.removeGroupTable(\''+codeNew+'\')'
		  });
	  tdActions.insert(imgUp);
	  tdActions.insert(" ");
	  tdActions.insert(imgDown);
	  tdActions.insert(" ");
	  tdActions.insert(imgSave);
	  tdActions.insert(" ");
	  tdActions.insert(imgDelete);

	  tr.insert(tdDescription);
	  tr.insert(tdActions);
	  $$('#groupList tbody')[0].insert(tr);
	  
  },
  getPositionGroup(groupId){
	  $$('#groupList tr')[1].id
	  var grupos = $$('#groupList tr');
	  for(var i=0;i<grupos.length;i++){
		  if(grupos[i].id=='group-'+groupId){
			  return i;
		  }
	  }
	  return 0;
  },
  saveGroup: function(groupId,nuevo){
	  var datadescription;
	  if($$('#groupList #group-'+groupId+' input').length>0){
		  datadescription = $$('#groupList #group-'+groupId+' input')[0].value;
	  }else{
		  datadescription = $$('#groupList #group-'+groupId+' td[name=description]')[0].innerHTML
	  }
	  
	  var action;
	  var orden = this.getPositionGroup(groupId);
	  if(nuevo){
		  action='addGroup';
	  }else{
		  action='saveGroup'
	  }
	  
	  var params = {groupId : groupId,
				description:datadescription,
				orden:orden};
	  
	  var manager= this;
	  
	  this.ws(action,params,function(response){
		  var report = response.responseText.evalJSON();
		  if(report[0].type=="success-msg"){
			 if(nuevo){
				 datagroupId=report[0].id;
				 groupList[orden]= new Group(datagroupId,datadescription,orden);
			 }else{
				 groupList[orden].setDescription(datadescription);
			 }
			 manager.refreshGroupList();
		  }else if(report[0].type == 'error-msg'){
				manager.showMessageWSLog(response);
		  }
	  });
  },
  removeGroup: function(groupId,nuevo){
	  if(nuevo){
			$('group'+linkId).remove();
	  }else{
		  var manager= this;
		  var params = {groupId : groupId};
		  this.ws('delGroup',params,function(response){
			  var report = response.responseText.evalJSON();
			  if(report[0].type=="success-msg"){
				  $('group-'+groupId).remove();
				  manager.refreshGroupListActual();
				  manager.refreshGroupList();
			  }else if(report[0].type == 'error-msg'){
					manager.showMessageWSLog(response);
			  }
			  
		  });
		}
  },
  refreshGroupListActual : function(){
	  var filters = $$('#groupList tr');
	  var elements ="";
	  var auxGroupList = new Array();
	  var orden = 1;
	  for(var i=0;i<filters.length;i++){
    	  if(!filters[i].hasClassName('headings')){
    		  var code = filters[i].id.substring(6,filters[i].id.length);
    		  var description = filters[i].childElements()[0].innerHTML;
    		  auxGroupList[orden]=new Group(code,description,orden);
    		  orden++;
    	  }
      }  
	  groupList=auxGroupList;
  },
  saveGroupOLD: function(){
	  $$("#groupList img[title='save']").each(function(element){
		     element.onclick();
		});
	  var filters = $$('#groupList tr');
	  var elements ="";
	  var auxGroupList = new Array();
	  for(var i=0;i<filters.length;i++){
    	  if(!filters[i].hasClassName('headings')){
    		  var code = filters[i].childElements()[0].innerHTML;
    		  var description = filters[i].childElements()[1].innerHTML;
    		  var orden = i;
    		  elements += "|"+code+";"+description+";"+orden;
    		  auxGroupList[code]=description;
    	  }
      }
	  var params = {data : elements};
	  var manager = this;
	  this.ws("saveGroup",params,function(response){
		  manager.showMessageWSLog(response);
		  groupList=auxGroupList;
		  manager.refreshGroupList();
	  });
	  
  },
  removeGroupTable: function(code){
	  $('group-'+code).remove();
  },
  manageCombo: function(code){
	  var validator  = new Validation('comboEdit_form');
	  validator.reset();
	  $('butReturnComboList').style.display="inline";
	  $('butDelCombo').style.display="inline";
	  var params = {combo_id : code};
	  var manager = this;
	  this.ws("loadCombo",params,function(response){
		  var combo = response.responseText.evalJSON();
		  
		  $('combo_code').setAttribute('disabled','true');
		  $('combo_code').value=combo.combo_id;
		  $('combo-parameter').value=combo.parameter;
		  $('combo-title').value=combo.title;
		  $('combo-type').value=combo.type;
		  
		  if(combo.atributes.selectType=="" || combo.atributes.selectType == undefined){
			$('select-unique').checked=true;
		  } else {
			$(combo.atributes.selectType).checked=true;
		  }
		  
		  $('titlePageComboEdit').innerHTML="Manage filter "+combo.title;
		  
		  if(combo.type == "set"){
		      $$('#setComboList tr').each(function (element){
					if(!element.hasClassName("headings")){
						element.remove();
					}
			  })
			  var setsList = combo.atributes.set;
			  var setsListArray = setsList.split("|");
			  for(var i=1;i<setsListArray.length;i++){
				  manager.addSet();
				  var valuesArray = setsListArray[i].split(";");
				  $$('#setComboList tr')[i].childElements()[0].childElements()[0].childElements()[0].value=valuesArray[0];
				  $$('#setComboList tr')[i].childElements()[1].childElements()[0].value=valuesArray[1];
			  }
		  }
		  if(combo.type == "evaluated"){
			  $('evaluated-expresion').value=combo.atributes.sql;
		  }
		  manager.showComboType();
		  manager.showMenu('container-ComboAdd');
		  if(combo.type == "select"){
			  $('sqlCombo').value=combo.atributes.sql;
			  manager.codeMirrorSql('sqlCombo',false);
		  }else{
			  $('sqlCombo').innerHTML=" ";
		  }
		  $('container-Combo-menu').addClassName('active');
		  $('container-ComboAdd-menu').removeClassName('active');
		  
	  });
	  this.action="saveCombo";
  },
  showComboType: function(){
	  var tipo = $('combo-type').value;
	  if(tipo!="select"){
		  $$('.CodeMirror').each(function (element){
			  element.remove();
		  });
		  cmText = undefined;
		  $('setCombo').style.display="none";
	  }
	  if(tipo!="set"){
		  var filters = $$('#setComboList tr');
		  for(var i=1;i<filters.length;i++){
			  if(i==1){
				  filters[i].childElements()[0].childElements()[0].childElements()[0].value="";
				  filters[i].childElements()[1].childElements()[0].value="";
			  }else{
				  filters[i].remove();  
			  }
		  }
	  }
	  
	  if(tipo=="select"){
		  
		  this.codeMirrorSql('sqlCombo',false);
		  $('setCombo').style.display="none";
		  $('combo_edit_field_listType').show();
	  }else if (tipo=="set") {
		  
		  $('setCombo').style.display="inline";
		  $('combo_edit_field_listType').show();
	  } else {
		$('combo_edit_field_listType').hide();
	  }
	  
	  if(tipo=="evaluated"){
		$('evaluated-expresion').addClassName('required-entry');	
		$('combo_edit_field_evaluatedType').show();
		
	  } else{
		$('evaluated-expresion').removeClassName('required-entry');	
		$('combo_edit_field_evaluatedType').hide();	  
		
	  }
  },
  addSet: function(){
      var elementos = $$('#setComboList tbody tr').size();
      $$('#setComboList tbody tr img[title="add new row"]').each(function(element){
    	  element.remove();
      });
	  var tr =new Element('tr');
	  var tdValue =new Element('td', {'class':'no-link'}); 
	  var inputValue =new Element('input', {'type':'text', 
										   'class': 'input-text' , 
										   	'value':''});
	  var divValue =new Element('div', {'class':'field-100'});
	  divValue.insert(inputValue);
	  tdValue.insert(divValue);
	  var tdDes =new Element('td', {'class':'no-link'}); 
	  var inputDes =new Element('input', {'type':'text', 
										   'class': 'input-text' , 
										   'value':''});
	  var img = new Element('img',{'src':URL_TYPE_SKIN+'adminhtml/default/default/images/cancel_icon.gif',
		  						   'class':'v-middle',
		  						   'title':'remove',
		  						   'alt':'remove',
		  						   'onclick':'reportManager.removeSet(this)'});
	  var imgAdd = new Element('img',{'src':URL_TYPE_SKIN+'adminhtml/default/default/images/rule_component_add.gif',
			   'class':'v-middle',
			   'title':'add new row',
			   'alt':'add new row',
			   'onclick':'reportManager.addSet()'});
	  tdDes.insert(inputDes);
	  tdDes.insert(" ");
	  tdDes.insert(imgAdd);
	  tdDes.insert(" ");
	  if(elementos>0) tdDes.insert(img);
	  tr.insert(tdValue);
	  tr.insert(tdDes);
	  $$('#setComboList tbody')[0].insert(tr);
	  inputValue.focus();
  },
  removeSet: function(setrow){
	  setrow.parentNode.parentNode.remove();
	  $$('#setComboList tbody tr img[title="add new row"]').each(function(element){
    	  element.remove();
      });
	  var elementos = $$('#setComboList tbody tr').size();
	  elementos--;
	  var ultimoTR = $$('#setComboList tbody tr')[elementos];
	  var td = ultimoTR.childElements()[1];
	  var imgAdd = new Element('img',{'src':URL_TYPE_SKIN+'adminhtml/default/default/images/rule_component_add.gif',
		   'class':'v-middle',
		   'title':'add new row',
		   'alt':'add new row',
		   'onclick':'reportManager.addSet()'});
	  td.insert(imgAdd);
  },
  saveCombo: function(){
	  if(cmText != undefined){
		  cmText.save();
	  }
	  var validator  = new Validation('comboEdit_form');
      if (validator.validate()) {
	  
		  var combo_id = $('combo_code').value;
		  var title = $('combo-title').value;
		  var parameter = $('combo-parameter').value;
		  var tipo = $('combo-type').value;
		  var sql = "";
		  var setValues = "";
		  if(tipo=="select"){
			  sql =  cmText.getValue();
		  }else if(tipo=="set"){
			  var filters = $$('#setComboList tr');
			  for(var i=1;i<filters.length;i++){
				  var setValue = filters[i].childElements()[0].childElements()[0].childElements()[0].value;
				  var labelValue = filters[i].childElements()[1].childElements()[0].value;
				  
				  if(setValue.trim()!=""  && labelValue.trim()!=""){
					  setValues += "|"+setValue+";"+labelValue;
				  }
			  }
		  }else if(tipo=="evaluated"){
			 sql =  $('evaluated-expresion').value;
		  }		  
		  var selectType = "";
		  if(tipo=="select" || tipo=="set") var selectType = $$('input:checked[type="radio"][name="combo-select-type"]').pluck('value');
		  
		  var params = {combo_id : combo_id,title:title,parameter:parameter,tipo:tipo,sql:sql,setValues:setValues,selectType:selectType};
		  var manager = this;
		  this.ws(this.action,params,function(response){
			  manager.showMessageWSLog(response);
			  
			  var message = response.responseText.evalJSON();
			  if(message[0].type=="success-msg"){
				  if(manager.action=="addCombo"){
					  combo_id=message[0].id;
					  $('combo_code').value = combo_id;
				  }
				  
				  manager.updateCombo2List(combo_id,title,parameter,tipo);
				  manager.refreshComboList();
				  if(manager.action=="addCombo"){
					  $$('#container-ComboAdd-menu')[0].removeClassName('active');
					  $$('#container-Combo-menu')[0].addClassName('active');
					  $('butDelCombo').style.display="inline";
					  $('butReturnComboList').style.display="inline";
					  $('titlePageComboEdit').innerHTML="Manage "+title;
					  manager.action="saveCombo";
					  $('combo_code').setAttribute('disabled','true');
				  }
				  
			  }
			  
			  
		  });
      }
  },
  addCombo : function(){
	  var validator  = new Validation('comboEdit_form');
	  validator.reset();
	  $('select-unique').checked=true;
	  $('butDelCombo').style.display="none";
	  $('butReturnComboList').style.display="none";
	  $('combo_code').removeAttribute('disabled');
	  $('combo_code').value="";
	  $('combo-parameter').value="";
	  $('combo-title').value="";
	  $('combo-type').value="";
	  $('titlePageComboEdit').innerHTML="Add filter";
	  $('sqlCombo').innerHTML=" ";
	  $('evaluated-expresion').value="";
	  this.showComboType();
	  this.action="addCombo";
	  this.showMenu('container-ComboAdd');
  },
  removeSetComboList: function(button){
	  elementoImg = button;
  },
  delCombo: function(){
	  var combo_id = $('combo_code').value;
	  this.action="delCombo";
	  var manager=this;
	  this.ws(this.action,{combo_id:combo_id},function(response){
		  var report = response.responseText.evalJSON();
		  manager.showMessageWSLog(response);
		  var message = response.responseText.evalJSON();
		  if(message[0].type=="success-msg"){
			  manager.removeCombo2List(combo_id);
			  manager.refreshComboList();
			  //$$("#comboList tr[id='combo-"+combo_id+"']")[0].remove()
			  manager.showMenu('container-Combo');
		  }
	  });
  },
  openAdminReport: function(report_id){
	  this.url=urlAdmin;
	  this.addVarToUrl("action","loadReport");
	  this.reload(this.addVarToUrl("id", report_id));
  },
  openAdminCombo: function(combo_id){
	  this.url=urlAdmin;
	  this.addVarToUrl("action","loadCombo");
	  this.reload(this.addVarToUrl("id", combo_id));
  },
  collapse: function(section_id){
	  if($(section_id).style.display=="" || $(section_id).style.display=="inline"){
		  this.collapseClose(section_id);
	  }else{
		 this.collapseOpen(section_id);
	  }
	  return false;
  },
  collapseClose: function(section_id){
	  $(section_id).style.display='none';
	  $(section_id+'-head').removeClassName('open');
  },
  collapseOpen: function(section_id){
	  $(section_id).style.display='';
	  $(section_id+'-head').addClassName('open');
  },
  
  addReport : function(){
	  var validator  = new Validation('comboEdit_form');
	  validator.reset();
	  this.action="addReport";
	  
	  $('titlePageReport').innerHTML="Add Report";
	  
	  $('butReturnReportList').style.display="none";
	  $('butDelReport').style.display="none";
	  $('duplicateReport').style.display="none";
	  $('butSaveReport').style.display="none";
	  $$('#butViewReport')[0].style.display="none";
	  $('butAddReport').style.display="inline";
	  $('butExportReport').style.display="none";
	  $('butSendReportMarket').style.display="none";
	  
	  $('general_code').value="";
	  $('general_code').removeAttribute('disabled');
	  $('general_title').value="";
	  $('sqlReport').innerHTML=" ";
	  $('sqlReport').value=" ";
	  
	  $$('.CodeMirror').each(function (element){
		  element.remove();
	  });
	  cmText = undefined;
	  
	  $$('#comboListReport tr').each(function(element) {
		    element.addClassName('selected');
		});
	  
	  this.removeComboSelected();
	  
	  this.codeMirrorSql('sqlReport',false);
	  this.showMenu('container-ReportEdit');
	  this.restartChartConfig();
	  
	  $('reportEditRowlinkList').show();
	  $('reportEditRowlinkSelected').hide(); 
	  $('reportEditRowlinkSelectedID').value = "";
	  this.collapseClose('report_editSQL_field');
	  this.collapseClose('report_editFilters_field');
	  this.collapseClose('report_editChart_field');
	  this.collapseClose('report_editLink_field');
	  
	  croneditor.reset();
	  
	  $('cronActive').checked = false;
	  $('cronNoRow').checked = false;
	  $('cronEmailPerRow').checked = false;
	  $('cronLogActionSelect').value=""
	  $$('#report_cronLog_table tbody input[type=checkbox]').each(function(element){
		  element.parentElement.parentElement.remove();
	  });
	  
	  $$('#reportEditCron_form #report_cron_email input[type=text]').each(function(element){
		  element.value= "";
		});
	  if(this.isTinymceInit('cronEmailText'))  tinyMCE.execCommand('mceRemoveControl', false, 'cronEmailText');
		  
	  $$('#reportEditCron_form #report_cron_email textarea').each(function(element){
		  element.value= "";
		});
	  
	  
	  
	  var validator  = new Validation('reportEditGeneral_form');
	  validator.reset();
	  validator  = new Validation('report_editChartValueList_form');
	  validator.reset();
	  validator  = new Validation('reportEditlinkListTD_form');
	  validator.reset();
	  validator  = new Validation('reportEditRowlinkSelected_form');
	  validator.reset();
	  validator  = new Validation('reportEditCron_form');
	  validator.reset();
	  
	  
  },
  refreshReportList: function(){
	  $$('#reportList tr').each(function (element){
			if(!element.hasClassName('headings')){
               element.remove();
           }
	  });
	  
	  $$('#reportListExport tr').each(function (element){
			if(!element.hasClassName('headings')){
             element.remove();
         }
	  });
	  
	  /*$$('#setupPermissions_resources_container div').each(function (element){
             element.remove();
         
	  });*/
	  
	  
	  $groupAnt="";
	  var table = $$('#reportList tbody')[0];
	  var tableExport = $$('#reportListExport tbody')[0];
	  var tablePermissions = $$('#setupPermissions_resources_container')[0];
	  
	  var divPermission = new Element('div',{'class':'f-left'});
	  var divPermission2 = new Element('div',{'class':'tree x-tree'});
	  
	  
	  
	  var numRows=0;
	  for(var groupDes in reportList){
		  if (reportList.hasOwnProperty(groupDes)) {
			  var primero=true;
			  var ulPermissions = new Element('ul',{'class':'x-tree-root-ct x-tree-lines'});
			  var divPermissions3 = new Element('div',{'class':'x-tree-root-node'});
			  var liGroupPermission = new Element('li',{'class':'x-tree-node'});
			  var divGroupPermission = new Element('div',{'class':'x-tree-node-el  x-tree-node-expanded'});
			  var spanGroupPermission = new Element('span',{'class':'x-tree-node-indent'});
			  var imgGroupPermission = new Element('img',{'src':URL_TYPE_MEDIA+'serpini_sqlreport/spacer.gif','class':'x-tree-ec-icon x-tree-elbow-minus'});
			  var imgGroupPermissionView = new Element('img',{'src':URL_TYPE_MEDIA+'serpini_sqlreport/folder.png','class':'x-tree-ec-icon'});
			  var spanGroupPermission2 = new Element('span',{'unselectable':'on'});
			  spanGroupPermission2.insert(" "+groupDes);
			  divGroupPermission.insert(spanGroupPermission);
			  divGroupPermission.insert(imgGroupPermission);
			  divGroupPermission.insert(imgGroupPermissionView);
			  divGroupPermission.insert(spanGroupPermission2);

			  liGroupPermission.insert(divGroupPermission);

			  var ulGroupPermission = new Element('ul',{'class':'x-tree-node-ct'});
			  
			  for(var i=0;i< reportList[groupDes].length;i++){
				  var tr = new Element('tr');
				  var trExport = new Element('tr');
				  if(primero){
					  var tdGroup = new Element('td',{'rowspan':reportList[groupDes].length,
						  							   'style':'vertical-align: middle'});
					  var tdGroupExport = new Element('td',{'rowspan':reportList[groupDes].length,
							   'style':'vertical-align: middle'});
							   
					  
					  tdGroup.insert(groupDes);
					  tdGroupExport.insert(groupDes);
					  tr.insert(tdGroup);
					  trExport.insert(tdGroupExport);
					  
					  primero=false;
				  } 
				  var tdReport =new Element('td',{'onclick': "reportManager.manageReport('"+reportList[groupDes][i].getCode()+"')",
					  							  'class':'tdSelectable'});
				  var tdReportExport =new Element('td',{'onclick': "reportManager.exportReport('"+reportList[groupDes][i].getCode()+"')",
					  							  'class':'tdSelectable'});
				  tdReport.insert(reportList[groupDes][i].getDescription());
				  tdReportExport.insert(reportList[groupDes][i].getDescription());
				  tr.insert(tdReport);
				  trExport.insert(tdReportExport);
				  
				  table.insert(tr);
				  tableExport.insert(trExport);
				  
				  var liReportPermission = new Element('li',{'class':'x-tree-node'});
				  var divReportPermission = new Element('div',{'class':'x-tree-node-el  x-tree-node-expanded','name':'reportPermission','report':reportList[groupDes][i].getCode()});
				  var spanReportPermission = new Element('span',{'class':'x-tree-node-indent'});
				  var imgReportPermission1 = new Element('img',{'src':URL_TYPE_MEDIA+'serpini_sqlreport/spacer.gif','class':'x-tree-elbow-line'});
				  spanReportPermission.insert(imgReportPermission1);
				  var imgReportPermission2 = new Element('img',{'src':URL_TYPE_MEDIA+'serpini_sqlreport/spacer.gif','class':'x-tree-ec-icon x-tree-elbow-minus'});
				  var imgReportPermissionView = new Element('img',{'src':URL_TYPE_MEDIA+'serpini_sqlreport/btn_show-hide_icon.gif','class':'x-tree-ec-icon','onclick':'reportManager.chagePermissionViewRole("'+reportList[groupDes][i].getCode()+'")','action':'view'});
				  var imgReportPermissionEdit = new Element('img',{'src':URL_TYPE_MEDIA+'serpini_sqlreport/customization.png','class':'x-tree-ec-icon','onclick':'reportManager.chagePermissionEditRole("'+reportList[groupDes][i].getCode()+'")','action':'edit'});
			      var imgReportPermission3 = new Element('img',{'src':URL_TYPE_MEDIA+'serpini_sqlreport/spacer.gif','class':'x-tree-node-ico','unselectable':'on'});
				  var aReportPermission = new Element('a',{'hidefocus':'on','href':'#'});
				  var spanReportPermission2 = new Element('span',{'unselectable':'on'});
				  spanReportPermission2.insert(" "+reportList[groupDes][i].getDescription());
				  
				  divReportPermission.insert(spanReportPermission);
				  divReportPermission.insert(imgReportPermission2);
				  divReportPermission.insert(imgReportPermissionView);
				  divReportPermission.insert(imgReportPermissionEdit);
				  divReportPermission.insert(spanReportPermission2);
				  liReportPermission.insert(divReportPermission);
				 
				 ulGroupPermission.insert(liReportPermission);
				  
			  }
			  liGroupPermission.insert(ulGroupPermission);
			  divPermissions3.insert(liGroupPermission);
			  ulPermissions.insert(divPermissions3);
			  divPermission2.insert(ulPermissions);
		  }
	  }
	  divPermission.insert(divPermission2);
	  tablePermissions.insert(divPermission);
	  
	  this.getRolePermission($('rolePermissions').getValue());
  },
  updateReport2List: function(report_code,description,group){
	  // Comprobamos si lo han metido en un nuevo grupo
	  if(reportList.hasOwnProperty(group)){
		  // El grupo existe
		  // Buscamos por el grupo
		  var encontrado=false;
		  for(var i=0;i< reportList[group].length && !encontrado;i++){
			  if(report_code==reportList[group][i].getCode()){
				  // Cambia s�lo la descripci�n, no grupo
				  reportList[group][i].setDescription(description);
				  encontrado=true;
			  }
		  }
		  
		  if(!encontrado){
			  // Lo han cambiado a un grupo existente
			  // 	Lo borramos del grupo anterior
			  this.removeReport2List(report_code);
			 //  Lo insertamos
			  reportList[group].splice(0,0,new Report(report_code,description,group));
			  
		  }
		  
	  }else{
		  // No existe el grupo, lo insertamos
		  // Lo buscamos por si antes estaba
		  this.removeReport2List(report_code);
		  
		  reportList[group]=new Array(new Report(report_code,description,group));
	  }
	  
	  
  },
  removeReport2List: function(report_code){
	  for(var groupDes in reportList){
		  if (reportList.hasOwnProperty(groupDes)) {
			  for(var i=0;i< reportList[groupDes].length;i++){
				  if(reportList[groupDes][i].getCode()==report_code){
					  reportList[groupDes].splice(i,1);
				  }
			  }
		  }
	  }
  },
  refreshComboList: function(){
	  $$('#comboList tr').each(function (element){
			if(!element.hasClassName('headings')){
             element.remove();
         }
	  });
	  
	  $$('#comboList4Report tr').each(function (element){
			if(!element.hasClassName('headings')){
           element.remove();
       }
	  });
	  
	  
	  
	  var table = $$('#comboList tbody')[0];
	  var table4Report = $$('#comboList4Report tbody')[0];
	  
	  for(var i=0;i< filtersList.length;i++){
		  var tr = new Element('tr',{'id':'combo-'+filtersList[i].getCode(),
			  							 'onClick':"reportManager.manageCombo('"+filtersList[i].getCode()+"')"});
		  
		  var tdParameter = new Element('td');
		  tdParameter.insert(filtersList[i].getParameter());
		  
		  var tdDescription = new Element('td');
		  tdDescription.insert(filtersList[i].getDescription());
		  
		  var tdType = new Element('td');
		  tdType.insert(filtersList[i].getType());
		  
		  tr.insert(tdParameter);
		  tr.insert(tdDescription);
		  tr.insert(tdType);
		  table.insert(tr);
		  
		  var tr4Report = new Element('tr',{'id':'combo4report-'+filtersList[i].getCode()});
		  var tdCode4Report = new Element('td',{'name':'code-combo','style':'display:none'});
		  tdCode4Report.insert(filtersList[i].getCode());
		  
		  var tdParameter4Report = new Element('td');
		  tdParameter4Report.insert(filtersList[i].getParameter());
		  
		  var tdDescription4Report = new Element('td');
		  tdDescription4Report.insert(filtersList[i].getDescription());
		  
		  var tdType4Report = new Element('td');
		  tdType4Report.insert(filtersList[i].getType());
		  
		  tr4Report.insert(tdCode4Report);
		  tr4Report.insert(tdParameter4Report);
		  tr4Report.insert(tdDescription4Report);
		  tr4Report.insert(tdType4Report);
		  
		  tr4Report.observe("mousedown",function (event) { 
				if(this.hasClassName('selected')){
					this.removeClassName('selected');
					this.addClassName('over');
				}else{
					this.addClassName('selected');
					this.removeClassName('over');
				}
			});

		  tr4Report.observe("mouseover",function (event) { 
				if(!this.hasClassName('selected')){
					this.addClassName('over');
				}
			});

		  tr4Report.observe("mouseout",function (event) { 
				if(!this.hasClassName('selected')){
					this.removeClassName('over');
				}
			});
		  table4Report.insert(tr4Report);
	  } 
	  
  },
  updateCombo2List: function(code,description,parameter,type){
	// Buscamos por el code
	  var encontrado=false;
	  for(var i=0;i< filtersList.length && !encontrado;i++){
		  if(code==filtersList[i].getCode()){
			  // Cambia la descripci�n, parametro y tipo
			  filtersList[i].setDescription(description);
			  filtersList[i].setParameter(parameter);
			  filtersList[i].setType(type);
			  encontrado=true;
		  }
	  }
	  
	  if(!encontrado){
		 //  Lo insertamos
		  filtersList.splice(0,0,new Combo(code,description,parameter,type));
	  }
  },
  removeCombo2List: function(combo_code){
	  for(var i=0;i< filtersList.length;i++){
		  if(filtersList[i].getCode()==combo_code){
			  filtersList.splice(i,1);
		  }
	  }
  },
  getComboList : function(code){
	  for(var i=0;i< filtersList.length;i++){
		  if(code==filtersList[i].getCode()){
			  return filtersList[i]
		  }
	  }
	  return null;
  },
  saveAdmin: function(){
	  this.action="saveSetup";
	  var db_host = $('db_host').value;
	  if(db_host!=""){
		  $$('#system_dbConnection_table input').each(function(el){
			  el.addClassName('required-entry');
		  });
	  }else{
		  $$('#system_dbConnection_table input').each(function(el){
			  el.removeClassName('required-entry');
		  });
		  $$("#system_dbConnection_table .validation-advice").each(function(el){
			  el.remove();
		  })
	  }

	  var validator  = new Validation('setupEdit_form');
      if (validator.validate()) {
    	  var valores = "";
    	  $$('#setupEdit_form input').each(function(element){
    		    valores += "@@|@@"+element.id+"@@=@@"+element.value;
    		});
		  $$('#setupEdit_form select').each(function(element){
    		    valores += "@@|@@"+element.id+"@@=@@"+element.value;
    		});
		  $$('#setupEdit_form textarea').each(function(element){ 	
  		    valores += "@@|@@"+element.id+"@@=@@"+element.value;
  			});
		  $$('#setupEdit_form a.check').each(function(element){
  		    valores += "@@|@@"+element.id+"@@=@@true";
  		  });
		  $$('#setupEdit_form a.uncheck').each(function(element){
	  		    valores += "@@|@@"+element.id+"@@=@@false";
	  		  });
		  var params = {data:valores};
		  var manager = this;
		  this.ws(this.action,params,function(response){
			  manager.showMessageWSLog(response);  
		  });
      }
  },
  refreshGroupList: function(){
	  $$('#groupList tr').each(function (element){
			if(!element.hasClassName('headings')){
             element.remove();
         }
	  });
	  
	  $$('#general_group option').each(function (element){
           element.remove();
	  });
	  
	  
	  var table = $$('#groupList tbody')[0];
	  var sel = $('general_group');
	  for(var i=1;i< groupList.length;i++){
		  grupo=groupList[i];
			  var tr = new Element('tr',{'id':"group-"+grupo.getCode()});
			  
			  var tdDes = new Element('td',{'class':'tdSelectable',
				  							'name':'description',
				  							'onclick':"reportManager.manageGroup('"+grupo.getCode()+"')"});
			  tdDes.insert(grupo.getDescription());
			  
			  var tdActions = new Element('td');
			  var imgUp = new Element('img',{'src':URL_TYPE_MEDIA+'serpini_sqlreport/arrow_up.png',
				  							 'class':'v-middle',
				  							 'title':'up',
				  							 'alt':'up'});
			  imgUp.observe("click", function up(event) {
					var element = event.element().ancestors()[0];
	                element = element.parentNode;
	                var previous = element.previous();
	                if (previous) {
	                    previous.remove();
	                    element.insert({after:previous});
	                }
	                var codigo = element.id.substr(6,element.id.length);
	                reportManager.refreshGroupListActual();
	                reportManager.refreshGroupList();
	                reportManager.saveGroup(codigo,false);
	            });
			  var imgDown = new Element('img',{'src':URL_TYPE_MEDIA+'serpini_sqlreport/arrow_down.png',
					 'class':'v-middle',
					 'title':'down',
					 'alt':'down'});
			  imgDown.observe("click", function down(event) {
	                var element = event.element().ancestors()[0];
	                element = element.parentNode;
	                var next = element.next();
	                if (next) {
	                    next.remove();
	                    element.insert({before:next});
	                }
	                var codigo = element.id.substr(6,element.id.length);
	                reportManager.refreshGroupListActual();
	                reportManager.refreshGroupList();
	                reportManager.saveGroup(codigo,false);
	            });
			  var imgDelete = new Element('img',{'src':URL_TYPE_SKIN+'adminhtml/default/default/images/cancel_btn_icon.gif',
					 'class':'v-middle',
					 'title':'delete',
					 'alt':'delete',
					 'onClick':"reportManager.removeGroup('"+grupo.getCode()+"',false)"});
			  tdActions.insert(imgUp);
			  tdActions.insert(" ");
			  tdActions.insert(imgDown);
			  tdActions.insert(" ");
			  tdActions.insert(imgDelete);
			  
			  
			  tr.insert(tdDes);
			  tr.insert(tdActions);
			  table.insert(tr);
			  
			  var op = new Element('option',{'value':grupo.getCode()});
			  op.insert(grupo.getDescription());
			  
			  sel.insert(op);
		  
	  }
	  
  },
  manageGroup: function(groupId){
		 if($$('#group-'+groupId+' td[name=description] input').length==0){
			 var tdeditable = $$('#group-'+groupId+' td[name=description]')[0];
			 var value= htmlspecialchars_decode(tdeditable.innerHTML);
			 var c =new Element('input', {'type':'text', 
										  'id': 'description' , 
										  'style': 'width:100%',
										  'value':value,
										  'onblur':"reportManager.saveGroup('"+groupId+"',false)"}); 
			 tdeditable.innerHTML = "";
			 tdeditable.insert(c);
			 c.focus();
		 }
   },
  restartChartConfig: function(){
	  var tabla  = $$('#chartValueList tbody')[0];
	  var elements = tabla.childElementCount;
	  $$('#report_editChart_field img.img-onoff').each(function(imagen){
		  if(imagen.src.indexOf("grey.png")<0){
			  imagen.setAttribute('src',imagen.src.replace('.png','grey.png'));
			  imagen.removeClassName("selected");
		  }
		});
	  for(i = 1;i<=elements;i++){
		  tabla.removeChild(tabla.childElements()[tabla.childElementCount-1]);
	  }
  },
  showChartConfig: function(chartType){
	  
	  var chartTypeOld = "";
	  if($$('#report_editChart_field img.img-onoff.selected').size()>0){
		  chartTypeOld = $$('#report_editChart_field img.img-onoff.selected')[0].title;
	  }

	  if(chartType == null || chartType == chartTypeOld){
			this.restartChartConfig();
	  }else{
		  $$('#report_editChart_field img.img-onoff').each(function(imagen){
			  if(imagen.title==chartType){
				  imagen.setAttribute('src',imagen.src.replace('grey.png','.png'));
				  imagen.addClassName("selected");
			  }else if(imagen.src.indexOf("grey.png")<0){
				  imagen.setAttribute('src',imagen.src.replace('.png','grey.png'));
				  imagen.removeClassName("selected");
			  }
			});
		  
		  var tabla  = $$('#chartValueList tbody')[0];
		  
		  if(tabla.childElementCount==0){
			  var tr =new Element('tr');
			  var td1 =new Element('td', {'class':'label'});
			  var label1 =new Element('label', {'for':'report_chart_charxvalue'});
			  var span1 = new Element('span',{'class':'required'});
			  label1.insert("X value");
			  span1.insert(" *");
			  label1.insert(span1);
			  
			  td1.insert(label1);
			  
			  var td2 =new Element('td', {'class':'value'});
			  var input1 =new Element('input', {'type':'text', 
												   'id': 'report_chart_charxvalue' , 
												   'name': 'report_chart_charxvalue',
												   'class':'input-text required-entry',
												   	'value':''}); 
			  td2.insert(input1);
			  
			  var td3 = new Element('td',{'class':'scope-label'});
			  td3.insert("Column number");
			  tr.insert(td1);
			  tr.insert(td2);
			  tr.insert(td3);
			  
			  tabla.insert(tr);
			  
			  var tr =new Element('tr');
			  var td1 =new Element('td', {'class':'label'});
			  var label1 =new Element('label', {'for':'report_chart_charyvalue'});
			  var span1 = new Element('span',{'class':'required'});
			  
			  label1.insert("Y value");
			  span1.insert(" *");
			  label1.insert(span1);
			  td1.insert(label1);
			  var td2 =new Element('td', {'class':'value'});
			  var input1 =new Element('input', {'type':'text', 
												   'id': 'report_chart_charyvalue1' , 
												   'name': 'report_chart_charyvalue1',
												   'class':'input-text required-entry',
												   	'value':''}); 
			  td2.insert(input1);
			  
			  var td3 = new Element('td',{'class':'scope-label'});
			  
			  var imgAdd = new Element('img', {'src':URL_TYPE_SKIN+'adminhtml/default/default/images/accordion_open.png',
				  							  'class': 'v-middle',
				  							  'title':'addYValue',
				  							  'alt':'addYValue',
				  							  'onclick':'reportManager.addChartYValue();'
				  							  });
			  
			  
			  td3.insert(imgAdd);
			  td3.insert(" Column number")
			  
			  tr.insert(td1);
			  tr.insert(td2);
			  tr.insert(td3);
			  tabla.insert(tr); 
			  
		  }
		  
		  //if(chartType == 'pieChart' || chartType == 'pieDonutChart' || chartType == 'discreteBarChart'){
			  // Eliminamos todos los yValues
			  var elements = tabla.childElementCount;
			  for(i = 3;i<=elements;i++){
				  tabla.removeChild(tabla.childElements()[tabla.childElementCount-1]);
			  }
		  //}
	  }
	  
	  
  },
  addChartYValue: function(){
	  var tabla  = $$('#chartValueList tbody')[0];
	  
	  var chartType = $$('#report_editChart_field img.img-onoff.selected')[0].title;
	  
	  if(chartType != 'pieChart' && chartType != 'pieDonutChart' && chartType != 'discreteBarChart'){
		  
		  var number = tabla.childElementCount -1;
		  var tr =new Element('tr');
		  var td1 =new Element('td', {'class':'label'});
		  var label1 =new Element('label', {'for':'report_chart_charyvalue'});
		  label1.insert("Y value");
		  td1.insert(label1);
		  var td2 =new Element('td', {'class':'value'});
		  var input1 =new Element('input', {'type':'text', 
											   'id': 'report_chart_charyvalue' , 
											   'name': 'report_chart_charyvalue',
											   'class':'input-text',
											   	'value':''}); 
		  td2.insert(input1);
		  
		  var td3 = new Element('td',{'class':'scope-label'});
		  
		  var imgAdd = new Element('img', {'src':URL_TYPE_SKIN+'adminhtml/default/default/images/accordion_open.png',
			  							  'class': 'v-middle',
			  							  'title':'addYValue',
			  							  'alt':'addYValue',
			  							  'onclick':'reportManager.addChartYValue();'
			  							  });
		  
		  var imgRemove = new Element('img', {'src':URL_TYPE_SKIN+'adminhtml/default/default/images/accordion_close.png',
				  'class': 'v-middle',
				  'title':'removeYValue',
				  'alt':'removeYValue',
				  'onclick':'reportManager.removeChartYValue(this);'
				  });
		  
		  td3.insert(imgAdd);
		  td3.insert(" ");
		  td3.insert(imgRemove);
		  td3.insert(" Column number")
		  
		  tr.insert(td1);
		  tr.insert(td2);
		  tr.insert(td3);
		  tabla.insert(tr); 
	  }
	  
  },
  removeChartYValue : function(imagen){
	  imagen.parentNode.parentNode.remove();
	  
  },
  exportActiveReport: function(){
	  var report_id = $('general_code').value;
	  this.exportReport(report_id);
  },
  exportReport : function(report_id){
	  var lastUrl = this.url;
	  this.url=urlExport;
	  this.addVarToUrl("method", "REPORT");
	  this.reload(this.addVarToUrl("id", report_id));
	  this.url=lastUrl;
  },
  postToFrame : function(formId) {
      // create dynamic frame
	  $('report_list_import_result_field').innerHTML="";
      if (!$(this.ifrElemName)) {
          $('html-body').insert({
        	  bottom:'<iframe name="' + this.ifrElemName + '" id="' + this.ifrElemName + '" style="display:none;"/>'
          });
      }
      var action = "importReport";
      var newActionUrl = urlImport + (urlImport.match(new RegExp('\\?')) ? '&action='+action+'' : '?action='+action+'');

      // show mask, temporary set new target and submit form
      var loadingMask = $('loading-mask');
      var formElem    = $(formId);
      var oldTarget   = formElem.target;
      var oldAction   = formElem.action;

      Element.clonePosition(loadingMask, $$('#html-body .wrapper')[0], {offsetLeft:-2})
      toggleSelectsUnderBlock(loadingMask, false);
      loadingMask.show();
      setLoaderPosition();
      formElem.target = this.ifrElemName;
      formElem.action = newActionUrl;

      //formElem.action += (formElem.action.lastIndexOf('?') != -1 ? '&' : '?') + 'form_key=' + encodeURIComponent(formElem.form_key.value);
      formElem.submit();
      formElem.target = oldTarget;
      formElem.action = oldAction;
      
  },
  postToFrameComplete : function(response){
      var loadingMask = $('loading-mask');
      $(this.ifrElemName).remove();
      toggleSelectsUnderBlock(loadingMask, true);
      loadingMask.hide();
      var messages = response.evalJSON();
      var ul =new Element('ul', {'class':'messages'});
	  for(var i =0;i<messages.length;i++){
		  var li = new Element('li',{ 'class': messages[i].type})
		  var ul2 =new Element('ul');
		  var li2 = new Element('li');
		  var span = new Element('span');
		  span.insert(messages[i].msg);
		  li2.insert(span);
		  ul2.insert(li2);
		  li.insert(ul2);
		  ul.insert(li);
		  
		  if(messages[i].type=="success-msg" && messages[i].object_type=="report"){
			  this.updateReport2List(messages[i].report_id,messages[i].description,messages[i].group_description);
			  this.refreshReportList();
		  }
		  
	  }
	  
	  var resultados  = $('report_list_import_result_field');
	  resultados.insert(ul);
	  
  },
  changePermissionResource: function (){
	$('container-Permissions-menu').addClassName('changed');
	$('setupPermissions_resources_container').toggle();
  },
  chagePermissionViewRole: function (report){
	$('container-Permissions-menu').addClassName('changed');
	var encontrado = false;
	for(var i =0;i<this.rolePermission.length;i++){
		var permision= this.rolePermission [i];
		if(permision.report_id==report){
			encontrado = true;
			if(this.rolePermission[i].read==1){
				this.rolePermission[i].read = 0;
				$$('#setupPermissions_resources_container div[report='+report+'] img[action=view]')[0].addClassName ('desactive');
			} else{
				this.rolePermission[i].read = 1;
				$$('#setupPermissions_resources_container div[report='+report+'] img[action=view]')[0].removeClassName ('desactive');
			}
		}
	}
	if(!encontrado){
		this.rolePermission.push(
			{report_id: report, read: 1, edit: 0}
		);
		$$('#setupPermissions_resources_container div[report='+report+'] img[action=view]')[0].removeClassName ('desactive');
	}
	
  },
  chagePermissionEditRole: function(report){
	$('container-Permissions-menu').addClassName('changed');
	var encontrado = false;
	for(var i =0;i<this.rolePermission.length;i++){
		var permision= this.rolePermission [i];
		if(permision.report_id==report){
			encontrado = true;
			if(this.rolePermission[i].edit==1){
				this.rolePermission[i].edit = 0;
				$$('#setupPermissions_resources_container div[report='+report+'] img[action=edit]')[0].addClassName ('desactive');
			} else{
				this.rolePermission[i].edit = 1;
				$$('#setupPermissions_resources_container div[report='+report+'] img[action=edit]')[0].removeClassName ('desactive');
			}
		}
	}
	if(!encontrado){
		this.rolePermission.push(
			{report_id: report, read: 0, edit: 1}
		);
		$$('#setupPermissions_resources_container div[report='+report+'] img[action=edit]')[0].removeClassName ('desactive');
	}
  },
  chagePermissionViewGroupRole: function(group){
	$('container-Permissions-menu').addClassName('changed');
	alert("TODO change view to group:"+group);
  },
  chagePermissionEditGroupRole: function(group){
	$('container-Permissions-menu').addClassName('changed');
	alert("TODO change edit to group:"+group);
  },
  getRolePermission: function(role){
	$('container-Permissions-menu').removeClassName('changed');
	var params = {"role" : role};
	var manager=this;
	this.ws("getPermissionRole",params,function(response){
		  manager.rolePermission = response.responseText.evalJSON();
		  if(manager.rolePermission.length==0){
			//ALL
			$('setupPermissions_resources_container').hide();
			$('rolePermissionsResource').setValue(1);
			$$('#setupPermissions_resources_container div[name=reportPermission] img').each(function(element){ element.addClassName('desactive');});
		  }else{
			// CUSTOM
			$('setupPermissions_resources_container').show();
			$('rolePermissionsResource').setValue(0);
			$$('#setupPermissions_resources_container div [name=reportPermission]').each(function (elementReport){
			
				var report = elementReport.getAttribute('report');
				elementReport.childElements().each(function (element){
					if(element.nodeName=="IMG"){
						if(element.hasAttribute('action')){
							var action = element.getAttribute('action');
							if(manager.hasPermissionRoleReport(report,action)){
								// activar
								element.removeClassName ('desactive');
							}else{
								// desactivar
								element.addClassName ('desactive');
							}
						}
					}
				})
				
			});
		  }
		  
	  });
  },
  hasPermissionRoleReport: function(report,action){
	var haspermision = false;
	for(var i =0;i<this.rolePermission.length;i++){
		var permision= this.rolePermission [i];
		if(permision.report_id==report){
			if(action=='view'){
				haspermision = (permision.read==1?true:false);
			}else{
				haspermision = (permision.edit==1?true:false);
			}
		}
	}
	return haspermision;
  },
  savePemsissionRole: function(){
	var permissionResource = $('rolePermissionsResource').value;
	var permissions = (permissionResource==0?JSON.stringify(this.rolePermission):"[{}]");
	var role_id = $('rolePermissions').value;
	var params = {role_id : role_id,permissions:permissions};
	var manager= this;
	this.ws('savePermissionRole',params,function(response){
		  manager.showMessageWSLog(response);
		  var message = response.responseText.evalJSON();
		  if(message[0].type=="success-msg"){
			  $('container-Permissions-menu').removeClassName('changed');
		  }
	});
  },
  refreshLinksList: function(type,all){
	if(all){
	  $$('#linkList'+type+' tr').each(function (element){
			if(!element.hasClassName('headings')){
             element.remove();
			}
	  });
	}
	  
	  $$('#reportEditlinkList'+type+' tr').each(function (element){
			if(!element.hasClassName('headings')){
             element.remove();
			}
	  });

	  
	  var listLink;
	  if('TR'==type){
		linkList=linkListTR;
	  }else if('TD'==type){
		linkList=linkListTD;
	  }else{
	    linkList = new Array();
	  }
	  var tableA = $$('#linkList'+type+' tbody')[0];
	  var tableB = $$('#reportEditlinkList'+type+' tbody')[0];
	  
	  for(var i=0;i< linkList.length;i++){
			// tabla de la ventana de link edit
		 if(all){
		  var trA = new Element('tr',{'id':'link'+type+'-'+linkList[i].getId()});
		  
		  var tdDescriptionA = new Element('td',{ 'class':'tdSelectable',
												 'name':'description',
			  							         'onClick':"reportManager.manageLink('"+linkList[i].getId()+"','"+type+"','description')"});
		  tdDescriptionA.insert(linkList[i].getDescription());
		  
		  var tdUrlA = new Element('td',{ 'class':'tdSelectable',
												 'name':'url',
			  							         'onClick':"reportManager.manageLink('"+linkList[i].getId()+"','"+type+"','url')"});
		  tdUrlA.insert(linkList[i].getUrl());

		  var tdTypeA = new Element('td');
		  var imgDeleteA = new Element('img', {'src':URL_TYPE_SKIN+'adminhtml/default/default/images/cancel_btn_icon.gif',
											  'class': 'v-middle',
											  'title':'delete',
											  'alt':'delete',
											  'onclick': "reportManager.removeLink('"+linkList[i].getId()+"','"+type+"',false)"
											  });
		  tdTypeA.insert(imgDeleteA);
		  
		  trA.insert(tdDescriptionA);
		  trA.insert(tdUrlA);
		  trA.insert(tdTypeA);
		  tableA.insert(trA);
		 }
		  // Tabla de la ventana de report edit
		  if(type=='TR'){
			var trB = new Element('tr',{'id':'linkRE'+type+'-'+linkList[i].getId(),
									  'class':'tdSelectable',
									  'onclick': "reportManager.reportEditRowLinkSelected('"+linkList[i].getId()+"','')"});
		  } else {
			var trB = new Element('tr',{'id':'linkRE'+type+'-'+linkList[i].getId()});
		  }
									  
		  var tdColumnB = new Element('td',{'name':'column'});
		  var inputB =new Element('input', {'type':'text', 
											   'id': 'reportEditRowLinkInput-'+linkList[i].getId() , 
											   'name': linkList[i].getId(),
											   'class':'input-text validate-number',
											   	'value':''}); 
			
		  tdColumnB.insert(inputB);
		  
		  //var tdIdB = new Element('td',{'name':'link_id'});
		  //tdIdB.insert(linkList[i].getId());
		  
		  var tdDescriptionB = new Element('td',{'name':'description'});
		  tdDescriptionB.insert(linkList[i].getDescription());
		  
		  var tdUrlB = new Element('td',{ 'name':'url'});
		  tdUrlB.insert(linkList[i].getUrl());
		  if(type=='TD'){
			trB.insert(tdColumnB);
		  }
		  //trB.insert(tdIdB);
		  trB.insert(tdDescriptionB);
		  trB.insert(tdUrlB);
		  tableB.insert(trB);
	  } 
  },
  manageLink: function(linkId,type,element){
	 if($$('#link'+type+'-'+linkId+' td[name='+element+'] input').length==0){
		 var tdeditable = $$('#link'+type+'-'+linkId+' td[name='+element+']')[0];
		 var value= htmlspecialchars_decode(tdeditable.innerHTML);
		 var c =new Element('input', {'type':'text', 
									  'id': 'description' , 
									  'style': 'width:100%',
									  'value':value,
									  'onblur':"reportManager.saveLink('"+linkId+"','"+type+"','"+element+"',false)"}); 
		 tdeditable.innerHTML = "";
		 tdeditable.insert(c);
		 c.focus();
	 }
  },
  saveLink: function(linkId,type,element,nuevo){
	var datalinkId;
	var datadescription;
	var dataurl;
	var action;
	if(nuevo){
		action='addLink';
		var datadescription = $$('#link'+type+'-'+linkId+' td[name=description] input')[0].value;
		var dataurl = $$('#link'+type+'-'+linkId+' td[name=url] input')[0].value;
		
	}else{
		 action='saveLink';
		 datalinkId = linkId;
		 var datadescription = (element=='description'?$$('#link'+type+'-'+linkId+' td[name=description] input')[0].value:htmlspecialchars_decode($$('#link'+type+'-'+linkId+' td[name=description]')[0].innerHTML));
		 var dataurl = (element=='url'?$$('#link'+type+'-'+linkId+' td[name=url] input')[0].value:htmlspecialchars_decode($$('#link'+type+'-'+linkId+' td[name=url]')[0].innerHTML));
	}
	 var params = {linkId : datalinkId,
				description:datadescription,
				url:dataurl,
				type:type};
	  var manager= this;
	  
	  this.ws(action,params,function(response){
		  var report = response.responseText.evalJSON();
		  if(report[0].type=="success-msg"){
			 if(nuevo){
				datalinkId=report[0].id;
				$$('#link'+type+'-'+linkId)[0].setAttribute('id','link'+type+'-'+datalinkId);
				//$$('#link'+type+'-'+datalinkId+' td[name=link_id]')[0].innerHTML=datalinkId;
				$$('#link'+type+'-'+datalinkId+' td[name=description]')[0].innerHTML=datadescription;
				var valueDataUrl = htmlspecialchars(dataurl);
				$$('#link'+type+'-'+datalinkId+' td[name=url]')[0].innerHTML=valueDataUrl;
				$$('#link'+type+'-'+datalinkId+' td[name=url]')[0].setAttribute('onclick','reportManager.manageLink("'+datalinkId+'","TD","url")');
				$$('#link'+type+'-'+datalinkId+' td[name=url]')[0].addClassName('tdSelectable');
				$$('#link'+type+'-'+datalinkId+' td[name=actions] img[title=save]')[0].remove();
				imgDelete = $$('#link'+type+'-'+datalinkId+' td[name=actions] img[title=delete]')[0];
				imgDelete.setAttribute('onclick',imgDelete.getAttribute('onclick').replace("removeLink('"+linkId+"','"+type+"',true)","removeLink('"+datalinkId+"','"+type+"',false)"));
			 }else{
				  var tdeditable = $$('#link'+type+'-'+datalinkId+' td[name='+element+']')[0];
				  var value = htmlspecialchars($$('#link'+type+'-'+datalinkId+' td[name='+element+'] input')[0].value)
				  tdeditable.innerHTML = value;
			  }
			  manager.updateLink2List(datalinkId,datadescription,dataurl,type);
			  manager.refreshLinksList(type,false);
		  }else if(report[0].type == 'error-msg'){
				manager.showMessageWSLog(response);
		  }
		  
	  });

  },
  removeLink: function(linkId,type,nuevo){
	if(nuevo){
		$('link'+type+'-'+linkId).remove();
	}else{
	  var manager= this;
	  var params = {linkId : linkId};
	  this.ws('delLink',params,function(response){
		  var report = response.responseText.evalJSON();
		  if(report[0].type=="success-msg"){
			  $('link'+type+'-'+linkId).remove();
			  manager.removeLink2List(linkId,type);
			  manager.refreshLinksList(type,false);
		  }else if(report[0].type == 'error-msg'){
				manager.showMessageWSLog(response);
		  }
		  
	  });
	}
	
  },
  addLink: function(type){
		var linkId = Math.floor((Math.random() * 100) + 1);
		var table = $$('#linkList'+type+' tbody')[0];
		var tr = new Element('tr',{'id':'link'+type+'-'+linkId});
		  //var tdId = new Element('td',{'name':'link_id'});
		  //var cId =new Element('input', {'type':'text', 'name': 'link_id' , 'style': 'width:100%'});
		 // tdId.insert(cId);
		  
		  var tdDescription = new Element('td',{ 'name':'description'});
		  var cDescription =new Element('input', {'type':'text', 'name': 'description' , 'style': 'width:100%'});
		  tdDescription.insert(cDescription);
		  
		  
		  var tdUrl = new Element('td',{'name':'url'});
		  var cUrl =new Element('input', {'type':'text', 'name': 'url' , 'style': 'width:100%'});
		  tdUrl.insert(cUrl);
		  

		  var tdType = new Element('td',{'name':'actions'});
		  var imgSave = new Element('img', {'src':URL_TYPE_MEDIA+'serpini_sqlreport/disk.png',
											  'class': 'v-middle',
											  'title':'save',
											  'alt':'save',
											  'onclick': "reportManager.saveLink('"+linkId+"','"+type+"','',true)"
											  });
		  var imgDelete = new Element('img', {'src':URL_TYPE_SKIN+'adminhtml/default/default/images/cancel_btn_icon.gif',
											  'class': 'v-middle',
											  'title':'delete',
											  'alt':'delete',
											  'onclick': "reportManager.removeLink('"+linkId+"','"+type+"',true)"
											  });
		  tdType.insert(imgSave);
		  tdType.insert(' ');
		  tdType.insert(imgDelete);
		  
		  
		  //tr.insert(tdId);
		  tr.insert(tdDescription);
		  tr.insert(tdUrl);
		  tr.insert(tdType);
		  table.insert(tr);
  },
  updateLink2List: function(linkId,description,url,type){
	// Buscamos por el id
	  var encontrado=false;
	  var list = new Array();
	  if("TR"==type){
		list=linkListTR;
	  }else if("TD"==type){
		list=linkListTD;
	  }
	  for(var i=0;i< list.length && !encontrado;i++){
		  if(linkId==list[i].getId()){
			  // Cambia la descripci�n, url
			  list[i].setDescription(description);
			  list[i].setUrl(url);
			  encontrado=true;
		  }
	  }
	  
	  if(!encontrado){
		 //  Lo insertamos
		  list.splice(0,0,new Link(linkId,description,url,type));
	  }
  },
  removeLink2List: function(linkId,type){
	var list = new Array();
	  if("TR"==type){
		list=linkListTR;
	  }else if("TD"==type){
		list=linkListTD;
	  }
	  for(var i=0;i< list.length;i++){
		  if(list[i].getId()==linkId){
			  list.splice(i,1);
		  }
	  }
  },
  reportEditRowLinkSelected: function(linkId,listLinkTRvariables){
	var report_id = $('general_code').value;
	var params = {linkId : linkId,reportId:report_id};
	var manager = this;
	this.ws('getLink',params,function(response){
		  var link = response.responseText.evalJSON();
		  if(link.type_message=="success-msg"){
			  manager.showRowLinkReport(linkId,link.description,link.url,link.variables);
			  if(""!=listLinkTRvariables){
				  for(var i=0;i<listLinkTRvariables.length;i++){
					  $('reportEditRowlinkSelectedVAR-'+listLinkTRvariables[i][0]).value=listLinkTRvariables[i][1];	
				  }
			  }
		  }else if(link.type_message == 'error-msg'){
			  manager.showMessageWSLog(response);
		  }
		  
	  });
	
	
  },
  showRowLinkReport:function(id,description,url,variables){
	$('reportEditRowlinkList').hide();
	$('reportEditRowlinkSelected').show();
	$('reportEditRowlinkSelectedID').value=id;
	$('reportEditRowlinkSelectedDESCRIPTION').value=description;
	$('reportEditRowlinkSelectedURL').value=url;
	
	$$('#reportEditRowlinkSelectedVAR tr').each(function (element){
		 if(!element.hasClassName('headings')){
             element.remove();
         }
	});
	var table = $$('#reportEditRowlinkSelectedVAR tbody')[0];
	if(variables.length != 0){
		for(variable in variables){
		  var tr = new Element('tr',{'class':'variables'});
		  var tdLabel = new Element('td',{'class':'label'});
		  tdLabel.innerHTML='<label >'+variable+' <span class="required">*</span></label>';
		  
		  var tdValue = new Element('td',{'class':'scope-label'});
		  var inputValue = new Element('input',{'id':'reportEditRowlinkSelectedVAR-'+variable,
												'variable' : variable,
												'value':variables[variable],
												'class':'input-text required-entry validate-number',
												'type':'text',
												'style':'width: 30px'});
		  
		  tdValue.insert(inputValue);
		  tdValue.insert(" Column number");
		  tr.insert(tdLabel);
		  tr.insert(tdValue);
		  
		  table.insert(tr);
		}
	}
	
  },
  reportEditRowLinkUnSelected:function(){
	$('reportEditRowlinkList').show();
	$('reportEditRowlinkSelected').hide();
	$('reportEditRowlinkSelectedID').value = "";
  },
  xlsFormat:function(where,type,system){
	  var def="false";
	  
	  if(!system){
		  var format = where.substr(0,3);
		  def = $$("input[name='"+format+"Default']:checked")[0].value;
	  }
	  
	  var containerName = (system?'container-System':'container-ReportEdit');
	  
	  if(def=="false"){
		  if(type=="BackgroundColor"||type=="Color"){
			  $$('#'+containerName+' #'+where+type)[0].color.showPicker();
			  
		  }else{
			  var button = $$('#'+containerName+' #'+where+type)[0];
			  
			  if(!button.hasClassName("changed")){
				  button.addClassName("changed");
			  }
			  
			  if(button.hasClassName("check")){
				  //Desactivar
				  if(type!='AlignLeft'&&type!='AlignCenter' && type!='AlignRight'){
					  button.removeClassName("check");
					  button.addClassName("uncheck");
				  }  
				  
			  }else{
				  // Activar
				  
				  // Alineaciones
				  if(type=='AlignLeft'||type=='AlignCenter'||type=='AlignRight'){
					  $$('#'+containerName+' #'+where+'AlignLeft')[0].removeClassName("check");
					  $$('#'+containerName+' #'+where+'AlignCenter')[0].removeClassName("check");
					  $$('#'+containerName+' #'+where+'AlignRight')[0].removeClassName("check");
					  $$('#'+containerName+' #'+where+'AlignLeft')[0].addClassName("uncheck");
					  $$('#'+containerName+' #'+where+'AlignCenter')[0].addClassName("uncheck");
					  $$('#'+containerName+' #'+where+'AlignRight')[0].addClassName("uncheck");
				  }
				  
				  // Bordes
				  if (type=="BorderTop"||type=="BorderRight"||type=="BorderBottom"||type=="BorderLeft") {
					  $$('#'+containerName+' #'+where+'BorderAll')[0].removeClassName("check");
					  $$('#'+containerName+' #'+where+'BorderAll')[0].addClassName("uncheck");
					  
				  }else if (type=="BorderAll"){
					  $$('#'+containerName+' #'+where+'BorderTop')[0].removeClassName("check");
					  $$('#'+containerName+' #'+where+'BorderRight')[0].removeClassName("check");
					  $$('#'+containerName+' #'+where+'BorderBottom')[0].removeClassName("check");
					  $$('#'+containerName+' #'+where+'BorderLeft')[0].removeClassName("check");
					  $$('#'+containerName+' #'+where+'BorderTop')[0].addClassName("uncheck");
					  $$('#'+containerName+' #'+where+'BorderRight')[0].addClassName("uncheck");
					  $$('#'+containerName+' #'+where+'BorderBottom')[0].addClassName("uncheck");
					  $$('#'+containerName+' #'+where+'BorderLeft')[0].addClassName("uncheck");
					  
				  } 
				  button.removeClassName("uncheck");
				  button.addClassName("check");
				  
			  }
		  }
	  }
	  return false;
  },
  xlsChangeColor:function($where,$what){
	  $$('#container-ReportEdit #'+$where)[0].style.backgroundColor = '#'+$what.color;
	  //document.getElementById($where).style.backgroundColor = '#'+$what.color;
	 
	  if(!$what.hasClassName("changed")){
		  $what.addClassName("changed");
	  }
  },
  xlsActiveCustomize:function($status){
	  $$('#reportEditXls_form select').each(function(element){
		  if($status) element.disable(); 
		  else element.enable();
			});
  },
  pdfActiveCustomize:function($status){
	  $$('#reportEditPDF_form select').each(function(element){
		  if($status) element.disable(); 
		  else element.enable();
	   });
	  
	  $$('#reportEditPDF_form textarea').each(function(element){
		  if($status) element.disable(); 
		  else element.enable();
	   });
	  
	  $$('#reportEditPDF_form input').each(function(element){
		  if(element.name!="pdfDefault"){
			  if($status) element.disable(); 
			  else element.enable(); 
		  }
		  
	   });
  },
  getValuesCombo:function(element){
	  if(element.childElementCount<=1){
		  var comboList="";
		  $$('#report_cron_filters_list select').each(function(ele){
			  if(ele.childElementCount<=1){
				  var comboId=ele.getAttribute("name").substr(ele.getAttribute("name").indexOf("-")+1,ele.getAttribute("name").length);
				  comboList+=comboId+"|";
			  }
		  });
		  
		  this.ws('loadComboValues',{comboList:comboList},function(response){
				var comboList = response.responseText.evalJSON();
				if(comboList[0].type == 'error-msg'){
					manager.showMessageWSLog(response);
				}else{
					var combos = comboList[0].values;
					for(var comboId in combos){
						var valores = combos[comboId];
						var comboElement = $$('#report_cron_filters_list select[id=cronCombo-'+comboId+']')[0];
						for(var id in valores){
							var descripcion = valores[id];
							var option =new Element('option',{'value':id});
							option.insert(descripcion);
							comboElement.insert(option);
							
						}
						
						
					}
				}
				  
				  
		  	});
	  }
  },
  loadMarket : function(){
	  if(!this.marketLoaded){
		  var manager = this;
		  
		  this.ws('loadMarket','',function(response){
				var market = response.responseText.evalJSON();
				if(market[0].type == 'error-msg'){
					manager.showMessageWSLog(response);
				}else{
					var grupos = market[0].groupList;
					for(var i =0;i<grupos.length;i++){
						var id = grupos[i].id;
						var title = grupos[i].title;
						var iconUrl = grupos[i].iconurl;
						if("yes"==grupos[i].isnew){
							
							var divValue =new Element('div', {'class':'entry-edit-head collapseable','style':'background: #F58600'});
						}else{
							var divValue =new Element('div', {'class':'entry-edit-head collapseable'});
						}
						
						
						var aValue =new Element('a', {'id':"market_group_"+id+"-head",
													  'href': '#',
													  'class':'open',
													  'onClick':"reportManager.collapse('market_group_"+id+"')"});
						aValue.insert(title);
						divValue.insert(aValue);
						
						$$('#container-Market')[0].insert(divValue);
						
						var fieldset =new Element('fieldset', {'class':'config collapseable','id':'market_group_'+id});
						var divR =new Element('div', {'class':'grid','style':'margin:10px; OVERFLOW: auto; TOP: 48px;border: 1px solid #d6d6d6'});
						
						var tableR =new Element('table', {'class':'data',"cellspacing":"0"});
						
						var colgroupR = new Element('colgroup');
						colgroupR.insert(new Element('col',{'width':'200'}));
						colgroupR.insert(new Element('col',{'width':'50'}));
						colgroupR.insert(new Element('col'));
						colgroupR.insert(new Element('col',{'width':'100'}));
						tableR.insert(colgroupR);
						
						var headR = new Element('thead');
						var trHR = new Element('tr',{'class':'headings'});
						trHR.insert(new Element('th',{'class':'no-link'}).insert(new Element('span',{'class':'no-br'}).insert("Title")));
						trHR.insert(new Element('th',{'class':'no-link'}).insert(new Element('span',{'class':'no-br'}).insert("Version")));
						trHR.insert(new Element('th',{'class':'no-link'}).insert(new Element('span',{'class':'no-br'}).insert("Description")));
						trHR.insert(new Element('th',{'class':'no-link'}).insert(new Element('span',{'class':'no-br'}).insert("Action")));
						headR.insert(trHR)
						
						tableR.insert(headR);
						var reports = market[0].reportList;
						for(var j =0;j<reports.length;j++){
							if(reports[j].grupoId==id){
								if("yes"==reports[j].isnew){
									var tdTitle = new Element('td',{'class':'rowNew','style':"padding-left: 20px"}).insert(reports[j].title);
								}else{
									var tdTitle = new Element('td').insert(reports[j].title);
								}
								
								var tdVersion = new Element('td').insert(reports[j].version);
								var tdDescription = new Element('td').insert(reports[j].description);
								
								var trReport = new Element('tr');
								trReport.insert(tdTitle);
								trReport.insert(tdVersion);
								trReport.insert(tdDescription);
								var butonAction;
								var tdId=reports[j].id;
								switch (reports[j].estado) {
								  	case 'new':
								  		butonAction= new Element('button',{'type':'button','class':'scalable add','onclick':"reportManager.addReportMarket('add','"+reports[j].id+"','')",'style':'display: inline'});
								  		butonAction.insert(new Element('span').insert("Install"));
								  	break;
								  	case 'update':
								  		butonAction= new Element('button',{'type':'button','class':'scalable export','onclick':"reportManager.addReportMarket('update','"+reports[j].id+"','"+reports[j].code+"')",'style':'display: inline'});
								  		butonAction.insert(new Element('span').insert("Update"));
								    break;
								  	case 'instaled':
								  		butonAction= new Element('button',{'type':'button','class':'scalable delete','onclick':"reportManager.delReport('market','"+reports[j].code+"')",'style':'display: inline'});
								  		butonAction.insert(new Element('span').insert("Delete"));
								  		manager.marketReports[reports[j].code]=reports[j].id;
								    break;
								}
								
								var tdAction = new Element('td',{'id':'marketAction'+tdId}).insert(butonAction);
								trReport.insert(tdAction);
								
								tableR.insert(trReport);
							}
						}
						
						divR.insert(tableR);
						fieldset.insert(divR);
						
						$$('#container-Market')[0].insert(fieldset);
						
						manager.marketLoaded=true;
					}
					
				}
		  });
	  }
  },
  addReportMarket : function(action,codeMarket,code){
	  var manager = this;
	  this.ws('addReportMarket',{codeMarket:codeMarket,code:code},function(response){
			var market = response.responseText.evalJSON();
			if(market[0].type == 'error-msg'){
				manager.showMessageWSLog(response);
			}else{
				
				var report_code = "";
				var report_title = "";
				var group_description = "";
				var group_id = "";
		  		
	  			var msgs = market[0];
				for(var j =0;j<msgs.length;j++){
					if("success-msg"==msgs[j].type){
						$('messagesLog').addClassName(msgs[j].type);
						$('messagesLog').parentNode.style.display="inline";
						$('messagesLogText').innerHTML=msgs[j].msg;
						$('messagesLog').removeClassName("error-msg");
						setTimeout(function() {$('messagesLog').parentNode.style.display="none";},5000);
					}
					if("report"==msgs[j].object_type){
						report_code=msgs[j].report_id;
						report_title=msgs[j].description;
						group_description = msgs[j].group_description;
						group_id = msgs[j].group_id; 
						group_order = msgs[j].group_order;
					}
				}
				
				var tdAction = $$('#marketAction'+codeMarket)[0];
				tdAction.innerHTML="";
				var butonAction= new Element('button',{'type':'button','class':'scalable delete','onclick':"reportManager.delReport('market','"+report_code+"')",'style':'display: inline'});
		  		butonAction.insert(new Element('span').insert("Delete"));
		  		tdAction.insert(butonAction);
			  	//if(action=="add"){		
		  		manager.removeReport2List(code);
				manager.marketReports[report_code]=codeMarket;
		  			
		  		groupList[group_order]=new Group(group_id,group_description,group_order);
				manager.refreshGroupList();
					
		  		manager.updateReport2List(report_code,report_title,group_description);
				manager.refreshReportList();
		  		//}
		  		
			}
	  });
	  
  },
  cronAddParamContent : function (param){
	  var offset = prompt("Please insert the number of the column from which values will be displayed", "1");
	  if(this.isTinymceInit('cronEmailText')){
		  tinyMCE.get('cronEmailText').execCommand('mceInsertContent', false, $('prefix_parameter').value+param+"["+offset+"]");
	  }else{
		  $('cronEmailText').value = $('cronEmailText').value +" " + $('prefix_parameter').value+param +"["+offset+"]" + " ";
	  }
	  
  },
  setPage : function(page){
	  $('page').value=page;
	  this.doFilter();
	  
  },
  addCronLog : function(cronLog){
	  var even = true;
	  var table = $$('#report_cronLog_table tbody')[0];
	  if(null!=cronLog){
		  for(var i =0;i<cronLog.length;i++){
			  
			  var tr;
			  if(even){
				  tr  =new Element('tr', {'class': 'even'});
			  }else{
				  tr  =new Element('tr');
			  }
			  even = !even;
			 
	          var tdA = new Element('td',{'class':'a-center'});
	          var inputA = new Element('input',{'type':'checkbox','value':cronLog[i].log_id});
	          tdA.insert(inputA);
	          tr.insert(tdA);
	          
			  var td1 =new Element('td');
			  td1.insert(cronLog[i].created_at);
			  tr.insert(td1);
			  var td2 =new Element('td');
			  td2.insert(cronLog[i].to);
			  tr.insert(td2);
			  var td3 =new Element('td');
			  td3.insert(cronLog[i].cc);
			  tr.insert(td3);
			  var td4 =new Element('td');
			  td4.insert(cronLog[i].bcc);
			  tr.insert(td4);
			  var td5 =new Element('td');
			  td5.insert(cronLog[i].subject);
			  tr.insert(td5);
			  var td6 =new Element('td',{'class':"a-center"});
			  var imgHTML = new Element('img', {'src':URL_TYPE_MEDIA+'serpini_sqlreport/html.png',
				  'class': 'v-middle',
				  'title':'View Content',
				  'alt':'View Content',
				  'style':"cursor: pointer",
				  'onclick':'reportManager.openModal("Email content",reportManager.cronLogContent['+cronLog[i].log_id+'])'
				  }); 
			  td6.insert(imgHTML);
			  tr.insert(td6);
			  var td7 =new Element('td',{'class':"a-center"});
			  if(""==cronLog[i].error){
				  var imgOK = new Element('img', {'src':URL_TYPE_MEDIA+'serpini_sqlreport/accept.png',
					  'class': 'v-middle',
					  'title':'View Content',
					  'alt':'View Content'
					  }); 
				  td7.insert(imgOK);
			  }else{
				  var imgERROR = new Element('img', {'src':URL_TYPE_MEDIA+'serpini_sqlreport/error.png',
					  'class': 'v-middle',
					  'title':'View Content',
					  'alt':'View Content',
					  'style':"cursor: pointer",
					  'onclick':'reportManager.openModal("Email error",reportManager.cronLogError['+cronLog[i].log_id+'])'
					  }); 
				  td7.insert(imgERROR);
			  }
			  
			  tr.insert(td7);
			  
			  table.insert(tr);
			  
			  this.cronLogContent[cronLog[i].log_id]=cronLog[i].text;
			  this.cronLogError[cronLog[i].log_id]=cronLog[i].error;
			  
		  } 
	  }
	  
  },
  tableSelectAll: function(id){
	  $$('#'+id+' tbody input[type=checkbox]').each(function(element){
		  element.checked=true;
	  });
	  return false;
  },
  tableUnselectAll: function(id){
	  $$('#'+id+' tbody input[type=checkbox]').each(function(element){
		  element.checked=false;
	  });
	  return false;
  },
  doCronLog : function(){
	  var validator  = new Validation('form_cronLogAction');
      if (validator.validate()) {
    	  
		  var cronLogId="";
		  $$('#report_cronLog_table tbody input[type=checkbox]').each(function(element){
			  if(element.checked){
				  cronLogId +="|"+element.value;
			  }
		  });
		  if(""!=cronLogId){
			  var report_id = $('general_code').value;
			  var manager = this;
			  this.ws('removeCronLog',{report_id:report_id,cronLogList:cronLogId},function(response){
					var report = response.responseText.evalJSON();
					if(report[0].type == 'error-msg'){
							manager.showMessageWSLog(response);
					}else{
						$$('#report_cronLog_table tbody input[type=checkbox]').each(function(element){
							  if(element.checked){
								  element.parentElement.parentElement.remove();
							  }
						  });
						  
					}
			  });
		  }
		  
      }
  },
  openModal:function(title,contenido){
	  Dialog.info(contenido, {
          draggable:true,
          resizable:true,
          closable:true,
          className:"magento",
          windowClassName:"popup-window",
          title:title,
          width:700,
          //height:270,
          zIndex:1000,
          recenterAuto:false,
          hideEffect:Element.hide,
          showEffect:Element.show,
          id:'cronLogContent'
      });
  },
  closeModal: function(){
	  $('overlay_modal').remove();
	  $('overlay_modal-Content').remove();
	  
  },
  showEditor: function(id){
	  if(this.isTinymceInit(id)){
		  tinyMCE.execCommand('mceRemoveControl', false, id);
	  }else{
		  var plugins = 'inlinepopups,safari,pagebreak,style,layer,table,advhr,advimage,emotions,iespell,media,searchreplace,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras';
		  tinymce.init({
			    selector: "#"+id,
			    schema : 'html5',
	            mode : 'none',
	            elements : id,
	            theme : 'advanced',
	            plugins : plugins,
	            theme_advanced_buttons1 : 'magentowidget,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect',
	            theme_advanced_buttons2 : 'cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,forecolor,backcolor',
	            theme_advanced_buttons3 : 'tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,iespell,media,advhr,|,ltr,rtl,|,fullscreen',
	            theme_advanced_buttons4 : 'insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,pagebreak',
	            theme_advanced_toolbar_location : 'top',
	            theme_advanced_toolbar_align : 'left',
	            theme_advanced_statusbar_location : 'bottom',
	            theme_advanced_resizing : true,
	            convert_urls : false,
	            relative_urls : false,
	            doctype : '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',

			 });
	  }
	  return false;
	  
  },
  isTinymceInit: function(id){
	  for(var i =0;i<tinymce.editors.length;i++){
		  if(tinymce.editors[i].id==id){
			  return true;
		  }
	  }
	  return false;
  },
  testConnection : function(){
	  
	  $$('#system_dbConnection_table input').each(function(el){
		  el.addClassName('required-entry');
	  });
	  
	  var validator  = new Validation('system_dbConnection_table');
      if (validator.validate()) {
    	  var db_host = $('db_host').value;
    	  var db_name = $('db_name').value;
    	  var db_username = $('db_username').value;
    	  var db_password = $('db_password').value;
    	  var manager = this;
    	  
    	  this.ws('testConnection',{db_host:db_host,db_name:db_name,db_username:db_username,db_password:db_password},function(response){
    			var report = response.responseText.evalJSON();
    			if(report[0].type == 'error-msg'){
    					manager.showMessageWSLog(response);
    			}else{
    				if(report[0].status=="ok"){
    					$('resutlTestConnection').removeClassName('error');
    					$('resutlTestConnection').addClassName('ok');
    					
    				}else{
    					$('resutlTestConnection').removeClassName('ok');
    					$('resutlTestConnection').addClassName('error');
    				}
    				$('resutlTestConnection').innerHTML=report[0].msg;
    				setTimeout(function() {$('resutlTestConnection').innerHTML="";},10000);
    			}
    			
      	  });  
      }
      $$('#system_dbConnection_table input').each(function(el){
		  el.removeClassName('required-entry');
	  });
  },
  addGroupReport : function(){
	  var tr =new Element('tr');
	  var tdCode =new Element('td', {'name':'columns'}); 
	  var inputCode =new Element('input', {'type':'text', 
										   'id': 'columns' , 
										   'style': 'width:100%',
										   'class': 'required-entry validate-number',
										   	'value':''}); 
	  tdCode.insert(inputCode);
	  tr.insert(tdCode);
	  var tdDescription =new Element('td', {'name':'description'}); 
	  var inputDescription =new Element('input', {'type':'text', 
										   'id': 'description' , 
										   'style': 'width:100%',
										   'class': 'required-entry',
										   	'value':''});
	  tdDescription.insert(inputDescription);
	  tr.insert(tdDescription);
	  $$('#reportGroupTable tbody')[0].insert(tr);
  },
  addGroupReportVal : function(columns,description){
	  var tr =new Element('tr');
	  var tdCode =new Element('td', {'name':'columns'}); 
	  var inputCode =new Element('input', {'type':'text', 
										   'id': 'columns' , 
										   'style': 'width:100%',
										   'class': 'required-entry validate-number',
										   	'value':''});
	  inputCode.value=columns;
	  
	  tdCode.insert(inputCode);
	  tr.insert(tdCode);
	  var tdDescription =new Element('td', {'name':'description'}); 
	  var inputDescription =new Element('input', {'type':'text', 
										   'id': 'description' , 
										   'style': 'width:100%',
										   'class': 'required-entry',
										   	'value':''});
	  inputDescription.value=description;
	  tdDescription.insert(inputDescription);
	  tr.insert(tdDescription);
	  $$('#reportGroupTable tbody')[0].insert(tr);
  },
  showExpPDFOptions : function(){
	  if("PDF"==$('cronEmailAttach').value){
		  this.hideExpPDFOptions(true);
	  }else{
		  this.hideExpPDFOptions(false);
	  }
  },
  hideExpPDFOptions : function(show){
	  if(show){
		  $('TRpdfTitleString').show();
		  $('TRpdfDescriptionString').show();
		  $('TRpdfFooterString').show();
	  }else{
		  $('TRpdfTitleString').hide();
		  $('TRpdfDescriptionString').hide();
		  $('TRpdfFooterString').hide();
	  }
  },
  sendReportMarket : function(){
	  var report_id = $('general_code').value;
	  var params = {code : report_id};
	  var manager = this;
	  this.ws("sendReportMarket",params,function(response){
		  manager.showMessageWSLog(response);
		  
		  var message = response.responseText.evalJSON();
		  if(message[0].type=="success-msg"){
			  
		  }

	  });
  }
  

};


var Combo = Class.create();
Combo.prototype = {
  initialize: function(code,description,parameter,type) {
	  this.code = code;
	  this.description = description;
	  this.parameter = parameter;
	  this.type=type;
	  switch (this.getType()) {
	  	case 'date':
	  		this.value="";
	  	break;
	  	case 'select':
	  		this.value=new Array();
	    break;
	  	case 'text':
	  		this.value="";
	    break;
	  	case 'set':
	  		this.value=new Array();
	    break;
	  }
  },
  getCode: function(){
	  return this.code;
  },
  getDescription: function(){
	  return this.description;
  },
  getParameter: function(){
	  return this.parameter;
  },
  getType: function(){
	  return this.type;
  },
  getValue: function(){
	  return this.value;
  },
  setDescription: function(description){
	  this.description = description;
  },
  setParameter: function(parameter){
	  this.parameter=parameter;
  },
  setType: function(type){
	  this.type=type;
  },
  setValue: function(value){
	  this.value=value;
  },
  printHTML : function(){
	  var result = "";
	  var buttonAdd = ' <button id="butAddParamCron" type="button" class="scalable import" onclick="reportManager.cronAddParamContent(\''+this.getParameter()+'\')" style="display: inline;"><span>Add to Content</span></button>';
	  switch (this.getType()) {
    	case 'date':
    		result = "<input class=\"input-text no-changes required-entry\" type=\"text\" id=\"cronCombo-"+this.getCode()+"\" name=\"cronCombo-"+this.getCode()+"\" value=\""+this.getValue()+"\" style=\"width:5em\"> ";
    		URL_TYPE_SKIN+"adminhtml/default/default/images/ico_success.gif";
    		result += "<img class=\"link\" src=\""+URL_TYPE_SKIN+"adminhtml/default/default/images/grid-cal.gif"+"\" class=\"v-middle\" title=\""+this.getDescription()+"\" alt=\""+this.getDescription()+"\" id=\"cronCombo-"+this.getCode()+"_trig\" />";
    		result += buttonAdd;
    		result += "<script type=\"text/javascript\"> "+
                    " Calendar.setup({"+
                    "    inputField : 'cronCombo-"+this.getCode()+"',"+
                    "    ifFormat : '"+DATE_FORMAT+"',"+
                    "    button : 'cronCombo-"+this.getCode()+"_trig',"+
                    "    align : 'Bl',"+
                    "    singleClick : true"+
                    "});"+
                    "</script>";
    	break;
    	case 'set':
    	case 'select':
    		result = "<select id=\"cronCombo-"+this.getCode()+"\" name=\"cronCombo-"+this.getCode()+"\" title=\""+this.getDescription()+"\" class=\"select required-entry\" onclick=\"reportManager.getValuesCombo(this)\">";
    		result += "</select> ";
    		result += buttonAdd;
    		break;
    	case 'text':
    		result = " <input class=\"input-text no-changes required-entry\" type=\"text\" id=\"cronCombo-"+this.getCode()+"\" name=\"cronCombo-"+this.getCode()+"\" value=\""+this.getValue()+"\" > ";
    		result += buttonAdd;
    		
    		break;
	  }
	 
	  return result;
  }
};

var Report = Class.create();
Report.prototype = {
  initialize: function(code,description,group) {
	  this.code = code;
	  this.description = description;
	  this.group=group;
  },
  getCode: function(){
	  return this.code;
  },
  getDescription: function(){
	  return this.description;
  },
  getGroup: function(){
	  return this.group;
  },
  setDescription: function(description){
	  this.description = description;
  },
  setGroup: function(group){
	  this.group=group;
  }
  
};

var Group = Class.create();
Group.prototype = {
	  initialize: function(code,description,orden) {
		  this.code = code;
		  this.description = description;
		  this.orden=orden;
	  },
	  getCode: function(){
		  return this.code;
	  },
	  getDescription: function(){
		  return this.description;
	  },
	  getOrden: function(){
		  return this.orden;
	  },
	  setDescription: function(description){
		  this.description = description;
	  },
	  setOrden: function(orden){
		  this.orden=orden;
	  }
};

var Link = Class.create();
Link.prototype = {
  initialize: function(id,description,url,type) {
	  this.id = id;
	  this.description = description;
	  this.url = url;
	  this.type=type;
  },
  getId: function(){
	  return this.id;
  },
  getDescription: function(){
	  return this.description;
  },
  getUrl: function(){
	  return this.url;
  },
  getType: function(){
	  return this.type;
  },
  setDescription: function(description){
	  this.description = description;
  },
  setUrl: function(url){
	  this.url=url;
  },
  setType: function(type){
	  this.type=type;
  }
};

function htmlspecialchars(string, quote_style, double_encode) {
  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined' || quote_style === null) {
    quote_style = 2;
  }
  string = string.toString();
  if (double_encode !== false) {
    // Put this first to avoid double-encoding
    string = string.replace(/&/g, '&amp;');
  }
  string = string.replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

  var OPTS = {
    'ENT_NOQUOTES': 0,
    'ENT_HTML_QUOTE_SINGLE': 1,
    'ENT_HTML_QUOTE_DOUBLE': 2,
    'ENT_COMPAT': 2,
    'ENT_QUOTES': 3,
    'ENT_IGNORE': 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') {
    // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i = 0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      } else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/'/g, '&#039;');
  }
  if (!noquotes) {
    string = string.replace(/"/g, '&quot;');
  }

  return string;
}

function htmlspecialchars_decode(string, quote_style) {

  var optTemp = 0,
    i = 0,
    noquotes = false;
  if (typeof quote_style === 'undefined') {
    quote_style = 2;
  }
  string = string.toString()
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
  var OPTS = {
    'ENT_NOQUOTES': 0,
    'ENT_HTML_QUOTE_SINGLE': 1,
    'ENT_HTML_QUOTE_DOUBLE': 2,
    'ENT_COMPAT': 2,
    'ENT_QUOTES': 3,
    'ENT_IGNORE': 4
  };
  if (quote_style === 0) {
    noquotes = true;
  }
  if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
    quote_style = [].concat(quote_style);
    for (i = 0; i < quote_style.length; i++) {
      // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
      if (OPTS[quote_style[i]] === 0) {
        noquotes = true;
      } else if (OPTS[quote_style[i]]) {
        optTemp = optTemp | OPTS[quote_style[i]];
      }
    }
    quote_style = optTemp;
  }
  if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
    string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should
    // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
  }
  if (!noquotes) {
    string = string.replace(/&quot;/g, '"');
  }
  // Put this in last place to avoid escape being double-decoded
  string = string.replace(/&amp;/g, '&');

  return string;
}
