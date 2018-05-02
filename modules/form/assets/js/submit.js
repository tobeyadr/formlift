
function formliftSubmitV2(formObject)
{
    if ( typeof formlift_ajax_object === 'undefined' ){
        alert( 'Ajax Url is undefined.' );
        return false;
    }

    var formId = jQuery(formObject).attr('data-id');
    var loader = jQuery("#wait-"+formId);
    loader.css('display', 'inline-block');
    var successMsg = jQuery("#success-"+formId);
    successMsg.css('display', 'none');
    var errorMsg = jQuery("#error-"+formId);
    errorMsg.css('display', 'none');
    var formData = new FormData(formObject);
    var request = new XMLHttpRequest();
    request.open("POST", formlift_ajax_object.ajax_url, true);

    request.onreadystatechange = function() {//Call a function when the state changes.
        if( request.readyState === XMLHttpRequest.DONE && request.status === 200 ){
            //var response = request.responseText;
            loader.css('display', 'none');
            jQuery('.formlift-error-response').attr('class', 'formlift-error-response formlift-no-error');

            try {
                var response = JSON.parse( request.responseText );
            } catch ( e ) {
                alert( request.responseText );
            }

            if ( typeof response['url'] !== "undefined" ){
                successMsg.css('display', 'inline-block');
                /* create the XID field */
                if ( typeof response['xid'] !== "undefined" ){
                    var xid = document.createElement("INPUT");
                    xid.type = 'hidden';
                    xid.name = 'inf_form_xid';
                    xid.value = response['xid'];
                    formObject.appendChild(xid);
                }
                /* set the action of the form with the returned URL and submit it*/
                jQuery(formObject).attr('action', response['url']);
                jQuery(formObject).attr('onsubmit', '');
                jQuery(".remove-on-submit").attr('disabled','disabled');
                formObject.submit()
                
            } else if ( typeof response['msg'] !== "undefined"  ){
                console.log( response['msg'] );
                successMsg.css('display', 'inline-block');
            } else {
                /* is it errors? */
                errorMsg.css('display', 'inline-block');
                //check to see if there is recaptcha on the page.
                if(document.getElementsByClassName('g-recaptcha').length > 0){
                    grecaptcha.reset();
                }
                for ( var id in response ) {
                    // check if the property/key is defined in the object itself, not in parent
                    if (response.hasOwnProperty(id)) {     
                        /* add to error box in form */
                        try{
                            var node = document.getElementById( 'error-' + id + '-' + formId );
                            node.setAttribute( 'class', "formlift-error-response" );
                            node.innerHTML = response[id];
                        } catch ( err ) {
                            console.log( response[id] );
                            alert(response[id]);
                        }
                    }
                }
            }
        } else if (request.readyState === XMLHttpRequest.DONE && request.status !== 200 ) {
            loader.css('display', 'none');
            alert( "Please contact your system administrator. This form is unable to function due to restrictions placed on your wp-ajax.php file or another unknown error." );
        }
    };

    request.send( formData );

    return false;
}