<?php
function getBrowserLanguage()
{
    $acceptedLanguages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    $languages = explode(',', $acceptedLanguages);
    foreach ($languages as $language) {
        // Extract just the language code (e.g., "en-US;q=0.9" becomes "en-US")
        $lang = strtok($language, ';'); // Separate the locale and quality factor
        return substr($lang, 0, 2);     // Return the first two letters (e.g., "en", "es")
    }
    return 'unknown';
}

/**
 * Checks if the browser supports a specific image format.
 *
 * @param string $format The MIME type of the image format to check, e.g., 'image/webp' or 'image/avif'.
 * @return bool True if the format is supported, false otherwise.
 */
function supportsImageFormat($format)
{
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        $acceptHeader = $_SERVER['HTTP_ACCEPT'];
        return stripos($acceptHeader, $format) !== false;
    }
    return false;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Device Capability Detection</title>
    <script async defer>/* use inline head script to prevent cors blocking and enhance compatibilty */
        document.addEventListener("DOMContentLoaded", function () {

            var viewportWidth = window.innerWidth;
            var viewportHeight = window.innerHeight;
            var viewportRatio = viewportWidth / viewportHeight;
            var viewportOrientation = (viewportWidth > viewportHeight) ? 'landscape' : 'portrait';

            document.getElementById('viewport-width').textContent = viewportWidth;
            document.getElementById('viewport-height').textContent = viewportHeight;
            document.getElementById('viewport-orientation').textContent = viewportOrientation;
            document.getElementById('viewport-ratio').textContent = viewportRatio;
            document.getElementById('viewport-dpr').textContent = window.devicePixelRatio;

            // ^ simple detections first, to increase backward compatibility

            function addListItem(/* @@type {HTMLElement} */ aListElement, aText) {
                var newListItem = document.createElement('li');
                newListItem.textContent = aText;
                aListElement.appendChild(newListItem);
            }

            function parseUserAgentForVersion() {
                const userAgent = navigator.userAgent;
                let match;

                // Match major version for Chrome/Chromium-based browsers
                if ((match = userAgent.match(/Chrome\/(\d+)/))) {
                    return match[1];
                }

                // Match major version for Firefox
                if ((match = userAgent.match(/Firefox\/(\d+)/))) {
                    return match[1];
                }

                // Match major version for Safari
                if ((match = userAgent.match(/Version\/(\d+)/)) && userAgent.includes('Safari')) {
                    return match[1];
                }

                // Match major version for Edge
                if ((match = userAgent.match(/Edg\/(\d+)/))) {
                    return match[1];
                }

                // Match major version for Opera
                if ((match = userAgent.match(/OPR\/(\d+)/))) {
                    return match[1];
                }

                // Match major version for Internet Explorer (extremely outdated browsers)
                if ((match = userAgent.match(/MSIE (\d+)/)) || (match = userAgent.match(/rv:(\d+)/))) {
                    return match[1];
                }

                // If no match, return "unknown"
                return 'Unknown';
            }

            var a11yQueryList = document.getElementById('ul-a11y');

            var prefersReducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
            if (prefersReducedMotionQuery.matches) {
                addListItem(a11yQueryList, 'prefers reduced motion');
            }

            var prefersReducedTransparencyQuery = window.matchMedia('(prefers-reduced-transparency)');
            if (prefersReducedTransparencyQuery.matches) {
                addListItem(a11yQueryList, 'prefers reduced transparency');
            }

            var prefersMoreContrastQuery = window.matchMedia('(prefers-contrast: more)');
            if (prefersMoreContrastQuery.matches) {
                addListItem(a11yQueryList, 'prefers more contrast');
            }

            if (CSS && CSS.supports('prefers-color-scheme: dark')) {
                addListItem(a11yQueryList, 'prefers dark mode color scheme');
            }

            var supportDetailsList = document.getElementById('support-details');

            if (typeof globalThis === 'object') {
                addListItem(supportDetailsList, 'supports globalThis');
            }

            if (typeof IntersectionObserver === 'function') {
                addListItem(supportDetailsList, 'supports IntersectionObserver');
            }

            document.getElementById('ua-string').textContent = navigator.userAgent;
            document.getElementById('ua-os').textContent = navigator.platform;


            if (navigator.userAgentData && navigator.userAgentData.brands) {
                // The API provides a list of brands; the first one is usually the official browser brand.
                const brands = navigator.userAgentData.brands;
                const officialBrand = brands.find(brand => brand.brand !== 'Not A;Brand');
                if (officialBrand) {
                    document.getElementById('ua-name').textContent = officialBrand.brand;
                    var majorVersion = parseUserAgentForVersion();
                    if (majorVersion) {
                        document.getElementById('ua-version').textContent = majorVersion;
                    }
                }
                ;
            } else {
                document.getElementById('ua-name').textContent = navigator.appName;
                document.getElementById('ua-version').textContent = navigator.appVersion;
            }

            if ('userAgentData' in navigator) {
                navigator.userAgentData.getHighEntropyValues(['mobile']).then(ua => {
                    if (ua.mobile) {
                        document.getElementById('device-class').textContent = 'mobile';
                    } else {
                        document.getElementById('device-class').textContent = 'desktop';
                    }
                });
            }
            if (matchMedia('(pointer: coarse)').matches) {
                document.getElementById('device-input-type').textContent = 'touch';
            } else {
                document.getElementById('device-input-type').textContent = 'no-touch';
            }
        });
    </script>
    <style>
        .feature {
            display: none;
        }

        @media (forced-colors: active) {
            .feature--forced-colors {
                display: block;
            }
        }
    </style>
</head>
<body>
<h1>Device Capability Detection and Diagnosis</h1>

<p>This detection page probes and lists some device and browser capabilities and properties.
    Detection is done using JavaScript and CSS media queries, and PHP to analyze HTTP headers.
    Please reload this page to re-run the detection.</p>

<p>This free service is provided "as-is" without warranty of any kind.
    Please note that the information provided is not guaranteed to be accurate or complete.</p>

<h2>Device, Browser, and Platform</h2>
<ul class="user-agent">
    <li>User agent string: <span id="ua-string"></span></li>
    <li>Platform/operating system (OS): <span id="ua-os"></span></li>
    <li>Browser name: <span id="ua-name">unknown</span></li>
    <li>Browser version: <span id="ua-version"></span></li>
    <li>Device class: <span id="device-class">unknown</span></li>
    <li>Touch input type: <span id="device-input-type"></span></li>
    <li>Default language: <?php echo getBrowserLanguage() ?></li>
</ul>

<h2>Viewport Properties</h2>
<ul id="viewport">
    <li>Viewport dimensions (geometry): <span id="viewport-width">___</span> x <span id="viewport-height">___</span>
    </li>
    <li>Viewport orientation: <span id="viewport-orientation"></span></li>
    <li>Viewport ratio: <span id="viewport-ratio"></span></li>
    <li>Pixel density (resolution, device pixel ratio): <span id="viewport-dpr"></span></li>
</ul>

<h2>Image Type and Feature Support</h2>
<ul id="support-details">
    <?php if (supportsImageFormat('image/webp')) : ?>
        <li>supports webp (image/webp)</li>
    <?php endif; ?>
    <?php if (supportsImageFormat('image/avif')) : ?>
        <li>supports AVIF (image/avif)</li>
    <?php endif; ?>
</ul>

<ul id="ul-a11y">
</ul>
<ul id="ul-css">
    <li class="feature feature--forced-colors">forced colors mode is active</li>
</ul>

<h2>System Fonts</h2>
<ul id="fonts">
    <li style="font-family:'Helvetica Neue',monospace">Helvetica Neue</li>
    <li style="font-family:'Helvetica',monospace">Helvetica</li>
    <li style="font-family:'Futura',monospace">Futura</li>
    <li style="font-family:'Verdana',monospace">Verdana</li>
    <li style="font-family:'Noto Sans',monospace">Noto Sans</li>
    <li style="font-family:'Dairantou',monospace">Dairantou</li>
    <li style="font-family:'Arial',monospace">Arial</li>
    <li style="font-family:'Segoe UI',monospace">Segoe UI</li>
    <li style="font-family:'Roboto',monospace">Roboto</li>
    <li style="font-family:'Inter',monospace">Inter</li>
    <li style="font-family:'sans-serif',monospace">sans-serif</li>

    <li style="font-family:'Georgia',monospace">Georgia</li>
    <li style="font-family:'Garamond',monospace">Garamond</li>
    <li style="font-family:'Times New Roman',monospace">Times New Roman</li>
    <li style="font-family:'Times',monospace">Times</li>
    <li style="font-family:'Georgia',monospace">Georgia</li>
    <li style="font-family:'serif',monospace">serif</li>

    <li style="font-family:'nonExistANto!!la34redsaf',monospace">nonexistant (should always fall back to monospace)</li>
</ul>
</body>
</html>