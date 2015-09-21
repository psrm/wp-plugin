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
                console.log( token );
                $( '.donation-modal' ).modal( 'show' );
                $( '.modal-body' ).text( 'Your information has been validated. Now processing your donation. Please wait.' );

                var args = {
                        action: 'process_donation',
                        amount: $( '.donation_amount_form:checked' ).val(),
                        stripeToken: token.id,
                        email: token.email
                    },
                    customAmount = $( '#custom_amount' ).val();

                if ( customAmount ) {
                    args.customAmount = customAmount;
                }

                // Set delay for front end ux.
                setTimeout( $.post(
                    psrm.ajaxurl,
                    args,
                    function ( data ) {
                        var modal_body = $( '.modal-body' );
                        modal_body.empty();
                        data = JSON.parse( data );
                        if ( data.success ) {
                            modal_body.text( data.message );
                            $( '.donation-analytics' ).html( data.analytics );
                        } else {
                            modal_body.text( data.message );
                            modal_body.append( data.responseText );
                        }
                    }
                ), 2000 );
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