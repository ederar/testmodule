{if $reassurances && !empty($reassurances) }
    <div class="reassurance-2">
        <div class="container">
            <div class="row">
            {foreach from=$reassurances item=reassurance}
                <div class="col-md-4">
                   
                        <a href="{if $reassurance.link} {$reassurance.link} {else} # {/if}">
                            <div class="icon">
                                <img src="{$uri}{$reassurance.icon}" alt="{$reassurance.alt}" width="{$iconWidth}" height="{$iconHeight}">
                            </div>
                            <div class="reassurance-content text-center mt-1">
                                <h5>{$reassurance.title}</h5>
                                {$reassurance.description|truncate:100|escape:"html" nofilter}
                            </div>
                        </a>
                
                </div>
            {/foreach}
            </div>
        </div>
    </div>
    
{/if}