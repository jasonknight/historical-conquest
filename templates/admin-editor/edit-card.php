<?php
namespace HistoricalConquest;
?>
<form method="post" action="?action=historical-conquest-game&admin-editor=1&subaction=edit-card">
    <input type="hidden" name="card[id]" value="0" />
    <table width="100%">
        <tr>
            <td colspan="2">
                <p class="label">Name</p>
                <input type="text" name="card[name]" />
            </td>
            <td colspan="2">
                <p class="label">Deck</p>
                <input type="text" name="card[deck]" />
                <p class="label">ID</p>
                <input type="text" name="card[ext_id]" />
                <p class="label">Year</p>
                <input type="text" name="card[year]" />
            </td>
        </tr>
        
        <tr>
            <td colspan="2">
                <p class="label">Attack</p>
                <input type="text" name="card[strength]" />
                <p class="label">Defense</p>
                <input type="text" name="card[defense]" />
                <p class="label">Carry Capacity</p>
                <input type="text" name="card[carry_capacity]" />
            </td>
            <td colspan="2">
                <p class="label">When to play</p>
                <input type="text" name="card[when_to_play]" />
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <p class="label">Type</p>
                <select name="card[maintype]">
                    <?php foreach (options_by_prefix('CARD') as $opt ) { ?>
                        <option><?php echo $opt; ?></option>
                    <?php } ?>
                </select>
                <p class="label">Religion</p>
                <select name="card[religion]">
                    <?php foreach (options_by_prefix('RELIGION') as $opt ) { ?>
                        <option><?php echo $opt; ?></option>
                    <?php } ?>
                </select>
                <p class="label">Ethnicity</p>
                <select name="card[ethnicity]">
                    <?php foreach (options_by_prefix('ETH') as $opt ) { ?>
                        <option><?php echo $opt; ?></option>
                    <?php } ?>
                </select>
                <p class="label">Continent</p>
                <select name="card[religion]">
                    <?php foreach (options_by_prefix('CONTINENT') as $opt ) { ?>
                        <option><?php echo $opt; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p class="label">Illustration Image</p>
                <input type="file" name="card[illustration]" />
                <p class="label">Background Image</p>
                <input type="file" name="card[background_image]" />
            </td>
            <td colspan="2">
                <p class="label">Background Color</p>
                <input type="text" name="card[background_color]" />
                <p class="label">Text Color</p>
                <input type="text" name="card[text_color]" />
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <p class="label">Reference</p>
                <input type="text" name="card[reference]" />
                <p class="label">Reference 2</p>
                <input type="text" name="card[reference2]" />
            </td>
            <td colspan="2">
                <p class="label">Background Color</p>
                <input type="text" name="card[background_color]" />
                <p class="label">Gender</p>
                <select name="card[gender]">
                    <option>male</option>
                    <option>female</option>
                    <option>unknown</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <p class="label">Summary</p>
                <textarea cols="40" rows="5" name="card[summary]"></textarea>
                <p class="label">Abilities Desc </p>
                <textarea cols="40" rows="5" name="card[ability_desc]" disabled></textarea>
                <h3>Ability Definitions</h3>
                <table align="center" width="100%">
                    <?php $render('admin-editor/_ability.php',['i' => 0]); ?>
                    <tr>
                        <td align="right">
                            <hr />
                            <p>
                                <a class="button add-ability">Add Ability</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <script type="text/javascript">
                    (function ($) {
                        $('a.add-ability').on('click',function (e) {
                            e.preventDefault(); 
                            let html = $('tr.ability-row:last').html();
                            let matches = html.match(/abilities\[(\d+)\]/);
                            console.log('matches',matches[1]);
                            let index = parseInt(matches[1]);
                                index++;
                            let new_name = 'abilities[' + index + ']';
                            while ( html.indexOf(matches[0]) != -1 ) {
                                html = html.replace(matches[0],new_name);
                            }
                            let tmp = $('<tr>' + html  + '</tr>');
                            tmp.addClass('ability-row');
                            tmp.find('input').val('');
                            tmp.find('select').val('');
                            tmp.find('textarea').val('');
                            tmp.insertAfter($('tr.ability-row').last());
                            tmp.find('.button').attr('onclick','');
                            tmp.find('.button').on('click', function () {
                                 tmp.remove();
                            });
                        });
                    })(jQuery);
                </script>
            </td>
        </tr>
        <tr>
            <td colspan="4" align="right"><input type="submit" name="submit" value="Update/Save" /></td>
        </tr>
    </table>
</form>
<h3>Raw JSON</h3>
<pre class="card-json-data" style="overflow-x: scroll;">

</pre>
