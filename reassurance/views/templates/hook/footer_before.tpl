{if $reassurances && !empty($reassurances) }
    <div class="reassurance my-5">
        <div class="container">
            <div class="row">
                {foreach from=$reassurances item=reassurance}
                    <div class="col-md-4">
                        <div class="row">
                            <a href="{if $reassurance.link} {$reassurance.link} {else} # {/if}">
                                <div class="col-md-3">
                                    <img class="icon text-center" src="{$uri}{$reassurance.icon}" alt="{$reassurance.alt}" width="{$iconWidth}" height="{$iconHeight}">
                                </div>
                                <div class="col-md-9">
                                    <h5>{$reassurance.title}</h5>
                                    {$reassurance.description|truncate:100|escape:"html" nofilter}
                                </div>
                            </a>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
   
{/if}