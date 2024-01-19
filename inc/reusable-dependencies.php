<?php 

// This snippet register or change reusable dependencies
// %%BREAKDANCE_REUSABLE_GSAP%%
// %%BREAKDANCE_REUSABLE_SCROLL_TRIGGER%%
add_action('breakdance_reusable_dependencies_urls', function ($urls) {
    $urls['gsap'] = THEME_URI . '/assets/lib/gsap.min.js';
    $urls['scrollTrigger'] = THEME_URI . '/assets/lib/ScrollTrigger.min.js';
    return $urls;
 });
?>