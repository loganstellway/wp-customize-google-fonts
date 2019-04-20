<?php

namespace LoganStellway\GoogleFonts\Customize;

// Prevent direct access to script
defined( 'ABSPATH' ) or die();

if ( ! class_exists('\LoganStellway\GoogleFonts\Customize\Control') ) {
    class Control extends \WP_Customize_Control
    {
        public $type = 'google_fonts';
        private $_googleApiKey;
        protected $_googleFontData;
        protected $_googleFontFamilies;

        /**
         * Get Google Fonts Data
         * 
         * @return mixed
         */
        protected function getGoogleFontsData() {
            if ( !$this->_googleFontData ) {
                if ( $data = wp_cache_get( 'json-font-data', 'wp-google-font-customize' ) ) {
                    // If cached object exists
                    $this->_googleFontData = \json_decode( $data, 1 );
                } else {
                    // If cached object does not exist
                    $url = 'https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=' . $this->getApiKey();
        
                    try {
                        $ch = curl_init();
                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                        curl_setopt( $ch, CURLOPT_URL, $url );
                        $data = curl_exec( $ch );
                        curl_close( $ch );
                    } catch ( \Exception $e ) {
                        curl_close( $ch );
                        return array();
                    }

                    // Decode
                    $decoded = \json_decode( $data, 1 );

                    // Check if correct data is present
                    if ( isset( $decoded['items'] ) ) {
                        // Set cached object
                        wp_cache_set( 'json-font-data', $data, 'wp-google-font-customize' );
                        $this->_googleFontData = $decoded;
                    }
                }
            }

            return $this->_googleFontData;
        }

        /**
         * Get Font String
         * 
         * @return string
         */
        protected function getVariantString( array $font )
        {
            if ( isset($font['family']) && is_string($font['family']) && isset($font['variants']) && is_array($font['variants']) ) {
                $replace = array(
                    0 => array('regular','italic'),
                    1 => array('400','i'),
                );

                return str_replace( ' ', '+', $font['family'] ) . ':' . implode( ',', array_map( function( $variant ) use ( $replace ) {
                    switch ($variant) {
                        case 'italic':
                            $variant = '400i';
                            break;
                        case 'regular':
                            $variant = '400';
                            break;
                    }
                    return str_replace( $replace[0], $replace[1], $variant );
                }, $font['variants'] ) );
            }
        }

        /**
         * Get font string for embedding
         * 
         * @return string
         */
        public function getFontFamilies()
        {
            if ( !$this->_googleFontFamilies ) {
                $data = $this->getGoogleFontsData();

                if ( $data && is_array( $data ) && isset( $data['items'] ) ) {
                    $fonts = array();

                    foreach ( $data['items'] as $font ) {
                        $fonts[] = $this->getVariantString( $font );
                    }

                    $this->_googleFontFamilies = array_filter( $fonts, function( $font ) {
                        return $font ? true : false;
                    } );
                }
            }

            return $this->_googleFontFamilies;
        }
        
        /**
         * Get Google API Key
         */
        protected function getApiKey()
        {
            if ( !$this->_googleApiKey ) {
                $this->_googleApiKey = get_option( 'loganstellway-googlefonts-api-key' );
            }

            return $this->_googleApiKey;
        }
        
        /**
         * Enqueue assets
         */
        public function enqueue()
        {
            // Return if no API key provided
            if ( !$this->getApiKey() ) return;

            // Enqueue assets
            wp_enqueue_style('cdn/select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css', false, null);
            wp_enqueue_script('cdn/select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js', ['jquery'], null, true);

            // Enqueue Google Fonts
            if ( $families = $this->getFontFamilies() ) {
                $i = 1;
                $fonts = array();

                foreach ( $families as $key => $family ) {
                    if ( ( $key + 1 ) % 10 == 0 ) {
                        $i++;
                    }

                    if ( !isset( $fonts[ $i ] ) ) {
                        $fonts[ $i ] = array();
                    }

                    $fonts[ $i ][] = $family;
                }

                foreach ( $fonts as $key => $collection ) {
                    wp_enqueue_style( 'google/all-fonts' . $key, 'https://fonts.googleapis.com/css?family=' . implode( '|', $collection ), false, null );
                }

                wp_enqueue_script( 'loganstellway/google-fonts-select2', GOOGLE_FONTS_CUSTOMIZE_PLUGIN_SCRIPT, ['jquery','cdn/select2'], 54, true );
                wp_localize_script( 'loganstellway/google-fonts-select2', 'lsgooglefonts', array(
                    'data' => $this->getGoogleFontsData(),
                ) );
            }
        }
 
        /**
         * Render Content
         */
        protected function render_content()
        {
            // Return if no API key provided
            if ( !$this->getApiKey() ) return;

            $input_id         = '_customize-input-' . $this->id;
            $description_id   = '_customize-description-' . $this->id;
            $describedby_attr = ( ! empty( $this->description ) ) ? ' aria-describedby="' . esc_attr( $description_id ) . '" ' : '';

            ?>
            <?php if ( ! empty( $this->label ) ) : ?>
                <label for="<?php echo esc_attr( $input_id ); ?>" class="customize-control-title"><?php echo esc_html( $this->label ); ?></label>
            <?php endif; ?>
            <?php if ( ! empty( $this->description ) ) : ?>
                <span id="<?php echo esc_attr( $description_id ); ?>" class="description customize-control-description"><?php echo $this->description; ?></span>
            <?php endif; ?>

            <div class="google-fonts-select2-container">
                <input type="hidden" data-role="input" id="<?php echo esc_attr( $input_id ); ?>" <?php echo $describedby_attr; ?> <?php $this->link(); ?> value="<?php echo $this->value() ?>">
            </div>
            <script type="text/javascript">
            if (window.attachEvent) {
                window.attachEvent('onload', googleFontScope.init.bind(googleFontScope));
            } else {
                if (window.onload) {
                    var curronload = window.onload;
                    var newonload = function(evt) {
                        curronload(evt);
                        googleFontScope.init.bind(googleFontScope)(evt);
                    };
                    window.onload = newonload;
                } else {
                    window.onload = googleFontScope.init.bind(googleFontScope);
                }
            }
            </script>
            <style type="text/css">.select2-container{z-index: 9999999}</style>
            <?php
        }
    }
}
