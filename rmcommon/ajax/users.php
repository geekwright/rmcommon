<?php
// $Id: users.php 902 2012-01-03 07:09:16Z i.bitcero $
// --------------------------------------------------------------
// EXM System
// Content Management System
// Author: BitC3R0 <bitc3r0@gmail.com>
// Email: bitc3r0@gmail.com
// License: GPL 2.0
// --------------------------------------------------------------

require dirname(dirname(dirname(__DIR__))) . '/mainfile.php';
//require_once XOOPS_ROOT_PATH.'/modules/rmcommon/loader.php';

$common->ajax()->prepare();

$common->checkToken();

$tpl = new RMTemplate();
$db  = XoopsDatabaseFactory::getDatabaseConnection();

$type = 0;
$s    = '';
$kw   = '';
$ord  = 2;
$all  = false;

foreach ($_REQUEST as $k => $v) {
    $$k = $v;
}

if (!isset($field) || '' == $field) {
    _e('Sorry, you are not allowed to view this page', 'rmcommon');
    die();
}

$field = addslashes($field);
$kw    = addslashes($kw);

if (is_string($s) && '' != $s) {
    $selected = explode(',', $s);
} elseif (is_array($s)) {
    $selected = $s;
} else {
    $selected = [];
}

$selected_string = implode(',', $selected);

$sql = 'SELECT COUNT(*) FROM ' . $db->prefix('users') . ' WHERE level>0';
if ('' != $kw) {
    $sql .= " AND (uname LIKE '%$kw%' OR name LIKE '%$kw%')";
}

list($num) = $db->fetchRow($db->query($sql));

$page  = isset($pag) ? $pag : 1;
$limit = isset($limit) && $limit > 0 ? $limit : 36;

$tpages = ceil($num / $limit);
$page   = $page > $tpages ? $tpages : $page;
if ($num % $limit > 0) {
    $tpages++;
}

$start = $num <= 0 ? 0 : ($page - 1) * $limit;

$nav = new RMPageNav($num, $limit, $page, 4);
$nav->target_url('javascript:usersField.goto_page({PAGE_NUM}, ' . $type . ');');

$sql = str_replace('COUNT(*)', 'uid, uname', $sql);
switch ($ord) {
    case '0':
        $sql .= ' ORDER BY user_regdate';
        break;
    case '1':
        $sql .= ' ORDER BY uname';
        break;
    default:
        $sql .= ' ORDER BY uid';
        break;
}
$sql .= " LIMIT $start,$limit";
//$sql = "SELECT uid, uname FROM ".$db->prefix("users")." WHERE level>0 LIMIT $start,$limit";
$result = $db->query($sql);

$users = [];
if ($all) {
    $users[] = ['id' => 0, 'name' => __('All', 'rmcommon'), 'check' => in_array(0, $selected, true)];
}

while (false !== ($row = $db->fetchArray($result))) {
    $users[] = ['id' => $row['uid'], 'name' => $row['uname'], 'check' => in_array($row['uid'], $selected, true)];
}

$selecteds = [];
if (is_array($selected) && count($selected) > 0) {
    $sql    = 'SELECT uid,uname FROM ' . $db->prefix('users') . ' WHERE level>0 AND uid IN (' . implode(',', $selected) . ')';
    $result = $db->query($sql);
    while (false !== ($row = $db->fetchArray($result))) {
        $selecteds[] = ['id' => $row['uid'], 'name' => $row['uname'], 'check' => true];
    }
}

$field_type = $type ? 'checkbox' : 'radio';

$nav->render(false);

ob_start();
include RMTemplate::get()->get_template('rmc-form-users.php', 'module', 'rmcommon');
$content = ob_get_clean();

$common->ajax()->response(__('Seleccionar usuario', 'sessions'), 0, 1, [
                                                                   'content' => $content,
                                                               ]);
