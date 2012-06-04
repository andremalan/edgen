/* To edit use source file: js/source/onLoad.js  */

jQuery(document).ready(function($){if(fadeContent!=="none"&&!oldIE){elems=(fadeContent==="all")?"#Top, #Middle, #Bottom":"#Middle";hiddenClass=(fadeContent==="all")?"invisibleAll":"invisibleMiddle";if(fadeContent==="all"||fadeContent==="content"){$("#Wrapper").children(elems).css("opacity","0").end().parent("body").removeClass(hiddenClass);setTimeout(function(){$("#Middle").animate({"opacity":"1"},250);},100);if(fadeContent==="all"){setTimeout(function(){$("#Top").animate({"opacity":"1"},200);},0);setTimeout(function(){$("#Bottom").animate({"opacity":"1"},300);},200);}}}$(window).bind("resize",function(){var browserHeight=$(window).height();var wrapperHeight=$("#Wrapper").outerHeight()+$("#Wrapper").offset().top;if(browserHeight>wrapperHeight){$middle=$("#Middle .contentMargin");contentHeight=$middle.height();minHeight=contentHeight+(browserHeight-wrapperHeight);$middle.css("min-height",minHeight+"px");}});$(window).trigger("resize");lightboxOpacity=0.8;$(".gallery .gallery-item a").attr("rel","gallery");$("a.popup[href*='http://www.youtube.com/watch?']").colorbox({href:function(){var id=/[\?\&]v=([^\?\&]+)/.exec(this.href);url="http://www.youtube.com/embed/"+id[1];if(!id[1]){url=this.href;}return url;},iframe:true,innerWidth:function(){w=$.getUrlVars(this.href)["width"]||640;return w;},innerHeight:function(){h=$.getUrlVars(this.href)["height"]||390;return h;},opacity:lightboxOpacity});$("a.popup[href*='http://www.vimeo.com/'], a.popup[href*='http://vimeo.com/']").colorbox({href:function(){var id=/vimeo\.com\/(\d+)/.exec(this.href);url="http://www.vimeo.com/moogaloop.swf?clip_id="+id[1];if(!id[1]){url=this.href;}return url;},iframe:true,innerWidth:function(){w=$.getUrlVars(this.href)["width"]||640;return w;},innerHeight:function(){h=$.getUrlVars(this.href)["height"]||360;return h;},opacity:lightboxOpacity});$("a[href$='.jpg'],a[href$='.jpeg'],a[href$='.png'],a[href$='.gif'],a[href$='.tif'],a[href$='.tiff'],a[href$='.bmp']").colorbox({maxWidth:"90%",maxHeight:"90%",opacity:lightboxOpacity});$("a.popup[href$='#LoginPopup'], .popup > a[href$='#LoginPopup']").each(function(){this.href=this.hash;});$("a.popup[href^='#'], .popup > a[href^='#']").colorbox({maxWidth:"90%",maxHeight:"90%",inline:true,href:this.href,opacity:lightboxOpacity}).removeClass("popup");$(".popup > a[href^='#']").parent().removeClass("popup");$(".popup").colorbox({maxWidth:"90%",maxHeight:"90%",opacity:lightboxOpacity});$("a[href$='#popup']").colorbox({maxWidth:"90%",maxHeight:"90%",href:function(){if(this.href){return this.href.replace("#popup","");}},opacity:lightboxOpacity});$(".iframe").colorbox({width:"80%",height:"80%",iframe:true});$("a[href$='#iframe']").colorbox({width:"80%",height:"80%",iframe:true,href:function(){if(this.href){return this.href.replace("#iframe","");}},opacity:lightboxOpacity});if($(".portfolio-list").length>0){var pGroup=$(".portfolio-list");pGroup.each(function(){var h=0;pItems=jQuery(this).find(".item-container");pItems.each(function(i,val){if(jQuery(this).height()>h){h=jQuery(this).height();}});pItems.css("height",h+"px");});}var $buttonElement=$('input[type="submit"]:not(.noStyle), input[type="button"]:not(.noStyle), input[type="reset"]:not(.noStyle), button:not(.noStyle)');$($buttonElement).addClass("btn");if((BrowserDetect.browser=="Safari"&&BrowserDetect.OS=="Windows")||BrowserDetect.browser=="Opera"){$(".btn").addClass("safariBtn");}BpWidgetHover();$(".messageBox .closeBox").click(function(){jQuery(this).parent(".messageBox").fadeTo(400,0.001).slideUp();});if(jQuery(".tabList").length>0){$(".tabList").sTabs();}if(jQuery(".toggleItem").length>0){$(".toggleItem").simpleToggle();}if(jQuery(".section-tabs").length>0){$(".section-tabs").simpleSlideTop();}switch(toolTips){case"all links":$tipItems=$("a[title]");break;case"class":$tipItems=$(".tip");break;default:$tipItems=false;}if($tipItems.length>0){$tipItems.qtip({position:{my:"bottom center",at:"top center",adjust:{y:-3}}});microBlogTips();}if(typeof jq=="function"&&typeof Cufon=="function"){jq("#BP-Content").ajaxComplete(function(evt,request,settings){setTimeout(function(){Cufon.refresh();},115);});}if(typeof jq=="function"){jq(".widget_bp_groups_widget, .widget_bp_core_members_widget").ajaxComplete(function(evt,request,settings){setTimeout(function(){BpWidgetHover();if(typeof Cufon=="function"){Cufon.refresh();}},215);});}});function BpWidgetHover(){elements=".widget_bp_groups_widget, .widget_bp_core_members_widget";jQuery(elements).find("li:not(.hoverable)").hover(function(){jQuery(this).addClass("hover");if(typeof Cufon=="function"){Cufon.refresh();}},function(){jQuery(this).removeClass("hover");if(typeof Cufon=="function"){Cufon.refresh();}}).click(function(){url=jQuery(this).find(".item-title a").attr("href");if(url){document.location=url;}}).addClass("hoverable");}jQuery.extend({getUrlVars:function(url){var vars=[],hash;if(!url){url=window.location.href;}var hashes=url.slice(window.location.href.indexOf("?")+1).split("&");for(var i=0;i<hashes.length;i++){hash=hashes[i].split("=");vars.push(hash[0]);vars[hash[0]]=hash[1];}return vars;},getUrlVar:function(name,url){if(!url){url=window.location.href;}return jQuery.getUrlVars(url)[name];}});var oldIE=false;var BrowserDetect={init:function(){this.browser=this.searchString(this.dataBrowser)||"An unknown browser";this.version=this.searchVersion(navigator.userAgent)||this.searchVersion(navigator.appVersion)||"an unknown version";this.OS=this.searchString(this.dataOS)||"an unknown OS";},searchString:function(data){for(var i=0;i<data.length;i++){var dataString=data[i].string;var dataProp=data[i].prop;this.versionSearchString=data[i].versionSearch||data[i].identity;if(dataString){if(dataString.indexOf(data[i].subString)!=-1){return data[i].identity;}}else{if(dataProp){return data[i].identity;}}}},searchVersion:function(dataString){var index=dataString.indexOf(this.versionSearchString);if(index==-1){return;}return parseFloat(dataString.substring(index+this.versionSearchString.length+1));},dataBrowser:[{string:navigator.vendor,subString:"Apple",identity:"Safari",versionSearch:"Version"},{string:navigator.userAgent,subString:"MSIE",identity:"Explorer",versionSearch:"MSIE"},{prop:window.opera,identity:"Opera"}],dataOS:[{string:navigator.platform,subString:"Win",identity:"Windows"}]};BrowserDetect.init();if(BrowserDetect.browser=="Explorer"&&BrowserDetect.version<9){oldIE=BrowserDetect.version;}(function($){$.fn.sTabs=function(opts){var options=$.extend({},$.fn.sTabs.defaults,opts);return this.each(function(){$(this).addClass("tabs");$(this).find("a").each(function(){$($(this).attr("href")).addClass("tab").hide();$(this).bind(options.eventType,function(e){e.preventDefault();$(this).addClass("active");options.animate?$($(this).attr("href")).fadeIn(options.duration):$($(this).attr("href")).show();$($(this).parent().siblings().find("a")).each(function(){$(this).removeClass("active");$($(this).attr("href")).hide();});});});var first=$(this).find("li:nth-child("+options.startWith+")").children("a");$(first).addClass("active");$($(first).attr("href")).show();});};$.fn.sTabs.defaults={animate:false,duration:300,startWith:1,eventType:"click"};})(jQuery);(function($){$.fn.simpleToggle=function(opts){var options=$.extend({},$.fn.simpleToggle.defaults,opts);return this.each(function(){$title=$(this).children(".togTitle");$title.each(function(){$(this).click(function(){$item=$(this);$item.next(".togDesc").slideToggle("fast",function(){$icon=$item.children(".iconSymbol");if($(this).css("display")=="block"){$icon.removeClass("plus").addClass("minus");}else{$icon.removeClass("minus").addClass("plus");}});});});});};$.fn.simpleToggle.defaults={};})(jQuery);(function($){$.fn.simpleSlideTop=function(opts){var options=$.extend({},$.fn.simpleSlideTop.defaults,opts);return this.each(function(){$container=$(this);$(this).find("a").each(function(){contentID=$(this).attr("href");$(contentID).hide();$(this).bind(options.eventType,function(e){e.preventDefault();if($(this).hasClass("active")){$($(this).parents("ul").find("a")).each(function(){$(this).removeClass("active");$($(this).attr("href")).slideUp(options.duration*0.65);});}else{$tab=$(this);hasActive=false;$container.find("a").each(function(){if($(this).hasClass("active")){hasActive=true;}});$($(this).parent().siblings().find("a")).each(function(){$(this).removeClass("active");$($(this).attr("href")).slideUp(options.duration*0.65);});if(hasActive){$($(this).attr("href")).delay(options.duration*0.65).slideDown(options.duration);}else{$($(this).attr("href")).slideDown(options.duration);}$tab.addClass("active");}return false;});});if(options.startOpen){var first=$(this).find("li:nth-child("+options.defaultTab+")").children("a");$(first).addClass("active");$($(first).attr("href")).show();}});};$.fn.simpleSlideTop.defaults={duration:300,defaultTab:1,startOpen:false,eventType:"click"};})(jQuery);function microBlogTips(){jQuery('a[href^="http://twitter.com"]').each(function(){var username=jQuery(this).attr("href").match(/^http:\/\/twitter\.com\/(\w+)\/?/);position={my:"bottom center",at:"top center",adjust:{y:-3}};if(username&&username.length>1){username=username[1];}else{return;}jQuery(this).qtip({content:{text:'<div class="microBlogTip-loading twitterTip">...</div>',ajax:{url:"http://api.twitter.com/1/statuses/user_timeline/"+username+".json?callback=?",data:{count:1},dataType:"jsonp",success:function(tweet){this.set("content.text",'<div class="microBlogTip-username">'+username+": </div> "+tweet[0].text);return false;}}},style:{classes:"microBlogTip twitterTip"},position:position});});jQuery('a[rel="buzz"]').each(function(){var username=jQuery(this).text();jQuery(this).qtip({content:{text:"Loading Buzz feed...",ajax:{url:"http://ajax.googleapis.com/ajax/services/feed/load",data:{q:"http://buzz.googleapis.com/feeds/"+username+"/public/posted",num:1,output:"json",v:"1.0"},dataType:"jsonp",success:function(json){try{var entries=json.responseData.feed.entries,snippet=entries[0].contentSnippet;snippet=snippet.replace(/\b(https?\:\/\/\S+)/gi,' <a href="$1">$1</a>');this.set("content.text",snippet);}catch(e){this.set("content.text","Error: "+json.responseDetails);}return false;}}},style:{classes:"microBlogTip buzzTip"}});});}if(fadeContent!=="none"&&!oldIE){setTimeout(function(){jQuery("body").removeClass("invisibleAll invisibleMiddle");},1000);}