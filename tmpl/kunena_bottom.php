<?php
/**
 * @version $Id$
 * 
 * @package Kunena
 *
 * @Copyright (C) 2010 Schultschik Websolution All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking

defined( '_JEXEC' ) or die();
if(is_array($res)){
	$doc = & JFactory::getDocument();
	$style = '#Kunena div.sw_kbirthday td.kcol-first{width:1%;}
				#Kunena .swkbicon{
					background: url("media/mod_sw_kbirthday/birthday.png") no-repeat center top transparent scroll;
					height: 32px;
					width: 32px;}';
	$doc->addStyleDeclaration($style);
?>
<div class="kblock sw_kbirthday <?php echo $params->get('moduleclass_sfx', '') ?>">
	<div class="kheader">
		<span class="ktoggler">
			<!-- <a class="ktoggler close" rel="sw_kbirthday" title="<?php echo JText::_('COM_KUNENA_TOGGLER_COLLAPSE') ?>" />-->
		</span>
		<h2><span class="ktitle km">
			<?php echo JText::_('SW_KBIRTHDAY_BIRTHDAY')?>
		</span></h2>
	</div>
	<div class="kcontainer" id="sw_kbirthday">
		<div class="kbody">
			<table class = "kblocktable">
				<tr class = "krow2">
					<td class = "kcol-first">
						<div class="swkbicon"></div>
					</td>
					<td class = "kcol-mid km">
						<div class="sw_kbirthdy ks">
							<?php foreach ($res as $v){
								
								echo $v['link'];
							}?>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
<?php
}else{
	echo $res;
}?>