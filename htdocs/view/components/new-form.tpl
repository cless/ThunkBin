    {if isset($errors)}
    <div class="box">
    {foreach $errors as $error}
        {$error}<br>
    {/foreach}
    </div>
    {/if}
    <div class="p-area">
        <form method="post" action="new/save/">
            <div class="p-header" >
                <div><label for="title">Title</label> <input type="text" id="title" name="title" {if isset($values.title)}value="{$values.title}" {/if}/></div>
                <div><label for="author">Author</label> <input type="text" id="author" name="author" {if isset($values.author)}value="{$values.author}" {/if}/></div>
                <div>
                    <label for="state">State</label>
                    <select name="state" id="state">
                        <option value="0"{if isset($values.state) && $values.state == 0} selected="selected"{/if}>public</option>
                        <option value="1"{if isset($values.state) && $values.state == 1} selected="selected"{/if}>private</option>
                        <option value="2"{if isset($values.state) && $values.state == 2} selected="selected"{/if}>encrypted</option>
                    </select>
                </div>
                <div id="pass"><label for="passphrase">Passphrase</label> <input type="text" name="passphrase" id="passphrase" {if isset($values.passphrase)}value="{$values.passphrase}" {/if}/></div>
                <div>
                    <label for="expiration">Expiration</label>
                    <select name="expiration" id="expiration">
                        <option value="0"{if isset($values.expiration) && $values.expiration == 0} selected="selected"{/if}>Never</option>
                        <option value="600"{if isset($values.expiration) && $values.expiration == 600} selected="selected"{/if}>10 Minutes</option>
                        <option value="3600"{if isset($values.expiration) && $values.expiration == 3600} selected="selected"{/if}>1 Hour</option>
                        <option value="86400"{if isset($values.expiration) && $values.expiration == 86400} selected="selected"{/if}>1 Day</option>
                        <option value="604800"{if isset($values.expiration) && $values.expiration == 604800} selected="selected"{/if}>1 Week</option>
                        <option value="2592000"{if isset($values.expiration) && $values.expiration == 2592000} selected="selected"{/if}>1 Month</option>
                        <option value="7776000"{if isset($values.expiration) && $values.expiration == 7776000} selected="selected"{/if}>3 Months</option>
                    </select>
                </div>
            </div>
            <div id="tabs">
            {section name=files start=0 loop=$maxfiles step=1}
                <div class="p-file">
                    <div>
                        <label for="filename{$smarty.section.files.index}">Filename</label>
                        <input type="text" name="filename{$smarty.section.files.index}" id="filename{$smarty.section.files.index}" {if isset($values)}value="{$values.filename[$smarty.section.files.index]}" {/if}/>
                    </div>
                    <div>
                        <label for="lang{$smarty.section.files.index}">Language</label>
                        <select name="lang{$smarty.section.files.index}" id="lang{$smarty.section.files.index}">
                            {foreach $languages as $lang}
                            <option value="{$lang.id}"{if isset($values) && $values.lang[$smarty.section.files.index] == $lang.id} selected="selected"{/if}>{$lang.name} 
                            </option>

                            {/foreach}
                        </select>
                    </div>
                    <div class="contentwrap">
                        <textarea name="contents{$smarty.section.files.index}" id="contents{$smarty.section.files.index}" rows="10" cols="30" >{if isset($values)}{$values.contents[$smarty.section.files.index]}{/if}</textarea>
                    </div>
                </div>
            {/section}
            </div>
            <div>
            <input type="submit" name="submit" />
            </div>
        </form>
    </div>
