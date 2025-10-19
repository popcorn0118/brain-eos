jQuery(function($){
    // alert("Hello World");
    let h2_elms = jQuery('h2');
    h2_elms.each(function(index, element){
        if(element.innerText == "個人化設定"){
            $(element).hide();
        }
    });
    $("#application-passwords-section,.user-sessions-wrap,.user-rich-editing-wrap,.user-admin-color-wrap,.user-comment-shortcuts-wrap,.show-admin-bar,.user-language-wrap").hide();
});