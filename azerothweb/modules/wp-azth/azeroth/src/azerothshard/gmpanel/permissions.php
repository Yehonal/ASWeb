<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            @import url(http://fonts.googleapis.com/css?family=Lato:300,400,700);
            body {
                display: inline-block;
                width: 100%;
                font-family: 'Lato', sans-serif;
            }
            #results {
                width: 90%;
            }
            /* Base Styles */
            #cssmenu ul,
            #cssmenu li,
            #cssmenu a {
                list-style: none;
                margin: 0;
                padding: 0;
                border: 0;
                line-height: 1;
                font-family: 'Lato', sans-serif;
            }
            #cssmenu {
                border: 1px solid #133e40;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                -ms-border-radius: 5px;
                -o-border-radius: 5px;
                border-radius: 5px;
                width: auto;
                max-width: 1000px;
                margin: 0 auto;
            }
            #cssmenu ul {
                zoom: 1;
                background: #36b0b6;
                background: -moz-linear-gradient(top, #36b0b6 0%, #2a8a8f 100%);
                background: -webkit-gradient(linear, left top, left bottom, color-stop(0%, #36b0b6), color-stop(100%, #2a8a8f));
                background: -webkit-linear-gradient(top, #36b0b6 0%, #2a8a8f 100%);
                background: -o-linear-gradient(top, #36b0b6 0%, #2a8a8f 100%);
                background: -ms-linear-gradient(top, #36b0b6 0%, #2a8a8f 100%);
                background: linear-gradient(top, #36b0b6 0%, #2a8a8f 100%);
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='@top-color', endColorstr='@bottom-color', GradientType=0);
                padding: 5px 10px;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                -ms-border-radius: 5px;
                -o-border-radius: 5px;
                border-radius: 5px;
            }
            #cssmenu ul:before {
                content: '';
                display: block;
            }
            #cssmenu ul:after {
                content: '';
                display: table;
                clear: both;
            }
            #cssmenu li {
                float: left;
                margin: 0 5px 0 0;
                border: 1px solid transparent;
            }
            #cssmenu li a {
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                -ms-border-radius: 5px;
                -o-border-radius: 5px;
                border-radius: 5px;
                padding: 8px 15px 9px 15px;
                display: block;
                text-decoration: none;
                color: #ffffff;
                border: 1px solid transparent;
                font-size: 16px;
            }
            #cssmenu li.active {
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                -ms-border-radius: 5px;
                -o-border-radius: 5px;
                border-radius: 5px;
                border: 1px solid #36b0b6;
            }
            #cssmenu li.active a {
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                -ms-border-radius: 5px;
                -o-border-radius: 5px;
                border-radius: 5px;
                display: block;
                background: #1e6468;
                border: 1px solid #133e40;
                -moz-box-shadow: inset 0 5px 10px #133e40;
                -webkit-box-shadow: inset 0 5px 10px #133e40;
                box-shadow: inset 0 5px 10px #133e40;
            }
            #cssmenu li:hover {
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                -ms-border-radius: 5px;
                -o-border-radius: 5px;
                border-radius: 5px;
                border: 1px solid #36b0b6;
            }
            #cssmenu li:hover a {
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                -ms-border-radius: 5px;
                -o-border-radius: 5px;
                border-radius: 5px;
                display: block;
                background: #1e6468;
                border: 1px solid #133e40;
                -moz-box-shadow: inset 0 5px 10px #133e40;
                -webkit-box-shadow: inset 0 5px 10px #133e40;
                box-shadow: inset 0 5px 10px #133e40;
            }
            li {
                line-height: 30px;
                border-bottom: 1px solid #002c53;
                margin-bottom: 10px;
                padding: 2px 2px 2px 2px;
            }
        </style>
    </head>
    <body>
        <?php
        //if (!$link = mysql_connect('azerothshard.servegame.com', 'giuseppe', '')) {
        if (!$link = mysqli_connect('25.121.200.252', 'azth-web-server', 'tiasp.4mysql')) {
            echo 'Could not connect to mysql';
            exit;
        }

        $roles = array(
            100012 => array("name" => "Azeroth Player", "for" => 1),
            100011 => array("name" => "Test Player", "for" => 2),
            100013 => array("name" => "Test GM", "for" => 2),
            100014 => array("name" => "Master Test GM", "for" => 2),
            100001 => array("name" => "GM T1 - Supporter", "for" => 1),
            100002 => array("name" => "GM T2 - Protector", "for" => 1),
            100010 => array("name" => "Story Teller", "for" => 1),
            100006 => array("name" => "Master Story Teller", "for" => 1),
            100005 => array("name" => "Entertainer ", "for" => 1),
            100004 => array("name" => "Master Entertainer", "for" => 1),
            192 => array("name" => "Admin", "for" => -1)
        );

        $realms = array(
            1 => "AzerothShard",
            2 => "Test Realm"
        );

        function getRole($role) {
            global $realm;
            return makeParam(array("role" => $role, "realm" => $realm));
        }

        function makeParam($params) {
            return strtok($_SERVER["REQUEST_URI"], '?') . "?" . http_build_query($params);
        }

        $role = $_GET["role"];
        $realm = $_GET["realm"] ? $_GET["realm"] : 1;
        ?>
    <center><h1>Permissions Viewer</h1></center>
    <br>

    <div id='cssmenu'>
        <ul>
            <?php for ($i = 1; $i <= count($realms); $i++): ?>
                <li <?php if ($i == $realm) echo "class='active'"; ?>>
                    <a class="button" href="<?= makeParam(array("realm" => $i)) ?>"><span><?=$realms[$i]?></span></a>
                </li>
            <?php endfor; ?>
        </ul>
    </div>

    <div id='cssmenu'>
        <ul>
            <li <?php if (!$role) echo "class='active'"; ?>>
                <a class="button" href="<?= getRole("") ?>"><span>Manual</span></a>
            </li>
            <?php
            foreach ($roles as $k => $r) {
                if ($roles[$k]["for"]<0 || $roles[$k]["for"] == $realm) {
                    ?>
                    <li <?php if ($k == $role) echo "class='active'"; ?>>
                        <a class="button" href="<?= getRole($k) ?>"><span><?= $roles[$k]["name"] ?></span></a>
                    </li>
                    <?php
                }
            }
            ?>
        </ul>
    </div>
    <br>
    <br>
    <br>

    <?php
    $dbWorld = "azth_" . $realm . "_world";

    $query = "";
    if (!$role) {
        if (!mysqli_select_db($link, $dbWorld)) {
            echo 'Could not select database';
            exit;
        }

        echo "<center><h2> Manuale dei comandi </h2></center>";

        $query = "SELECT permission as id, CONCAT('<span><b>',NAME,'</b></span><br/>( ',HELP,' )') as name FROM command;";
    } else {
        if (!mysqli_select_db($link, 'azth_auth')) {
            echo 'Could not select database';
            exit;
        }

        echo "<center><h2> Permessi per il ruolo : ".$roles[$role]['name']." </h2></center>";

        $query = "SELECT DISTINCT r.id AS id, t.id AS user_id, r.name as name FROM (
    SELECT a.id AS ID, 
        IFNULL(i.linkedId,
        IFNULL(h.linkedId,
        IFNULL(g.linkedId,
        IFNULL(f.linkedId,
        IFNULL(e.linkedId, 
        IFNULL(d.linkedId, 
        IFNULL(c.linkedId, 
        IFNULL(b.linkedId, a.linkedId))))))))
        LINK FROM rbac_linked_permissions a 
        LEFT JOIN rbac_linked_permissions b ON (a.linkedId = b.id) 
        LEFT JOIN rbac_linked_permissions c ON (b.linkedId = c.id) 
        LEFT JOIN rbac_linked_permissions d ON (c.linkedId = d.id) 
        LEFT JOIN rbac_linked_permissions e ON (d.linkedId = e.id) 
        LEFT JOIN rbac_linked_permissions f ON (e.linkedId = f.id) 
        LEFT JOIN rbac_linked_permissions g ON (f.linkedId = g.id) 
        LEFT JOIN rbac_linked_permissions h ON (g.linkedId = h.id) 
        LEFT JOIN rbac_linked_permissions i ON (h.linkedId = i.id)

    ) t JOIN rbac_permissions r ON (LINK = r.id)
    WHERE t.id = " . $role . " ORDER BY r.id DESC;";
    }

    $result = mysqli_query($link, $query);

    if (!$result) {
        echo "DB Error, could not query the database\n";
        echo 'MySQL Error: ' . mysql_error();
        exit;
    }
    ?>
    <div id="results">
        <ul>
            <?php
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<li id='".$row['id']."'><a href='permissions.php#". $row['id']."'>" . $row['id'] ."</a> - ". $row['name'] . "</li>";
            }
            mysqli_free_result($result);
            ?>
        </ul>
    </div>

    Special thanks to Luce for SQL Queries ;)
    <?php ?>

</body>
</html>