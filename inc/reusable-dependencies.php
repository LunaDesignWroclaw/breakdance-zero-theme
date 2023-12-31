<?php 
add_action('breakdance_reusable_dependencies_urls', function ($urls) {
    $urls['gsap'] = THEME_URI . '/assets/lib/gsap.min.js';
    $urls['scrollTrigger'] = THEME_URI . '/assets/lib/ScrollTrigger.min.js';
    return $urls;
 });
?>