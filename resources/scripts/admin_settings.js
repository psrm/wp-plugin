jQuery( document ).ready( function ( $ ) {
    var $editable = document.getElementById( 'editable' );
    if ( $editable ) {

        var editableList = Sortable.create( $editable, {
            animation: 150,
            filter: '.js-remove',
            handle: '.drag-handle',
            onFilter: function ( evt ) {
                var el = editableList.closest( evt.item ); // get dragged item
                el.parentNode.removeChild( el );
            }
        } );


        $( '#add_amount_button' ).click( function ( e ) {
            e.preventDefault();
            var fieldName = $( '#add_amount_name' ).val();
            var amountAdded = $( '#add_amount' ).val();
            $( '#editable' ).append( '<li><input type="hidden" name="' + fieldName + '[]" value="' + amountAdded + '"><span class="drag-handle">&#9776;</span>' + amountAdded + ' <i class="js-remove">✖</i></li>' );
            $( '#add_amount' ).val( '' );
        } );
    }

} );