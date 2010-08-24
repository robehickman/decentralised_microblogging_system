/*
 * Copyright 2010 Robert Hickman
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
 * implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Add exists method to jquery object
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
$.fn.exists = function () {
    return $(this).length !== 0;
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Generic form counter limiter
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function count_limit_input(input, display, submit, limit)
{
    var chars = $('#' + input).val().length
    
    var remaining = limit - chars
    $('#' + display).text(remaining)

    if(remaining < 0)
    {
        $('#' + submit).attr('disabled', true);
    }
    else
    {
        $('#' + submit).attr('disabled', false);
    }
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Counter wrappers for bio and message input boxes
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
function count_message()
{
    count_limit_input('message_input', 'chars_remaining', 'message_submit', 140)
}

//+++++++++++++++++++++++++++
function count_bio()
{
    count_limit_input('bio_input', 'bio_remaining', 'settings_submit', 160)
}

/*+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
 * Attach events
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++*/
$(document).ready( function() {
    if($('#message_input').exists())
        count_message();

    if($('#bio_input').exists())
        count_bio();

// It is necessary to bind to both keyup and keypress events, keyup
// provides single keypress feedback, keypress handles key holding
    $('#message_input').keyup(count_message)
    $('#message_input').keypress(count_message)

    $('#bio_input').keyup(count_bio)
    $('#bio_input').keypress(count_bio)
})
