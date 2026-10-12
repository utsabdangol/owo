<?php
/**
 * OWO — Placeholder image generator
 * Serves a lightweight SVG placeholder when product images are missing.
 */
header('Content-Type: image/svg+xml');
header('Cache-Control: public, max-age=86400');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<svg xmlns="http://www.w3.org/2000/svg" width="400" height="500" viewBox="0 0 400 500">
  <rect width="400" height="500" fill="#f5f5f5"/>
  <text x="200" y="230" font-family="Inter,sans-serif" font-size="48" font-weight="700"
        fill="#d4d4d4" text-anchor="middle" letter-spacing="6">OWO</text>
  <text x="200" y="270" font-family="Inter,sans-serif" font-size="12"
        fill="#9a9a9a" text-anchor="middle" letter-spacing="2">NO IMAGE</text>
</svg>
