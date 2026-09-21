<style>
.loader-overlay{position:fixed;inset:0;z-index:9999;display:grid;place-items:center;background:#101010;opacity:1;transition:opacity 550ms ease}
.loader-overlay.hide{opacity:0;pointer-events:none}
.loader-box{position:relative;width:300px;aspect-ratio:360/80;transform:scale(1);transition:transform 550ms cubic-bezier(0.7,0,0.3,1);will-change:transform}
@media(min-width:768px){.loader-box{width:360px}}
.loader-overlay:not(.in) .loader-box{transform:scale(0)}
.loader-svg{position:absolute;inset:0;width:100%;height:100%;transform:translate3d(0,0,0);content-visibility:visible}
@media(prefers-reduced-motion:reduce){.loader-overlay,.loader-box{transition-duration:0.01ms!important}}
</style>
