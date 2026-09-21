<?php if (!session_id()) session_start(); ?>
<script>
(function(){
    var o=document.getElementById('loaderOverlay');
    if(!o)return;
    var ip='<?php echo md5($_SERVER["REMOTE_ADDR"] ?? "x"); ?>';
    var k='_ld_'+ip;
    var now=Date.now();
    try{var v=localStorage.getItem(k);if(v&&(now-parseInt(v,10))<300000){o.parentNode.removeChild(o);return}}catch(e){}
    requestAnimationFrame(function(){requestAnimationFrame(function(){o.classList.add('in')})});
    setTimeout(function(){o.classList.add('hide');try{localStorage.setItem(k,String(now))}catch(e){}},1400);
    setTimeout(function(){if(o.parentNode)o.parentNode.removeChild(o)},2100);
})();
</script>
