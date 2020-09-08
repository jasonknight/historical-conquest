<?php
namespace HistoricalConquest;
?>
<div id="player_control_panel">
    <div id="player_cp_left">
        <ul class="menu">
            <li><a class="btn btn-primary button button-primary" href="?hcgame-rules-dl=1">Download Rules</a></li>
            <li><a class="btn btn-primary button button-primary manage-decks">Manage Decks</a></li>
            <li><a class="btn btn-primary button button-primary challenge-player">Challenge Player</a></li>
            <li><a class="btn btn-primary button button-primary digital-store">Digital Store</a></li>
        </ul>
    </div>
    <div id="player_cp_right"></div>
</div>
<?php $render('_card-templates.php'); ?>
<div class="template create-deck-template">
    <div class="form create-deck-form">
        <p class="label">Name</p>
        <p><input class="deck-name" type="text" name="deck[name]" /></p>
        <p><input class="btn btn-primary button button-primary save-button" type="submit" name="submit" value="Create" /></p>
    </div>
</div>
<div class="template deck-display-template">
    <div class="deck">
        <div class="deck-name"></div>
    </div>
</div>
<div class="template deck-editor-template">
    <div class="deck-editor">
        <div class="column left-column"></div>
        <div class="column center-column"></div>
        <div class="column right-column"></div>
    </div>
</div>
<style type="text/css">
    div.deck-editor {
        width: 98%;
    }
    div.deck-editor div.column {
        float: left;
        margin-right: 5px;
        border: 1px solid black;
        min-height: 400px;
    }
    div.deck-editor div.center-column {
        width: 10%;
    }
    div.deck-editor div.left-column,div.deck-editor div.right-column {
        width: 44%;
        overflow-y: scroll;
        height: 400px;
        padding: 5px;
    }
    div.deck {
        color: black;
        text-align: center;
        width: 15%;
        height: 10%;
        float: left;
        padding: 5px;
        margin: 5px;
        border: 1px solid black;
        cursor: pointer;
    }
    div.create-deck-form {
        color: black;
        padding: 10px;
        margin: 5px;
    }
    div.create-deck-form p.label {
        font-size: 120%;
        margin-bottom: 2px;
    }
    #player_control_panel {
        background-image: url("<?php echo dirname(plugin_dir_url(__FILE__)); ?>/assets/img/generic_background.png"); 
        background-position: 50% 50%;
        background-size: cover;
        width: 100%;
        min-height: 450px;
    } 
    #player_cp_left {
        width: 18%; 
        margin: 1%;
        float: left;
        min-height: 400px;
        background-color: rgb(102, 102, 153, 0.5);
    }
    #player_cp_left ul.menu {
        list-style-type: none;
        margin: 0;
    }
    #player_cp_left ul.menu .button {
        width: 100%; 
    }
    #player_cp_right {
        margin: 1%;
        width: 78%; 
        float: left;
        min-height: 400px;
        background-color: rgb(102, 102, 153, 0.5);
    } 
</style>
  <link rel="stylesheet" href="/wp-content/plugins/historical-conquest/assets/style.css?<?php echo time(); ?>" />
<script type="text/javascript">
window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    window.notices = [];
    window.types = <?php echo json_encode(get_types_for_js()); ?>;
    window.carddb = <?php echo json_encode(get_carddb(),JSON_PRETTY_PRINT); ?>;
    window.layers = {
        card: 1,
        overlay: 2,
        popup: 3,
        popup2: 4,
        alert: 5 
    };
    window.owned_cards = <?php echo json_encode($owned_cards,JSON_PRETTY_PRINT); ?>;
<?php $asset('player-control-panel.js'); ?>
</script>
