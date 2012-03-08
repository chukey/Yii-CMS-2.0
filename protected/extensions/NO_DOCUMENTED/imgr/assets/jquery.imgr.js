/*
 * IMGr :: jQuery Image Rounder v1.0.1
 * http://steamdev.com/imgr
 *
 * Copyright 2010, SteamDev
 * Released under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 *
 * Date: Wed October 20, 2010
 */

(function($) {
	
	$.fn.imgr = function(settings) {
		
		var isWeb = $.browser.webkit;
		var isIE = $.browser.msie;
		var isIEOld = $.browser.msie && parseInt($.browser.version)<9;
		var isMoz = $.browser.mozilla;
		var isOp = $.browser.opera;
		
		var defaults = {
						radius:"10px",
						size:"0px",
						color:"#000",
						style:"solid"
						};
		
		if(settings){$.extend(defaults,settings);};
		
		defaults.style = defaults.style.toLowerCase();
		defaults.radius = defaults.radius.toString();
		defaults.size = parseInt(defaults.size)+"px";
		defaults.color = (colourNameToHex(defaults.color));
		if(defaults.color.indexOf('#')== -1){defaults.color = "#"+defaults.color;}
		
		var tl,tr,bl,br;
		var cor = (defaults.radius).split(" ");
		if(cor.length==1 || cor.length > 4){ tl = tr = bl = br = cor[0]; };
		if(cor.length==2){ tl = tr = cor[0]; bl = br = cor[1]; };
		if(cor.length==3){ tl = cor[0]; tr = cor[1]; bl = br = cor[2]; };
		if(cor.length==4){ tl = cor[0]; tr = cor[1]; br = cor[2]; bl = cor[3]; };
		tl=parseInt(tl);tr=parseInt(tr);bl=parseInt(bl);br=parseInt(br);
		

		var rad = tl+"px " + tr+"px " + br+"px " + bl+"px";
		
		return this.each(function(){
			var o = $(this);
			
			if(o[0].nodeName.toLowerCase()=="img"){
				
				var src = o.attr("src");
				var padt,padr,padb,padl,margt,margr,margb,margl,top,right,bottom,left,float,pos,offt,offr,offb,offl,_w,_h,_stroke,vDashStyle,vLineStyle;
				
				vDashStyle = "solid";
				vLineStyle = "single";
				if(defaults.style=="dotted"){vDashStyle="dot";}
				if(defaults.style=="dashed"){vDashStyle="dash";}
				if(defaults.style=="double"){vLineStyle="thinthin";}
				
				_w = parseInt(o.width());
				_h = parseInt(o.height());

				_stroke = parseInt(defaults.size);
				
				padt = parseInt(o.css('padding-top'));
				padr = parseInt(o.css('padding-right'));
				padb = parseInt(o.css('padding-bottom'));
				padl = parseInt(o.css('padding-left'));
				
				margt = o.css('margin-top'); margt = (margt=='auto')?0:parseInt(margt);
				margr = o.css('margin-right'); margr = (margr=='auto')?0:parseInt(margr);
				margb = o.css('margin-bottom'); margb = (margb=='auto')?0:parseInt(margb);
				margl = o.css('margin-left'); margl = (margl=='auto')?0:parseInt(margl);
				
				var posI = o.position();
				
				top = o.css('top');
				right = o.css('right');
				bottom = o.css('bottom');
				left = o.css('left');
				
				if(isOp){
				
					if(right!='auto'){
						if((parseInt(posI.left) + o.width()) == parseInt(right)){
							right="auto";	
						}
					}
					
					if(left!='auto'){
						if(parseInt(posI.left) == parseInt(left)){
							left="auto";	
						}
					}
					
					if(top!='auto'){
						if(parseInt(o[0].offsetTop) == parseInt(top)){
							top="auto";
						}
					}
					
					if(bottom!='auto'){
						if((parseInt(o[0].offsetTop) + o.height()) == parseInt(bottom)){
							bottom="auto";
						}
					}
				
				}

				float = o.css('float');
				pos = o.css('position');
				if (pos!="absolute"){pos="relative";}
				
				offt = (padt + margt)+"px ";
				offr = (padr + margr)+"px ";
				offb = (padb + margb)+"px ";
				offl = (padl + margl)+"px ";
				
				if(isIEOld){
					
					$(window).load(function(){	
											
						if(document.namespaces['v']==null) { 
							document.namespaces.add("v","urn:schemas-microsoft-com:vml");
							var vmlStyle = document.createStyleSheet();
							vmlStyle.addRule("v\\:shape","behavior: url(#default#VML);");
							vmlStyle.addRule("v\\:fill","behavior: url(#default#VML);");
							vmlStyle.addRule("v\\:stroke","behavior: url(#default#VML);");
						}
						
					});
					
					var _halfStroke = Math.round(_stroke/2);
					offt = (parseInt(offt)+_halfStroke)+"px ";
					offr = (parseInt(offr)+_halfStroke)+"px ";
					offb = (parseInt(offb)+_halfStroke)+"px ";
					offl = (parseInt(offl)+_halfStroke)+"px ";
					
					if(!o.parent().hasClass('imgr')){
						
						$(window).load(function(){
							
							var style = "display:inline-block;margin:"+offt + offr + offb + offl+";padding:0;top:"+top+";right:"+right+";bottom:"+bottom+";left:"+left+";float:"+float+";position:"+pos+";width:"+(_w+_stroke)+"px;height:"+(_h+_stroke)+"px;";
							o.css({'margin':'0','padding':'0','top':'0','right':'0','bottom':'0','left':'0','border':'0 none','float':'none'});
							o.wrap("<span class='imgr' style='"+style+"'></span>");	
							var shape = "<v:shape class='vml-shape' strokecolor='"+defaults.color+"' stroked='"+((_stroke==0)?'f':'t')+"' strokeweight='"+defaults.size+"' coordorigin='0 0' coordsize='"+((_stroke==0)?(_w-1)+' '+(_h-1):(_w-_stroke)+' '+(_h-_stroke))+"' style='width:"+_w+"px;height:"+_h+"px;position:absolute;' "
									+ "path='m "+tl+",0 l "+(_w-tr)+",0 qx "+_w+","+tr+" l "+_w+","+(_h-br)+" qy "+(_w-br)+","+_h+" l "+bl+","+_h+" qx 0,"+(_h-bl)+" l 0,"+tl+" qy "+tl+",0 e'>"
									+ "</v:shape>";
							var fill = "<v:fill class='vml-fill' src='"+src+"' type='frame' style='width:100%;height:100%;'></v:fill>";
							var bdrStyle = "<v:stroke dashstyle='"+vDashStyle+"' linestyle='"+vLineStyle+"' />";
							
							var obj = document.createElement(shape);
							obj.innerHTML = fill + bdrStyle;
							var par = o[0].parentNode;
							o.css({'position':'absolute','z-index':'1','opacity':'0'});
							if(o.parent()[0].nodeName.toLowerCase() == "a"){o.parent().css('cursor','pointer');}
							if(o.parent().parent()[0].nodeName.toLowerCase() == "a"){o.parent().parent().css('cursor','pointer');}
							if(o.parent().parent().parent()[0].nodeName.toLowerCase() == "a"){o.parent().parent().parent().css('cursor','pointer');}
							par.appendChild(obj);
							
							var cache = {
										width:_w,
										height:_h,
										stroke:_stroke,
										color:defaults.color,
										border:defaults.size,
										tl:tl,
										tr:tr,
										bl:bl,
										br:br,
										src:src,
										offt:offt,
										offl:offl,
										offb:offb,
										offr:offr,
										vDash:vDashStyle,
										vLine:vLineStyle
										};
										
							o.parent('.imgr').data("cache",cache);
						
						});
						
					} else {
						
						var data = o.parent('.imgr').data('cache');
						if(!settings.radius){
							tl = data.tl;
							tr = data.tr;
							bl = data.bl;
							br = data.br;
						}
						if(!settings.size){
							defaults.size = data.border;
							_stroke = data.stroke;
						}
						if(!settings.color){
							defaults.color = data.color;
						}
						if(!settings.style){
							vDashStyle = data.vDash;
							vLineStyle = data.vLine;
						}
						
						var shape = "<v:shape class='vml-shape' strokecolor='"+defaults.color+"' stroked='"+((_stroke==0)?'f':'t')+"' strokeweight='"+defaults.size+"' coordorigin='0 0' coordsize='"+((_stroke==0)?(data.width-1)+' '+(data.height-1):(data.width-_stroke)+' '+(data.height-_stroke))+"' style='width:"+data.width+"px;height:"+data.height+"px;position:absolute;' "
								+ "path='m "+tl+",0 l "+(data.width-tr)+",0 qx "+data.width+","+tr+" l "+data.width+","+(data.height-br)+" qy "+(data.width-br)+","+data.height+" l "+bl+","+data.height+" qx 0,"+(data.height-bl)+" l 0,"+tl+" qy "+tl+",0 e'>"
								+ "</v:shape>";
						var fill = "<v:fill class='vml-fill' src='"+src+"' type='frame' style='width:100%;height:100%;'></v:fill>";	
						var bdrStyle = "<v:stroke dashstyle='"+vDashStyle+"' linestyle='"+vLineStyle+"' />";
						
						var obj = document.createElement(shape);
						obj.innerHTML = fill + bdrStyle;
						var par = o[0].parentNode;
						o.css({'position':'absolute','z-index':'1','opacity':'0'});
						o.parent('.imgr').find('.vml-shape').remove();
						
						var _halfStroke = Math.round(_stroke/2);
						var _oldStroke = Math.round(data.stroke/2);
						
						offt = (parseInt(data.offt)-_oldStroke+_halfStroke)+"px ";
						offr = (parseInt(data.offr)-_oldStroke+_halfStroke)+"px ";
						offb = (parseInt(data.offb)-_oldStroke+_halfStroke)+"px ";
						offl = (parseInt(data.offl)-_oldStroke+_halfStroke)+"px ";
						
						o.parent('.imgr').css('margin',offt + offr + offb + offl).css('width',(data.width+_stroke)).css('height',(data.height+_stroke));
						
						if(o.parent()[0].nodeName.toLowerCase() == "a"){o.parent().css('cursor','pointer');}
						if(o.parent().parent()[0].nodeName.toLowerCase() == "a"){o.parent().parent().css('cursor','pointer');}
						if(o.parent().parent().parent()[0].nodeName.toLowerCase() == "a"){o.parent().parent().parent().css('cursor','pointer');}
						par.appendChild(obj);							

						var cache = {
									width:data.width,
									height:data.height,
									stroke:_stroke,
									color:defaults.color,
									border:defaults.size,
									tl:tl,
									tr:tr,
									bl:bl,
									br:br,
									src:src,
									offt:offt,
									offl:offl,
									offb:offb,
									offr:offr,
									vDash:vDashStyle,
									vLine:vLineStyle
									};
									
						o.parent('.imgr').data("cache",cache);							
						
					}
					
				} else {
					
					var style = "display:inline-block;border-radius:"+rad+";border-color:"+defaults.color+";border-style:"+defaults.style+";border-width:"+defaults.size+";background-repeat:no-repeat;background-origin:border-box;background-image:url("+src+");background-size:101% 101%;";
					style += "margin:"+offt + offr + offb + offl+";padding:0;top:"+top+";right:"+right+";bottom:"+bottom+";left:"+left+";float:"+float+";position:"+pos+";";
					
					if(isMoz){
						style += "-moz-border-radius:"+rad+";-moz-background-origin:border-box;-moz-background-size:101% 101%;";	
					}
					if(isWeb){
						
						if(typeof rad=="string"){rad = rad.split(" ");}
						style += "-webkit-border-top-left-radius:"+rad[0]+";-webkit-border-top-right-radius:"+rad[1]+";-webkit-border-bottom-right-radius:"+rad[2]+";-webkit-border-bottom-left-radius:"+rad[3]+";-webkit-background-origin:border-box;-webkit-background-size:101% 101%;";
					}
	
					
					if(!o.parent().hasClass('imgr')){
						o.css({'margin':'0','padding':'0','top':'0','right':'0','bottom':'0','left':'0','border':'0 none','float':'none'});
						o.wrap("<span class='imgr' style='"+style+"'></span>");				
					} else {
						var par = o.parent('.imgr');
												
						if(settings.radius){
							if(!isWeb){
								par.css('border-radius',rad);
							} else {
								par.css({"-webkit-border-top-left-radius":rad[0],"-webkit-border-top-right-radius":rad[1],"-webkit-border-bottom-right-radius":rad[2],"-webkit-border-bottom-left-radius":rad[3]});
							}
							if(isMoz){par.css('-moz-border-radius',rad);}

						}
						if(settings.size){par.css('border-width',defaults.size);}
						if(settings.color){par.css('border-color',defaults.color);}
						if(settings.style){par.css('border-style',defaults.style);}
					}
					
					o.css("opacity","0");
					
				}
			
			} // end if img
			
		}); // end each
		

		

	} // end imgr

})(jQuery);

function colourNameToHex(colour){
	
    var colours = {"aliceblue":"#f0f8ff","antiquewhite":"#faebd7","aqua":"#00ffff","aquamarine":"#7fffd4","azure":"#f0ffff",
    "beige":"#f5f5dc","bisque":"#ffe4c4","black":"#000000","blanchedalmond":"#ffebcd","blue":"#0000ff","blueviolet":"#8a2be2","brown":"#a52a2a","burlywood":"#deb887",
    "cadetblue":"#5f9ea0","chartreuse":"#7fff00","chocolate":"#d2691e","coral":"#ff7f50","cornflowerblue":"#6495ed","cornsilk":"#fff8dc","crimson":"#dc143c","cyan":"#00ffff",
    "darkblue":"#00008b","darkcyan":"#008b8b","darkgoldenrod":"#b8860b","darkgray":"#a9a9a9","darkgreen":"#006400","darkkhaki":"#bdb76b","darkmagenta":"#8b008b","darkolivegreen":"#556b2f",
    "darkorange":"#ff8c00","darkorchid":"#9932cc","darkred":"#8b0000","darksalmon":"#e9967a","darkseagreen":"#8fbc8f","darkslateblue":"#483d8b","darkslategray":"#2f4f4f","darkturquoise":"#00ced1",
    "darkviolet":"#9400d3","deeppink":"#ff1493","deepskyblue":"#00bfff","dimgray":"#696969","dodgerblue":"#1e90ff",
    "firebrick":"#b22222","floralwhite":"#fffaf0","forestgreen":"#228b22","fuchsia":"#ff00ff",
    "gainsboro":"#dcdcdc","ghostwhite":"#f8f8ff","gold":"#ffd700","goldenrod":"#daa520","gray":"#808080","green":"#008000","greenyellow":"#adff2f",
    "honeydew":"#f0fff0","hotpink":"#ff69b4",
    "indianred ":"#cd5c5c","indigo ":"#4b0082","ivory":"#fffff0","khaki":"#f0e68c",
    "lavender":"#e6e6fa","lavenderblush":"#fff0f5","lawngreen":"#7cfc00","lemonchiffon":"#fffacd","lightblue":"#add8e6","lightcoral":"#f08080","lightcyan":"#e0ffff","lightgoldenrodyellow":"#fafad2",
    "lightgrey":"#d3d3d3","lightgreen":"#90ee90","lightpink":"#ffb6c1","lightsalmon":"#ffa07a","lightseagreen":"#20b2aa","lightskyblue":"#87cefa","lightslategray":"#778899","lightsteelblue":"#b0c4de",
    "lightyellow":"#ffffe0","lime":"#00ff00","limegreen":"#32cd32","linen":"#faf0e6",
    "magenta":"#ff00ff","maroon":"#800000","mediumaquamarine":"#66cdaa","mediumblue":"#0000cd","mediumorchid":"#ba55d3","mediumpurple":"#9370d8","mediumseagreen":"#3cb371","mediumslateblue":"#7b68ee",
    "mediumspringgreen":"#00fa9a","mediumturquoise":"#48d1cc","mediumvioletred":"#c71585","midnightblue":"#191970","mintcream":"#f5fffa","mistyrose":"#ffe4e1","moccasin":"#ffe4b5",
    "navajowhite":"#ffdead","navy":"#000080",
    "oldlace":"#fdf5e6","olive":"#808000","olivedrab":"#6b8e23","orange":"#ffa500","orangered":"#ff4500","orchid":"#da70d6",
    "palegoldenrod":"#eee8aa","palegreen":"#98fb98","paleturquoise":"#afeeee","palevioletred":"#d87093","papayawhip":"#ffefd5","peachpuff":"#ffdab9","peru":"#cd853f","pink":"#ffc0cb","plum":"#dda0dd","powderblue":"#b0e0e6","purple":"#800080",
    "red":"#ff0000","rosybrown":"#bc8f8f","royalblue":"#4169e1",
    "saddlebrown":"#8b4513","salmon":"#fa8072","sandybrown":"#f4a460","seagreen":"#2e8b57","seashell":"#fff5ee","sienna":"#a0522d","silver":"#c0c0c0","skyblue":"#87ceeb","slateblue":"#6a5acd","slategray":"#708090","snow":"#fffafa","springgreen":"#00ff7f","steelblue":"#4682b4",
    "tan":"#d2b48c","teal":"#008080","thistle":"#d8bfd8","tomato":"#ff6347","turquoise":"#40e0d0",
    "violet":"#ee82ee",
    "wheat":"#f5deb3","white":"#ffffff","whitesmoke":"#f5f5f5",
    "yellow":"#ffff00","yellowgreen":"#9acd32"};

    if (typeof colours[colour.toLowerCase()] != 'undefined'){return colours[colour.toLowerCase()];} else {return colour;};
}



