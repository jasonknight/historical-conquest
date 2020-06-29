<html>
    <head>
        <script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
        <link rel="stylesheet" href="/wp-content/plugins/historical-conquest/assets/style.css" />
        <script type="text/javascript">
            window.board = <?php echo include(dirname(__DIR__) . '/tools/generate_player.php');?>;
            window.carddb = <?php $asset('card-db.json'); ?>;
            window.card_layer = 1;
            window.overlay_layer = 2;
            window.popup_layer = 3; 
            window.alert_layer = 4;
            <?php $asset('game.js'); ?>
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
        
    </body>
</html>
