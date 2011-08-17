        <p>
        {foreach $pagination as $page}
        {if !$page.active}<a href="{$page.link}">{/if}{$page.page}{if !$page.active}</a>{/if} 
        {/foreach} 
        </p>
        <ul class="list">
        {foreach $pastes as $paste}
            <li><a href="view/pub/{$paste.link}">{$paste.title}</a> {$paste.author}</li>
        {/foreach}
        </ul>
