/**
 * Simple Jquery Form Builder (SJFB)
 * Copyright (c) 2015 Brandon Hoover, Hoover Web Development LLC (http://bhoover.com)
 * http://bhoover.com/simple-jquery-form-builder/
 * SJFB may be freely distributed under the included MIT license (license.txt).
 */

$(function(){

    //If loading a saved form from your database, put the ID here. Example id is "1".
    var formID = '1';

    //Adds new field with animation
    $("#add-field a").click(function() {
        event.preventDefault();
        $(addField($(this).data('type'))).appendTo('#form-fields').hide().slideDown('fast');
        $('#form-fields').sortable();
    });

    //Removes fields and choices with animation
    $("#sjfb").on("click", ".delete", function() {
        if (confirm('Are you sure?')) {
            var $this = $(this);
            $this.parent().slideUp( "slow", function() {
                $this.parent().remove()
            });
        }
    });

    //Makes fields required
    $("#sjfb").on("click", ".toggle-required", function() {
        requiredField($(this));
    });

    //Makes choices selected
    $("#sjfb").on("click", ".toggle-selected", function() {
        selectedChoice($(this));
    });

    //Adds new choice to field with animation
    $("#sjfb").on("click", ".add-choice", function() {
        $(addChoice()).appendTo($(this).prev()).hide().slideDown('fast');
        $('.choices ul').sortable();
    });
		
    //Saving form
    $("#sjfb").submit(function(event) {
        event.preventDefault();

        //Loop through fields and save field data to array
        var fields = [];
        $('.field').each(function() {

            var $this = $(this);

            //field type
            var fieldType = $this.data('type');

            //field label
            var fieldLabel = $this.find('.field-label').val();

			//field name
            var fieldName = $this.find('.field-name').val();
			
            //field required
            var fieldReq = $this.hasClass('required') ? 1 : 0;

            //check if this field has choices
            if($this.find('.choices li').length >= 1) {

                var choices = [];

                $this.find('.choices li').each(function() {

                    var $thisChoice = $(this);

                    //choice label
                    var choiceLabel = $thisChoice.find('.choice-label').val();

                    //choice selected
                    var choiceSel = $thisChoice.hasClass('selected') ? 1 : 0;
					
					//choice file
					var choiceFile = $thisChoice.find('.choice-file1').val();
					
                    choices.push({
                        label: choiceLabel,
                        sel: choiceSel,
						file: choiceFile
                    });

                });
            }

            fields.push({
                type: fieldType,
                label: fieldLabel,
				name: fieldName,
                req: fieldReq,
                choices: choices
            });

        });

		var frontEndFormHTML = '';

        //Save form to database
        //Demo doesn't actually save. Download project files for save
		var token = document.getElementsByName("_token")[0].value;
        var data = JSON.stringify([{"name":"formID","value":formID},{"name":"formFields","value":fields}]);
		var form_name = document.getElementsByName("form_name")[0].value;
		var form_id = document.getElementsByName("form_id")[0].value;
        $.ajax({
            method: "POST",
            url: base_url+"/forms/create",
            data: "form_name="+form_name+"&data="+data+"&_token="+token+"&form_id="+form_id,
            dataType: 'json',
            success: function (msg) {
				var obj=msg;
				console.log(obj);
				if(obj.status=="1")
				{
					$('.alert_success').css('display','block');
					$("html, body").animate({ scrollTop: 0 }, "fast");
					setTimeout(function(){ location.replace(base_url+"/forms") }, 2000);
				}else{
					$('.alert_error').css('display','block');
					$('.form_name_error').html(obj.error);
					$("html, body").animate({ scrollTop: 0 }, "fast");
				}
                
            }
        });
    });

    //load saved form
    //loadForm(formID);

});

//Add field to builder
function addField(fieldType) {

    var hasRequired, hasChoices;
    var includeRequiredHTML = '';
    var includeChoicesHTML = '';

    switch (fieldType) {
        case 'text':
            hasRequired = true;
            hasChoices = false;
            break;
        case 'textarea':
            hasRequired = true;
            hasChoices = false;
            break;
        case 'select':
            hasRequired = true;
            hasChoices = true;
            break;
        case 'radio':
            hasRequired = true;
            hasChoices = true;
            break;
        case 'checkbox':
            hasRequired = true;
            hasChoices = true;
            break;
        case 'button':
            hasRequired = false;
            hasChoices = false;
            break;
		case 'slider':
            hasRequired = false;
            hasChoices = true;
            break;
    }

    if (hasRequired) {
        includeRequiredHTML = '' +
            '<label>Required? ' +
            '<input class="toggle-required" type="checkbox">' +
            '</label>'
    }

    if (hasChoices) {
		if(fieldType == 'slider'){
			includeChoicesHTML = '' +
            '<div class="choices">' +
            '<ul></ul>' +
            '<button type="button" class="add-choice">Add Slide</button>' +
            '</div>'
		}else{
        includeChoicesHTML = '' +
            '<div class="choices">' +
            '<ul></ul>' +
            '<button type="button" class="add-choice">Add Choice</button>' +
            '</div>'
		}
    }

    return '' +
        '<div class="field" data-type="' + fieldType + '">' +
        '<button type="button"  class="delete"><i class="glyphicon glyphicon-trash"></i></button>' +
        '<h3>' + fieldType + '</h3>' +
        '<label>Label:' +
        '<input type="text" class="field-label">' +
        '</label></br>' +
		'<label>Name:' +
        '<input type="text" class="field-name">' +
        '</label>' +
        includeRequiredHTML +
        includeChoicesHTML +
        '</div>'
}

//Make builder field required
function requiredField($this) {
    if (!$this.parents('.field').hasClass('required')) {
        //Field required
        $this.parents('.field').addClass('required');
        $this.attr('checked','checked');
    } else {
        //Field not required
        $this.parents('.field').removeClass('required');
        $this.removeAttr('checked');
    }
}

function selectedChoice($this) {
    if (! $this.parents('li').hasClass('selected')) {

        //Only checkboxes can have more than one item selected at a time
        //If this is not a checkbox group, unselect the choices before selecting
        if ($this.parents('.field').data('type') != 'checkbox') {
            $this.parents('.choices').find('li').removeClass('selected');
            $this.parents('.choices').find('.toggle-selected').not($this).removeAttr('checked');
        }

        //Make selected
        $this.parents('li').addClass('selected');
        $this.attr('checked','checked');

    } else {

        //Unselect
        $this.parents('li').removeClass('selected');
        $this.removeAttr('checked');

    }
}

//Builder HTML for select, radio, and checkbox choices
function addChoice(v='') {
	var file_length = $('.choice-file').length;
	var file_id = file_length+1;
	if(v['file']){
		var file_url = v['file'];
		var file = '<img width="50" id="choice_image_'+file_id+'" src="http://igen1/c2c/public/uploads/'+file_url+'">';
	}else {
		var file_url = '';
		var file = '';
	}
    return '' +
        '<li>' +
        '<label>Choice: ' +
        '<input type="text" class="choice-label">' +
		'<input type="hidden" class="choice-file1" id="file_name_'+file_id+'">' +
        '</label></br>' +
		'<label>File Upload: ' +
        '<input type="file" onchange="uploadfile('+file_id+')" id="file_'+file_id+'" class="choice-file">' +
        '</label>' +
		file +
        '<label>Selected? ' +
        '<input class="toggle-selected" type="checkbox">' +
        '</label>' +
        '<button type="button" class="delete"><i class="glyphicon glyphicon-trash"></i></button>' +
        '</li>'
}




if(page_condition == "page_forms_edit"){
	loadForm(form_id);
}
//Loads a saved form from your database into the builder
function loadForm(formID) {
    $.getJSON(base_url+"/forms/edit_form/" + formID, function(data) {
        if (data) {
            //go through each saved field object and render the builder
            $.each( data, function( k, v ) {
                //Add the field
                $(addField(v['type'])).appendTo('#form-fields').hide().slideDown('fast');
                var $currentField = $('#form-fields .field').last();

                //Add the label
                $currentField.find('.field-label').val(v['label']);
				
				//Add the name
                $currentField.find('.field-name').val(v['name']);  
				
                //Is it required?
                if (v['req']) {
                    requiredField($currentField.find('.toggle-required'));
                }

                //Any choices?
                if (v['choices']) {
				
                    $.each( v['choices'], function( k, v ) {
                        //add the choices
                        $currentField.find('.choices ul').append(addChoice(v));

                        //Add the label
                        $currentField.find('.choice-label').last().val(v['label']);
						
						//Add the file
						$currentField.find('.choice-file1').last().val(v['file']);
						/*  if(v['file']!=''){
							$('#choice_image_'+(k+1)).attr('src', 'http://igen1/c2c/public/uploads/'+v['file']);
						} */
						
                        //Is it selected?
                        if (v['sel']) {
                            selectedChoice($currentField.find('.toggle-selected').last());
                        }
                    });
                }

            });

            $('#form-fields').sortable();
            $('.choices ul').sortable();
        }
    });
}

function uploadfile(file_id){
	
	var token = document.getElementsByName("_token")[0].value;
	var form_data = new FormData();
	var gallery_image=$('#file_'+file_id).val();
	var file_data = $('#file_'+file_id).prop('files')[0];
	form_data.append('file', file_data);
	form_data.append('_token', token);
	
			$.ajax({ 
                    method: "POST",
					url: base_url+"/forms/uploadfile", 
                    data:form_data, 
					dataType: 'json',
                    contentType: false, 
                    processData: false, 
                    success: function (data, status) {
                           
						   $('#file_name_'+file_id).val(data.file_name);
                        },
                        error: function (data, status, e) {
                            alert(e);
                        }
                });
}
        