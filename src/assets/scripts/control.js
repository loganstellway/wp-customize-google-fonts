var googleFontScope = {
    /**
     * Get Google Font Data
     * 
     * @param {int} Optional - the index of the font
     * @return {object}
     */
    getData: function( id )
    {
        window.lsgooglefonts = window.lsgooglefonts || { data: { items: [] } };

        if ( !window.lsgooglefonts.initialized ) {
            window.lsgooglefonts.initialized = 1;

            window.lsgooglefonts.data.items.map( $.proxy( function( family, i ) {
                family.id = i;
                family.title = family.family;
                family.text = this.getTemplate( family );
                family.html = this.getTemplate( family, '2em' );
                return family;
            }, this ) );
        }
    
        return isNaN( id ) ? window.lsgooglefonts.data.items : window.lsgooglefonts.data.items[id] || {};
    },

    /**
     * Get Initial Font Values
     * 
     * @param {string} name 
     * @return {int}
     */
    getInitVals: function( val )
    {
        var variant = val.split( ':' );

        if ( variant.length == 2 ) {
            var f,
                v,
                family,
                data = this.getData();

            variant[0] = variant[0].replace( '+', ' ' );

            for ( f in data ) {
                if ( data[f].family == variant[0] ) {
                    family = data[f];

                    if ( family.variants ) {
                        for ( v in family.variants ) {
                            if ( this.encodeVariant( family.variants[v] ) == variant[1] ) {
                                return [f, v];
                            }
                        }
                    }
                    break;
                }
            }
        }

        return [];
    },

    /**
     * Get Paged Google Font Data
     * 
     * @param {int} count 
     * @param {int} page 
     * @return {object}
     */
    getDataPage: function( count, page ) {
        count = count || 10;
        page = page || 1;

        var i = ( page - 1 ) * count,
            data = this.getData(),
            page = [];

        for ( count; count >= 0; count-- ) {
            if (!data[i]) break;
            page.push( data[i] );
            i++;
        }

        return page;
    },

    /**
     * Get Font Weight
     * 
     * @param  {array}  variant data
     * @param  {int}    [optional] variant index
     * @return {string}
     */
    getWeight: function( variants, index ) {
        if ( ( isNaN( index ) || !variants[index] ) && variants.indexOf( 'regular' ) >= 0 ) {
            return 400;
        } else {
            var weight = variants[ index || 0 ].replace( /[A-z]/g, '' );
            return weight.length == 0 ? 400 : weight;
        }
    },

    /**
     * Get font style
     * 
     * @param {array}   variant data
     * @param {int}     [optional] variant index
     * @return {string}
     */
    getStyle: function( variants, index ) {
        if ( ( isNaN(index) || !variants[index] ) && variants.indexOf( 'regular' ) >= 0 ) {
            return 'normal';
        } else {
            return variants[ index || 0 ].indexOf( 'italic' ) >= 0 ? 'italic' : 'normal';
        }
    },

    /**
     * Template for Select2 Options
     * 
     * @param {object}  font data
     * @param {string}  [optional] font size
     * @param {int}     [optional] variant index
     * @return {string}
     */
    getTemplate: function( family, size, variant ) {
        return '<div style="font-family: ' + family.family + ';' + 
                            'font-weight: ' + this.getWeight( family.variants, variant ) + ';' +
                            'font-style: ' + this.getStyle( family.variants, variant ) + ';' +
                            ( size ? 'font-size: ' + size : '' ) + 
                '">' + family.family + '</div>';
    },

    /**
     * Encode Google Font Variant
     * 
     * @param  {string} variant
     * @return {string}
     */
    encodeVariant: function( variant ) {
        switch ( variant ) {
            case 'regular':
                variant = '400';
                break;
            case 'italic':
                variant = '400i';
                break;
        }

        return variant
                    .replace( 'regular', '400' )
                    .replace( 'italic', 'i' );
    },

    /**
     * Select Family
     * 
     * @param {object} container
     * @param {int}    font index
     * @param {int}    variant index
     */
    selectFamily: function( container, f, v ) {
        if ( isNaN( parseInt( f ) ) ) return;

        // Get active family
        var family = this.getData( f );

        // Remove old variant select
        container.find( '[data-role=variant]' ).remove();

        // If there are varaiants
        if ( family.variants && family.variants.length > 1 ) {
            var data = [];

            for ( var i = 0; i <= family.variants.length; i++ ) {
                data.push( {
                    id: i,
                    title: family.family,
                    text: this.getTemplate( family, null, i ),
                    html: this.getTemplate( family, '2em', i ),
                } );
            }
            
            // Add the variant select
            container.append( '<label data-role="variant"><span>Variant:</span><select></select></label>' );
            container.find( 'label[data-role=variant] select' )
                .select2( {
                    data: data,
                    escapeMarkup: function( d ) { return d; },
                    templateResult: function(d) { return d.html; },
                    templateSelection: function(d) { return d.text; }
                } )
                .on( 'change.select2', $.proxy( function( e ) {
                    this.selectVariant(
                        container,
                        family,
                        e.target.value
                    );
                }, this ) );

            if ( v && typeof family.variants[v] !== 'undefined' ) {
                container
                    .find( 'label[data-role=variant] select' )
                    .val( v )
                    .change();
            }
        } else {
            this.selectVariant(
                container,
                family,
                0
            );
        }
    },

    /**
     * Select variant
     * 
     * @param {object} container
     * @param {object} family
     * @param {int}    variant index
     */
    selectVariant: function( container, family, v ) {
        var url = [
            family.family.replace( / /g, '+' ),
            this.encodeVariant( family.variants[v] ) || null
        ];

        container
            .find( '[data-role=input]' )
            .val( url.join( ':' ) )
            .change();
    },

    /**
     * Init Google Font Select
     */
    init: function()
    {
        ( $.proxy( function( $ ) {
            $( '.google-fonts-select2-container' ).each( $.proxy( function( index, container ) {
                var container = $( container );

                // Check of the control has been initialized
                if ( !container.data( 'initialized' ) ) {
                    container.data( 'initialized', true );

                    // Create family select
                    container
                        .append( '<label data-role="family"><span>Family:</span><select></select></label>' );
                    container
                        .find( 'label[data-role=family] select' )
                        .select2( {
                            data: this.getData(),
                            escapeMarkup: function( d ) { return d; },
                            templateResult: function( d ) { return d.html; },
                            templateSelection: function( d ) { return d.text; }
                        } )
                        .on( 'change.select2', $.proxy( function( e ) {
                            // Create variant select if necessary
                            this.selectFamily(
                                container,
                                e.target.value
                            );

                            this.selectVariant(
                                container,
                                this.getData( e.target.value ),
                                0
                            );
                        }, this ) );

                    // Get initialized values
                    var data = this.getInitVals( container.find( '[data-role=input]' ).val() );
                    if ( data.length > 1 && this.getData( data[0] ) ) {
                        container
                            .find( 'label[data-role=family] select' )
                            .val( data[0] )
                            .change();

                        this.selectFamily(
                            container,
                            data[0],
                            data[1]
                        );
                    }
                }
            }, this ) );
        }, this )( jQuery ) );
    }
};


googleFontScope.init();
