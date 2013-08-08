tinymce.ThemeManager.add("modern",function(e){function t(){function t(t){var i,o=[];if(t)return d(t.split(/[ ,]/),function(t){function n(){var n=e.selection;"bullist"==r&&n.selectorChanged("ul > li",function(e,n){for(var i,o=n.parents.length;o--&&(i=n.parents[o].nodeName,"OL"!=i&&"UL"!=i););t.active("UL"==i)}),"numlist"==r&&n.selectorChanged("ol > li",function(e,n){for(var i,o=n.parents.length;o--&&(i=n.parents[o].nodeName,"OL"!=i&&"UL"!=i););t.active("OL"==i)}),t.settings.stateSelector&&n.selectorChanged(t.settings.stateSelector,function(e){t.active(e)},!0),t.settings.disabledStateSelector&&n.selectorChanged(t.settings.disabledStateSelector,function(e){t.disabled(e)})}var r;"|"==t?i=null:c.has(t)?(t={type:t},u.toolbar_items_size&&(t.size=u.toolbar_items_size),o.push(t),i=null):(i||(i={type:"buttongroup",items:[]},o.push(i)),e.buttons[t]&&(r=t,t=e.buttons[r],t.type=t.type||"button",u.toolbar_items_size&&(t.size=u.toolbar_items_size),t=c.create(t),i.items.push(t),e.initialized?n():e.on("init",n)))}),n.push({type:"toolbar",layout:"flow",items:o}),!0}for(var n=[],i=1;10>i&&t(u["toolbar"+i]);i++);return n.length||t(u.toolbar||f),n}function n(){function t(t){var n;return"|"==t?{text:"|"}:n=e.menuItems[t]}function n(n){var i,o,r,a,s;if(s=tinymce.makeMap((u.removed_menuitems||"").split(/[ ,]/)),u.menu?(o=u.menu[n],a=!0):o=h[n],o){i={text:o.title},r=[],d((o.items||"").split(/[ ,]/),function(e){var n=t(e);n&&!s[e]&&r.push(t(e))}),a||d(e.menuItems,function(e){e.context==n&&("before"==e.separator&&r.push({text:"|"}),e.prependToContext?r.unshift(e):r.push(e),"after"==e.separator&&r.push({text:"|"}))});for(var l=0;l<r.length;l++)"|"==r[l].text&&(0===l||l==r.length-1)&&r.splice(l,1);if(i.menu=r,!i.menu.length)return null}return i}var i,o=[],r=[];if(u.menu)for(i in u.menu)r.push(i);else for(i in h)r.push(i);for(var a=u.menubar?u.menubar.split(/[ ,]/):r,s=0;s<a.length;s++){var l=a[s];l=n(l),l&&o.push(l)}return o}function i(t){function n(e){var n=t.find(e)[0];n&&n.focus()}e.shortcuts.add("Alt+F9","",function(){n("menubar")}),e.shortcuts.add("Alt+F10","",function(){n("toolbar")}),e.shortcuts.add("Alt+F11","",function(){n("elementpath")}),t.on("cancel",function(){e.focus()})}function o(t,n){function i(e){return{width:e.clientWidth,height:e.clientHeight}}var o,r,a,s;o=e.getContainer(),r=e.getContentAreaContainer().firstChild,a=i(o),s=i(r),t=Math.max(u.min_width||100,t),n=Math.max(u.min_height||100,n),t=Math.min(u.max_width||65535,t),n=Math.min(u.max_height||65535,n),m.css(o,"width",t+(a.width-s.width)),m.css(r,"width",t),m.css(r,"height",n),e.fire("ResizeEditor")}function r(t,n){var i=e.getContentAreaContainer();l.resizeTo(i.clientWidth+t,i.clientHeight+n)}function a(){function o(){d&&d.moveRel&&d.visible()&&!d._fixed&&d.moveRel(e.getBody(),["tl-bl","bl-tl"])}function r(){d&&(d.show(),o(),m.addClass(e.getBody(),"mce-edit-focus"))}function a(){d&&(d.hide(),m.removeClass(e.getBody(),"mce-edit-focus"))}function s(){return d?(d.visible()||r(),void 0):(d=l.panel=c.create({type:h?"panel":"floatpanel",classes:"tinymce tinymce-inline",layout:"flex",direction:"column",autohide:!1,autofix:!0,fixed:!!h,border:1,items:[u.menubar===!1?null:{type:"menubar",border:"0 0 1 0",items:n()},u.toolbar===!1?null:{type:"panel",name:"toolbar",layout:"stack",items:t()}]}),d.renderTo(h||document.body).reflow(),i(d),r(),e.on("nodeChange",o),e.on("activate",r),e.on("deactivate",a),void 0)}var d,h;return u.fixed_toolbar_container&&(h=m.select(u.fixed_toolbar_container)[0]),u.content_editable=!0,e.on("focus",s),e.on("blur",a),e.on("remove",function(){d&&(d.remove(),d=null)}),{}}function s(r){var a,s,d;return a=l.panel=c.create({type:"panel",classes:"tinymce",style:"visibility: hidden",layout:"stack",border:1,items:[u.menubar===!1?null:{type:"menubar",border:"0 0 1 0",items:n()},u.toolbar===!1?null:{type:"panel",layout:"stack",items:t()},{type:"panel",name:"iframe",layout:"stack",classes:"edit-area",html:"",border:"1 0 0 0"}]}),u.resize!==!1&&(s={type:"resizehandle",direction:u.resize,onResizeStart:function(){var t=e.getContentAreaContainer().firstChild;d={width:t.clientWidth,height:t.clientHeight}},onResize:function(e){o(d.width+e.deltaX,d.height+e.deltaY)}}),u.statusbar!==!1&&a.add({type:"panel",name:"statusbar",classes:"statusbar",layout:"flow",border:"1 0 0 0",items:[{type:"elementpath"},s]}),a.renderBefore(r.targetNode).reflow(),u.width&&tinymce.DOM.setStyle(a.getEl(),"width",u.width),e.on("remove",function(){a.remove(),a=null}),i(a),{iframeContainer:a.find("#iframe")[0].getEl(),editorContainer:a.getEl()}}var l=this,u=e.settings,c=tinymce.ui.Factory,d=tinymce.each,m=tinymce.DOM,h={file:{title:"File",items:"newdocument"},edit:{title:"Edit",items:"undo redo | cut copy paste pastetext | selectall"},insert:{title:"Insert",items:"|"},view:{title:"View",items:"visualaid |"},format:{title:"Format",items:"bold italic underline strikethrough superscript subscript | formats | removeformat"},table:{title:"Table"},tools:{title:"Tools"}},f="undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image";l.renderUI=function(t){var n=u.skin!==!1?u.skin||"lightgray":!1;return n&&(tinymce.Env.documentMode<=7?tinymce.DOM.loadCSS(tinymce.baseURL+"/skins/"+n+"/skin.ie7.min.css"):tinymce.DOM.loadCSS(tinymce.baseURL+"/skins/"+n+"/skin.min.css"),e.contentCSS.push(tinymce.baseURL+"/skins/"+n+"/content"+(e.inline?".inline":"")+".min.css")),e.on("ProgressState",function(e){l.throbber=l.throbber||new tinymce.ui.Throbber(l.panel.getEl("body")),e.state?l.throbber.show(e.time):l.throbber.hide()}),u.inline?a(t):s(t)},l.resizeTo=o,l.resizeBy=r});