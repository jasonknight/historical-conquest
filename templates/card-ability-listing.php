<?php
namespace HistoricalConquest;
?>
<table>
    <?php foreach ( $cards as $card ) { ?>
        <tr>
            <td>
                <?php echo $card['name']; ?>
            </td>
            <td>
                <?php echo $card['ext_id']; ?>
            </td>
            <td>
                <?php echo type_to_name($card['maintype']); ?>
            </td>
            <td>
                <?php echo $card['ability_desc']; ?>
            </td>
        </tr>
    <?php } ?>
</table>
