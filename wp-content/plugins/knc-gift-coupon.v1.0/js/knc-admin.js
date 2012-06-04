//var new_element_number = 0;
function add_form_field() {
  time = new Date();

  //new_element_number += 1;
  new_element_number = time.getTime();
  new_element_id = "form_id_"+new_element_number;
  
  new_element_contents = "";
  new_element_contents += "<td class='namecol'><input type='text' name='knc_gc_name["+new_element_number+"]' value='' /></td>\n\r";
  new_element_contents += "<td class='valcol'><input type='text' name='knc_gc_validity["+new_element_number+"]' value='' /></td>\n\r";
  new_element_contents += "<td class='dmycol'><select name='knc_gc_dmy["+new_element_number+"]'>"+HTML_FORM_FIELD_TYPES+"</select></td>\n\r"; 
  new_element_contents += "<td class='trash_td'><a class='image_link' href='#' onclick='return remove_new_form_field(\""+new_element_id+"\");'><img src='"+KNC_GC_URL+"/images/trash.gif' alt='trash_can' title='delete' /></a></td>\n\r";

  new_element = document.createElement('tr');
  new_element.id = new_element_id;
  document.getElementById("coupon_name_list_body").appendChild(new_element);

  jQuery('#'+new_element_id).append(new_element_contents);
  jQuery('#'+new_element_id).addClass('coupon_fields');
  
  return false;
}

function remove_new_form_field(id) {
  element_count = document.getElementById("coupon_name_list_body").childNodes.length;
  if(element_count > 1) {
    target_element = document.getElementById(id);
    document.getElementById("coupon_name_list_body").removeChild(target_element);
  }
  return false;
}

function knc_tiny_mce(){
tinyMCE.init({
	mode : "specific_textareas", 
	editor_selector : "mail_message", 
	width : "100%", 
	theme : "advanced", 
	skin : "wp_theme", 
	theme_advanced_buttons1 : "bold,italic,strikethrough,|,bullist,numlist,blockquote,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,spellchecker,fullscreen,wp_adv", 
	theme_advanced_buttons2 : "formatselect,underline,justifyfull,forecolor,|,pastetext,pasteword,removeformat,|,media,charmap,|,outdent,indent,|,undo,redo,wp_help", 
	theme_advanced_buttons3 : "", 
	theme_advanced_buttons4 : "", 
	language : "en",
	spellchecker_languages : "+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv", 
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	theme_advanced_resize_horizontal : false,
	relative_urls : "", 
	remove_script_host : "", 
	convert_urls : "", 
	apply_source_formatting : "", 
	remove_linebreaks : "1", 
	gecko_spellcheck : "1", 
	entities : "38,amp,60,lt,62,gt", 
	accessibility_focus : "1", 
	tabfocus_elements : "major-publishing-actions", 
	media_strict : "", 
	paste_remove_styles : "1", 
	paste_remove_spans : "1", 
	paste_strip_class_attributes : "all", 
	wpeditimage_disable_captions : "", 
	plugins : "safari,inlinepopups,spellchecker,paste,wordpress,media,fullscreen,wpeditimage,wpgallery,tabfocus"
});
}