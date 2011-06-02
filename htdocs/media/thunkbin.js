$(document).ready(function()
{
    $("#tabs").data('active', 0);           /* Active tab is always 0 on page reload */
    $("#tabs ul").css('display', 'block');  /* Display buttons in JS enabled browsers */
    $("#pass").css('display', 'none');      /* Hide the passphrase */
    $("#state").change(function(){            /* display passphrase when state encrypted is selected */
        if ($(this).val() == 2)
            $("#pass").css('display', 'block');
        else
            $("#pass").css('display', 'none');
    });

    $("#tabs #hideme").each(function(i){$(this).css('display', 'none'); });
    /* Loop over all tab button */
    $("#tabs ul li").each(function(i)
    {
        /* Give the button a proper id and assign the proper class */
        $(this).attr('id', 'tabhead-' + i);
        if ($("#tabs").data('active') == i)
            $(this).addClass('tab-active');
        else
            $(this).addClass('tab-inactive');

        /* Set the onclick handler for this tab button */
        $(this).click(function()
        {
            active = $("#tabs").data('active');
            if (active == i)
                return;
            $("#tab-" + active).addClass('tab-hidden');
            $("#tab-" + i).removeClass('tab-hidden');
            $("#tabhead-" + active).removeClass('tab-active');
            $("#tabhead-" + active).addClass('tab-inactive');
            $("#tabhead-" + i).addClass('tab-active');
            $("#tabhead-" + i).removeClass('tab-inactive');
            $("#tabs").data('active', i);
        });
    });
    
    /* Loop over all tabs and give them the proper id and class */
    $("#tabs > div").each(function(i)
    {
        $(this).attr('id', 'tab-' + i);
        if(i != 0)
            $(this).addClass('tab-hidden');
    });
});
