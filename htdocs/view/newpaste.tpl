{include 'components/header.tpl'}
{if isset($error)}
<div>
    {$error}
</div>
{else}
{include 'components/new-form.tpl'}
{/if}
{include 'components/footer.tpl'}

