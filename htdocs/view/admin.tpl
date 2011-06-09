{include 'components/header.tpl'}
{if isset($login)}
    {include 'components/adminlogin.tpl'}
{elseif isset($settings)}
    {include 'components/adminsettings.tpl'}
{/if}
{include 'components/footer.tpl'}
