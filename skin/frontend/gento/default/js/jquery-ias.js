/*!
 * Infinite Ajax Scroll, a jQuery plugin
 * Version 1.0.2
 * https://github.com/webcreate/infinite-ajax-scroll
 *
 * Copyright (c) 2011-2013 Jeroen Fiege
 * Licensed under MIT:
 * https://raw.github.com/webcreate/infinite-ajax-scroll/master/MIT-LICENSE.txt
 */
!function(a){"use strict";Date.now=Date.now||function(){return+new Date},a.ias=function(b){function h(){var b;if(e.onChangePage(function(a,b,d){f&&f.setPage(a,d),c.onPageChange.call(this,a,d,b)}),c.triggerPageThreshold>0)i();else if(a(c.next).attr("href")){var h=d.getCurrentScrollOffset(c.scrollContainer);v(function(){n(h)})}return f&&f.havePage()&&(k(),b=f.getPage(),d.forceScrollTop(function(){var c;b>1?(p(b),c=m(!0),a("html, body").scrollTop(c)):i()})),g}function i(){l(),c.scrollContainer.scroll(j)}function j(){var a,b;a=d.getCurrentScrollOffset(c.scrollContainer),b=m(),a>=b&&(q()>=c.triggerPageThreshold?(k(),v(function(){n(a)})):n(a))}function k(){c.scrollContainer.unbind("scroll",j)}function l(){a(c.pagination).hide()}function m(b){var d,e;return d=a(c.container).find(c.item).last(),0===d.size()?0:(e=d.offset().top+d.height(),b||(e+=c.thresholdMargin),e)}function n(b,d){var f;return(f=a(c.next).attr("href"))?(c.beforePageChange&&a.isFunction(c.beforePageChange)&&c.beforePageChange(b,f)===!1||(e.pushPages(b,f),k(),s(),o(f,function(b,e){var h,g=c.onLoadItems.call(this,e);g!==!1&&(a(e).hide(),h=a(c.container).find(c.item).last(),h.after(e),a(e).fadeIn()),f=a(c.next,b).attr("href"),a(c.pagination).replaceWith(a(c.pagination,b)),t(),l(),f?i():(c.noneleft&&a(c.container).find(c.item).last().after(c.noneleft),k()),c.onRenderComplete.call(this,e),d&&d.call(this)})),void 0):k()}function o(b,d,e){var g,i,j,f=[],h=Date.now();e=e||c.loaderDelay,a.get(b,null,function(b){g=a(c.container,b).eq(0),0===g.length&&(g=a(b).filter(c.container).eq(0)),g&&g.find(c.item).each(function(){f.push(this)}),d&&(j=this,i=Date.now()-h,e>i?setTimeout(function(){d.call(j,b,f)},e-i):d.call(j,b,f))},"html")}function p(b){var c=m(!0);c>0&&n(c,function(){k(),e.getCurPageNum(c)+1<b?(p(b),a("html,body").animate({scrollTop:c},400,"swing")):(a("html,body").animate({scrollTop:c},1e3,"swing"),i())})}function q(){var a=d.getCurrentScrollOffset(c.scrollContainer);return e.getCurPageNum(a)}function r(){var b=a(".ias_loader");return 0===b.size()&&(b=a('<div class="ias_loader">'+c.loader+"</div>"),b.hide()),b}function s(){var d,b=r();c.customLoaderProc!==!1?c.customLoaderProc(b):(d=a(c.container).find(c.item).last(),d.after(b),b.fadeIn())}function t(){var a=r();a.remove()}function u(b){var d=a(".ias_trigger");return 0===d.size()&&(d=a('<div class="ias_trigger"><a href="#">'+c.trigger+"</a></div>"),d.hide()),a("a",d).unbind("click").bind("click",function(){return w(),b.call(),!1}),d}function v(b){var e,d=u(b);c.customTriggerProc!==!1?c.customTriggerProc(d):(e=a(c.container).find(c.item).last(),e.after(d),d.fadeIn())}function w(){var a=u();a.remove()}var c=a.extend({},a.ias.defaults,b),d=new a.ias.util,e=new a.ias.paging(c.scrollContainer),f=c.history?new a.ias.history:!1,g=this;h()},a.ias.defaults={container:"#container",scrollContainer:a(window),item:".item",pagination:"#pagination",next:".next",noneleft:!1,loader:'<img src="images/loader.gif"/>',loaderDelay:10,triggerPageThreshold:20,trigger:"Load more items",thresholdMargin:0,history:!0,onPageChange:function(){},beforePageChange:function(){},onLoadItems:function(){},onRenderComplete:function(){},customLoaderProc:!1,customTriggerProc:!1},a.ias.util=function(){function e(){a(window).load(function(){b=!0})}var b=!1,c=!1,d=this;e(),this.forceScrollTop=function(e){a("html,body").scrollTop(0),c||(b?(e.call(),c=!0):setTimeout(function(){d.forceScrollTop(e)},1))},this.getCurrentScrollOffset=function(a){var b,c;return b=a.get(0)===window?a.scrollTop():a.offset().top,c=a.height(),b+c}},a.ias.paging=function(){function f(){a(window).scroll(g)}function g(){var b,f,g,j,k;b=e.getCurrentScrollOffset(a(window)),f=h(b),g=i(b),d!==f&&(j=g[0],k=g[1],c.call({},f,j,k)),d=f}function h(a){for(var c=b.length-1;c>0;c--)if(a>b[c][0])return c+1;return 1}function i(a){for(var c=b.length-1;c>=0;c--)if(a>b[c][0])return b[c];return null}var b=[[0,document.location.toString()]],c=function(){},d=1,e=new a.ias.util;f(),this.getCurPageNum=function(b){return b=b||e.getCurrentScrollOffset(a(window)),h(b)},this.onChangePage=function(a){c=a},this.pushPages=function(a,c){b.push([a,c])}},a.ias.history=function(){function c(){b=!!(window.history&&history.pushState&&history.replaceState),b=!1}var a=!1,b=!1;c(),this.setPage=function(a,b){this.updateState({page:a},"",b)},this.havePage=function(){return this.getState()!==!1},this.getPage=function(){var a;return this.havePage()?(a=this.getState(),a.page):1},this.getState=function(){var a,c,d;if(b){if(c=history.state,c&&c.ias)return c.ias}else if(a="#/page/"===window.location.hash.substring(0,7))return d=parseInt(window.location.hash.replace("#/page/",""),10),{page:d};return!1},this.updateState=function(b,c,d){a?this.replaceState(b,c,d):this.pushState(b,c,d)},this.pushState=function(c,d,e){var f;b?history.pushState({ias:c},d,e):(f=c.page>0?"#/page/"+c.page:"",window.location.hash=f),a=!0},this.replaceState=function(a,c,d){b?history.replaceState({ias:a},c,d):this.pushState(a,c,d)}}}(jQuery);