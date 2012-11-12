function insertRichEditor(field, value) {
	$('#'+field).append(value);
}

// Chargement de la template ou du dernier enregistrement
function start(init){
    
	//return;
    var tmp1 = $('<div/>');
    tmp1.append($template.find('html'));
    var html1 = tmp1.html();
    templatehtml = html1;
    $template = $(html1);
    tmp1.remove();
	    
    // Chargement de la template
	if(init) {
		//alert('init true');
	    
	    //console.log(html);
	    document.getElementById('preview').contentDocument.write(html1);
		//$preview.contents()[0].innerHTML = html1;
    } else {
    
    	var tmp2 = $('<div/>');
	    tmp2.append($designer.find('html'));
	    var html2 = tmp2.html();
	    designerhtml = html2;
	    $designer = $(html2);
	    tmp2.remove();
	    document.getElementById('preview').contentDocument.write(html2);
		//$preview.contents()[0].innerHTML = html2;   	
    }
	
	
    $preview.find("repeater").each( function(e) {	    	
	    id_repeater ++;
	    $(this).attr("data-id", id_repeater);
        if(init) {
        	$(this).find("layout").not(":first").remove();
        }
        repeaterData[id_repeater] = new Object();
        repeaterAddHeader($(this));
        
    });
    
    // data-external : chargements liste content / user
	if(init) {
		$preview.find("repeaters").each( function(e) {			
            var url = $(this).attr("data-external");	
            var ref = $(this).attr("data-ref");
            //alert(url);
            if(url) {    
            	$(this).find("repeater").remove();        	
                $.post(url, function(data) {
                    for(var i= 0; i < data.length; i++)
					{
        				var tmp = repeaterAdd(ref);
        				tmp.attr("data-id_content", data[i]);
        				repeaterApplyContent(tmp.attr("data-id"), "load");
					}
                }, 'json'); 
            }
		}); 
	}
    
	// Ajout du JS + CSS	
	$preview.find("head").append(css);
	$preview.find("body")[0].appendChild(script);
	$preview.find("body")[0].appendChild(script2);
	$preview.find("body")[0].appendChild(script3);
	sizeIFrame();
	
    // Recuperation de tous les types de repeaters et ajout des options dans le menu deroulant
    $("#stRepeater").html('<option value="0">Ins�rer un �l�ment</option>');
    $template.find("repeater").each( function(e) {
        $("#stRepeater").append(new Option($(this).attr('data-name'), $(this).attr('data-ref'), false, false));
    });
    // Si ajout repeater
    $('#stRepeater').change( function(e) {
    	editClose();
        repeaterAdd($(this).val());
    });
    
	/*$preview.find("repeaters").sortable({
		items: "repeater"
	});*/
}



// Ajout d'un repeater
function repeaterAdd(ref) {
    var templateRepeaters   = $template.find("repeaters[data-ref=" + ref + "]");
    var previewRepeaters    = $preview.find("repeaters[data-ref=" + ref + "]");  
    var lastRepeater        = previewRepeaters.find("repeater");    
    var multiple            = previewRepeaters.attr('data-multiple');

    if(!multiple && lastRepeater.html()) {
        alert("Ce contenu a d�j� �t� ins�r�");
    } else {
        previewRepeaters.append( templateRepeaters.html() ).children(':last').hide().fadeIn(500);
        id_repeater ++;
        newRepeater = previewRepeaters.find("repeater").last();
        newRepeater.find("layout").not(':first').remove();
        newRepeater.attr("data-id", id_repeater);
        //if(newRepeater.attr("data-type") != "content" && newRepeater.attr("data-type") != "user") {
            repeaterData[id_repeater] = new Object();
        //}
        
        repeaterAddHeader(newRepeater);
    }     

    $('#stRepeater option[value=0]').attr("selected", "selected");
	
	sizeIFrame();
	return newRepeater;
}

// Ajout des fonctions de modification d'un repeater
function repeaterAddHeader(repeater) {
    var id          = repeater.attr('data-id');
    var displayEdit = '<div class="repeaterEdit"></div>';
    displayEdit += '<div class="repeaterBTEdit">';
    displayEdit += '<a class="repeaterEdit">Modifier</a>';
    displayEdit += '<a class="repeaterDuplicate">Dupliquer</a>';
    displayEdit += '<a class="repeaterDelete">Supprimer</a>';
    displayEdit += '</div>';

    repeater.find(".repeaterEdit, .repeaterBTEdit").remove();
    repeater.prepend(displayEdit);    
    
    repeaterInitChanges(id);
}

// Initialisation des actions pour les fonctions de modification d'un repeater
function repeaterInitChanges(id) {
    //alert(id);
    var repeater    = $preview.find("repeater[data-id=" + id + "]"); 
    var repeaters   = $preview.find("repeaters[data-ref=" + repeater.attr('data-ref') + "]"); 
    
    repeater.find("div.repeaterEdit").css({ opacity: 0 });
    repeater.hover( function() {
         $(this).find('a.repeaterEdit').fadeIn(200);
        if(repeaters.attr('data-multiple')) $(this).find('a.repeaterDuplicate').fadeIn(200);
        $(this).find('a.repeaterDelete').fadeIn(200);
        $(this).find("div.repeaterEdit").css({ opacity: 0.2 });
    }, function() {
        $(this).find('a.repeaterEdit').fadeOut(200);
        if(repeaters.attr('data-multiple')) $(this).find('a.repeaterDuplicate').fadeOut(200);
        $(this).find('a.repeaterDelete').fadeOut(200);
        $(this).find("div.repeaterEdit:not(.active)").css({ opacity: 0 });
    });
    // Si clic sur bouton Modifier
    repeater.find('.repeaterEdit:not(.repeaterDuplicate)').click( function() {
    	$preview.find("repeater").removeClass('active');
    	$preview.find(".repeaterEdit").removeClass('active');
    	repeater.find("div.repeaterEdit").addClass('active');
    	repeater.addClass('active');
        repeaterEdit(id);
    });
    // Si clic sur bouton Dupliquer
    repeater.find('.repeaterDuplicate').click( function() {
        repeaterDuplicate(id);
    });
    // Si clic sur bouton Supprimer
    repeater.find('.repeaterDelete').click( function() {
        repeaterRemove(id);
    });
    
	sizeIFrame();
}

// Modification des champs du repeater
function repeaterDuplicate (id) {
    var repeater = $preview.find("repeater[data-id=" + id + "]");
    id_repeater ++;
    repeater.clone(true).insertAfter(repeater).attr("data-id", id_repeater);
    
	sizeIFrame();	
}

// Changement de mise en page d'un repeaters
function layoutChange (id, ref) { 
    var repeater            = $preview.find("repeater[data-id=" + id + "]");
    var templateRepeaters   = $template.find("repeater[data-ref=" + repeater.attr("data-ref") + "]");
    repeater.find("layout").html(templateRepeaters.find("layout[data-ref=" + ref + "]").html());
    if(repeater.attr("data-type") == "content") repeaterApplyContent(id, 'layoutChange');
    else if(repeater.attr("data-type") == "user") repeaterApplyUser(id, 'layoutChange');
    else repeaterApplyData(id, 'layoutChange');    
	sizeIFrame();
}

// Modification des champs du repeater
function repeaterEdit (id) {
    var edit = $("#edit");

    var repeater = $preview.find("repeater[data-id="+ id + "]"); 
    
    var templateRepeaters = $template.find("repeater[data-ref=" + repeater.attr("data-ref") + "]");
    edit.html("");
    edit.stop().fadeIn(150);
    
    
    var displayEdit = "";
    
    displayEdit += '<div class="btClose">Fermer</div>';
    
    displayEdit += 'Mise en forme : <select class="stLayout"></select><br clear="both">';
    
    //displayEdit += '<div class="repeaterUpdate" data-id="'+ id + '">Modifier les champs</div>';
    if(repeater.attr("data-type") == "content") {
        //displayEdit += '<div class="repeaterSelect" data-id="'+ id + '">S�lectionner un contenu</div>';
        
        var id_content = "";
        if(repeater.attr("data-id_content")) id_content = repeater.attr("data-id_content");
        displayEdit += "<p><b>S�lectionner un contenu</b></p><hr>";
        displayEdit += 'ID : <input type="text" id="select_id_content" value="' + id_content + '">';
        displayEdit += '<br />Chercher et charger un contenu : <input type="text" id="search_content"><div id="results"></div>';
        displayEdit += '<div class="btApply">Charger le contenu</div>';
        
        
        
        
    }else if(repeater.attr("data-type") == "user") {
        displayEdit += '<div class="repeaterSelect" data-id="'+ id + '">S�lectionner un utlisateur</div>';
    }
    
    //displayEdit += '<br clear="both"><div class="repeaterUp" data-id="'+ id + '">Monter</div>';
    
    //displayEdit += '<div class="repeaterDown" data-id="'+ id + '">Descendre</div>';
    
    displayEdit += '<br clear="both"><p><b>Modifier les champs</b></p><hr>';  
        
    edit.html(displayEdit);
    
    
        $("#search_content").bind('change keydown keyup',function() {
        	//alert('ok' + $(this).val());
        	
    		var repeater            = $preview.find("repeater[data-id=" + id + "]");
    		var templateRepeaters   = $template.find("repeater[data-ref=" + repeater.attr("data-ref") + "]");
    		var id_type = repeater.find("layout[data-ref=" + $('.stLayout').val() + "]").attr('data-id_type');
		    var request = $.ajax({
		        url: 'helper/content-picker',
		        data: {
		            q: $(this).val(),
		            id_type: id_type
		        }
		    });
		    request.done(function(data) {
		        $('#results').html(data);
		    });
        });
    
    if(repeater.attr("data-type") == "content") {
        $(".btApply").click( function() {
        	
            repeater.attr("data-id_content", $('#select_id_content').val());
            //alert($('#select_id_content').val());
            repeaterApplyContent(id);
            repeaterEdit(id); 
        });
    }
    
    // Select layout
    templateRepeaters.find("layout").each( function(e) {
        edit.find(".stLayout").append(new Option($(this).attr('data-name'), $(this).attr('data-ref'), false, false));
    });
    
    // Affichage du formulaire de modification des champs
    repeater.find("item").each( function(e) {
        
        var ref     = $(this).attr('data-ref');
        var rich    = $(this).attr('data-richtext');
        var type    = $(this).attr('data-type');
        var name    = $(this).attr('data-name');
        var value   = $(this).html();
        if(ref) {
            edit.append("<b>" + name + "</b><br>");
            if(type == "singleline") {
                edit.append('<input data-ref="' + ref + '" type="text" value="' + value + '">');
            }
            if(type == "multiline") {
                edit.append('<textarea data-ref="' + ref + '" id="' + ref + '" data-richtext="' + rich + '">' + value + '</textarea>');                
            }
            edit.append("<hr>");
        }
    });      
    edit.append('<div class="btApply">Enregistrer</div>');
    $(".btApply").click( function() {
        repeaterApplyData(id);
    });
    edit.find("textarea").each( function(e) {
        if($(this).attr("data-richtext") == "1") {
            $(this).tinymce({
                width : "350",
                // General options
                theme : "advanced",
                plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

                // Theme options
                theme_advanced_buttons1 : "mediapicker,bold,italic,underline,|,link,unlink,|,pasteword",
                theme_advanced_toolbar_location : "top",
                theme_advanced_toolbar_align : "left",
                theme_advanced_statusbar_location : "bottom",
                theme_advanced_resizing : true,
                
                invalid_elements : "p",
                
				setup : function(ed) {
				    ed.addButton('mediapicker', {
				        title : 'Ins�rer des images',
				        image : '/admin/core/ui/img/_img/myb.gif',
				        onclick : function() {
							mediaPicker(ed.id, 'mce');
				        }
				    });
				}
            });
        }
    }); 
    $(".btClose").click( function() {    	
    	repeater.find("div.repeaterEdit").removeClass('active');
        repeater.find("div.repeaterEdit").css({ opacity: 0 });
        edit.fadeOut(150);
        edit.html("");        
    	$preview.find("repeater").removeClass('active');       
    });
    edit.find(".stLayout").change( function(e) {
        layoutChange(id, $(this).val());
    });
    edit.find('.repeaterSelect').click( function() {
        repeaterSelect(id);
    });
    edit.find('.repeaterRemove').click( function() {
        repeaterRemove(id);
    });
    /*edit.find('.repeaterUp').click( function() {
        repeaterMove(id, 'up');
    });
    edit.find('.repeaterDown').click( function() {
        repeaterMove(id, 'down');
    });*/
    

    
    // Ajout des formulaires de modification des champs
    
    //alert(el);
}

function editClose() {
    var edit = $("#edit");
    edit.stop().fadeOut(150);
    edit.html("");	
}
// Selection d'un contenu ou utilisateur
function repeaterSelect (id) {
    var edit = $("#edit");
    edit.html("");
    edit.stop().fadeIn(150);
    
    var repeater = $preview.find("repeater[data-id="+ id + "]");
    
    edit.html('<div class="btClose">Fermer</div>');
        
    if(repeater.attr("data-type") == "content") {
        
        var id_content = "";
        if(repeater.attr("data-id_content")) id_content = repeater.attr("data-id_content");
        edit.append("<p><b>S�lectionner un contenu</b></p><hr>");
        edit.append('ID : <input type="text" value="' + id_content + '" id="select_id_content">');
        edit.append('<div class="btApply">Charger le contenu</div>');
        
        $(".btApply").click( function() {
            repeaterApplyContent(id); 
        });
        
        $("#select_id_content").change( function() {
        	alert('ok' + $(this).val());
            //repeaterApplyContent(id); 
        });
        
        
    }else if(repeater.attr("data-type") == "user") {
        edit.html("<p><b>S�lectionner un utlisateur</b></p><hr>");
        
    }
    $(".btClose").click( function() {
		editClose();     
    });
}

// Application des champs saisis pour le repeater
function repeaterApplyData(id, action) {
    var repeater = $preview.find("repeater[data-id="+ id + "]");
    if(action == 'layoutChange') {
        repeater.find("layout item").each( function(e) {
            var ref     = $(this).attr("data-ref");
            var value   = repeaterData[id][ref];
            $(this).html(value);         
        });
        
    }else {
        $("#edit input, #edit textarea").each( function(e) {
            var ref     = $(this).attr("data-ref");    
            var value   = $(this).val();
            repeater.find("item[data-ref=" + ref + "]").html(value);
            repeaterData[id][ref] = value;
        });   
    }
    
	sizeIFrame();
}

// Application du contenu selectionne pour le repeater
function repeaterApplyContent(id, action) {
    var edit = $("#edit");
    var repeater = $preview.find("repeater[data-id="+ id + "]");
        
    var id_content = "";

    if(repeater.attr("data-id_content") > 0) id_content = repeater.attr("data-id_content");
    if(action != 'layoutChange' && action != 'load') id_content = edit.find("input").val();
    $.post('helper/designer-content', { id_content: id_content }, function(data) {
        if(data != 0) {
            repeater.find("item").each( function(e) {
                var ref     = $(this).attr("data-ref");
                var value   = "";
                if(action == 'layoutChange' && repeaterData[id][ref]) {
                    value = repeaterData[id][ref];
                } else {
                    value = new Object();
                    value = eval("data."+$(this).attr("data-fieldKey"));
                }
                $(this).html(value);
            });
            repeater.attr("data-id_content", id_content);
        }
        repeater.find("item").each( function(e) {
            var item        = $(this);
            var ref         = $(this).attr("data-ref");
            var url         = $(this).attr("data-url");
            if(action == 'layoutChange' && repeaterData[id][ref]) {
            } else {
                if(url) {
                    $.post(url, { id_content: id_content }, function(dataitem) {
                        if(dataitem != '') item.html(dataitem);
                    });                         
                }  
            } 
        });
    }, 'json');
    
	sizeIFrame();
}

// Application du User selectionne pour le repeater
function repeaterApplyUser(id, action) {

    var edit = $("#edit");
    var repeater = $preview.find("repeater[data-id="+ id + "]");
        
    var id_user = "";

    if(repeater.attr("data-id_user")) id_user = repeater.attr("data-id_user");
    if(action != 'layoutChange') id_user = edit.find("input").val();
    $.post('helper/designer-user', { id_user: id_user }, function(data) {
        if(data != 0) {
            repeater.find("item").each( function(e) {
                var item  = $(this);
                var value = new Object();
                value = eval("data."+$(this).attr("data-fieldKey"));
                item.html(value);
            });
            repeater.attr("data-id_user", id_user);
        }
        repeater.find("item").each( function(e) {
            var item        = $(this);
            var url         = $(this).attr("data-url");
            if(url) {
                $.post(url, { id_user: id_user }, function(dataitem) {
                    if(dataitem != '') item.html(dataitem);
                });                         
            }
        });
    }, 'json');
    
	sizeIFrame();
}


// Suppression d'repeater
function repeaterRemove (id) {
    var repeater = $preview.find("repeater[data-id="+ id + "]");
    if(confirm("Etes-vous s�r de vouloir supprimer ce bloc ?")) {
        repeater.fadeOut(200, function() {$(this).remove()});
    	$("#edit").fadeOut(150);
		sizeIFrame();  
    }  
}


// Hauteur automatique iframe
function sizeIFrame() {
	//$("#preview").height($("#preview").contents().find("html").outerHeight() + 100);
}