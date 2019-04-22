# Google Fonts for Theme Customizer

## Description

Add Google Fonts customizer component for use with the WordPress [Theme Customizer](https://en.support.wordpress.com/customizer/).

## Installation

**Manual Installation**

1. Upload (and extract if necessary) plugin to the \"/wp-content/plugins/\" directory.
2. Activate the plugin through the \"Plugins\" menu in WordPress.
3. Navigate to the Settings page and add your [Google API Key](https://developers.google.com/fonts/docs/developer_api#identifying_your_application_to_google) with access to the [Fonts API](https://developers.google.com/fonts/docs/developer_api)

**Composer Installation**

From your project root, run:

```
composer require loganstellway/wp-customize-google-fonts
```

## Usage

From your theme's `functions.php` file, create the customize component instance(s):

```
<?php

// Add Section
$wp_customize->add_section('typography', array(
    'title' => __( 'Typography' ),
    'priority' => 37,
));

// Add Google Font Customize Components
if ( class_exists( '\LoganStellway\GoogleFonts\Customize\Control' ) ) {
    // Content Font
    $wp_customize->add_setting('google_fonts_content', array());
    $wp_customize->add_control(new \LoganStellway\GoogleFonts\Customize\Control($wp_customize, 'google_fonts_content', array(
        'label' => __( 'Content Font', 'sage' ),
        'section' => 'typography',
    )));

    // Heading Font
    $wp_customize->add_setting('google_fonts_heading', array());
    $wp_customize->add_control(new \LoganStellway\GoogleFonts\Customize\Control($wp_customize, 'google_fonts_heading', array(
        'label' => __( 'Heading Font', 'sage' ),
        'section' => 'typography',
    )));
}
```

Enqueue the Google Fonts stylesheet within a [`wp_enqueue_scripts`](https://developer.wordpress.org/reference/hooks/wp_enqueue_scripts/) hook:

```
<?php

add_action('wp_enqueue_scripts', function () {
    if ( class_exists( '\LoganStellway\GoogleFonts\Customize\Control' ) ) {
        $fonts = array_filter( array (
            get_theme_mod('google_fonts_content'),
            get_theme_mod('google_fonts_heading'),
        ) );

        if ( count( $fonts ) ) {
            $url = \LoganStellway\GoogleFonts\Helpers\getBaseUrl() . implode( '|', $fonts );
            wp_enqueue_style('theme/google-fonts', $url);
        }
    }
}, 100);
```

Implement the fonts within a [`wp_head`](https://developer.wordpress.org/reference/hooks/wp_head/) hook:

```
<?php

add_action('wp_head', function() {
    if ( class_exists( '\LoganStellway\GoogleFonts\Customize\Control' ) ) {
        if ( $content_font = get_theme_mod('google_fonts_content', null ) ) {
            $content_font = \LoganStellway\GoogleFonts\Helpers\getFontParts( $content_font );
            ?>
            <style type="text/css">
            html,body {
                font-family: "<?php echo $content_font['family'] ?>";
                font-weight: <?php echo $content_font['weight'] ?>;
                font-style: <?php echo $content_font['style'] ?>;
            }
            </style>
            <?php
        }

        if ( $heading_font = get_theme_mod('google_fonts_content', null ) ) {
            $heading_font = \LoganStellway\GoogleFonts\Helpers\getFontParts( $heading_font );
            ?>
            <style type="text/css">
            h1,.h1,h2,.h2,h3,.h3,h4,.h4,h5,.h5,h6,.h6 {
                font-family: "<?php echo $heading_font['family'] ?>";
                font-weight: <?php echo $heading_font['weight'] ?>;
                font-style: <?php echo $heading_font['style'] ?>;
            }
            </style>
            <?php
        }
    }
});
```
