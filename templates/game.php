<?php
    namespace HistoricalConquest;
?>
<!DOCTYPE HTML>
<html>
    <head>
        <script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
  <link rel="stylesheet" href="/wp-content/plugins/historical-conquest/assets/style.css?<?php echo time(); ?>" />
        <script type="text/javascript">
            window.get = <?php echo json_encode($_GET); ?>;
            window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            window.notices = [];
            window.user_id = <?php echo \get_current_user_id(); ?>;
            window.types = <?php echo json_encode(get_types_for_js()); ?>;
            <?php if ( get('game_id') && can_play_game(get('game_id')) ) { ?>
                window.board = <?php echo json_encode(get_game_board(get('game_id')),JSON_PRETTY_PRINT); ?>;
            <?php } else { ?>
                window.board = <?php echo include(dirname(__DIR__) . '/tools/generate_player.php');?>;
            <?php } ?>
            window.carddb = <?php echo json_encode(get_carddb(),JSON_PRETTY_PRINT); ?>;
            window.layers = {
                card: 1,
                overlay: 2,
                popup: 3,
                popup2: 4,
                alert: 5 
            };
            // game is here
            <?php $asset('game.js'); ?>
            // game is here
        </script>
    </head>
    <body>
        <?php $render('_card-templates.php'); ?> 
        <div class="tab-panel"></div>
        <div class="main container"></div> 
        <h1>Active Abilities Debug</h1>
        <div class="abilitymats container"></div> 
        <h1>Active Damage Debug</h1>
        <div class="damagemats container"></div>
    </body>
</html>
