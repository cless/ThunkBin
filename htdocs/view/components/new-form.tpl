    <div class="p-area">
        <form method="post" action="new/save/">
            <div class="p-header" >
                <label for="title">Title: <input type="text" name="title" /></label>
                <label for="author">Author: <input type="text" name="author" /></label>
                <label for="state">State:
                    <select name="state">
                        <option value="0">public</option>
                        <option value="1">private</option>
                    </select>
                </label>
                <label for="Expiration">Expiration:
                    <select name="expiration">
                        <option value="0">Never</option>
                        <option value="600">10 Minutes</option>
                        <option value="3600">1 Hour</option>
                        <option value="86400">1 Day</option>
                        <option value="604800">1 Week</option>
                        <option value="2592000">1 Month</option>
                        <option value="7776000">3 Months</option>
                    </select>
                </label>
            </div>
            {section name=files start=0 loop=4 step=1}
            <div class="p-file">
                <label for="filename{$smarty.section.files.index}">Filename: <input type="text" name="filename{$smarty.section.files.index}" /></label>
                <label for="lang{$smarty.section.files.index}">Language:
                    <select name="lang{$smarty.section.files.index}">
                        {foreach $languages as $lang}
                        <option value="{$lang.id}">{$lang.name}</option>
                        {/foreach}
                    </select>
                </label>
                <label for="contents{$smarty.section.files.index}">
                    <textarea name="contents{$smarty.section.files.index}" cols="130" rows="17"></textarea>
                </label>
            </div>
            {/section}
            <input type="submit" name="submit" />
        </form>
    </div>
