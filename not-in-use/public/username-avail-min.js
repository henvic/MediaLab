var clearusernamelinkcache=function(){document.getElementById("usernamelinkpreview").innerHTML="Your new URL: http://www.plifk.com/<b>&lt;username&gt;</b>/";document.getElementById("submit").disabled=false};var valusernameCallback={success:function(c){if(c.responseText!==undefined){if(c.responseText!==""){var a=c.responseXML.documentElement;var b=document.getElementById("newusername").value;if(a.getElementsByTagName("username")[0]){document.getElementById("usernamelinkpreview").innerHTML='Username <b class="warning">'+encodeURIComponent(b)+"</b> is already taken. Try another one.";document.getElementById("submit").disabled=true}else{document.getElementById("usernamelinkpreview").innerHTML='Your new URL: http://www.plifk.com/<b class="ok">'+encodeURIComponent(b)+"</b>/ (available!)"}}}},failure:function(a){clearusernamelinkcache()}};var availusernameEvent=function(b){if(document.getElementById("newusername")){var a=document.getElementById("newusername").value;YAHOO.util.Connect.asyncRequest("POST","/proxy/api/?method=/people/info&username="+encodeURIComponent(a),valusernameCallback)}else{clearusernamelinkcache()}};var usernamelinkEvent=function(c){var a=document.getElementById("newusername").value.toLowerCase();if(!a){clearusernamelinkcache()}else{if(a.length>15){document.getElementById("usernamelinkpreview").innerHTML='Keep your username less than <span class="warning"><b>15</b> characters.</span>';document.getElementById("submit").disabled=true}else{var b=/^[0-9A-Za-z_\-]+$/;if(!b.test(a)){document.getElementById("usernamelinkpreview").innerHTML='<span class="warning">This username is invalid.</span> You can only use <b>a-z</b>, <b>0-9</b>, <b>_</b> and <b>-</b> for your username.';document.getElementById("submit").disabled=true}else{document.getElementById("usernamelinkpreview").innerHTML="Your URL: http://www.plifk.com/<b>"+encodeURIComponent(a)+"</b>/";document.getElementById("submit").disabled=false}}}};YAHOO.util.Event.on(window,"load",availusernameEvent);YAHOO.util.Event.addListener("newusername","change",availusernameEvent);YAHOO.util.Event.addListener("newusername","keyup",usernamelinkEvent);