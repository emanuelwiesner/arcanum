
/* This the main JS for arcanum in [de] */

function search_memo (){
	
        var q = $("#searchform input[name=q]").val();
        if (q.length < 2){
                $("#searchresult").html("Mindesanzahl Zeichen: 2");
                return false;
        }

        $("#searchresult").show().html("suche...");
        $("#search_icon").attr('src','/dl/img/ajax-loader_middle.gif');
        $(".category").hide();

        $.ajax({
                url: "http://arcanum.dreamwriter.org/search/memo",
                data:"q=" + encodeURIComponent(q),
                type:"POST",
                success: function(data) {

                        var data = jQuery.parseJSON(data);
                        if (data.count == 0){
                                $("#searchresult").html("Keine Ergebnisse gefunden!");
                                return false;
                        } else {
                                $("#searchresult").html("Ergebnisse: " + data.count);
                        }

                        var ret = data.ret;
                        for (var c = 0; c <= ret.length - 1; c++){                                           
                                var cat_id = ret[c];
				$("#cat_form_" + cat_id).show();
                                $("#note_" + cat_id).show();
                        }
                        $("#search_icon").delay(1000).attr('src','/dl/img/icons/magnifying_glass_16x16.png');
                }
        });
        return false;
}

function search_cat (){
	
	var q = $("#searchform input[name=q]").val();	
	if (q.length < 2){
		$("#searchresult").html("Mindesanzahl Zeichen: 2");
		return false;
	}

	$("#searchresult").show().html("suche...");
        $("#search_icon").attr('src','/dl/img/ajax-loader_middle.gif');
	$(".category").hide();

	$.ajax({
		url: "http://arcanum.dreamwriter.org/search/live",
		data:"q=" + encodeURIComponent(q),
		type:"POST",
		success: function(data) {

			var data = jQuery.parseJSON(data);
			if (data.count == 0){
				$("#searchresult").html("Keine Ergebnisse gefunden!");
				return false;
			} else {
				$("#searchresult").html("Ergebnisse: " + data.count);
			}

			var ret = data.ret;
			for (var c = 0; c <= ret.categories.length - 1; c++){						
				var cat_id = ret.categories[c];
				$("#cat_form_" + cat_id).show();
			
				portids = new Array();
				for (var p = 0; p <= ret.portals[cat_id].length; p++){
					portids[p] = ret.portals[cat_id][p];
				}
				load_portal(cat_id, 1, portids);
			}
			$("#search_icon").delay(1000).attr('src','/dl/img/icons/magnifying_glass_16x16.png');
		}
	});
	return false;
}

function load_common_used (id){
	
	if ($('#common_used_' + id).hasClass('common_used_yes')){
		var oldval='yes';
	} else {
		var oldval='no';
	}	
	$.get('http://arcanum.dreamwriter.org/portals/common_used_change&id_port_change_common_used=' + id + '&port_change_common_used_old_val=' + oldval, function(data) {

		$('#common_used_' + id).toggleClass('common_used_no');
		$('#common_used_' + id).toggleClass('common_used_yes');
		set_message("Favoritenzugehörigkeit geändert!");


	});
}

function change_common_used_new_portal (id){
	$('#common_used_' + id).toggleClass('common_used_no');
	$('#common_used_' + id).toggleClass('common_used_yes');
}

function load_portal (id, reload, portids){

        if (id == ''){ return false; }

	var cat_id = '#category_' + id;
	var class_id = '.category_' + id;
	var opt = $('#action').val();
		
	if ( ($(cat_id).html() == '') || (reload == 1) ) {
		$(class_id).attr('src','/dl/img/ajax-loader_middle.gif');
		
	dataString = 'id=' + id + '&opt=' + opt;

        if (portids != undefined){
                dataString = dataString + '&portstoshow=' + encodeURIComponent(JSON.stringify(portids));
        }
        
	$.ajax({  
  		  type: "POST",  
  		  url: "http://arcanum.dreamwriter.org/portals/show",  
  		  data: dataString,  
  		  success: function (reqCode) {
  		  			$(class_id).attr('src','/dl/img/icons/read_more_16x16.png');
		    		if (reqCode != 0) {
		    			$(cat_id).html(reqCode);
		    			$(".arc_textarea").autosize({append: "\n"});
					} else{
						set_message('Keine angelegten Portale in dieser Kategorie!');
						$(cat_id).html('');
	    			}
  		 }	   
  		});
		$(cat_id).addClass('loaded');

	} else if ( $(cat_id).hasClass('loaded') ) {		
		$(cat_id).toggle();
	}
}


function change_options (div){
	$('.options_change').hide();
	$('.choose').removeClass('active');
	
	$(div + '_change').slideDown();
	$(div).addClass('active');			

	$('#subnav li').removeClass('active');
	$(div).parent().addClass('active');
}

function walk_register (div){
	$('.reg_change').hide();	
	$(div).slideDown();	
}

function do_register (){
    error = false;
	reg_form = '#register';
	
    var username = $(reg_form + ' #username').val();	    	
    var password1 = $(reg_form + ' #password1').val();
    var password2 = $(reg_form + ' #password2').val();
    var colour = $(reg_form + ' #colour').val();
    regexpmatch = /#[0-9a-fA-F]{6}/;
	
    var dataString = '&username='+ encodeURIComponent(username) + '&password_1='+ encodeURIComponent(password1) + '&password_2='+ encodeURIComponent(password2) + '&colour=' + colour + '&action=doit'; 
   
    //CHECK INV MODE
        	var dataString = dataString + '&captcha=' + $(reg_form + ' #captchatext').val() + '&captchacount=' + $(reg_form + ' #captcha_count').val();
	if ($(reg_form + ' #captchatext').val() == '') {
                set_message('Bitte den angezeigten Text eintippen!');
                error = true;
	}
    
	if (username == '') {
		set_message('Fehlende Angabe: Benutzername');
		error = true;
	} else if (password1 == '') {
		set_message('Fehlende Angabe: Passwort');
		error = true;
	} else if (password1 != password2) {
		set_message('Passwörter stimmen nicht überein');
		error = true;
	} else if (regexpmatch.test(colour) == false) {
		set_message('Lieblingsfarbe - Diese muss in der Form #HEX_CODE vorliegen!');
		error = true;
	}
	
	if (error == false) { 
		$.ajax({  
			  type: "POST",  
			  url: "http://arcanum.dreamwriter.org/register/doit",  
			  data: dataString,  
			  success: function (reqCode) {
					if (reqCode == 1) {
							text = '<b class="info">' + username + '</b>,  du hast dich erfolgreich registriert';
							$(reg_form).fadeOut(400).delay(400).hide().html(text).fadeIn(400);	
							
					} else if (reqCode == 2){
							reloadcaptcha();
							set_message('Du hast das Captcha falsch eingegeben! Ein neues wurde generiert!');

					} else {
						set_message('<div class="error">' + reqCode + '</div>');
					}
			 }
		   
		}); 
	} else {
		return false;
	}
}

function reloadcaptcha() {
	reg_form = '#register';

	count = $(reg_form + ' #captcha_count').val();
	count++;	
	$(reg_form + ' #captcha_img').attr("src", "http://arcanum.dreamwriter.org/getcaptcha" + '/' + count);
	$(reg_form + ' #captcha_count').val(count);
}

function change_cat_form (id){
	var cat_form = '#cat_form_' + id;
	cat_form_old = $(cat_form).html();
	
	$(cat_form + ' .deletecat').toggle();
	$(cat_form + ' .savecat').toggle();	
	
	if ($(cat_form).hasClass('loaded')) {
		$(cat_form + ' #input_name_' + id).attr('readonly', 'readonly');
		$(cat_form + ' #input_desc_' + id).attr('readonly', 'readonly');
	} else { 
		$(cat_form + ' #input_name_' + id).removeAttr("readonly");
		$(cat_form + ' #input_desc_' + id).removeAttr("readonly");
	}
	$(cat_form).toggleClass('loaded');
}

function change_memo_form (id){
	var memo_form = '#cat_form_' + id;
	
		$(memo_form + ' .deletecat').toggle();
		$(memo_form + ' .savecat').toggle();		
	
	if ($(memo_form).hasClass('loaded')) {
		$(memo_form + ' #memo_title_' + id).attr('readonly', 'readonly');
		$(memo_form + ' #memo_note_' + id).attr('readonly', 'readonly');		
	} else {
		$(memo_form + ' #memo_title_' + id).removeAttr("readonly");
		$(memo_form + ' #memo_note_' + id).removeAttr("readonly");
	}
	$(memo_form).toggleClass('loaded');
}

function change_portal_form (id){
		
	var port_form = '#portal_form_' + id;
	portal_form_old = $(port_form).html();
	change_img = port_form + ' .changeportal';
	loaded_autologin = port_form + ' #portal_loaded_autologin_' + id;
	
	if ($(change_img).hasClass('loaded')) {
		$(port_form + ' #input_name_' + id).attr('readonly', 'readonly');
		$(port_form + ' #input_link_' + id).attr('readonly', 'readonly');
		$(port_form + ' #input_desc_' + id).attr('readonly', 'readonly');	

		$(port_form + ' #input_link_' + id).attr('style', 'width="210px";');
		$(port_form + ' #input_link_' + id).hide();
		
	} else {
		if ( ($(loaded_autologin ).val() == '1') && (!($(loaded_autologin).hasClass('fix'))) ){
			
			/* 
			// FIXME ?
			//  change_autologin(id, 'portal'); 
			*/

			$(loaded_autologin).toggleClass('fix');
		}
		
		$(port_form + ' #input_name_' + id).removeAttr("readonly");
		$(port_form + ' #input_link_' + id).removeAttr("readonly");
		$(port_form + ' #input_desc_' + id).removeAttr("readonly");
		$(port_form + ' #input_link_' + id).show();
	
	}
	$(port_form + ' .deleteportal').toggle();
	$(port_form + ' .autologin').toggle();
	$(port_form + ' .saveportal').toggle();	
	
	$(change_img).toggleClass('loaded');
		if ($(loaded_autologin ).val() == '1'){
			/*
			* $(port_form + ' #input_link_' + id).toggle();
			*/
		}
	
}

function change_arc_form (id){
	var arc_form = '#arcanum_form_' + id;
	arc_form_old = $(arc_form).html();
	
	$('.arcanums .change').hide();
	$('.arcanums .gen_passwd').show();
	$('.arcanums .save').show();	
	
	$(arc_form + ' .in').removeAttr("readonly");
}

	    function save_cat (id){
	    	var cat_form = '#cat_form_' + id;
	    	
		    $(cat_form + ' #input_name_' + id).attr('readonly', 'readonly');
	    	$(cat_form + ' #input_desc_' + id).attr('readonly', 'readonly');
	    	
	        var category = $(cat_form + ' #input_name_' + id).val();	    	
	        var desc = $(cat_form + ' #input_desc_' + id).val();
	        var action = 'update';

	        var dataString = 'id_cat='+ id + '&category='+ encodeURIComponent(category) + '&desc=' + encodeURIComponent(desc) + '&action=' + action; 
			
			//$(cat_form).fadeOut(400).hide().html('<img src="/dl/img/ajax-loader_middle.gif">').fadeIn(400);
	        
	        $.ajax({  
	    		  type: "POST",  
	    		  url: "http://arcanum.dreamwriter.org/categories",  
	    		  data: dataString,  
	    		  success: function (reqCode) {
			    		if (reqCode == 1) {
	  						$(cat_form).fadeOut(400).delay(400).hide().html(window.cat_form_old).fadeIn(400);
	  						
	  						$('#input_name_' + id).val(category);
	  						$('#input_desc_' + id).val(desc);
	  						
	  						set_message('Änderungen gespeichert!');	
	  					} else{
	  						$(cat_form).fadeOut(400).delay(400).hide().html('<div class="error">FEHLER [' + reqCode + ']</div>').fadeIn(400);
		    			}
	    		 }
  		   
	    	}); 
    	
	    }
	    
	    
	    
	    function save_memo (id){
	    	var cat_form = '#cat_form_' + id;
	    	
		    $(cat_form + ' #memo_title_' + id).attr('readonly', 'readonly');
		    $(cat_form + ' #memo_note_' + id).attr('readonly', 'readonly');
	    	
	    	$(cat_form + ' .savecat').hide();
	    	$(cat_form + ' .deletecat').hide();
	    	
	        var title = $(cat_form + ' #memo_title_' + id).val();	    	
	        var note = $(cat_form + ' #memo_note_' + id).val();
	        var action = 'update';

	        var dataString = 'id_memo='+ id + '&title='+ encodeURIComponent(title) + '&note=' + encodeURIComponent(note) + '&action=' + action; 
			
	        $.ajax({  
	    		  type: "POST",  
	    		  url: "http://arcanum.dreamwriter.org/memo",  
	    		  data: dataString,  
	    		  success: function (reqCode) {
			    		if (reqCode == 1) {
	  						$(cat_form).fadeOut(400).delay(400).fadeIn(400);
							$(cat_form).toggleClass('loaded');
	  						
	  						set_message('Änderungen gespeichert!');	
	  					} else{
	  						$(cat_form).fadeOut(400).delay(400).hide().html('<div class="error">FEHLER [' + reqCode + ']</div>').fadeIn(400);
		    			}
	    		 }
  		   
	    	}); 
    	
	    }
	    
	    
	    function del_memo (id){

		    if(!(confirm('Wirklich löschen?'))){
		    	return false;
		    }
			var cat_form = '#cat_form_' + id;

		    var action = 'del';
	    	var dataString = 'id_memo=' + id + '&action=' + action;
	    	
	        $.ajax({  
	    		  type: "POST",  
	    		  url: "http://arcanum.dreamwriter.org/memo",  
	    		  data: dataString,  
	    		  success: function (reqCode) {
			    		if (reqCode == 1) {
			    			
	  						$(cat_form).fadeOut(400);
	  						set_message("gelöscht");
  						
		    			} else {
		    				set_message('<div class="error">FEHLER [' + reqCode + ']</div>');
			    		}
	    		 }
  		   
	    	}); 
	    }

            function remember_arc (id){
                var dataString = 'id_arc=' + id + '&action=remember';
		var old_pw = '#old_pw_' + id;
                $.ajax({  
                          type: "POST",  
                          url: "http://arcanum.dreamwriter.org/arcanums",  
                          data: dataString,  
                          success: function (reqCode) {
                                        if (reqCode == 1) {
                                                	$(old_pw).fadeOut(400);
                                                        set_message("Änderungen gespeichert!");
                                        } else {
                                                set_message('<div class="error">FEHLER [' + reqCode + ']</div>');
                                        }
                         }
                }); 
            }

	    
	    function del_cat (id){

		    if(!(confirm('Wirklich löschen?'))){
		    	return false;
		    }

			var cat_form = '#cat_form_' + id;
	    	
		    $(cat_form + ' #input_name_' + id).attr('readonly', 'readonly');
	    	$(cat_form + ' #input_desc_' + id).attr('readonly', 'readonly');
		    
		    var action = 'del';
	    	var dataString = 'id_cat=' + id + '&action=' + action;
	    	var cat_form = '#cat_form_' + id;
	    	
	        $.ajax({  
	    		  type: "POST",  
	    		  url: "http://arcanum.dreamwriter.org/categories",  
	    		  data: dataString,  
	    		  success: function (reqCode) {
			    		if (reqCode == 1) {
							/*
	  							$(cat_form).fadeOut(400).delay(400).hide().html("gelöscht").fadeIn(400).delay(400).fadeOut(400);
	  							$(cat_form).hide();
	  						*/
	  						$(cat_form).fadeOut(400);
	  						set_message("gelöscht");
  						
	  					} else if (reqCode == 2){
	  						/*$(cat_form).fadeOut(400).delay(400).hide().fadeIn(400);*/
	  						set_message('<div class="error"">Es befinden sich Dateien in dieser Kategorie!</div>');
		    			} else {
		    				set_message('<div class="error">FEHLER [' + reqCode + ']</div>');
			    		}
	    		 }
  		   
	    	}); 
	    }

	    function set_message (text){		    
		var innerdiv = '#footer';
		var msgdiv = '#msg';

		if ( $(msgdiv).length == 0 ) {			
	    		$(innerdiv).append('<div class="shadow" id="shadow-msg"><div id="msg"><div class="content-inner"></div></div></div><div class="shadow-bottom" id="shadow-bottom-msg"></div>');
		}

		$(msgdiv).hide();
		$("#shadow-msg").hide();		

		$(msgdiv).html(text);

		$(msgdiv).fadeIn(1500).delay(3000).fadeOut(1500);

		if (!($('#shadow-msg').is(":visible"))) {
			$("#shadow-msg").fadeIn(1500).delay(3000).fadeOut(1500);
		}
	
		$("#shadow-msg").delay(2500).fadeOut(1500);
		$("#shadow-bottom-msg").delay(3000).fadeOut(1500);
			
	    }

	    function add_portal (id){
	    	var add_portal_form = '#add_portal_form_' + id;
	    	
			$(add_portal_form + ' #input_portalname_' + id).attr('readonly', 'readonly');
	    	$(add_portal_form + ' #input_portaldesc_' + id).attr('readonly', 'readonly');
	    	$(add_portal_form + ' #input_link_' + id).attr('readonly', 'readonly');

	    	$(add_portal_form + ' #input_portal_login_' + id).attr('readonly', 'readonly');
	    	$(add_portal_form + ' #input_portal_pass_' + id).attr('readonly', 'readonly');
	    	
	        var name = $(add_portal_form + ' #input_portalname_' + id).val();	    	
	        var desc = $(add_portal_form + ' #input_portaldesc_' + id).val();
	        var link = $(add_portal_form + ' #input_link_' + id).val();
	        var autolink = $(add_portal_form + ' #add_portal_autologin_' + id).val();
	        var portal_login = $(add_portal_form + ' #input_portal_login_' + id).val();
	        var portal_pass = $(add_portal_form + ' #input_portal_pass_' + id).val();
	        
	        $(add_portal_form + ' #input_portalname_' + id).val('');	    	
	        $(add_portal_form + ' #input_portaldesc_' + id).val('');
	        $(add_portal_form + ' #input_link_' + id).val('');
	        $(add_portal_form + ' #add_portal_autologin_' + id).val('0');
	        $(add_portal_form + ' #input_portal_login_' + id).val('');
	        $(add_portal_form + ' #input_portal_pass_' + id).val('');
	        
	        var common_used = '';

	        if ( $('#common_used_' + id).hasClass('common_used_yes') ){
				var common_used = 'on'; 
		    }
	        
		if (desc == undefined){
			desc = '';
		}

	        var action = 'add';
	        dataString = 'id_categories='+ id + '&name='+ encodeURIComponent(name) + '&desc=' + encodeURIComponent(desc) + '&link=' + encodeURIComponent(link) + '&portal_login=' + encodeURIComponent(portal_login) + '&portal_pass=' + encodeURIComponent(portal_pass) + '&common_used=' + common_used + '&action=' + action + '&autolink=' + autolink; 
	        $.ajax({  
	    		  type: "POST",  
	    		  url: "http://arcanum.dreamwriter.org/portals",  
	    		  data: dataString,  
	    		  success: function (reqCode) {
			    		if (reqCode == 1) {	
			    			load_portal(id, 1);		
			    			 $(add_portal_form).hide();
	  						set_message('Portal angelegt');	
	  					} else {
	  						set_message('<div class="error">FEHLER [' + reqCode + ']</div>');
		    			}
	    		 }
  		   
	    	}); 

                $(add_portal_form + ' #input_portalname_' + id).removeAttr('readonly');
                $(add_portal_form + ' #input_portaldesc_' + id).removeAttr('readonly');
                $(add_portal_form + ' #input_link_' + id).removeAttr('readonly');
                $(add_portal_form + ' #input_portal_login_' + id).removeAttr('readonly');
                $(add_portal_form + ' #input_portal_pass_' + id).removeAttr('readonly');	
	    }

	    function save_portal (id){
	    	var portal_form = '#portal_form_' + id;
	    	
		    $(portal_form + ' #input_name_' + id).attr('readonly', 'readonly');
	    	$(portal_form + ' #input_desc_' + id).attr('readonly', 'readonly');
	    	$(portal_form + ' #input_link_' + id).attr('readonly', 'readonly');
	    	
	        var name = $(portal_form + ' #input_name_' + id).val();	    	
	        var desc = $(portal_form + ' #input_desc_' + id).val();
	        var link = $(portal_form + ' #input_link_' + id).val();
	        var autolink = $(portal_form + ' #portal_autologin_' + id).val();
	        var action = 'update';
	
		if (desc == undefined){
                        desc = '';
                }
	 
	        var dataString = 'id_port='+ id + '&name='+ encodeURIComponent(name) + '&desc=' + encodeURIComponent(desc) + '&action=' + action + '&link=' + encodeURIComponent(link) + '&autolink=' + autolink; 
	        $.ajax({  
	    		  type: "POST",  
	    		  url: "http://arcanum.dreamwriter.org/portals",  
	    		  data: dataString,  
	    		  success: function (reqCode) {
			    		if (reqCode == 1) {
				    		
	  						$(portal_form).fadeOut(400).delay(400).hide().html(window.portal_form_old).fadeIn(400);
	  						
	  						$(portal_form + ' #input_name_' + id).val(name);
	  						$(portal_form + ' #input_desc_' + id).val(desc);
	  						$(portal_form + ' #input_link_' + id).val(link);
	  						
	  						set_message('Änderungen gespeichert!');	
	  					} else{
	  						set_message('<div class="error">FEHLER [' + reqCode + ']</div>');
		    			}
	    		 }
  		   
	    	}); 
    	
	    }


	    function del_portal (id){

		    if(!(confirm("Wirklich löschen?"))){
		    	return false;
		    }

			var portal_form = '#portal_form_' + id;
	    	
		    $(portal_form + ' #input_name_' + id).attr('readonly', 'readonly');
	    	$(portal_form + ' #input_desc_' + id).attr('readonly', 'readonly');
	    	$(portal_form + ' #input_link_' + id).attr('readonly', 'readonly');
		    
		    var action = 'del';
	    	var dataString = 'id_port=' + id + '&action=' + action;
	    	
	        $.ajax({  
	    		  type: "POST",  
	    		  url: "http://arcanum.dreamwriter.org/portals",  
	    		  data: dataString,  
	    		  success: function (reqCode) {
			    		if (reqCode == 1) {
	  						/*
	  						$(portal_form).fadeOut(400).delay(400).hide().html("gelöscht").fadeIn(400).delay(400).fadeOut(400);
	  						*/
	  						$(portal_form).fadeOut(400);
	  						set_message('gelöscht');
	  					} else {
	  						set_message('<div class="error">FEHLER [' + reqCode + ']</div>');
			    		}
	    		 }
  		   
	    	}); 
	    }

function load_arcanum (id,reload){
	var arcanum = '#arcanum_' + id;
	var load_arcanum_div = '#load_arcanum_' + id;

	if (($(arcanum).html() == '') || (reload == true)){
		$(load_arcanum_div).removeClass('arcanum_decrypt');
		$(load_arcanum_div).addClass('loading_small');

		$.get('http://arcanum.dreamwriter.org/arcanums/show&id=' + id, function(data) {
			$(arcanum).html(data);
			$(load_arcanum_div).removeClass('loading_small')
			$(load_arcanum_div).addClass('arcanum_decrypt');
		
			/* Most ugly bugfix ever ! */ 	
			var w = $(window).width();
			var w_arc = $(arcanum).css('width').replace('px', '');
			var p_arc = $(arcanum).css('padding-left').replace('px', '');
			var l = (w / 2) - (w_arc / 2) - p_arc;
			/* Most ugly bugfix ever ! */

			$(arcanum).css('left', l + 'px');
			$(arcanum).fadeIn(400);
		});
		
	} else {
		$(arcanum).fadeOut(400);
		$(arcanum).delay(1000).html('');
	}
	
}

	function save_arcanum (id, parentportal){
	    	var arc_form = '#arcanum_form_' + id;
	    	var arc_form_parent = '#arcanum_' + parentportal;
	    	
	    	$(arc_form_parent + ' .save').toggle();
	    	$(arc_form_parent + ' .loading').toggle();
	    	
		    $(arc_form + ' #input_login_' + id).attr('readonly', 'readonly');
	    	$(arc_form + ' #input_pass_' + id).attr('readonly', 'readonly');
	    	
	        var login = $(arc_form + ' #input_login_' + id).val();	    	
	        var pass = $(arc_form + ' #input_pass_' + id).val();
	        var action = 'update';

	        var dataString = 'id_arc='+ id + '&portal_login='+ encodeURIComponent(login) + '&portal_pass=' + encodeURIComponent(pass) + '&action=' + action; 
	       
	        $.ajax({  
	    		  type: "POST",  
	    		  url: "http://arcanum.dreamwriter.org/arcanums",  
	    		  data: dataString,  
	    		  success: function (reqCode) {
			    		if (reqCode == 1) {
							
							load_arcanum(parentportal, true);
						
	  						set_message('Änderungen gespeichert!');	
	  						
	  						$(arc_form_parent + '.save').toggle();
	    					$(arc_form_parent + '.loading').toggle();
	  					} else{
	  						set_message('<div class="error">FEHLER [' + reqCode + ']</div>');
		    			}
	    		 }
  		   
	    	}); 
    	
	    }
	    
	    	function doarclogin () {
	    		
	    		var username = $('#login #username').val();  
	    		var password = $('#login #password').val(); 
				var patternlock = $('#patternlock').val();
				
				if (window.loaded_patternlock == patternlock){
					patternlock = '0';
				}
	
				$.post('http://arcanum.dreamwriter.org/login', { username: encodeURIComponent(username), password: encodeURIComponent(password), patternlock: patternlock },

		   			function(data) { 
		   				var ret = jQuery.parseJSON(data);						
	
		   				if (ret.code == '0'){
		   					$('#login_text').html('Login erfolgreich.');
		   					window.location = ret.msg;
		   					
						} else if (ret.code == '2') {							
							$('.patternlockcontainer').show();
							
		   				} else if (ret.code == '1') {		   							   					
		   					reset_patternlock();
		   					$('.patternlockcontainer').hide();
							$('#login_text').html(ret.msg);
							
		   				}
		   			}
		   		);

			}

		function doarclogout() {

			$('#msg').html('');
			$('#subnav').html('');
			$('#nav').html('');
			$('#timer').html('');
			$('.arc').html('');
			$('.arc').hide();
			$('.add_portal').hide();
			$('#timerinfo').html('&nbsp;');
			$('#modulename').html('Ausloggen....');
			$('#content').animate({
				height: '0px',
				paddingBottom: '0px'
			}, 500);
			
			
			window.location = 'http://arcanum.dreamwriter.org/login/logout';	
			
		}
	
			function reset_patternlock() {
				$('.patternlockbutton').removeClass('touched');
				$('.patternlockbutton').removeClass('multiple');
							
				$('.patternlocklinediagonalbackwards').removeAttr('style');
				$('.patternlocklinediagonalforward').removeAttr('style');
				$('.patternlocklinevertical').removeAttr('style');
				$('.patternlocklinehorizontal').removeAttr('style');
							
				$('#patternlock').val('');
			}
			
			function change_autologin (id, which) {
				
				if (which == 'addportal'){
					var fheight = 28;
					var fwidth = 558;
					var sheight = 100;
					var swidth = 740;				
				} else {
					var fheight = 28;
					var fwidth = 558;
					var sheight = 80;
					var swidth = 600;
				}			
				
				var loginimg   = '#' + which + '_form_' + id + ' .autologin';
				var logininput = '#' + which + '_autologin_' + id;
				var inputfield = '#' + which + '_form_' + id + ' #input_link_' + id;
				
				if ($(loginimg).hasClass('loaded')) {
					$(loginimg).attr('src', '/dl/img/icons/comment_stroke_16x14.png');
				
					$(inputfield).css('height', fheight + 'px');
					$(inputfield).css('width', fwidth + 'px');
				
					$(logininput).val('0');
				} else {
					$(loginimg).attr('src', '/dl/img/icons/comment_fill_16x14.png');

					$(inputfield).css('height', sheight + 'px');
					$(inputfield).css('width', swidth + 'px');
					
					$(logininput).val('1');
				}
				$(loginimg).toggleClass('loaded');
			}

function generate_password (len) {
        password = '';
        min = 33;
        max = 122;

        regexpmatch = /[A-Za-z0-9]{1}/;

        while (password.length <= len) {
                x = String.fromCharCode(Math.floor(Math.random() * (max - min + 1)) + min);

                if (password.length == 0 || password.length == len){
                        while (regexpmatch.test(x) == false) {
                                x = String.fromCharCode(Math.floor(Math.random() * (max - min + 1)) + min);
                        }
                }
                password = password + x;
        }
        return password;
}


function timer_regenerate () {
	$.get('http://arcanum.dreamwriter.org/timer/regenerate', 
		function(data) {
                        if (/^[0-9]/.test(data)){
				$('#timer').html(data);
                        } else {
				window.location = 'http://arcanum.dreamwriter.org/login/logout&msg=s:46:"Aus Sicherheitsgründen wurdest du ausgeloggt.";';
                        }
		}
	);
}

function gen_passwd (id) {
        $('#arcanum_form_' + id + ' #input_pass_' + id).val(generate_password(12));
}
function gen_passwd_new (id){
	$('#input_portal_pass_' + id).val(generate_password(12));
}
