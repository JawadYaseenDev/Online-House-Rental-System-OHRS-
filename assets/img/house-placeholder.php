<?php
/**
 * OHRS — Placeholder image generator
 * Generates a simple placeholder image for missing house/avatar images.
 */
header('Content-Type: image/svg+xml');
$color = $_GET['color'] ?? '1a56db';
$text  = urlencode($_GET['text'] ?? 'OHRS');
echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="500" viewBox="0 0 800 500">
  <rect width="800" height="500" fill="#f3f4f6"/>
  <rect width="800" height="500" fill="#{$color}" fill-opacity="0.08"/>
  <text x="400" y="220" text-anchor="middle" font-family="Inter,system-ui,sans-serif"
        font-size="80" fill="#{$color}" opacity="0.35">&#127968;</text>
  <text x="400" y="300" text-anchor="middle" font-family="Inter,system-ui,sans-serif"
        font-size="24" fill="#6b7280">{$text}</text>
  <text x="400" y="335" text-anchor="middle" font-family="Inter,system-ui,sans-serif"
        font-size="14" fill="#9ca3af">Image not available</text>
</svg>
SVG;
