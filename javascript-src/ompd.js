
function showStatus() {
    alert ('ok');
}
    
function initialize() {
    //document.searchform.txt.name = 'artist';
    //document.searchform.txt.focus();
    //evaluateSuggest('');
}


function evaluateSuggest(list) {
    var suggest;
    if (list == '') {
        suggest = '<form action="">';
        suggest += '<input type="text" value="no suggestion" readonly class="autosugest_readonly">';
        suggest += '<\/form>';
    }
    else {
        suggest = '<form action="" name="suggest" id="suggest" onSubmit="suggestKeyStroke(1)" onClick="suggestKeyStroke(1)" onKeyDown="return suggestKeyStroke(event)">';
        suggest += '<select name="txt" size="6" class="autosugest">';
        for (var i in list)
            suggest += '<option value="' + list[i] + '">' + list[i] + '<\/option>';
        suggest += '<\/select><\/form>';
    }
    //document.getElementById('suggest').innerHTML = suggest;
}


function searchformKeyStroke(e) {
    var keyPressed;
    if (typeof e.keyCode != 'undefined')    keyPressed = e.keyCode;
    else if (typeof e.which != 'undefined') keyPressed = e.which;
    if (keyPressed == 40 && typeof document.suggest == 'object') // Down key
        {//document.suggest.txt.focus()
        };
}


function suggestKeyStroke(e) {
    var keyPressed;
    if (e == 1)                                 keyPressed = 13;
    else if (typeof e.keyCode != 'undefined')   keyPressed = e.keyCode;
    else if (typeof e.which != 'undefined')     keyPressed = e.which;
    if (keyPressed == 13 && document.suggest.txt.value != '') { // Enter key
        if (document.searchform.action.value == 'view1all')
            document.searchform.action.value = 'view3all';
        document.searchform.txt.value = document.suggest.txt.value;
        document.searchform.filter.value = 'exact';
        document.searchform.submit();
        return false;
    }
    else if (keyPressed == 38 && document.suggest.txt.selectedIndex == 0) { // Up key
        document.suggest.txt.selectedIndex = -1;
        document.searchform.txt.focus();
        return false;
    }
}

function selectTab(obj) {

    if (obj.id == 'albumartist') {
        document.getElementById('albumartist').className = 'tab_on';
        document.getElementById('trackartist').className = 'tab_off';
        document.getElementById('tracktitle').className  = 'tab_off';
        document.getElementById('albumtitle').className  = 'tab_off';
        document.getElementById('quicksearch').className  = 'tab_off';
        document.getElementById('searchform').style.visibility  = 'visible';
        document.getElementById('quicksearchform').style.visibility  = 'hidden';
        document.searchform.txt.select();
        document.searchform.txt.focus();
        document.searchform.txt.name = 'artist';
        document.searchform.action.value = 'view1';
        ajaxRequest(window.ompd.url.json.albumArtist + window.ompd.escapeFunc(document.searchform.txt.value), evaluateSuggest);
    }
    else if (obj.id == 'albumtitle') {
        document.getElementById('albumartist').className = 'tab_off';
        document.getElementById('trackartist').className = 'tab_off';
        document.getElementById('tracktitle').className  = 'tab_off';
        document.getElementById('albumtitle').className  = 'tab_on';
        document.getElementById('quicksearch').className  = 'tab_off';
        document.getElementById('searchform').style.visibility  = 'visible';
        document.getElementById('quicksearchform').style.visibility  = 'hidden';
        document.searchform.txt.select();
        document.searchform.txt.focus();
        document.searchform.txt.name = 'title';
        document.searchform.action.value = 'view2';
        ajaxRequest(window.ompd.url.json.albumTitle + window.ompd.escapeFunc(document.searchform.txt.value), evaluateSuggest);
    }
    else if (obj.id == 'trackartist') {
        document.getElementById('albumartist').className = 'tab_off';
        document.getElementById('trackartist').className = 'tab_on';
        document.getElementById('tracktitle').className  = 'tab_off';
        document.getElementById('albumtitle').className  = 'tab_off';
        document.getElementById('quicksearch').className  = 'tab_off';
        document.getElementById('searchform').style.visibility  = 'visible';
        document.getElementById('quicksearchform').style.visibility  = 'hidden';
        document.searchform.txt.select();
        document.searchform.txt.focus();
        document.searchform.txt.name = 'artist';
        document.searchform.action.value = 'view1all';
        ajaxRequest(window.ompd.url.json.trackArtist + window.ompd.escapeFunc(document.searchform.txt.value), evaluateSuggest);
    }
    else if (obj.id == 'tracktitle') {
        document.getElementById('albumartist').className = 'tab_off';
        document.getElementById('trackartist').className = 'tab_off';
        document.getElementById('tracktitle').className  = 'tab_on';
        document.getElementById('albumtitle').className  = 'tab_off';
        document.getElementById('quicksearch').className  = 'tab_off';
        document.getElementById('searchform').style.visibility  = 'visible';
        document.getElementById('quicksearchform').style.visibility  = 'hidden';
        document.searchform.txt.select();
        document.searchform.txt.focus();
        document.searchform.txt.name = 'title';
        document.searchform.action.value = 'view3all';
        ajaxRequest(window.ompd.url.json.trackTitle + window.ompd.escapeFunc(document.searchform.txt.value), evaluateSuggest);
    }
    
    else if (obj.id == 'quicksearch') {
        document.getElementById('albumartist').className = 'tab_off';
        document.getElementById('trackartist').className = 'tab_off';
        document.getElementById('tracktitle').className  = 'tab_off';
        document.getElementById('albumtitle').className  = 'tab_off';
        document.getElementById('quicksearch').className  = 'tab_on';
        document.getElementById('searchform').style.visibility  = 'hidden';
        document.getElementById('quicksearchform').style.visibility  = 'visible';
        document.searchform.txt.select();
        document.searchform.txt.focus();
        document.searchform.txt.name = 'title';
        document.searchform.action.value = 'view3all';
        ajaxRequest(window.ompd.url.json.quickSearch + window.ompd.escapeFunc(document.searchform.txt.value), evaluateSuggest);
    }
}




$(document).ready(function () {
    $('#iframeRefresh').click(function() {  
        $('#iframeRefresh').removeClass("icon-anchor");
        $('#iframeRefresh').addClass("icon-selected fa-spin");
        var size = $tileSize;
        var request = $.ajax({  
            url: "ajax-suggested.php",  
            type: "POST",  
            data: { tileSize : size },  
            dataType: "html"
        }); 
        
        request.done(function( data ) {  
            if (data.indexOf('tile') > 0) { //check if any album recieved
                $("[id='suggested']").show();
                $( "#suggested_container" ).html( data );   
            }
            else {
                $("[id='suggested']").hide();
            }
            calcTileSize();
            console.log (data.length);
        }); 
        
        request.fail(function( jqXHR, textStatus ) {  
            //alert( "Request failed: " + textStatus ); 
        }); 
    
        request.always(function() {
            $('#iframeRefresh').addClass("icon-anchor");
            $('#iframeRefresh').removeClass("icon-selected fa-spin");
        });
    
    });
    $('#iframeRefresh').click();
});
