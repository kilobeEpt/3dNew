<?php
// Performance Optimizations Template
// Include this at the end of the <head> section
?>
<!-- Resource hints for better performance -->
<link rel="dns-prefetch" href="//fonts.googleapis.com">
<link rel="dns-prefetch" href="//fonts.gstatic.com">

<!-- Critical CSS should be inlined here -->
<style>
/* Critical above-the-fold CSS */
body {
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
.header {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: var(--color-bg, #ffffff);
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #000;
    color: #fff;
    padding: 8px;
    text-decoration: none;
    z-index: 100;
}
.skip-link:focus {
    top: 0;
}
</style>
