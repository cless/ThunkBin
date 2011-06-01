<div class="p-area">
    <div class="p-header">
        <span>Title</span> {$header.title} <br />
        <span>Author</span> {$header.author} <br />
        <span>Link</span> {$header.link} <br />
        <span>Created</span> {$header.created}<br />
        <span>Expires</span> {if isset($header.expires)}{$header.expires}{else}never{/if}</br />
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
</div>
