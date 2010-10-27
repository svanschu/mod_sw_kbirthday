<?php
/**
 * @version $Id$
 * 
 * @package SW KBirthday Module
 *
 * @Copyright (C) 2010 Schultschik Websolution All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking

defined( '_JEXEC' ) or die();

require_once (dirname(__FILE__).DS.'helper.php');

//get the birthday list with connection links
$bday = new ModSWKbirthdayHelper($params);
$res = $bday->getUserBirthday();

if(empty($res)) $res = JText::_('SW_KBIRTHDAY_NOUPCOMING');

$tmpl = $params->get('tmpl');
require(JModuleHelper::getLayoutPath('mod_sw_kbirthday', $tmpl));
