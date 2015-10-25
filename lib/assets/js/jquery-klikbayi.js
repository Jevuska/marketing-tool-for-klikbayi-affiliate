/*
 * @package KLIKBAYI
 * @category Core
 * @author Jevuska
 * @version 1.0
 */
 
 ( function( $ ) {
    $.fn.klikbayi = function() {
		
		var kb_nonce = heartbeatSettings.nonce,
			id = klikbayiSet.textdomain,
			scarea = $("textarea#shortcode"),
			phparea = $("textarea#phpcode"),
			scmain = $(".mainsc").text();
			phpmain = $(".mainphp").text();
			
		function generate_shortcode() 
        {
			$("#generate").on("click", function(e) 
            {
				e.preventDefault();
				e.stopImmediatePropagation();
                var sc = [], php = [];
				$(".table-" + id +" input:checked").each(function() {
					if ( $(this).parents("tr").find("td:nth-child(4) kbd").hasClass("active") ){
						if ( $(this).val() !== '' ) {
							var value = $.trim( $(this).val() ),
							values = value.replace(/"/g, "'");
							sc.push(value);
							php.push(values);
						}
					}
				});
				
				if (sc.length == 0) {
					$("textarea.shortcode-result").removeAttr('style');
                    scarea.val(scmain);
					phparea.val(phpmain.substring(6,30));
                } else {
                    sccode = $.trim(sc.join(' ')).replace(/\s\s+/g, ' ');
					
					phpcode = $.trim(php.join(",\n'")).replace(/\s\s+/gi, ' ').replace(/=/g, "' => ").replace(/\n/g, "\n  ");
					
                    if (sccode.length > 0) {
                        scarea.val(scmain.substring(0,9) + ' ' + sccode + scmain.substring(9));
						
						phparea.val(phpmain.substring(6,28) + ",\n array(\n  '" + phpcode + "\n )\n);");
						
						$("textarea.shortcode-result").removeAttr('style');
						$.each($("textarea.shortcode-result"), function() {
							var offset = this.offsetHeight - this.clientHeight;
			 
							var resizeTextarea = function(el) {
								$(el).css('height', 'auto').css('height', el.scrollHeight + offset);
							};
							resizeTextarea(this);
						});			
                    }
				}
				return false;
			})
		}
		
		function select_par_shortcode()
        {			
            $(".table-" + id + " tr input").each(function() 
            {
				var self = $(this);
                self.on("click", function(e)
				{
					var kbd = self.parents("tr").find("td:nth-child(4) kbd");
					kbd.removeClass("wp-ui-highlight active");						

                    if (self.is(":checked")) 
					{							
						kbd.each(function() 
						{
							var el = $(this);
							var dft = el.closest("td").siblings("td:nth-child(3)").text();
							if (el.text() == dft) 
								{
									el.off("click").css({
										"color": "#ccc",
										"background": "#efefef"
									});
								} else {
									chs_val(el, dft, kbd);
									hover_val(el);
								}
							})
                    } else {               
                        kbd.each(function() {
							var el = $(this);
                            el.off("click mouseenter mouseleave").css(
							{
								"color": "",
								"background": ""
							}).parents("tr").find("input").val("");
							$('kbd[class=""]').removeAttr('class');
							$('kbd[style=""]').removeAttr('style');
                        }
					)};
                })
            });
        }
		
		function hover_val(el){
			el.mouseenter(function()
			{
				$("kbd:not(.active)").removeClass("wp-ui-highlight");
				$(this).addClass("wp-ui-highlight");
			}).mouseleave(function() {
				$("kbd:not(.active)").removeClass("wp-ui-highlight");
				$('td kbd[class=""]').removeAttr('class');
			});
		}
		
		function chs_val(el,dft,kbd) 
        {
            el.on("click", function(e) {
                e.stopImmediatePropagation();
				var self = $(this);
                var param = self.closest("td").siblings("td:nth-child(2)").text();
                var opt = self.text();
                if (opt !== dft) {
                    if (el.hasClass("active")) {
                        click_highlighted(el, dft);
                    } else {
                        var valopt = param + '="' + opt + '"';
						self.parents("tr").find("td:nth-child(4) kbd").removeClass("wp-ui-highlight active");
						self.addClass("wp-ui-highlight active");
						self.parents("tr").find("input").val(valopt);
                        self.css({
                            "color": "",
                            "background": ""
                        })
                    }
                }
                return false;
            });
        }
		
		function click_highlighted( el )
        {
            el.removeClass("wp-ui-highlight active");
			el.parents("tr").find("input").val("");
        }
		
        return this.each(function() 
        {
			postboxes.add_postbox_toggles(pagenow);
            generate_shortcode();
			select_par_shortcode();
        })
    }
}
(jQuery));