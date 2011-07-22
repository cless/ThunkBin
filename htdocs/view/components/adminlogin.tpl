    <div class="box">
        {if isset($errors)}
        {foreach $errors as $error}
        <p class="error">{$error}</p>
        {/foreach}
        {/if}
        <form action="admin/" method="post">
        <label for="username">Username: </label><input type="text" id="username" name="username" /><br />
        <label for="password">Password: </label><input type="password" id="password" name="password" /><br />
        <input type="hidden" name="token" value="{$token}" />
        <input type="submit" name="submit" />
        </form>
    </div>
