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
			clsIpt = "new-input",
			clsBtnOK = "button button-secondary",
			txtArray = [ "form_title", "button_text" ],
			scarea = $( "textarea#shortcode" ),
			phparea = $( "textarea#phpcode" ),
			scmain = $( ".mainsc" ).text();
			phpmain = $( ".mainphp" ).text();
			
		function generate_shortcode() 
        {
			$( "#generate" ).on( "click", function( e ) 
            {
				e.preventDefault();
				e.stopImmediatePropagation();
                var sc = [], php = [];
				$( ".table-" + id +" input:checked" ).each( function() {
					if ( $( this ).parents( "tr" ).find( "td:nth-child(4) kbd" ).hasClass( "active" ) ){
						if ( '' !== $( this ).val() ) {
							var value = $.trim( $( this ).val() ),
							values = value.replace( /"/g, "'" );
							sc.push( value );
							php.push( values );
						}
					}
				});
				
				if ( 0 == sc.length ) {
					$( "textarea.shortcode-result" ).removeAttr( "style" );
                    scarea.val( scmain );
					phparea.val( phpmain.substring( 6, 30 ) );
                } else {
                    sccode = $.trim( sc.join(' ') ).replace( /\s\s+/g, " " );
					phpcode = $.trim( php.join( ",\n'" ) ).replace( /\s\s+/gi, " " ).replace( /=/g, "' => " ).replace( /\n/g, "\n  " );
					
                    if ( 0 < sccode.length ) {
                        scarea.val( scmain.substring( 0, 9 ) + ' ' + sccode + scmain.substring( 9 ) );
						
						phparea.val( phpmain.substring( 6, 28 ) + ",\n array(\n  '" + phpcode + "\n )\n);" );
						
						$( "textarea.shortcode-result" ).removeAttr( "style" );
						$.each($( "textarea.shortcode-result" ), function() {
							var offset = this.offsetHeight - this.clientHeight;
			 
							var resizeTextarea = function( el ) {
								$( el ).css( "height", "auto" ).css( "height", el.scrollHeight + offset );
							};
							resizeTextarea( this );
						});			
                    }
				}
				return false;
			})
		}

		function btn_reset() {
            $( "#reset_code" ).on( "click", function() {
                item_reset();
            })
        }
		
		function select_all() {
            $( "#cb-select-all-1, #cb-select-all-2" ).on( "click", function() {
				if ( $( this ).is( ":checked" ) ) {
					$( "table.table-" + id + " input").not( "#cb-select-all-1, #cb-select-all-2" ).each( function() {
						var kbd = $( this ).parents( "tr" ).find( "td:nth-child(4) kbd" );
						kbd.removeClass( "wp-ui-highlight active" );
						$( "." + clsIpt).remove();
						kbd.each( function() {
							var el = $( this );
							var dft = el.closest( "td" ).siblings( "td:nth-child(3)" ).text();
							if ( el.text() == dft )  {
								el.off( "click" ).css( {
									"color": "#ccc",
									"background": "#efefef"
								} );
							} else {
								shw_input( el, dft );
								chs_val( el, dft, kbd );
								hover_val( el );
							}
						});
					});
				} else {
					item_reset();
				}
            });
		}
		
		function select_par_shortcode() {
			$( "table.table-" + id + " input").not( "#cb-select-all-1, #cb-select-all-2" ).each( function() {
                $( this ).on( "click", function() {
					each_sc( $( this ) );
                })
            })
        }
		
		function each_sc( self ) {
			var kbd = self.parents( "tr" ).find( "td:nth-child(4) kbd" );
			
			kbd.removeClass( "wp-ui-highlight active" );
			$( "." + clsIpt).remove();
			
			if ( self.is( ":checked" ) ) {

				kbd.each( function() {
					var el = $( this );
					var dft = el.closest( "td" ).siblings( "td:nth-child(3)" ).text();
					
					if ( el.text() == dft )  {
						el.off( "click" ).css( {
							"color": "#ccc",
							"background": "#efefef"
						} );
					} else {
						shw_input( el, dft );
						chs_val( el, dft, kbd );
						hover_val( el );
					}
				});
			} else {
				kbd.each( function() {
					var el = $( this );
					el.off( "click mouseenter mouseleave" ).css( {
						"color": "",
						"background": ""
					}).parents( "tr" ).find( "input" ).val( "" );
					$( "kbd[class='']" ).removeAttr( "class" );
					$( "kbd[style='']" ).removeAttr( "style" );
				} )
			}
		}
		
		function item_reset() {
			$( "." + clsIpt ).remove();
			$( "kbd" ).off( "click mouseenter mouseover" ).css( { "color": "", "background": "" } ).removeClass( "wp-ui-highlight active" );
			$( "kbd[class='']" ).removeAttr( "class" );
			$( "textarea.shortcode-result" ).removeAttr( "style" );
			$( "textarea.shortcode-result" ).val( "" );
			$( "table.table-" + id + " tr input" ).prop( "checked", false );
		}
		
		function hover_val( el ) {
			el.mouseenter( function() {
				$( "kbd:not(.active)" ).removeClass( "wp-ui-highlight" );
				$( this ).addClass( "wp-ui-highlight" );
			}).mouseleave(function() {
				$( "kbd:not(.active)" ).removeClass( "wp-ui-highlight" );
				$( "td kbd[class='']" ).removeAttr( "class" );
			});
		}
		
		function chs_val( el, dft, kbd ) {
            el.on( "click", function( e ) {
                e.stopImmediatePropagation();
				var self = $( this );
                var param = self.closest( "td" ).siblings( "td:nth-child(2)" ).text();
                var opt = self.text();
                if ( opt !== dft ) {
					 $( "." + clsIpt ).remove();
                    if ( el.hasClass( "active" ) ) {
                        click_highlighted( el, dft );
                    } else {
						opt = ( opt == klikbayiL10n.null ) ? "" : opt;
                        var valopt = param + '="' + opt + '"';
						
						self.parents( "tr" ).find( "td:nth-child(4) kbd" ).removeClass( "wp-ui-highlight active" );
						self.addClass( "wp-ui-highlight active" );
						self.parents( "tr" ).find( "input" ).val( valopt );
                        self.css( {
                            "color" : "",
                            "background" : ""
                        } );
                    }
                }
                return false;
            });
        }
		
		function click_highlighted( el, dft ) {
            el.removeClass( "wp-ui-highlight active" );
			el.parents( "tr" ).find( "input" ).val( "" );
			shw_input( el, dft );
        }
		
		function shw_input( el, dft ) {
			var opt = el.parents( "tr" ).find( "input" ).attr( "class" );
			if ( -1 != txtArray.indexOf( opt ) ) {
                var inputType = create_input_type( opt ),
					idBtn = "btn-" + opt,
					idBtnOK = "btn-ok-" + opt;
					
				var input = $( "<div>",{ "id": idBtn, "class": clsIpt, html: $( "<p>", { "html" : $( inputType ).add( $( "<button>",{ "id" :idBtnOK, "class" : clsBtnOK, "html" : klikbayiL10n.ok } ) ) } ) } );
				
				el.parents( "td" ).find( "kbd:last" ).after( input );
				
				if ( 0 < $( "#btn-" + opt).length ) {
					$( "#btn-ok-" + opt ).click( function( e ) {
					e.preventDefault();
					empty = $( "<em>", { text:klikbayiL10n.null } );
					var opts = $.trim( $( ".kbd-" + opt ).text() );
					if ( opts != dft ) {
						el.closest( "td" ).children( "kbd" ).css( {
							"color": "",
							"background": ""
						});
						opts = ( "" == opts ) ? $( empty ) : opts;
						el.html( opts );
						$( "#btn-" + opt ).remove();
					} else {
						msg_error( $( this ), klikbayiL10n.notice1 );
					}
					return false;
					})
				}
            }
		}
		
		function create_input_type( opt ) {
			var input = "<input type='";
			
			switch( opt ) {
				case "form_title" :
				case "button_text" :
					input += "text' ";
					input += "class='kbd-" + opt;
					input += "' value=''>";
					return input;
				break;
				default :
					input += "number' ";
					input += "class='kbd-" + opt;
					input += "' min=1 value='1'>";
					return input;
				break;
			}
        }
		
        return this.each(function() 
        {
			postboxes.add_postbox_toggles(pagenow);
            generate_shortcode();
			select_par_shortcode();
			select_all();
			btn_reset();
        })
    }
}
(jQuery));