        {if isset($error)}<p>{$error}</p>{/if}
        <form action="view/dec/{$pastelink}" method="post" >
        This paste is encrypted, please enter the passphrase:
        <input type="text" name="passphrase" />
        <input type="submit" name="submit" />
        </form>
