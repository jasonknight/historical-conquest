<div id="player_control_panel">
    <div id="player_cp_left">
        <ul class="menu">
            <li><a class="btn btn-primary button button-primary" href="?hcgame-rules-dl=1">Download Rules</a></li>
            <li><a class="btn btn-primary button button-primary">Challenge Player</a></li>
            <li><a class="btn btn-primary button button-primary">Manage Decks</a></li>
            <li><a class="btn btn-primary button button-primary">Digital Store</a></li>
        </ul>
    </div>
    <div id="player_cp_right"></div>
</div>
<style type="text/css">
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
