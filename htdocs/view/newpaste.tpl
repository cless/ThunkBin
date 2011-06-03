{include 'components/header.tpl'}
{if isset($error)}
<div>
    {$error}
</div>
{elseif isset($spam)}
<div>
    You are creating too many pastes. Please wait a while before creating more pastes.
</div>
{else}
{include 'components/new-form.tpl'}
{/if}
{include 'components/footer.tpl'}

