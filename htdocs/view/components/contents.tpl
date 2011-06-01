<div class="header">
    Title: {$header.title} <br />
    Author: {$header.author} <br />
    This paste was created on {$header.created} {if isset($header.expires)} and expires on {$header.expires}{/if}<br />
</div>
<div id="tabs">
    <ul>
        {foreach $files as $file}
        <li><a>{$file.filename} <i>({$file.lang})</i></a></li>
        {/foreach}
    </ul>
    {foreach $files as $file}
    <div id="tab" class="p-file">
        <div id="hideme">
            Filename: {$file.filename} <br />
            Language: {$file.lang} <br />
        </div>
        <div class="contents">{$file.contents}</div>
    </div>
    {/foreach}
</div>
