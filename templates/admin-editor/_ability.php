<?php
namespace HistoricalConquest;
?>
<tr class="ability-row ability-row-<?php echo $i; ?>">
    <td class="ability-form">
        <table align="center" width="90%">
            <tr>
                <td colspan="2">
                   <p class="label">Description</p> 
                    <textarea style="width: 100%;" name="abilities[<?php echo $i; ?>][description]"></textarea>
                </td>
            </tr>
            <tr> 
                <td>
                    <input type="hidden" name="abilities[<?php echo $i; ?>][id]" value="0"/>
                    <p class="label">Application Scope</p> 
                    <select name="abilities[<?php echo $i; ?>][apply_to_scope]">
                            <option>NONE</option>
                        <?php foreach (options_by_prefix('SCOPE') as $opt ) { ?>
                            <option><?php echo $opt; ?></option>
                        <?php } ?>
                    </select>
                    <p class="label">Application Type</p> 
                    <select name="abilities[<?php echo $i; ?>][apply_to_type]">
                            <option>NONE</option>
                        <?php foreach (options_by_prefix('APPLY') as $opt ) { ?>
                            <option><?php echo $opt; ?></option>
                        <?php } ?>
                    </select>
                    <p class="label">Ability Type</p> 
                    <select name="abilities[<?php echo $i; ?>][ability_type]">
                            <option>NONE</option>
                        <?php foreach (options_by_prefix('ABILITY') as $opt ) { ?>
                            <option><?php echo $opt; ?></option>
                        <?php } ?>
                    </select>
                    <p class="label">Usage Type</p> 
                    <select name="abilities[<?php echo $i; ?>][usage_type]">
                            <option>NONE</option>
                        <?php foreach (options_by_prefix('USAGE') as $opt ) { ?>
                            <option><?php echo $opt; ?></option>
                        <?php } ?>
                    </select>
                    <p class="label">Apply to Types</p> 
                    <textarea name="abilities[<?php echo $i; ?>][apply_to_card_types]"></textarea>
                    <p class="label">Named Function</p> 
                    <select name="abilities[<?php echo $i; ?>][named_function]">
                            <option>NONE</option>
                        <?php foreach (named_ability_functions() as $opt ) { ?>
                            <option><?php echo $opt; ?></option>
                        <?php } ?>
                    </select>
                </td>
                <td>
                    <p class="label">Attribute</p> 
                    <select name="abilities[<?php echo $i; ?>][affects_attribute]">
                            <option>NONE</option>
                        <?php foreach (['can_attack','morale','attack','defense','strength'] as $opt ) { ?>
                            <option><?php echo $opt; ?></option> 
                        <?php } ?>
                    </select>
                    <p class="label">Amount</p> 
                    <input type="text" name="abilities[<?php echo $i; ?>][affect_amount]" value="0"/>
                    <p class="label">Charges</p> 
                    <input type="text" name="abilities[<?php echo $i; ?>][charges]" value="1"/>
                   <p class="label">Ext IDs</p> 
                    <script type="text/javascript">
            (function ($) {
                $(function () {
                    $('input#filter_ext_ids_<?php echo $i; ?>').on('keyup',function () {
                        let id = '#ext_ids_<?php echo $i; ?>';
                        let filter = $(this);
                        $(id).find('option').each(function () {
                            $(this).show();
                            if ( filter.val().length < 2 )
                                return;
                            if ( $(this).text().indexOf(filter.val()) == -1 ) {
                                $(this).hide();
                            } 
                        }); 
                    }); 
                });
            })(jQuery);
                    </script>
                        <textarea name="abilities[<?php echo $i; ?>][apply_to_ext_ids]"></textarea> 
                </td>
            </tr>
            <tr>
                <td colspan="2" align="right"><hr /><br /><a class="button" onclick="">Remove</a></td> 
            </tr>
        </table>
    </td>
</tr>
