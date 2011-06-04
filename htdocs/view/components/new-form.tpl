    <div class="p-area">
        <form method="post" action="new/save/">
            <div class="p-header" >
                <div><label for="title">Title</label> <input type="text" id="title" name="title" /></div>
                <div><label for="author">Author</label> <input type="text" id="author" name="author" /></div>
                <div>
                    <label for="state">State</label>
                    <select name="state" id="state">
                        <option value="0">public</option>
                        <option value="1">private</option>
                        <option value="2">encrypted</option>
                    </select>
                </div>
                <div id="pass"><label for="passphrase">Passphrase</label> <input type="text" name="passphrase" id="passphrase" /></div>
                <div>
                    <label for="expiration">Expiration</label>
                    <select name="expiration" id="expiration">
                        <option value="0">Never</option>
                        <option value="600">10 Minutes</option>
                        <option value="3600">1 Hour</option>
                        <option value="86400">1 Day</option>
                        <option value="604800">1 Week</option>
                        <option value="2592000">1 Month</option>
                        <option value="7776000">3 Months</option>
                    </select>
                </div>
            </div>
            <div id="tabs">
            {section name=files start=0 loop=$maxfiles step=1}
                <div class="p-file">
                    <div>
                        <label for="filename{$smarty.section.files.index}">Filename</label>
                        <input type="text" name="filename{$smarty.section.files.index}" id="filename{$smarty.section.files.index}" />
                    </div>
                    <div>
                        <label for="lang{$smarty.section.files.index}">Language</label>
                        <select name="lang{$smarty.section.files.index}" id="lang{$smarty.section.files.index}">
                            {foreach $languages as $lang}
                            <option value="{$lang.id}">{$lang.name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="contentwrap">
                        <textarea name="contents{$smarty.section.files.index}" id="contents{$smarty.section.files.index}" rows="10" cols="30" ></textarea>
                    </div>
                </div>
            {/section}
            </div>
            <div>
            <input type="submit" name="submit" />
            </div>
        </form>
    </div>
