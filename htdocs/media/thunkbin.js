// New tab on click handler
function newtab()
{
    var open = $("#tabs").data('open');
    var max = $("#tabs").data('max');
    if(open >= max)
        return; // Cant add any more tabs (button should be hidden at this point anyway)

    // Remove new button and add a new tab
    $('#tab-new').remove();
    createtabbutton('File ' + (open + 1), open);
    
    open++;
    $("#tabs").data('open', open);
    
    // Add the new tab button again, unless we reached max tabs
    if(open < max)
    {
        $('#tabs ul').append($('<li><a><i style="font-size: x-small">add file</i></a></li>').addClass('tab-inactive').attr('id', 'tab-new'));
        $('#tab-new').click(newtab);
    }

    // we have more than 1 open tab, make the close buttons visible
    $('.closetab').css('display', '');
}

function langchange()
{
    $('#tabs').data('lastlang', $(this).val());
}

function closetab()
{
    id  = $(this).data('tabid');
    active = $('#tabs').data('active');
    open = $('#tabs').data('open') - 1;
    
    // Every time we close a tab we move all later tabs 1 space forward (copy form values)
    var msg = 'Closing tab ' + id + '\n'; 
    for(i = id; i < open; i++)
    {
        $('#filename' + i).val($('#filename' + (i + 1)).val());
        $('#lang' + i).val($('#lang' + (i + 1)).val());
        $('#contents' + i).val($('#contents' + (i + 1)).val());
    }

    // clear the last tabs contents and remove the tab button
    $('#filename' + open).val('');
    $('#lang' + open).val('');
    $('#contents' + open).val('');
    $('#tabhead-' + open).remove();
    $('#tabs').data('open', open);
    
    if(id < active || id == open) // closed tab was before active tab or was the last open tab, activate the correct tab
        $('#tabhead-' + (active - 1) + ' a:first-child').click();
    
    // Hide close buttons when only 1 tab is left
    if(open == 1)
        $('.closetab').css('display', 'none');
    
    // If we have less open tabs than max, add the 'new file' button if it is not yet there
    if(open < $('#tabs').data('max') && $('#tab-new').length == 0)
    {
        $('#tabs ul').append($('<li><a><i style="font-size: x-small">add file</i></a></li>').addClass('tab-inactive').attr('id', 'tab-new'));
        $('#tab-new').click(newtab);
    }
}

function verifyform()
{
    for(i = 0; i < $('#tabs').data('open'); i++)
    {
        if($('#contents' + i).val().length == 0)
        {
            alert('File ' + (i + 1) + ' is empty, please remove it or paste some content into it.');
            return false;
        }
    }
    return true;
}

// append new tab button and activate that tab
function createtabbutton(name, id)
{
    $('#lang' + id).val($('#tabs').data('lastlang'));
    closebutton = $('<a title="Close Tab">X</a>').addClass('closetab').css('display', 'none').data('tabid', id).click(closetab);
    namebutton = $('<a>' + name + '</a>').data('tabid', id).click(clicktab);
    button = $('<li></li>').addClass('tab-inactive').append(namebutton).append(closebutton);
    button.css('margin-right', '5px').attr('id', 'tabhead-' + id);
    $('#tabs ul').append(button);
    $('#tabhead-' + id + ' a:first-child').click();
}

// tab button click handler
function clicktab()
{
    tabid = $(this).data('tabid');

    active = $("#tabs").data('active');
    //if (active == tabid)
    //    return;

    $("#tab-" + active).addClass('tab-hidden');
    $("#tab-" + tabid).removeClass('tab-hidden');
    $("#tabhead-" + active).removeClass('tab-active');
    $("#tabhead-" + active).addClass('tab-inactive');
    $("#tabhead-" + tabid).addClass('tab-active');
    $("#tabhead-" + tabid).removeClass('tab-inactive');
    $("#tabs").data('active', tabid);
}

$(document).ready(function()
{
    $("#tabs").data('active', 0);           // Active tab is always 0 on page reload

    // display passphrase when state encrypted is selected */
    $("#pass").css('display', 'none');
    $("#state").change(function(){
        if ($(this).val() == 2)
            $("#pass").css('display', 'block');
        else
            $("#pass").css('display', 'none');
    });

    // Loop over all tabs and give them the proper id and class */
    var tabcount = 0;
    $("#tabs > div").each(function(i)
    {
        tabcount++;
        $(this).attr('id', 'tab-' + i);
        if(i != 0)
            $(this).addClass('tab-hidden');
    });
    $("#tabs").data('max', tabcount); // Save max # of tabs we counted

    $("#tabs #hideme").each(function(i){$(this).css('display', 'none'); });



    if($('form').length > 0)
    {
        // we're on a form page, create buttons
        $('#tabs').prepend($('<ul></ul>'));
        createtabbutton('File 1', 0);
        
        $('#tabs ul').append($('<li><a><i style="font-size: x-small">add file</i></a></li>').addClass('tab-inactive').attr('id', 'tab-new'));

        $('#tabs').data('open', 1);
        $('#tab-new').click(newtab);

        // Set onchange handlers for all language select boxes
        $('#tabs').data('lastlang', 1);
        $('#tabs select').each(function()
        {
            $(this).change(langchange);
        });

        $('form').submit(verifyform);
    }
    else
    {
        // we're not a form page, style buttons and handle clicks
        $("#tabs ul li").each(function(i)
        {
            /* Give the button a proper id and assign the proper class */
            $(this).data('tabid', i);
            $(this).attr('id', 'tabhead-' + i);
            if ($("#tabs").data('active') == i)
                $(this).addClass('tab-active');
            else
                $(this).addClass('tab-inactive');

            /* Set the onclick handler for this tab button */
            $(this).click(clicktab);
        });
    }
    
    $("#tabs ul").css('display', 'block');  /* Display buttons in JS enabled browsers */
});
