<?php
define('APPLICATION', '/CRM');

define('DATABASE_HOST', 'localhost');
define('DATABASE_USER', 'root');
define('DATABASE_PASSWORD', '');
define('DATABASE_NAME', 'crm');

define('MANAGER_ID', 'iijd__DFjUHg_ffIUHjn_ddfi_rtuhnGFD_oijh');
define('USERNAME', '3_ffRR0996_ffederKhbfyyf_443iknj_huvg');
define('FIRST_NAME', 'ffoJJHyg_87643_kkJuhhyg_765gtfVgj');
define('MIDDLE_NAME', 'iiu_876gt_IJuggt_7765frRRFVb_uhtfI998');
define('LAST_NAME', 'lkJI_iiuYTgj_qwedsaz_yyhtg_yyrhdgv_ry');
define('ROLES', 'llkUHB__uuhyTGRQW_ssdfhbGGVF_iijutFDS_uytr');

// Функции
function LoggedIn() {
    if(isset($_COOKIE[USERNAME]) && $_COOKIE[USERNAME] != '') {
        return true;
    }
    else {
        return false;   
    }
}

function GetManagerId() {
    return $_COOKIE[MANAGER_ID];
}

function IsInRole($role) {
    if(isset($_COOKIE[ROLES])) {
        $roles = unserialize($_COOKIE[ROLES]);
        if(in_array($role, $roles))
                return true;
    }
    
    return false;
}
?>
<meta charset="UTF-8">
<title>Принт-дизайн. Управление взаимоотношениями с клиентами</title>
<link href="<?=APPLICATION ?>/css/bootstrap.css" rel="stylesheet" rel="stylesheet" />
<link href="<?=APPLICATION ?>/css/main.css" rel="stylesheet" rel="stylesheet" />
<link rel="icon" type="image/png" href="<?=APPLICATION ?>/favicon.ico" />