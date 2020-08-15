<?php
    namespace HistoricalConquest;
?>
<html>
    <head>
        <script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
        <link rel="stylesheet" href="/wp-content/plugins/historical-conquest/assets/style.css" />
        <script type="text/javascript">
            window.types = <?php echo json_encode(get_types_for_js()); ?>;
            window.board = <?php echo include(dirname(__DIR__) . '/tools/generate_player.php');?>;
            window.carddb = <?php echo json_encode(get_carddb(),JSON_PRETTY_PRINT); ?>;
            window.card_layer = 1;
            window.overlay_layer = 2;
            window.popup_layer = 3; 
            window.popup_layer2 = 4; 
            window.alert_layer = 5;
            // game is here
            <?php $asset('game.js'); ?>
            // game is here
        </script>
    </head>
    <body>
        <div class="template card-template">
            <div class="card">
                <table>
                    <tr>
                        <td class="illustration"></td>
                        <td class="right-column">
                            <table>
                                <tr>
                                    <td class="name-plate"></td>
                                    <td class="date-category"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="history-plate"></td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="ability-plate"></td>
                                </tr>
                                <tr>
                                    <td class="reference"></td>
                                    <td class="str-attack-def"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="tab-panel"></div>
        <div class="main container"></div> 
        <h1>Active Abilities Debug</h1>
        <div class="abilitymats container"></div> 
        
    </body>
</html>
