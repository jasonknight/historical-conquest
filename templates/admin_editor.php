<?php
namespace HistoricalConquest;
?>
<html>
    <head>
        <style>
           #edit_card {
                border: 2px solid black;
                width: 61%; 
            } 
            tr.card-entry-row > td {
                cursor: pointer;
            }
            #edit_card table td {
                vertical-align: top;
            } 
            #edit_card p {
                margin: 0px;
            }
            #edit_card p.label {
                font-weight: bolder;
                text-transform: uppercase;
                font-size: 12px;
            }
            div.tab {
                display: none;
            }
            a.button {
                padding: 3px;
                padding-left: 10px;
                padding-right: 10px;
                background: gray;
                border: 1px solid darkgray;
                cursor: pointer;
            }
            
        </style>
<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>

    </head>
    <?php $cards_full = get_cards(); ?> 
    <?php $cards_updated = get_not_updated_cards(); ?> 
    <?php $cards_dup = get_duplicate_cards(); ?> 
    <?php $cards_without_abilities = get_cards_without_abilities(); ?> 
    <?php $cards_with_abilities = get_cards_with_abilities(); ?> 
    <body>
        <table class="tab-menu">
            <tr>
                <td>Deck: <?php echo select(['value' => $_SESSION['deck_filter'], 'id' => 'set_deck_filter', 'options' => get_unique_deck_values()]); ?></td>
                <td><button class="button" target="cards_full_list">Full List(<?php echo count($cards_full); ?>)</button></td>
                <td><button class="button" target="cards_not_updated">Not Updated List(<?php echo count($cards_updated); ?>)</button></td>
                <td><button class="button" target="cards_duplicated_ids">Duplicated List(<?php echo count($cards_dup); ?>)</button></td>
                <td><button class="button" target="cards_without_abilities">Without Abilities(<?php echo count($cards_without_abilities); ?>)</button></td>
                <td><button class="button" target="cards_with_abilities">With Abilities(<?php echo count($cards_with_abilities); ?>)</button></td>
            </tr>
        </table>
        <div id="cards_full_list" class="tab">
            <h2>Full List</h2>
            <?php $render('admin-editor/_cards-full-list.php',['cards' => $cards_full]); ?> 
        </div>
        <div id="cards_not_updated" class="tab">
            <h2>Not Updated List</h2>
            <?php $render('admin-editor/_cards-full-list.php',['cards' => $cards_updated]); ?> 
        </div>
        <div id="cards_duplicated_ids" class="tab">
            <h2>Cards duplicated</h2>
            <?php $render('admin-editor/_cards-full-list.php',['cards' => $cards_dup]); ?> 
        </div>
        <div id="cards_without_abilities" class="tab">
            <h2>Without Abilities</h2>
            <?php $render('admin-editor/_cards-full-list.php',['cards' => $cards_without_abilities]); ?> 
        </div>
        <div id="cards_with_abilities" class="tab">
            <h2>With Abilities</h2>
            <?php $render('admin-editor/_cards-full-list.php',['cards' => $cards_with_abilities]); ?> 
        </div>
        <div id="edit_card" class="tab">
<?php $render('admin-editor/edit-card.php',[]); ?> 
        </div>
        <script type="text/javascript">
            window.carddb = <?php echo json_encode($cards_full); ?>;
            window.types = <?php echo json_encode(get_types_for_js()); ?>;
(function ($) {
    $('div.tab').hide();
    $('table.tab-menu button').on('click',function() {
        let id = $(this).attr('target'); 
        $('div.tab').hide();
        $('div#'+id).show();
    });    
    $('table.tab-menu button').first().trigger($.Event('click'));
    $('select#set_deck_filter').on('change',function () {
        window.location = '?action=historical-conquest-game&admin-editor=1&deck=' + $(this).val(); 
    });
    $('tr.card-entry-row td').on('click',function () {
        let id = $(this).parent().attr('card-id'); 
        let record = window.carddb.filter(function (c) { return parseInt(c.id) == id });
        record = record[0];
        $('div.tab').hide();
        $('div#edit_card').show();
        for ( let key in record ) {
            let sel = 'input[name="card[' + key + ']"]';
            let val_to_set = record[key];
            if ( ['background_image','illustration'].indexOf(key) != -1 ) 
                continue;
            // Now we need to convert constant values to their names
            if ( ['ethnicity','maintype','continent','climate','religion'].indexOf(key) != -1 ) {
                for ( let type_key in window.types.key_values ) {
                    if ( parseInt(window.types.key_values[type_key]) == parseInt(val_to_set) ) {
                       val_to_set = type_key; 
                    }
                }
            }
            if ( key == 'abilities' ) {
                let sel = 'input[name*="abilities"]';
                $(sel).val('');
                sel = 'textarea[name*="abilities"]';
                $(sel).val('');
                sel = 'select[name*="abilities"]';
                $(sel).val('');
                let cnt = 0;
                $('tr.ability-row').each(function () {
                    cnt++;
                    if ( cnt > 1 )
                       $(this).remove(); 
                });
                for ( let i = 0; i < record.abilities.length; i++ ) {
                    let arows = $('tr.ability-row');
                    let row = arows.last();
                    let ability = record.abilities[i];
                    console.log("Ability",ability);
                    console.log('row',row);
                    for ( let akey in ability ) {
                        let aval = ability[akey]; 
                        let sel = 'input[name="abilities['+i+'][' + akey + ']"]';
                        console.log('sel',sel);
                        if ( ['usage_type','ability_type','apply_to_type','apply_to_scope'].indexOf(akey) != -1 ) {
                            for ( let type_key in window.types.key_values ) {
                                if ( parseInt(window.types.key_values[type_key]) == parseInt(aval) ) {
                                   aval = type_key; 
                                }
                            }
                        }
                        row.find(sel).val(aval);
                        sel = 'select[name="abilities['+i+'][' + akey + ']"]';
                        console.log('sel',sel);
                        row.find(sel).val(aval);
                        sel = 'textarea[name="abilities['+i+'][' + akey + ']"]';
                        console.log('sel',sel);
                        row.find(sel).val(aval);
                        row.find('.button').unbind('click');
                        row.find('.button').on('click',function (e) {
                            e.preventDefault();
                            row.remove(); 
                        });
                    }
                    $('div#edit_card .add-ability').trigger($.Event('click'));
                } 
                arows = $('tr.ability-row');
                if ( arows.length > 1 ) {
                    arows.each(function () {
                        desc = $(this).find('.ability-desc'); 
                        if ( desc.length > 0 && desc.val() == '' ) {
                            $(this).remove();
                        }
                    });
                }
            }
            $('div#edit_card').find(sel).val(val_to_set);
            $('div#edit_card').find('select[name="card[' + key + ']"]').val(val_to_set);
            $('div#edit_card').find('textarea[name="card[' + key + ']"]').val(val_to_set);
            $('div#edit_card').find('.card-json-data').html(JSON.stringify(record,null,2));
        }
    });
})(jQuery);
        </script>
        <div class="notices">
            <?php show_notices(); ?>
        </div>
    </body>
</html>
