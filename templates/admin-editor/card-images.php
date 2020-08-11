<?php namespace HistoricalConquest; ?>
<table width="100%">
    <?php $cards = get_cards();?>
    <?php foreach ( $cards as $card ) { ?>
        <tr>
        <td colspan="3">
            <a name="<?php echo $card['ext_id']; ?>" ></a>
            <h3><?php echo "[{$card['deck']}] {$card['ext_id']} - {$card['name']}"; ?></h3>
        </td>
        </tr>
        <tr>
            <td>
                <h4>Illustration</h4>
                <?php 
                    $iat = $card['illustration']; 
                    if ( !empty($iat) && $iat != '0' ) {
                        ?><img src="<?php echo get_thumb_image_url($iat); ?>" /><?php
                    }
                    $iat = null;
                ?>
            </td>
            <td>
                <h4>Background</h4>
                <?php 
                    $bat = $card['background_image']; 
                    if ( !empty($bat) && $bat != '0' ) {
                        ?><img src="<?php echo get_thumb_image_url($bat); ?>" /><?php
                    }
                    $bat = null;
                ?>
            </td>
            <td>
                <form method="POST" action="#<?php echo $card['ext_id']; ?>" enctype="multipart/form-data">
                    <input type="hidden" name="card_id" value="<?php echo $card['id']; ?>" />
                    <input type="hidden" name="card_ext" value="<?php echo $card['ext_id']; ?>" />
                    <p>Illustration</p>
                    <input type="file" name="illustration" />
                    <p>Background</p>
                    <input type="file" name="background" />
                    <br />
                    <input type="submit" name="submit" value="upload" />
                </form>
            </td>
        </tr>
    <?php } ?>
</table>
