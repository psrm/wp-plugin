jQuery( function ( $ ) {
    $( '.donation_amount_form' ).change( function () {
        if ( $( this ).val() == 'custom' ) {
            $( '.custom-donation-group' ).removeClass( 'hidden' );
        } else {
            $( '.custom-donation-group' ).addClass( 'hidden' );
        }
    } );

    if ( typeof StripeCheckout !== 'undefined' ) {

        var handler = StripeCheckout.configure( {
            key: psrm.stripe_pk,
            image: psrm.logo,
            locale: 'auto',
            token: function ( token ) {
                var args = {
                        action: 'process_donation',
                        amount: $( '.donation_amount_form:checked' ).val(),
                        fund: $( '.donation_fund' ).val(),
                        stripeToken: token.id,
                        email: token.email
                    },
                    customAmount = parseInt($('#custom_amount').val(), 10);

                if ( customAmount ) {
                    args.customAmount = customAmount;
                }

                $('.donation-form').html('<img src="https://www.psrm.org/wp-content/uploads/2017/12/loading_spinner.gif" height="50" width="50" alt="loading..."/>');
                $('.donation-button').hide();

                $.post(
                    psrm.ajaxurl,
                    args,
                    function ( data ) {
                        $('.donation-form').html(data);
                    }
                );
            }
        } );
    }

    $( '.donation-button' ).on( 'click', function ( e ) {
        var donation_form = $( '.donation_amount_form:checked' ),
            donation_button_error = $( '.donate-button-error' );

        donation_button_error.empty();

        if ( typeof donation_form.val() !== 'undefined' ) {
            if ( donation_form.val() == 'custom' ) {
                var donation_amount = $( '#custom_amount' ).val();
                var $customAmount = $('#custom_amount');
                donation_amount = parseInt($customAmount.val(), 10);

                if (donation_amount < parseInt(psrm.donation_amount_floor, 10)) {
                    donation_button_error.text('Donation amount must be at least $' + psrm.donation_amount_floor);
                    return;
                }

                if (donation_amount !== parseFloat($customAmount.val())) {
                    donation_button_error.text('Amount must be in whole dollars.');
                    return;
                }
            } else {
                var donation_amount = donation_form.attr( 'data-amount' );
            }
            // Open Checkout with further options
            handler.open( {
                name: psrm.name,
                description: 'Donate $' + donation_amount,
                zipCode: true,
                billingAddress: true,
                bitcoin: true,
                amount: donation_amount * 100
            } );
        } else {
            donation_button_error.text( 'Select a donation amount first.' );
        }
        e.preventDefault();
    } );


    // Close Checkout on page navigation
    $( window ).on( 'popstate', function () {
        handler.close();
    } );
} );