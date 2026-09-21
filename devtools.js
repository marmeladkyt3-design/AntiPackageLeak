(function(){
    var isWebView=!!(window.chrome&&window.chrome.webview);
    var LOCK='https://antiaileaks.ct.ws/F12otkazano';
    var kicked=false;

    function kick(){if(!kicked){kicked=true;window.location.replace(LOCK)}}

    function getUserId(){
        try{
            if(window._launcherUserId)return window._launcherUserId;
            var ls=localStorage.getItem('launcher_jwt');
            if(ls){var p=ls.split('.');if(p.length===3){var d=JSON.parse(atob(p[1].replace(/-/g,'+').replace(/_/g,'/')));if(d&&d.uid)return d.uid}}
        }catch(e){}
        return null;
    }

    document.addEventListener('keydown',function(e){
        if(e.key==='F12'||e.keyCode===123){e.preventDefault();e.stopPropagation();return false}
        if(e.ctrlKey&&e.shiftKey&&(e.key==='I'||e.key==='i'||e.key==='J'||e.key==='j'||e.key==='C'||e.key==='c')){e.preventDefault();e.stopPropagation();return false}
        if(e.ctrlKey&&(e.key==='U'||e.key==='u')){e.preventDefault();e.stopPropagation();return false}
    },true);

    document.addEventListener('keyup',function(e){
        if(e.key==='F12'||e.keyCode===123){e.preventDefault();return false}
    },true);

    document.addEventListener('contextmenu',function(e){e.preventDefault();return false});
    document.addEventListener('copy',function(e){e.preventDefault()});
    document.addEventListener('cut',function(e){e.preventDefault()});
    document.addEventListener('selectstart',function(e){if(e.target.tagName!=='INPUT'&&e.target.tagName!=='TEXTAREA')e.preventDefault()});

    document.addEventListener('mouseover',function(e){
        var a=e.target.closest?e.target.closest('a'):null;
        if(a)window.status='';
    },true);

    if(!isWebView){
        var detecting=false;
        setInterval(function(){
            if(detecting)return;
            detecting=true;
            var s=Date.now();
            try{debugger}catch(e){}
            if(Date.now()-s>80)kick();
            detecting=false;
        },1000);
    }
})();