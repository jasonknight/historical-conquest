<table width="100%">
    <?php 
        $headers = array_keys($cards[0]);
        $headers = array_filter($headers,function ($h) {
            return !in_array($h,['abilities','ability_desc','created_at','updated_at','summary','background_image','background_color','border_color','text_color','illustration','when_to_play','reference','reference2','climate']); 
        });
    ?>
    <tr>
        <?php foreach( $headers as $h ) { ?>
            <th><?php echo $h; ?></th>
        <?php } ?>
    </tr>
    <?php foreach ( $cards as $card ) { ?>
    <tr class="card-entry-row" card-ext-id="<?php echo $card['ext_id'];?>" card-id="<?php echo $card['id']; ?>">
            <?php foreach ( $headers as $h ) { ?>
                <td valign="top" class="<?php echo $h; ?>"><p class="<?php echo $h; ?>"><?php echo $card[$h]; ?></p></td>
            <?php } ?>
        </tr>
    <?php } ?>
</table>
