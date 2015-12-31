<?php

namespace Azth;

function showShoutbox() {
    ?>
    <style>
        .slidingDiv {
            height:auto;
            padding:20px;
            margin-top:10px;
            /* border-bottom:5px solid #3399FF;*/
        }

        .show_hide {
            display:none;
            font-size: 16px;
            font-weight: bold;
        }
        
        .skwidget-comment .wpulike {
            display:none;
        }
    </style>

    <center><a href="#" class="show_hide">>> Mostra/Nascondi la shoutbox <<</a></center>
    <div class="slidingDiv">

        <?php \sk_shoutbox(); ?>

        <a href="#" class="show_hide">Nascondi</a>
    </div>

    <script type="text/javascript">

        jQuery(".slidingDiv").hide();
        jQuery(".show_hide").show();

        jQuery('.show_hide').click(function () {
            jQuery(".slidingDiv").slideToggle();
        });

    </script>
    <?php
}
