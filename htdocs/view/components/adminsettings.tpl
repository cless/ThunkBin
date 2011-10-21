    <div class="box">
        {if isset($success)}<p>Settings saved successfully</p>{/if}
        {if isset($errors.token)}<p>{$errors.token}</p>{/if}

        <form action="admin/settings/" method="post">
            <table>
                <tr>
                    <td><label for="username">Username: </label></td>
                    <td><input type="text" id="username" name="username" value="{$values.username}"/></td>
                    <td colspan="2">{if isset($errors.username)}{$errors.username}{/if}</td>
                </tr>
                <tr>
                    <td><label for="password">Password: </label></td>
                    <td><input type="password" id="password" name="password" value="{$values.password}" /></td>
                    <td>{if isset($errors.password)}{$errors.password}{/if}</td>
                </tr>
                <tr>
                    <td><label for="password2">Repeat password: </label></td>
                    <td><input type="password" id="password2" name="password2" value="{$values.password2}" /></td>
                    <td>{if isset($errors.password2)}{$errors.password2}{/if}</td>
                </tr>
                <tr>
                    <td><label for="updatepass">Update Password: </label></td>
                    <td><input type="checkbox" id="updatepass" name="updatepass" value="1" /></td>
                    <td>Check this if you want to update the username/password too. Otherwise those are ignored.</td>
                </tr>
                <tr>
                    <td><label for="maxfiles">Max Files: </label></td>
                    <td><input type="text" id="maxfiles" name="maxfiles" value="{$values.maxfiles}" /></td>
                    <td{if !isset($errors.maxfiles)} colspan="2"{/if}>Maximum number of files per paste</td>
                    {if isset($errors.maxfiles)}<td>{$errors.maxfiles}</td>{/if}
                </tr>
                <tr>
                    <td><label for="spamtime">Spam Time: </label></td>
                    <td><input type="text" id="spamtime" name="spamtime" value="{$values.spamtime}" /></td>
                    <td{if !isset($errors.spamtime)} colspan="2"{/if}>Amount of seconds in the past used to determine if someone is a spammer</td>
                    {if isset($errors.spamtime)}<td>{$errors.spamtime}</td>{/if}
                </tr>
                <tr>
                    <td><label for="spamwarn">Spam Warn: </label></td>
                    <td><input type="text" id="spamwarn" name="spamwarn" value="{$values.spamwarn}" /></td>
                    <td{if !isset($errors.spamwarn)} colspan="2"{/if}>After this many pastes the user is shown a captcha</td>
                    {if isset($errors.spamwarn)}<td>{$errors.spamwarn}</td>{/if}
                </tr>
                <tr>
                    <td><label for="spamfinal">Spam Final: </label></td>
                    <td><input type="text" id="spamfinal" name="spamfinal" value="{$values.spamfinal}" /></td>
                    <td{if !isset($errors.spamfinal)} colspan="2"{/if}>After this many pastes the user is not allowed to makes new pastes</td>
                    {if isset($errors.spamfinal)}<td>{$errors.spamfinal}</td>{/if}
                </tr>
            </table>
            <input type="hidden" name="token" value="{$token}" />
            <input type="submit" name="submit" />
        </form>
        <form action="admin/settings/" method="post">
            <input type="hidden" name="token" value="{$token}" />
            <input type="submit" name="logout" value="Logout" />
        </form>
        <p>
    </div>
