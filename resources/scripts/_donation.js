jQuery(function($){
    $('input#cc_num').keypress(function(){
        $(this).payment('formatCardNumber');
    });

    $('input#expiration').keypress(function(){
        $(this).payment('formatCardExpiry');
    });

    $('input#cvc').keypress(function(){
        $(this).payment('formatCardCVC');
    });

    $('.anet-donation').submit(function(e){
        e.preventDefault();

        // Clear any previous warnings
        $('.input-group').removeClass('has-error has-feedback');
        $('.form-control-feedback').remove();
        $('.modal-footer').empty();

        // Gather required selectors
        var $amount = $('#amount'),
            $ccNum = $('#cc_num'),
            $expiration = $('#expiration'),
            $cvc = $('#cvc');

        // Submit payment information and handle response
        $.post(
            psrm.ajaxurl,
            {
                action: 'process_donation',
                amount: $amount.val(),
                cc_num: $ccNum.val(),
                expire_date: $expiration.val(),
                cvc: $cvc.val(),
                email: $('#email').val(),
            },
            function(data){
                var button = $('input[type="submit"][clicked="true"]');
                data = JSON.parse(data);
                if(data.success) {
                    button.val('Success!');
                    $('.modal-footer').html(data.message + data.analytics);
                } else {
                    var $errorIcon = $('<span class="fa fa-times form-control-feedback" aria-hidden="true"></span>');
                    switch (data.responseReasonCode) {
                        case '5':
                            insertError($amount, $errorIcon, 1);
                            break;
                        case '6':
                            insertError($ccNum, $errorIcon);
                            break;
                        case '7':
                            insertError($expiration, $errorIcon);
                            break;
                        case '8':
                            insertError($expiration, $errorIcon);
                            break;
                        case '17':
                            insertError($ccNum, $errorIcon);
                            break;
                        case '28':
                            insertError($ccNum, $errorIcon);
                            break;
                        case '37':
                            insertError($ccNum, $errorIcon);
                            break;
                        case '44':
                            insertError($cvc, $errorIcon);
                            break;
                        case '49':
                            insertError($amount, $errorIcon, 1);
                            break;
                        case '78':
                            insertError($cvc, $errorIcon);
                            break;
                    }
                    button.val('Make Donation');
                    button.prop('disabled', false);
                    $('.modal-footer').html(data.responseText);
                }
            }
        );
    });

    function insertError(element, errorIcon, amount){
        amount = typeof amount !== 'undefined';
        element.parent().addClass('has-error has-feedback');
        errorIcon.insertAfter(element);
        amount ? errorIcon.css('right', '50px') : '';
    }
});