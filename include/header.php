<?php
// Валидация формы логина
define('LOGINISINVALID', ' is-invalid');
$login_form_valid = true;

$login_username_valid = '';
$login_password_valid = '';

// Обработка отправки формы логина
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_submit'])){
    if($_POST['login_username'] == '') {
        $login_username_valid = LOGINISINVALID;
        $login_form_valid = false;
    }
    
    if($_POST['login_password'] == '') {
        $login_password_valid = LOGINISINVALID;
        $login_form_valid = false;
    }
    
    if($login_form_valid) {
        $login_manager_id = '';
        $login_username = '';
        $login_first_name = '';
        $login_middle_name = '';
        $login_last_name = '';
        $login_roles = '';

        $conn = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
        $sql = "select id, username, first_name, middle_name, last_name from manager where username='".$_POST['login_username']."' and password=password('".$_POST['login_password']."')";
        
        if($conn->connect_error) {
            die('Ошибка соединения: ' . $conn->connect_error);
        }
        $result = $conn->query($sql);
        if ($result->num_rows > 0 && $row = $result->fetch_assoc()) {
            $login_manager_id = $row['id'];
            setcookie(MANAGER_ID, $row['id'], 0, "/");
            
            $login_username = $row['username'];
            setcookie(USERNAME, $row['username'], 0, "/");
            
            $login_first_name = $row['first_name'];
            setcookie(FIRST_NAME, $row['first_name'], 0, "/");
            
            $login_middle_name = $row['middle_name'];
            setcookie(MIDDLE_NAME, $row['middle_name'], 0, "/");
            
            $login_last_name = $row['last_name'];
            setcookie(LAST_NAME, $row['last_name'], 0, "/");
        }
        else {
            $error_message = "Неправильный логин или пароль.";
        }
        
        if($login_manager_id != '') {
            $role_sql = "select r.name from manager_role ur inner join role r on ur.role_id = r.id where ur.manager_id = ".$login_manager_id;
            $role_result = $conn->query($role_sql);
            if($role_result->num_rows > 0) {
                $roles = array();
                $role_i = 0;
                while ($role_row = $role_result->fetch_assoc()) {
                    $roles[$role_i++] = $role_row['name'];
                }
                
                setcookie(ROLES, serialize($roles), 0, '/');
            }
        }
        
        $conn->close();
        
        if($login_username != '') {
            header("Refresh:0");
        }
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout_submit'])) {
    setcookie(MANAGER_ID, '', 0, "/");
    setcookie(USERNAME, '', 0, "/");
    setcookie(FIRST_NAME, '', 0, "/");
    setcookie(MIDDLE_NAME, '', 0, "/");
    setcookie(LAST_NAME, '', 0, "/");
    setcookie(ROLES, '', 0, "/");
    header("Refresh:0");
    header('Location: '.APPLICATION.'/');
}
?>
<div class="container-fluid">
    <nav class="navbar navbar-expand-sm">
        <a class="navbar-brand" href="<?=APPLICATION ?>/">
            <span class="font-awesome">&#xf015;</span>
        </a>
        <ul class="navbar-nav mr-auto">
            <?php
            $organization_status = $_SERVER['PHP_SELF'] == APPLICATION.'/organization/index.php' ? ' disabled' : '';
            $allorgs_status = $_SERVER['PHP_SELF'] == APPLICATION.'/organization/all.php' ? ' disabled' : '';
            $call_status = $_SERVER['PHP_SELF'] == APPLICATION.'/contact/index.php' ? ' disabled' : '';
            $planned_status = $_SERVER['PHP_SELF'] == APPLICATION.'/planned/index.php' ? ' disabled' : '';
            $order_status = $_SERVER['PHP_SELF'] == APPLICATION.'/order/index.php' ? ' disabled' : '';
            $manager_status = $_SERVER['PHP_SELF'] == APPLICATION.'/manager/index.php' ? ' disabled' : '';
            $personal_status = $_SERVER['PHP_SELF'] == APPLICATION.'/personal/index.php' ? ' disabled' : '';

            if(LoggedIn()) {
                // Количество запланированных контактов
                $planned_count = 0;
                
                $planned_conn = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME);
                $planned_sql = "select count(c.id) count "
                            . "from contact c "
                            . "inner join person p "
                            . "inner join organization o on p.organization_id = o.id "
                            . "on c.person_id = p.id "
                            . "where o.manager_id=".GetManagerId()." "
                            . "and c.next_date is not null "
                            . "and UNIX_TIMESTAMP(c.next_date) < UNIX_TIMESTAMP(DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY)) "
                            . "and (select count(id) from contact where person_id = p.id and UNIX_TIMESTAMP(date) >= UNIX_TIMESTAMP(CURRENT_DATE())) = 0";
                    
                    if($planned_conn->connect_error) {
                        die('Ошибка соединения: ' . $planned_conn->connect_error);
                    }
                    
                    $planned_result = $planned_conn->query($planned_sql);
                    if ($planned_result->num_rows > 0 && $planned_row = $planned_result->fetch_assoc()) {
                        $planned_count = $planned_row['count'];
                    }
                    $planned_conn->close();
            ?>
            <li class="nav-item">
                <a class="nav-link<?=$organization_status ?>" href="<?=APPLICATION ?>/organization/">Мои предприятия</a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?=$allorgs_status ?>" href="<?=APPLICATION ?>/organization/all.php">Все предприятия</a>
            </li>
            <li class='nav-item'>
                <a class="nav-link<?=$call_status ?>" href='<?=APPLICATION ?>/contact/'>Перв. действ. контакты</a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?=$planned_status ?>" href="<?=APPLICATION ?>/planned/">Запланировано<?=$planned_count == 0 ? '' : ' ('.$planned_count.')' ?></a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?=$order_status ?>" href="<?=APPLICATION ?>/order/">Заказы</a>
            </li>
            <?php
            }
            if(IsInRole('admin')) {
            ?>
            <li class="nav-item">
                <a class="nav-link<?=$manager_status ?>" href="<?=APPLICATION ?>/manager/">Менеджеры</a>
            </li>
            <?php
            }
            if(LoggedIn()) {
            ?>
            <li class="nav-item">
                <a class="nav-link<?=$personal_status ?>" href="<?=APPLICATION ?>/personal/">Мои настройки</a>
            </li>
            <?php
            }
            ?>
        </ul>
        <?php
        if(isset($_COOKIE[USERNAME]) && $_COOKIE[USERNAME] != '') {
        ?>
        <form class="form-inline" method="post">
            <label>
                <?php
                $full_manager_name = '';
                if(isset($_COOKIE[LAST_NAME]) && $_COOKIE[LAST_NAME] != '') {
                    $full_manager_name .= $_COOKIE[LAST_NAME];
                }
                if(isset($_COOKIE[FIRST_NAME]) && $_COOKIE[FIRST_NAME] != '') {
                    if($full_manager_name != '') $full_manager_name .= ' ';
                    $full_manager_name .= $_COOKIE[FIRST_NAME];
                }
                if(isset($_COOKIE[MIDDLE_NAME]) && $_COOKIE[MIDDLE_NAME] != '') {
                    if($full_manager_name != '') $full_manager_name .= ' ';
                    $full_manager_name .= $_COOKIE[MIDDLE_NAME];
                }
                echo $full_manager_name;
                ?>
                &nbsp;
            </label>
            <button type="submit" class="btn btn-outline-dark" id="logout_submit" name="logout_submit">Выход</button>
        </form>
        <?php
        }
        else {
        ?>
        <form class="form-inline my-2 my-lg-0" method="post">
            <div class="form-group">
                <input class="form-control mr-sm-2<?=$login_username_valid ?>" type="text" id="login_username" name="login_username" placeholder="Логин" value="<?=$_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_username']) ? $_POST['login_username'] : '' ?>" required="required" autocomplete="on" />
                <div class="invalid-feedback">*</div>
            </div>
            <div class="form-group">
                <input class="form-control mr-sm-2<?=$login_password_valid ?>" type="password" id="login_password" name="login_password" placeholder="Пароль" required="required" />
                <div class="invalid-feedback">*</div>
            </div>
            <button type="submit" class="btn btn-outline-dark my-2 my-sm-2" id="login_submit" name="login_submit">Войти</button>
        </form>
        <?php
        }
        ?>
    </nav>
</div>
<hr />