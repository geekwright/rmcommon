function loadUpdates(){$.get("updates.php",{action:"ajax-updates"},function(t){void 0!=t.token&&$("#cu-token").val(t.token),$("#rmc-updates").append(t),$(".rm-loading").fadeOut("fast"),$("#rmc-updates > .upd-item").each(function(){$(this).fadeIn("fast")})},"html")}function rmCheckUpdates(){$.get(xoUrl+"/modules/rmcommon/ajax/updates.php",{XOOPS_TOKEN_REQUEST:$("#cu-token").val()},function(t){return""!=t.token&&$("#cu-token").val(t.token),t.total<=0?!1:void rmCallNotifier(t.total)},"json")}function rmCallNotifier(t){if(!(0>=t)){if("function"==typeof updatesNotifier&&updatesNotifier(t),$("#updater-info").length<=0)return!1;$("#updater-info").html($("#updater-info").html().replace("%s",t)),$("#updater-info").fadeIn("fast")}}function loadUpdateDetails(id){if(null==id||void 0==id)return!1;var updates=eval($("#json-container").html()),update=updates[id].data;if(""==update.url)return!1;if(null==update.url.match(/^http:\/\//))return!1;var url=update.url.replace(/\&amp;/,"&");$("#details").html(""),$("#files").html(""),$("#upd-info .tab-container").addClass("loading"),$("#upd-info").modal(),$.get("updates.php",{action:"update-details",url:url},function(t){1==t.error&&(alert(t.message),""!=t.token&&$("#cu-token").val(t.token),$("#upd-info-blocker").click()),""!=t.token&&$("#cu-token").val(t.token),$("#details").html(t.data.details),$("#files").html(t.data.files),$("#upd-info .tab-container").removeClass("loading")},"json")}function installUpdate(id){if(null==id||void 0==id)return!1;var updates=eval($("#json-container").html()),update=updates[id].data;if(null==update.url.match(/^http:\/\//))return!1;var url=update.url.replace(/\&amp;/,"&");return $("#upd-warning .continue-update").attr("data-id",id),""!=update.warning&&void 0==warns[id]?void showWarning(update):1==update.login&&void 0==credentials[id]?($("#upd-login .ok-login").attr("data-id",id),void showLogin(update)):($("#upd-"+id+" .col-lg-4").hide(),$("#upd-"+id+" .col-lg-8").removeClass("col-lg-8").addClass("col-lg-12"),$("#upd-"+id).addClass("upd-item-process"),$("#upd-"+id+" .upd-progress").slideDown("fast"),void updateStepOne(update,id))}function downloadUpdate(id){if(null==id||void 0==id)return!1;var updates=eval($("#json-container").html()),update=updates[id].data;if(null==update.url.match(/^http:\/\//))return!1;var url=update.url.replace(/\&amp;/,"&");if(1==update.login&&void 0==credentials[id])return $("#upd-login .ok-login").attr("data-id",id),$("#upd-login").data("next","download"),void showLogin(update);var params={action:"later",url:url,credentials:void 0==credentials[id]?"":credentials[id],type:update.type,dir:update.dir};$.post("updates.php",params,function(t){return 1==t.error?(alert(t.message),void(""!=t.token&&$("#cu-token").val(t.token))):(""!=t.token&&$("#cu-token").val(t.token),$("#upd-"+id+" .button-later > i").removeClass("icon-spinner icon-spin").addClass("icon-time"),void(window.location.href="updates.php?action=getfile&file="+t.data.file))},"json")}function installLater(id){if(null==id||void 0==id)return!1;$("#upd-"+id+" .button-later > i").removeClass("icon-time").addClass("icon-spinner icon-spin");var updates=eval($("#json-container").html()),update=updates[id].data;if(null==update.url.match(/^http:\/\//))return!1;var url=update.url.replace(/\&amp;/,"&")+"&action=download";return 1==update.login&&void 0==credentials[id]?($("#upd-login .ok-login").attr("data-id",id),$("#upd-login").data("next","download"),void showLogin(update)):void downloadUpdate(id)}function showWarning(t){$("#upd-info-blocker").fadeIn("fast"),$("#upd-warning h4").html(t.title),$("#upd-warning p").html(t.warning),$("#upd-warning").fadeIn("fast")}function showLogin(update){$("#login-blocker").fadeIn("fast",function(){var updates=eval($("#json-container").html()),id=$("#upd-login .ok-login").data("id"),update=updates[id].data,a=document.createElement("a");a.href=update.url,$("#upd-login").fadeIn("fast"),$("#upd-login p").html($("#upd-login p").html().replace("%site%",'<a href="http://'+a.hostname+'" target="_blank">'+a.hostname+"</a>"))})}function updateStepOne(t,e){var n=t.url.replace(/\&amp;/,"&"),a={action:"first-step",url:n,credentials:void 0==credentials[e]?"":credentials[e],type:t.type,dir:t.dir,ftp:$("#ftp-form").serialize(),XOOPS_TOKEN_REQUEST:$("#cu-token").val()};incrementProgress("50%",e),$.post("updates.php",a,function(n){return 1==n.error?($("#upd-"+e+" .upd-progress .status").html(n.message),$("#upd-"+e+" .progress-bar").addClass("progress-bar-danger"),$("#upd-"+e+" .progress").removeClass("active"),""!=n.token&&$("#cu-token").val(n.token),!1):(""!=n.token&&$("#cu-token").val(n.token),$("#upd-"+e+" .upd-progress .status").html(n.message),void("module"==t.type||"plugin"==t.type?(incrementProgress("80%",e),local_update(e)):(incrementProgress("100%",e),$("#upd-"+e+" .progress-bar").addClass("progress-bar-success"),$("#upd-"+e+" .progress").removeClass("active"),$("#upd-"+e+" h4").addClass("update-done"))))},"json")}function local_update(id){var updates=eval($("#json-container").html()),update=updates[id].data,params={action:"local-update",type:update.type,module:update.dir,XOOPS_TOKEN_REQUEST:$("#cu-token").val()};$.post("updates.php",params,function(t){return 1==t.error?($("#upd-"+id+" .upd-progress .status").html(t.message),$("#upd-"+id+" .progress-bar").addClass("progress-bar-danger"),$("#upd-"+id+" .progress").removeClass("active"),""!=t.token&&$("#cu-token").val(t.token),!1):(""!=t.token&&$("#cu-token").val(t.token),cuHandler.modal.dialog({message:t.data.log,title:"Module update log",width:"large"}),$("#upd-"+id+" .upd-progress .status").html(t.message),incrementProgress("100%",id),$("#upd-"+id+" .progress-bar").addClass("progress-bar-success"),$("#upd-"+id+" .progress").removeClass("active"),void $("#upd-"+id+" h4").addClass("update-done"))},"json")}function runFiles(id,run){var files=eval(run),total=files.length-1,start=0;$("#files-blocker").fadeIn("fast",function(){$("#upd-run").fadeIn("fast",function(){$("#upd-run > iframe").attr("src",files[start]).load(function(){total>start?(start++,$(this).attr("src",files[start])):$("#upd-run").fadeOut("fast",function(){$("#files-blocker").fadeOut("fast",function(){$("#upd-"+id+" .upd-progress .status").html(langUpdated),incrementProgress("100%",id),$("#upd-"+id+" .progress").addClass("progress-bar-success").removeClass("active"),$("#upd-"+id+" h4").addClass("update-done")})})})})})}function incrementProgress(t,e){$("#upd-"+e+" .progress > .progress-bar").width(t)}var warns=new Array,credentials=new Array;$(document).ready(function(){$("#details").length>0&&$("#files").length>0&&loadUpdates(),$("#upds-ftp, #ftp-settings .btn-primary").click(function(){$("#ftp-settings").slideToggle("fast"),$("#upds-ftp").hasClass("active")?$("#upds-ftp").removeClass("active"):$("#upds-ftp").addClass("active")}),$("#refresh-updates").click(function(){$(this).children("span").addClass("fa-spin"),$(".rm-loading").fadeIn("fast"),$("#rmc-updates > div").each(function(){$(this).fadeOut("fast",function(){$(this).remove()})}),$.get(xoUrl+"/modules/rmcommon/ajax/updates.php",{XOOPS_TOKEN_REQUEST:$("#cu-token").val()},function(t){loadUpdates(),$("#refresh-updates > span").removeClass("fa-spin")},"json")}),$("#upd-warning .cancel-warning").click(function(){$("#upd-warning").fadeOut("fast",function(){$("#upd-info-blocker").fadeOut("fast")})}),$("#upd-warning .continue-update").click(function(){$("#upd-warning .cancel-warning").click();var t=$(this).attr("data-id");void 0==t||0>t||(warns[t]=1,installUpdate(t))}),$("#upd-login .cancel-login, #upd-login .close").click(function(){$("#upd-login").fadeOut("fast",function(){$("#login-blocker").fadeOut("fast"),$("#upd-login input").val("")})}),$("#upd-login .ok-login").click(function(){if(""==$("#uname").val())return void $("#uname").addClass("error").focus();if(""==$("#upass").val())return void $("#upass").addClass("error").focus();$("#upd-login .cancel-login").click();var t=$(this).attr("data-id");void 0==t||0>t||(credentials[t]=$("#uname").val()+":"+$("#upass").val(),"download"==$("#upd-login").data("next")?downloadUpdate(t):installUpdate(t))})});
