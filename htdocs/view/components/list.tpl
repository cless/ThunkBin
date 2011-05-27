        <ul class="list">
        {foreach $pastes as $paste}
            <li><a href="/viewpaste/pub/{$paste.link}">{$paste.title}</a> {$paste.author}</li>
        {/foreach}
        </ul>
