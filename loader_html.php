<?php
$loader_site_name = htmlspecialchars($SITE_NAME ?? 'AntiPackageLeak');
?>
<div class="loader-overlay" id="loaderOverlay" aria-hidden="true">
    <div class="loader-box">
        <div style="position:absolute;inset:0">
            <svg class="loader-svg" viewBox="0 0 360 80" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet">
                <defs>
                    <linearGradient id="_g1" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0%" stop-color="#6366f1"/>
                        <stop offset="55%" stop-color="#8b5cf6"/>
                        <stop offset="100%" stop-color="#a78bfa"/>
                    </linearGradient>
                </defs>
                <g transform="translate(128.6,-11.4) scale(0.514)">
                    <g transform="translate(123,77)">
                        <path fill="#5563bb" d="M31,31 C-3.2,31 -31,3.2 -31,-31 C-31,-31 -15,-31 -15,-31 C-15,-5.6 5.6,15 31,15 C31,15 31,31 31,31z"/>
                    </g>
                    <g>
                        <g transform="translate(82,82)">
                            <path fill="#101010" d="M-36,36 C-36,36 -36,0 -36,0 C-16.1,0 0,-16.1 0,-36 C0,-36 36,-36 36,-36 C36,3.7 3.7,36 -36,36z"/>
                        </g>
                        <g>
                            <path fill="url(#_g1)" d="M45.97,108 C45.97,108 45.97,92 45.97,92 C71.35,92 92,71.35 92,45.97 C92,45.97 108,45.97 108,45.97 C108,80.17 80.17,108 45.97,108z M108,154.03 C108,154.03 92,154.03 92,154.03 C92,141.74 87.21,130.18 78.52,121.49 C69.83,112.79 58.27,108 45.97,108 C45.97,108 45.97,92 45.97,92 C62.54,92 78.12,98.46 89.83,110.17 C101.55,121.89 108,137.47 108,154.03z"/>
                        </g>
                    </g>
                    <g>
                        <g transform="translate(118,118)">
                            <path fill="#101010" d="M0,36 C0,36 -36,36 -36,36 C-36,-3.7 -3.7,-36 36,-36 C36,-36 36,0 36,0 C16.1,0 0,16.1 0,36z"/>
                        </g>
                    </g>
                </g>
                <text x="180" y="76" text-anchor="middle" fill="rgba(255,255,255,0.9)" font-family="Sora, sans-serif" font-size="11" font-weight="700" letter-spacing="0.08em"><?php echo $loader_site_name; ?></text>
            </svg>
        </div>
    </div>
</div>
