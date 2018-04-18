function formliftScrollTo(hash){
    jQuery('html, body').animate({
        scrollTop: jQuery(hash).offset().top - 60}, 200);
}

function formliftOpenSection(evt, animName, section_name) {
    var i, x, tablinks;
    x = document.getElementById("formlift-settings-"+section_name).getElementsByClassName("formlift-section");
    for (i = 0; i < x.length; i++) {
        x[i].style.display = "none";
    }
    tablinks = document.getElementById("formlift-settings-"+section_name).getElementsByClassName("formlift-tab");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" formlift-active", "");
    }
    document.getElementById("_"+animName).style.display = "block";
    document.getElementById(section_name+"_active_tab").value = animName;
    evt.currentTarget.className += " formlift-active";
    formliftScrollTo("#formlift-settings-"+section_name);
}