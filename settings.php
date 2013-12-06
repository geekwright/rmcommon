<?php
// $Id$
// --------------------------------------------------------------
// Red México Common Utilities
// A framework for Red México Modules
// Author: Eduardo Cortés <i.bitcero@gmail.com>
// Email: i.bitcero@gmail.com
// License: GPL 2.0
// --------------------------------------------------------------

include_once '../../include/cp_header.php';
//require_once XOOPS_ROOT_PATH . '/modules/rmcommon/admin-loader.php';
define('RMCLOCATION','settings');

/**
 * Shows all modules that can be configured with this tool
 */
function show_configurable_items(){
    global $xoopsModule, $cuSettings, $xoopsModuleConfig, $rmTpl;

    $rmTpl->add_style("settings.css", 'rmcommon', array('footer' => 1));
    $db = XoopsDatabaseFactory::getDatabaseConnection();
    $sql = "SELECT mid FROM " . $db->prefix("modules") . " WHERE dirname='system' OR hasconfig=1 ORDER BY name ASC";
    $result = $db->query( $sql );

    $mh = xoops_gethandler( 'module' );
    $modules = array();

    while( $row = $db->fetchArray( $result ) ){

        $mod = $mh->get( $row['mid'] );
        $modules[] = array(
            'id' => $mod->mid(),
            'name' => $mod->getVar('name'),
            'logo' => XOOPS_URL . '/modules/' . $mod->getVar('dirname') . '/' . $mod->getInfo('image')
        );

    }

    $rmTpl->header();
    include $rmTpl->get_template("rmc-settings.php", 'module', 'rmcommon');
    $rmTpl->footer();

}

/**
 * Show the preferences for a specific module
 */
function show_module_preferences(){
    global $rmTpl;

    $mod = RMHttpRequest::get( 'mod', 'integer', 0 );
    if ( $mod <= 0 )
        RMUris::redirect_with_message(
            __('You have not specified a module!', 'rmcommon'), 'settings.php', RMMSG_WARN
        );

    $mh = xoops_gethandler( 'module' );
    $module = $mh->get( $mod );

    if ( $module->isNew() )
        RMUris::redirect_with_message(
            __('You have not specified a valid module ID!', 'rmcommon'), 'settings.php', RMMSG_ERROR
        );

    /**
     * Verify if module is rmcommon native or not
     */
    if ( $module->getInfo('rmnative') != 1 )
        RMUris::redirect_with_message(
            __('This module can not be configured with Common Utilities', 'rmcommon'),
            XOOPS_URL. '/modules/system/admin.php?fct=preferences&op=showmod&mod=' . $mod,
            RMMSG_INFO
        );

    /*
    Cargamos los valores y los datos para formar los campos
    */
    $values = RMSettings::module_settings( $module->getVar('dirname') );
    $configs = $module->getInfo('config');
    $settings_categories = $module->getInfo('categories');
    $categories = array();

    if ( empty( $settings_categories ) )
        $categories = array(
            'all' => array(
                'caption' => __( 'Preferences', 'rmcommon' )
            )
        );
    else{
        foreach($settings_categories as $category => $caption){
            $categories[$category] = array('caption' => $caption);
        }
    }

    unset($settings_categories);

    $fields = array(); // Container for all fields and values
    foreach( $configs as $option ){

        $id = $option['name'];

        $field = new stdClass();
        $field->id = $id;
        $field->value = isset( $values->$id) ? $values->$id : $option['default'];
        $field->caption = defined($option['title']) ? constant( $option['title'] ) : $option['title'];
        $field->description = defined($option['description']) ? constant( $option['description'] ) : $option['description'];
        $field->field = $option['formtype'];
        $field->type = $option['valuetype'];
        $field->options = isset($option['options']) ? $option['options'] : null;

        $category = isset($option['category']) ? $option['category'] : 'all';

        if ( isset( $categories[$category] ) )
            $categories[$category]['fields'][$id] = $field;
        else{
            if ( !isset( $categories['all'] ) )
                $categories['all'] = array('caption' => __('Preferences', 'rmcommon'));

            $categories['all']['fields'][$id] = $field;
        }

    }

    $categories = RMEvents::get()->run_event( 'rmcommon.settings.fields', $categories, $module );

    $rmTpl->add_style('settings.css', 'rmcommon', array('footer' => 1));

    /* Breadcrumb */
    $bc = RMBreadCrumb::get();
    if ($module->getVar('hasadmin'))
        $bc->add_crumb( $module->getVar('name'), XOOPS_URL . '/modules/' . $module->getVar('dirname') . '/' . $module->getVar('adminindex') );
    else
        $bc->add_crumb( $module->getVar('name'), '' );

    $bc->add_crumb( __('Settings', 'rmcommon') );


    $rmTpl->header();

    require $rmTpl->get_template('rmc-settings-form.php', 'module', 'rmcommon');

    $rmTpl->footer();

}


$action = RMHttpRequest::request( 'action', 'string', '' );

switch( $action ){

    /**
     * Show options for a specific element
     */
    case 'configure':
        show_module_preferences();
        break;

    default:
        show_configurable_items();
        break;

}