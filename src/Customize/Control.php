<?php

namespace LoganStellway\GoogleFonts\Customize;

// Prevent direct access to script
defined( 'ABSPATH' ) or die();

if ( ! class_exists('\LoganStellway\GoogleFonts\Customize\Control') ) {
    class Control extends \WP_Customize_Control
    {
        public $type = 'google_fonts';
        private $_googleApiKey;
        protected static $_googleFontData;
        protected $_googleFontString = '';

        /**
         * Get Google Fonts Data
         * 
         * @return mixed
         */
        protected function getGoogleFontsData() {
            if ( !self::$_googleFontData ) {
                $url = 'https://www.googleapis.com/webfonts/v1/webfonts?key=' . $api_key;
        
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
            
                self::$_googleFontData = \json_decode( $data, 1 );
            }

            return self::$_googleFontData;
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

                return str_replace( ' ', '+', $font['family'] ) . ':' . implode( ',', array_map( function($variant) {
                    switch ($variant) {
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
        public function getFontString()
        {
            if ( !$this->_googleFontString ) {
                if ( $data = $this->getGoogleFontsData() && is_array( $data ) && isset( $data['items'] ) ) {
                    $fonts = array();

                    foreach ( $data['items'] as $font ) {
                        if ( $string = $this->getVariantString( $font ) ) {
                            $fonts[] = $string;
                        }
                    }

                    $this->_googleFontString = implode('|', $fonts);
                }
            }

            return $this->_googleFontString;
        }
        
        /**
         * Get Google API Key
         */
        protected function getApiKey()
        {
            if ( !$this->_googleApiKey ) {
                $this->_googleApiKey = sanitize_key( get_option( 'loganstellway-googlefonts-api-key' ) );
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
            wp_enqueue_style('google/all-fonts', 'https://fonts.googleapis.com/css?family=' . $this->getFontString(), false, null);
            wp_enqueue_script('cdn/select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js', ['jquery'], null, true);
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

            <?php
            /*
            <select id="<?php echo esc_attr( $input_id ); ?>" <?php echo $describedby_attr; ?> <?php $this->link(); ?>>
                <?php
                foreach ( $this->choices as $value => $label ) {
                    echo '<option value="' . esc_attr( $value ) . '"' . selected( $this->value(), $value, false ) . '>' . $label . '</option>';
                }
                ?>
            </select>
            <?php
            */
        }
    }
}
