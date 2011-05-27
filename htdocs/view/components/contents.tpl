<div class="header">
    Title: {$header.title} <br />
    Author: {$header.author} <br />
    This paste was created on {$header.created} {if isset($header.expires)} and expires on {$header.expires}{/if}<br />
</div>
{foreach $files as $file}
<div class="file">
    Filename: {$file.filename} <br />
    Language: {$file.lang} <br />
    <div class="contents">{$file.contents}</div>
</div>
{/foreach}
