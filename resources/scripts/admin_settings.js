jQuery( document ).ready( function ( $ ) {
    var $editable = document.getElementsByClassName( 'editable' );
    if ( $editable ) {
        for ( var i = 0; i < $editable.length; i ++ ) {
            var editableList = [];
            editableList[ i ] = Sortable.create( $editable[ i ], {
                animation: 150,
                filter: '.js-remove',
                handle: '.drag-handle',
                onFilter: function ( evt ) {
                    var el = editableList[ i ].closest( evt.item ); // get dragged item
                    el.parentNode.removeChild( el );
                }
            } );
        }

        $( '#add_amount_button' ).click( function ( e ) {
            e.preventDefault();
            var fieldName = $( '#add_amount_name' ).val();
            var amountAdded = $( '#add_amount' ).val();
            $( '.donation-amounts' ).append( '<li><input type="hidden" name="' + fieldName + '[]" value="' + amountAdded + '"><span class="drag-handle">&#9776;</span>' + amountAdded + ' <i class="js-remove">✖</i></li>' );
            $( '#add_amount' ).val( '' );
        } );

        $( '#add_email_button' ).click( function ( e ) {
            e.preventDefault();
            var fieldName = $( '#add_email_name' ).val();
            var emailAdded = $( '#add_email' ).val();
            $( '.email-successful-donation' ).append( '<li><input type="hidden" name="' + fieldName + '[]" value="' + emailAdded + '"><span class="drag-handle">&#9776;</span>' + emailAdded + ' <i class="js-remove">✖</i></li>' );
            $( '#add_email' ).val( '' );
        } );
    }

} );