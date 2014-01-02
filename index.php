<?php

// error_reporting(E_ALL);

require("config.php");

define('LAST_RESERVED', 15); // We reserve 16 (0:15) keys in database for configuration and cache.

define('PASSWORD', 1); // Reserved keys.
define('LAST_GENERATED_INT', 2); // Reserved keys.

function query($template, $array) {
    global $pdo;
    $req = $pdo->prepare($template);
    $req->execute($array);
    if($req->errorCode()>0) { return false; }
    return $req->fetchAll();
}

function shortcut_encode($int) {
    return encode($int-LAST_RESERVED-1);
}

function shortcut_decode($hash) {
    return decode($hash)+LAST_RESERVED+1;
}

function url($int) {
    global $host, $protocol;
    return $protocol."://".$host.'/'.shortcut_encode($int);
}

function encode($int) {
    global $charset;
    
    $base = strlen($charset);
    
    $ret = array();
    $len = 0;
    while(pow($base, ++$len) <= $int); // Compute length of result.
    
    for ($i=0; $i<$len; $i++) {
        $power = pow($base, $len-1-$i);
        for ($j = $base-1; $j>=0; $j--) {
            if ($int >= $j * $power) {
                $int -= $j * $power;
                $ret[] = $charset[$j];
                break;
            }
        }
        $set = $charset;
    }
    return implode($ret, '');
}

function decode($hash) {
    global $charset;
    $hash = (string) $hash; /* PHP tries to be smart (and is, as usual, dumb as a box of rocks)                                 with strings that looks like integers. */
    
    $weight = strlen($hash);
    $base = strlen($charset);
    
    $int = 0;
    
    for ($i = 0; $i<strlen($hash); $i++) {
        $int += strpos($charset, $hash[$i]) * pow($base, --$weight);;
    }
    
    return $int;
}

$pdo = new PDO($db, $db_user, $db_password);

// Are we redirecting ?

// This seems to fail on Php 5.3 
if ( count($_GET)==1 && empty($_GET[array_keys($_GET)[0]]) ) {
    $key = shortcut_decode(array_keys($_GET)[0]);
    
    if ($key < 0) die404();
    
    $target = @query("SELECT target FROM $db_table WHERE id=? LIMIT 1", array($key))[0][0];
    print($target);
    if ($target) {
        header('Location: '.$target);
    } else die404();

    exit(0);
}

// User interface part 
// ===================

function die404() {
    global $errorPage;
    if ($errorPage) {
        header('Location: '.$errorPage);
    } else {
        print("404");
    }
    die();
}

function verifyAuth() {
    return (isset($_SESSION['authenticated']) && $_SESSION['authenticated']===true);
}


function enforceAuth() {
    if (!verifyAuth()) {
        global $flashes;
        include('auth.view.php');
        exit(0);
    }
}

// Template functions
// ==================

function tpl($v) {
    global $tpl;
    if (isset($tpl[$v])) print($tpl[$v]);
}

function tplErr ($v) {
    global $errors;
    if (isset($errors[$v])) print("error");
}

function tplErrMsg($v) {
    global $errors;
    if (isset($errors[$v])) {
        print("<small class='error'>".$errors[$v]."</small>");
    }
}

function flash($message, $level=''){
    global $flashes;
    $flashes[] = array($level, $message);
}

session_start();
$flashes = [];
$errors = [];
$tpl = [];

if (isset($_GET['op'])) {
    switch($_GET['op']) {
        case 'auth':
            if (!verifyAuth() && isset($_POST['password'])) {

                if (query("SELECT 1 FROM $db_table WHERE id = ? and target=PASSWORD(?) LIMIT 1", array(PASSWORD, $_POST['password']))) {
                    $_SESSION['authenticated'] = true;
                } else {
                    flash("Wrong password", 'warning');
                }
            }
            enforceAuth();
            break;
            
        case 'logout':
            enforceAuth();
            $_SESSION['authenticated'] = false;
            flash('You have been disconnected.');
            enforceAuth(); // Will land on auth page
            break;

        case 'delete': // @FIXME This is unsafe, as queries may be repeated.
            $id = $_GET['id'];
            if(! ($id && filter_var($id, FILTER_VALIDATE_INT))) {
                break;
            }
            if (query("DELETE FROM $db_table WHERE id=?", [$id])===false) {
                flash("Something went wrong, not deleted.", "warning");
            } 

            
        case 'shorten':
            if (!isset($_POST['target'])) break;
            $target = $_POST['target'];
            $custom = $custom ? shortcut_decode($_POST['shortcut']) : false;
            if ($custom>$maxint) {
                $tpl['sh_shortcut'] = $_POST['shortcut'];
                $errors['sh_shortcut'] = 'Custom shortcut too long.';
                $tpl['sh_target'] = $_POST['target'];
                break;
            }
            
            if(!filter_var($target, FILTER_VALIDATE_URL)) { 
                $tpl['sh_shortcut'] = $_POST['shortcut'];
                $errors['sh_target'] = 'Not a valid URL.';
                $tpl['sh_target'] = $_POST['target'];
                break;
            } 


            /** This query comes from [here](http://stackoverflow.com/questions/907284/how-do-i-get-first-unused-id-in-the-table) and it is cool,
            but it is obviously not safe if used in concurrent contexts. I'm assuming Yapus is for use on a personal webserver. */
            $query = <<<EOT
                SELECT  id
                FROM    (
                        SELECT  1 AS id
                        ) q1
                WHERE   NOT EXISTS
                        (
                        SELECT  1
                        FROM    $db_table
                        WHERE   id = 1
                        )
                UNION ALL
                SELECT  *
                FROM    (
                        SELECT  id + 1
                        FROM    $db_table t
                        WHERE   NOT EXISTS
                                (
                                SELECT  1
                                FROM    $db_table ti
                                WHERE   ti.id = t.id + 1
                                )
                        ORDER BY
                                id
                        LIMIT 1
                        ) q2
                ORDER BY
                        id
                LIMIT 1
EOT;
            
            $id = $custom ? $custom : query($query, array($_POST['target']))[0][0];
            if(query("INSERT INTO yapus VALUES (?, ?)", [$id, $target]) === false) {
                flash("Something went wrong, not created (attempted at ".$id.")", "warning");
            } else {
                $url = url($id);
                flash("Shortened to <a href='$url'>$url</a>.", "success");
            }
    }
}

$allUrls = query("SELECT id, target FROM $db_table WHERE id > ?", array(LAST_RESERVED));

enforceAuth();
include('main.view.php');

?>