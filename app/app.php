<?php

ini_set('error_reporting', E_ALL); ini_set('display_errors', 1); ini_set('display_startup_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

define('DIR', __DIR__  . '/..'); define('APP', __DIR__  . '/../app');  define('TPL', __DIR__  . '/../app/templates');

foreach (glob(APP . '/classes/*.php') as $file) require_once($file);

$msg = [
    'sqlite3' => 'Sqlite PHP extension not loaded.',
    'sqlite_rejected' => 'Не удалось подключиться к SQLite.',
    'dependencies' => 'We need to install php dependencies.',
    'sign_success' => 'Авторизация выполнена успешно.',
    'mysql_rejected' => 'Не удалось подключиться к MySQL.',
    'access_denied' => 'Доступ запрещен, требуются дополнительные полномочия.'
];

if (!extension_loaded('sqlite3')) error_page(TPL, array($GLOBALS['msg']['sqlite3']));
if (!file_exists(APP . '/db.db')) error_page(TPL, array($GLOBALS['msg']['sqlite_rejected']));
if (file_exists(DIR  . '/composer.json'))
if (!file_exists(DIR  . '/vendor/autoload.php')) error_page(TPL, array($GLOBALS['msg']['dependencies'])); else

require_once(DIR  . '/vendor/autoload.php');

if (class_exists('Dotenv\Dotenv')) { $dotenv = Dotenv\Dotenv::create(DIR); $dotenv->load(); /*echo getenv('DEBUG');*/ }

$config = parse_ini_file(APP . '/app.ini', true);
$storage = new SQLite3(APP . '/db.db');
try { $db = new PDO("mysql:host=".$config['database']['host'].";dbname=".$config['database']['dbname'], $config['database']['username'], $config['database']['passwd']); $db->query("SET NAMES utf8");  } catch (PDOException $error) { error_page(TPL, array($GLOBALS['msg']['mysql_rejected']));  }


if(!isset($app)) $app = '';
switch ($app) {
    case 'require':
        $data['status'] = 'Success';
        $require = websun_parse_template_path($data, TPL . '/index.tpl');
        break;
    default:
        $router = new Router(new Request);

        $router->get('/login', function() {
            session_start();
            $staff = getAuth();
            if (isset($_SESSION['user'])?(!empty($_SESSION['user']) && $_SESSION['user'] == $staff['login']):false) reroute('/admin');
            return websun_parse_template_path([], TPL . '/login.tpl');
        });

        $router->post('/signin', function($msg) {
            if(isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
                $staff = $GLOBALS['db']->query("
                SELECT
                s.login,
                s.password
                FROM
                staffs as s
                WHERE
                s.login = '".$_SERVER['PHP_AUTH_USER']."'
                ");

                $staff = $staff->fetch(PDO::FETCH_ASSOC);

                if (count($staff)>0) {

                    if (!($_SERVER['PHP_AUTH_USER'] ==  $staff['login']
                        && md5($_SERVER['PHP_AUTH_PW']) ==  $staff['password'])) {
                        header('HTTP/1.1 401 Unauthorized'); exit;
                    }
                } else {
                    header('HTTP/1.1 401 Unauthorized'); exit;
                }
            }
            session_start();
            $_SESSION['user'] = $_SERVER['PHP_AUTH_USER']; //$_SESSION['user_session'] = md5(rand());

            echo json_encode(array('success'=>true, 'message'=>$GLOBALS['msg']['sign_success']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        });

        $router->get('/signout', function() {
            session_start(); unset($_SESSION['user']); session_destroy();
            reroute('/login');
        });

        $router->get('/404', function() {
            error_page(TPL);
        });

        $router->get('/', function() {
            reroute('/admin');
        });

        $router->get('/admin', function() {
            reroute('/access-bot');
        });

        $router->get('/access-bot', function($request) {
            $staff = auth();
            (isset($staff['id'])?$u = PrivilegedUser::getByStaffId($staff['id']):'');
            (!$u->hasPrivilege("access-bot.view")?error_page(TPL, array($GLOBALS['msg']['access_denied'])):'');


            $data = [];
            $data['user'] = $_SESSION['user']; $data['page'] =  substr( strrchr($request->requestUri, '/'), 1);

            $staffs = $GLOBALS['db']->query("
            SELECT
            s.id,
            s.name
            FROM
            1c_staffs as s
            ");
            $data['staff'] = $staffs->fetchAll(PDO::FETCH_ASSOC);
            $data['staffs'] = json_encode($data['staff'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $rows = $GLOBALS['db']->query("
            SELECT
            s1c.id,
            s1c.name,
            s.phone,
            s.role,
            s.login,
            IF(s.role IS NOT NULL AND s.login IS NOT NULL,1,0) as auth,
            IF(s1c.id = 3034,1,0) as root
            FROM
            staffs s
            LEFT JOIN `1c_staffs.staffs` ss ON  ss.id_staff = s.id
            LEFT JOIN `1c_staffs` s1c ON s1c.id = ss.id_1c_staff
            WHERE
            s1c.id IS NOT NULL
            AND s.delete IS NULL
            ORDER BY s.id DESC 
            ");
            $data['list'] = $rows->fetchAll(PDO::FETCH_ASSOC); /*while ($row = $results->fetchArray()) { }*/

            $data['container'] = $data['page'].'.tpl';
            return websun_parse_template_path($data, TPL . '/container.tpl');
        });

        $router->get('/access-tills', function($request) {
            auth();

            $data = [];
            $data['user'] = $_SESSION['user']; $data['page'] =  substr( strrchr($request->requestUri, '/'), 1);

            $staffs = $GLOBALS['db']->query("
            SELECT
            s.id,
            s.name
            FROM
            1c_staffs as s
            ");
            $data['staff'] = $staffs->fetchAll(PDO::FETCH_ASSOC);
            $data['staffs'] = json_encode($data['staff'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $tills = $GLOBALS['db']->query("
            SELECT
            t.id,
            t.name as title
            FROM
            1c_tills as t
            LEFT JOIN  `1c_tills.bot_wallets` tw ON tw.id_1c_till = t.id
            WHERE
            tw.id_1c_till IS NOT NULL
            ");
            $data['till'] = $tills->fetchAll(PDO::FETCH_ASSOC);
            $data['tills'] = json_encode($data['till'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $rows = $GLOBALS['db']->query("
            SELECT
            s.id as staff_bot_id,
            s1c.id as staff_id,
            s1c.name as staff_name,
            w.id as wallet_bot_id,
            tw.id_1c_till as wallet_id,
            w.title as wallet_title
            FROM staffs_wallets sw
            LEFT JOIN staffs s ON s.id =sw.staff_id
            LEFT JOIN wallets w ON w.id = sw.wallet_id
            LEFT JOIN `1c_staffs.staffs` ss ON ss.id_staff = sw.staff_id
            LEFT JOIN `1c_staffs` s1c ON s1c.id = ss.id_1c_staff
            LEFT JOIN  `1c_tills.bot_wallets` tw ON tw.id_bot_wallet = w.id
            WHERE (s.role = 0 OR s.role = 1) AND w.cash = 0
            ORDER BY sw.date_create DESC, s1c.name ASC
            ");
            $rows = $rows->fetchAll(PDO::FETCH_ASSOC); /*while ($row = $results->fetchArray()) { }*/

            $data['list'] = [];
            foreach ($rows as $key => $row) {
                array_push($data['list'], array('id'=>$row['staff_id'],'name'=>$row['staff_name'],'login'=>$row['wallet_id'],'password'=>$row['wallet_title']));
            }

            $data['container'] = $data['page'].'.tpl';
            return websun_parse_template_path($data, TPL . '/container.tpl');
        });

        $router->get('/tills', function($request) {
            auth();

            $data = [];
            $data['user'] = $_SESSION['user']; $data['page'] =  substr( strrchr($request->requestUri, '/'), 1);

            $tills = $GLOBALS['db']->query("
            SELECT
            t.id,
            t.type,
            t.name as title,
            IF(tw.id_1c_till IS NULL, 1, 0) AS is_new
            FROM
            1c_tills as t
            LEFT JOIN  `1c_tills.bot_wallets` tw ON tw.id_1c_till = t.id
            ");
            $data['till'] = $tills->fetchAll(PDO::FETCH_ASSOC);

            $data['container'] = $data['page'].'.tpl';
            return websun_parse_template_path($data, TPL . '/container.tpl');
        });

        $router->get('/staffs', function($request) {
            auth();

            $data = [];
            $data['user'] = $_SESSION['user']; $data['page'] =  substr( strrchr($request->requestUri, '/'), 1);

            $staffs = $GLOBALS['db']->query("
            SELECT
            s.id,
            s.name,            
            sb.phone
            FROM `1c_staffs` s
            LEFT JOIN `1c_staffs.staffs` ss ON ss.id_1c_staff = s.id
            LEFT JOIN staffs sb ON sb.id = ss.id_staff
            WHERE
            s.delete = 0
            ");
            $data['staff'] = $staffs->fetchAll(PDO::FETCH_ASSOC);

            $data['container'] = $data['page'].'.tpl';
            return websun_parse_template_path($data, TPL . '/container.tpl');
        });

        $router->post('/api/access-bot/edit', function($request) {
            $request = $request->getBody();
            $rows = $GLOBALS['db']->query("
            SELECT
            ss.id_staff as id
            FROM
            `1c_staffs.staffs` ss
            WHERE
            ss.id_1c_staff = ".$request['id']."
            LIMIT 1
            ");
            $staff = $rows->fetch(PDO::FETCH_ASSOC);

            $GLOBALS['db']->query("
            UPDATE `staffs` SET `role` = '".$request['role']."', `phone` = '".$request['phone']."'".($request['role'] =='0'?', `login` = NULL':($request['auth'] && !empty($request['login']) && !empty($request['password'])?', `login` = \''.$request['login'].'\'':'')).($request['role'] =='0'?', `password` = NULL':($request['auth'] && !empty($request['login']) && !empty($request['password'])?', `password` = \''.md5($request['password']).'\'':''))." WHERE `staffs`.`id` = ".$staff['id'].";
            ");


            return;
        });

        $router->post('/api/access-bot/delete', function($request) {
            $request = $request->getBody();

            $rows = $GLOBALS['db']->query("
            SELECT
            ss.id_staff as id
            FROM
            `1c_staffs.staffs` ss
            WHERE
            ss.id_1c_staff = ".$request['id']."
            LIMIT 1
            ");
            $staff = $rows->fetch(PDO::FETCH_ASSOC);
            $now = date('Y-m-d H:i:s', mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));

            $GLOBALS['db']->query("
            DELETE FROM `1c_staffs.staffs` WHERE `1c_staffs.staffs`.`id_1c_staff` = ".$request['id']
            );
            $GLOBALS['db']->query("
            DELETE FROM `staffs_wallets` WHERE `staffs_wallets`.`staff_id` = ".$staff['id']
            );
            $GLOBALS['db']->query("
            UPDATE `staffs` SET `phone` = '".random(10)."', `delete` = '".$now."' WHERE `staffs`.`id` = ".$staff['id'].";
            ");
            return;
        });
        $router->post('/api/access-bot/add', function($request) {
            $request = $request->getBody();
            $rows = $GLOBALS['db']->query("
                SELECT * FROM `1c_staffs` WHERE `id` = '".$request['id']."'
            ");
            $staff = $rows->fetch(PDO::FETCH_ASSOC);

            $GLOBALS['db']->query("
            INSERT INTO `staffs` (`id`, `name`, `phone`, `login`, `password`, `hash`, `role`) 
            VALUES (NULL, '".firstname($staff['name'])."', '".$request['phone']."', ".($request['auth'] && !empty($request['login'])?'\''.$request['login'].'\'':'NULL').", ".($request['auth'] && !empty($request['password'])?'\''.md5($request['password']).'\'':'NULL').", NULL, '".$request['role']."')
            ");
            $GLOBALS['db']->query("
            INSERT INTO `1c_staffs.staffs` (`id_1c_staff`, `id_staff`) 
            VALUES ('".$request['id']."', '".$GLOBALS['db']->lastInsertId()."')
            ");
            return;
        });

        $router->post('/api/test', function($request) {
            header("Access-Control-Allow-Origin: http://localhost/rest-api-authentication-example/");
            header("Content-Type: application/json; charset=UTF-8");
            header("Access-Control-Allow-Methods: POST");
            header("Access-Control-Max-Age: 3600");
            header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

            $key = "example_key";
            $iss = "http://example.org";
            $aud = "http://example.com";
            $iat = 1356999524;
            $nbf = 1357000000;

            $token = array(
                "iss" => $iss,
                "aud" => $aud,
                "iat" => $iat,
                "nbf" => $nbf,
                "data" => array(
                    "id" => 7,
                    "firstname" => 'Владимир',
                    "lastname" => 'Иванов',
                    "email" => 'ivanov.vladimir.v@yandex.ru'
                )
            );

            http_response_code(200);

            $jwt = JWT::encode($token, $key);
            echo json_encode(
                array(
                    "message" => "Successful login.",
                    "jwt" => $jwt
                )
            );


        });

        $router->post('/api/tills/addlink', function($request) {
            $request = $request->getBody();
            $rows = $GLOBALS['db']->query("
                SELECT t.id, t.name, t.amount FROM 1c_tills t WHERE t.id = '".$request['till_id']."'
            ");
            $rows = $rows->fetch(PDO::FETCH_ASSOC);
            $GLOBALS['db']->query("INSERT IGNORE INTO wallets (id, title, balance, cash, sort) VALUES (NULL, '".$rows['name']."', ".$rows['amount'].", 0, NULL) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(`id`), `title` = '".$rows['name']."', `balance` = '".$rows['amount']."'") ;
            $id = $GLOBALS['db']->lastInsertId();
            $GLOBALS['db']->query("INSERT INTO `1c_tills.bot_wallets` (id_1c_till, id_bot_wallet, `update`) VALUES ('".$rows['id']."', ".$id.", 1)");
            return;
        });

        /* Не удаляем wallet удаляем только связь из за того что могут быть связи да и вооьще это не правильно при скрытии из бота */
        $router->post('/api/tills/deletelink', function($request) {
            $request = $request->getBody();
            $GLOBALS['db']->query("
                DELETE FROM `1c_tills.bot_wallets` WHERE `id_1c_till` = '".$request['till_id']."'
            ");
            return;
        });

        $router->post('/api/tills/add', function($request) {
            $request = $request->getBody();

            $rows = $GLOBALS['db']->query("
                SELECT S1.staff_id, S2.wallet_id FROM ( SELECT s.id as staff_id FROM staffs AS s LEFT JOIN `1c_staffs.staffs` ss ON ss.id_staff = s.id WHERE ss.id_1c_staff = '".$request['staff_id']."' ) AS S1, (SELECT w.id as wallet_id FROM wallets AS w LEFT JOIN `1c_tills.bot_wallets` tw ON tw.id_bot_wallet = w.id WHERE tw.id_1c_till = '".$request['till_id']."' ) AS S2
            ");
            $rows = $rows->fetch(PDO::FETCH_ASSOC);
            $now = date('Y-m-d H:i:s', mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y")));

            $GLOBALS['db']->query("INSERT INTO staffs_wallets (staff_id, wallet_id, date_create) VALUES (".$rows['staff_id'].", ".$rows['wallet_id'].", '".$now."')");
            return;
        });

        $router->post('/api/tills/delete', function($request) {
            $request = $request->getBody();
            $rows = $GLOBALS['db']->query("
                SELECT S1.staff_id, S2.wallet_id FROM ( SELECT s.id as staff_id FROM staffs AS s LEFT JOIN `1c_staffs.staffs` ss ON ss.id_staff = s.id WHERE ss.id_1c_staff = '".$request['staff_id']."' ) AS S1, (SELECT w.id as wallet_id FROM wallets AS w LEFT JOIN `1c_tills.bot_wallets` tw ON tw.id_bot_wallet = w.id WHERE tw.id_1c_till = '".$request['till_id']."' ) AS S2
            ");
            $rows = $rows->fetch(PDO::FETCH_ASSOC);
            $GLOBALS['db']->query("
                DELETE FROM `staffs_wallets` WHERE `staff_id` = '".$rows['staff_id']."' AND  `wallet_id` = '".$rows['wallet_id']."'
            ");
            return;
        });

        $router->get('/api/git/webhook', function() {
            if (class_exists('GitHubWebhook\Handler')) die(json_encode(array('success' => false, 'message' => 'GitHubWebhook disabled')));
            if (!isEnabled('shell_exec') || !function_exists('shell_exec'))  die(json_encode(array('success' => false, 'message' => 'shell_exec disabled')));

            $handler = new GitHubWebhook\Handler($GLOBALS['config']['setting']['git'], APP);

            if($handler->validate()) {
                $commands = array(
                    'whoami',
                    'git pull',
                    'composer install'
                );
                foreach($commands AS $command){
                    shell_exec($command);
                }
            } else {
                die(json_encode(array('success' => false, 'message' => 'secret key is not correct')));
            }

            return;
        });

}