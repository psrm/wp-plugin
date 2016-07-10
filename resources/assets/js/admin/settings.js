jQuery( document ).ready( function ( $ ) {
    var $editable_amounts = document.getElementById( 'editable_amounts' );
    if ( $editable_amounts ) {
        var editableAmountList = Sortable.create( $editable_amounts, {
            animation: 150,
            filter: '.js-remove-amounts',
            handle: '.drag-handle-amounts',
            onFilter: function ( evt ) {
                var el = editableAmountList.closest( evt.item ); // get dragged item
                el.parentNode.removeChild( el );
            }
        } );

        var $editable_funds = document.getElementById( 'editable_funds' );
        if ( $editable_funds ) {
            var editableFundList = Sortable.create( $editable_funds, {
                animation: 150,
                filter: '.js-remove-funds',
                handle: '.drag-handle-funds',
                onFilter: function ( evt ) {
                    var el = editableFundList.closest( evt.item );
                    el.parentNode.removeChild( el );
                }
            } );
        }

        $( '#add_amount_button' ).click( function ( e ) {
            e.preventDefault();
            var fieldName = $( '#add_amount_name' ).val();
            var amountAdded = $( '#add_amount' ).val();
            $( '.donation-amounts' ).append( '<li><input type="hidden" name="' + fieldName + '[]" value="' + amountAdded + '"><span class="drag-handle-amounts">&#9776;</span>' + amountAdded + ' <i class="js-remove-amount">✖</i></li>' );
            $( '#add_amount' ).val( '' );
        } );

        $( '#add_fund_button' ).click( function ( e ) {
            e.preventDefault();
            var fieldName = $( '#add_fund_name' ).val();
            var fundAdded = $( '#add_fund' ).val();
            $( '.donation-funds' ).append( '<li><input type="hidden" name="' + fieldName + '[]" value="' + fundAdded + '"><span class="drag-handle-funds">&#9776;</span>' + fundAdded + ' <i class="js-remove-funds">✖</i></li>' );
            $( '#add_fund' ).val( '' );
        } );

        $( '#add_email_button' ).click( function ( e ) {
            e.preventDefault();
            var fieldName = $( '#add_email_name' ).val();
            var emailAdded = $( '#add_email' ).val();
            $( '.email-successful-donation' ).append( '<li><input type="hidden" name="' + fieldName + '[]" value="' + emailAdded + '">' + emailAdded + ' <i class="js-remove-email">✖</i></li>' );
            $( '#add_email' ).val( '' );
        } );

        $( '.js-remove-email' ).click( function ( e ) {
            e.preventDefault();
            $( this ).parent().remove();
        } );
    }

} );