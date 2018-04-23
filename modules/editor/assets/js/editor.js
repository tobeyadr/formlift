var FormLiftEditor = {
    newFieldHtml: null,
    newFieldOptions: null,
    fieldOptions: null,
    editor: null,
    form_id : ThisFormID,
    operatingId: null,
    operation: 'switch',
    optionId: null,
    reloadCallbacks: [],

    init: function(fieldOptions){
        /* all the form options */
        this.fieldOptions = fieldOptions;
        /* get editor element */
        this.editor = document.getElementById('formlift-field-editor');
        /* activate sortable and buttons */
        /* init sortable */
        jQuery( ".formlift-sortable-fields" ).sortable({
            revert: true,
            tolerance:'pointer',

            placeholder: "ui-state-highlight",
            start: function(e,ui){
                ui.placeholder.width( ui.item.width() );
                ui.placeholder.height( ui.item.height() );
            }
        });
        jQuery( ".formlift-sortable-fields" ).disableSelection();

        jQuery( ".add-custom-field" ).off();
        jQuery( ".add-custom-field" ).on("click", function(){
            var querystart = this.href.indexOf("#");
            var listArgs = this.href.substring(querystart+1);
            listArgs = listArgs.split('=');
            var type = listArgs[1];
            FormLiftEditor.addField( type );
            formliftLightBox.close();
        });
        this.reload();
    
    },

    reload: function(){

        /* init add options for radio/select */
        jQuery( ".formlift-option-add" ).off();
        jQuery( ".formlift-option-add" ).on("click", function(){
            FormLiftEditor.addOption( this.parentNode, this.parentNode.getAttribute('data-field-id'));
        });
        /* init delete options for radio/select */
        jQuery( ".formlift-option-delete" ).off();
        jQuery( ".formlift-option-delete").on("click", function(){
            FormLiftEditor.deleteOption(this);
        });
        /* init delete field*/
        jQuery( ".formlift-delete-field" ).off();
        jQuery( ".formlift-delete-field" ).on("click", function(){
            FormLiftEditor.deleteField(this.getAttribute('data-delete-id'));
        });
        /* switch field type */
        jQuery( ".switch-field-type" ).off();
        jQuery( ".switch-field-type" ).on("change", function(){
            FormLiftEditor.replaceField(this.getAttribute('data-change-id'), this.value);
        });
        /* init switch width */
        jQuery( ".formlift-switch-width" ).off();
        jQuery( ".formlift-switch-width" ).on("click", function(){
            FormLiftEditor.changeFieldWidth(this.getAttribute('data-change-id'), this.value);
        });

        for( var i = 0; i < this.reloadCallbacks.length; i++ ){
            this.reloadCallbacks[i]();
        }
    },

    getFieldHtml: function(){
        this.continue = false;
        var ajaxCall = jQuery.ajax({
            type : "post",
            url : ajaxurl,
            data : {action: "formlift_get_field_html", options: this.newFieldOptions, form_id: this.form_id },
            success: function(html)
            {
                var wrapper = document.createElement('div');
                wrapper.innerHTML = html;
                FormLiftEditor.newFieldHtml = wrapper.firstChild;
                if (FormLiftEditor.operation == 'switch')
                    FormLiftEditor.doFieldReplace();
                else if (FormLiftEditor.operation == 'add')
                    FormLiftEditor.doAddField();
            }
        });
    },

    replaceField: function(id, type){
        var newOptions = this.fieldOptions[id];
        this.operatingId = id;
        this.operation = 'switch';
        newOptions.type = type;
        newOptions.display = 'on';
        this.newFieldOptions = JSON.stringify( newOptions );
        this.getFieldHtml();
    },

    doFieldReplace: function(){
        var currentField = document.getElementById("field-box-"+this.operatingId);
        this.editor.replaceChild(this.newFieldHtml, currentField);
        formliftLightBox.reload();

        /* show in lightbox */
        var newField = document.getElementById(this.operatingId+'-content');
        document.getElementById('formliftPopUpContent').innerHTML = newField.innerHTML;
        /* load editor */

        var editors = jQuery( newField ).find('.wp-editor');
        for( var i = 0; i < editors.length;i++ ){
            wp.editor.initialize( editors[i].id, { tinymce: true, quicktags: true } );
        }

        newField.innerHTML = '';
        this.reload();
    },

    addField: function( type ){
        var newID = type + '_'+ Math.random().toString(36).substr(2, 16);
        this.operatingId = newID;
        this.operation = 'add';
        var fieldOptions = {
            id: newID,
            name: newID,
            type: type,
            display: 'on',
            options: {}
        };
        /* special case */

        this.fieldOptions[newID] = fieldOptions;
        this.newFieldOptions = JSON.stringify(fieldOptions);
        this.getFieldHtml();
    },

    doAddField: function(){
        /* add to editor */
        this.editor.insertBefore(this.newFieldHtml, this.editor.firstChild);
        this.reload();
        /* open formliftLightBox */
        var url = "#source_d="+this.operatingId+"-content";
        formliftLightBox.reload();
        formliftLightBox.init(this.operatingId, url);
        this.reload();
    },


    deleteField: function(id){

        var result = confirm("Are you sure you want to delete this field?");

        if ( result ){
            var e = document.getElementById(id);
            this.editor.removeChild(e);
        }
    },

    changeFieldWidth: function(id, newWidth){
        if ( newWidth == '1/1' )
            var className = 'formlift-col formlift-span_4_of_4';
        else if ( newWidth == '1/2')
            className = 'formlift-col formlift-span_1_of_2';
        else if ( newWidth == '1/3')
            className = 'formlift-col formlift-span_1_of_3';
        else if ( newWidth == '2/3')
            className = 'formlift-col formlift-span_2_of_3';
        else if ( newWidth == '1/4')
            className = 'formlift-col formlift-span_1_of_4';
        else if ( newWidth == '3/4')
            className = 'formlift-col formlift-span_3_of_4';
        document.getElementById(id).className = className;
    },

    deleteOption: function(e){
        e.parentNode.parentNode.removeChild(e.parentNode);
    },
    /* e is the option container, not the delete button in this case */
    addOption: function(e, field_id){
        if (typeof this.fieldOptions[field_id].options == 'undefined')
            this.fieldOptions[field_id].options = {};

        this.optionId = 'option_' + Math.random().toString(36).substr(2, 16);

        this.operatingId = field_id;
        /* create the div */
        var ajaxCall = jQuery.ajax({
            type : "post",
            url : ajaxurl,
            data : {
                action: "formlift_get_option_html",
                field_id: this.operatingId,
                option_id: this.optionId,
                form_id: this.form_id
            },
            success: function(html)
            {
                var wrapper = document.createElement('div');
                wrapper.innerHTML = html;
                var newOption = wrapper.firstChild;
                e.parentNode.insertBefore( newOption, e.nextSibling );
                FormLiftEditor.fieldOptions[FormLiftEditor.operatingId].options[FormLiftEditor.optionId] = {value:null, label:null};
                FormLiftEditor.reload();
            }
        });
    }
};

function copy_shortcode( id ){
    var short_code_input = jQuery(id);
    short_code_input.select();

    try {
        var successful = document.execCommand('copy');
    } catch (err) {
        console.log('unable to copy');
    }
}