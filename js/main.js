;(function($){
    $.fn.extend({
        donetyping: function(callback,timeout){
            timeout = timeout || 1e3; // 1 second default timeout
            var timeoutReference,
                doneTyping = function(el){
                    if (!timeoutReference) return;
                    timeoutReference = null;
                    callback.call(el);
                };
            return this.each(function(i,el){
                var $el = $(el);
                // Chrome Fix (Use keyup over keypress to detect backspace)
                // thank you @palerdot
                $el.is(':input') && $el.on('keyup keypress paste',function(e){
                    // This catches the backspace and DEL button in chrome, but also prevents
                    // the event from triggering too preemptively. Without this line,
                    // using tab/shift+tab will make the focused element fire the callback.
                    if (e.type=='keyup' && !([8,46].includes(e.keyCode))){return;}

                    // Check if timeout has been set. If it has, "reset" the clock and
                    // start over again.
                    if (timeoutReference) clearTimeout(timeoutReference);
                    timeoutReference = setTimeout(function(){
                        // if we made it here, our timeout has elapsed. Fire the
                        // callback
                        doneTyping(el);
                    }, timeout);
                }).on('blur',function(){
                    // If we can, fire the event since we're leaving the field
                    doneTyping(el);
                });
            });
        }
    });
})(jQuery);

jQuery(document).ready(function () {
    window.update_pendant = function () {

        //https://staging.donjjewellery.com/wp-content/plugins/dynamic-pendants/dynamic-image.php

        jQuery.ajax({
            'url' : window.dynamicpendant.ajax,
            'method' : 'POST',
            'dataType' : 'html',
            'data' : {
                'action' : 'dynamic_pendant_image',
                'name' : jQuery('#dynamic_name').val(),
                'font' : jQuery('[name="dynamic_font"]').val()
            },
            'beforeSend' : function () {
                jQuery('#dynamic_pendant').css({'display':'none'});
                jQuery('.dynamic_loading').css({'display':'inherit'});
            },
            'complete' : function(data) {
                jQuery('#dynamic_pendant').attr('src',`data:image/png;base64,${data.responseText}`).css({'display':'inherit'});
                jQuery('.dynamic_loading').css({'display':'none'});
            }
        });
    }

    jQuery('#dynamic_name').donetyping(function (){
        window.update_pendant();
    });
    window.update_pendant();
});