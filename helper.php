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

require_once (JPATH_ADMINISTRATOR . DS. 'components' . DS. 'com_kunena' . DS . 'libraries' . DS . 'api.php');
require_once (JPATH_ADMINISTRATOR . DS. 'components' . DS. 'com_kunena' . DS . 'libraries' . DS . 'integration'.DS.'integration.php');
require_once (JPATH_BASE . DS. 'components' . DS. 'com_kunena' . DS . 'class.kunena.php');
require_once (JPATH_BASE . DS. 'components' . DS. 'com_kunena' . DS . 'lib' . DS . 'kunena.link.class.php');
require_once (JPATH_BASE . DS. 'components' . DS. 'com_kunena' . DS . 'lib' . DS . 'kunena.config.class.php');
require_once (JPATH_BASE . DS. 'components' . DS. 'com_kunena' . DS . 'lib' . DS . 'kunena.timeformat.class.php');

class ModSWKbirthdayHelper
{
	/*
	 * @since 1.6.0
	 * @param $from
	 * @param $to
	 * @param $limit
	 * @return list of users
	 */
	private function getBirthdayUser($from, $to, $limit, $today, $cbfield='', $btimeline)
	{
		$app			= JFactory::getApplication();
		$k_config		= KunenaFactory::getConfig ();
		if($k_config->username === 0) $username = 'name';
		else $username 	= 'username';
		$integration	= $k_config->integration_profile;
		if($integration == 'auto')
			$integration	= KunenaIntegration::detectIntegration ( 'profile' , true );
		$db		= JFactory::getDBO();
		if($integration === 'jomsocial'){
			$query = "SELECT b.username, b.name, b.id AS userid, YEAR(a.value) AS year, 
					MONTH(a.value) AS month,DAYOFMONTH(a.value) AS day
					FROM #__community_fields_values AS a 
					INNER JOIN #__users AS b
					ON a.user_id = b.id AND a.field_id = 3
					WHERE ( DAYOFYEAR(a.value)>={$from} AND DAYOFYEAR(a.value)<=";
			if($from>$to){
				$query .= "366) OR ( DAYOFYEAR(a.value)>=0 AND DAYOFYEAR(a.value)<={$to})";
			}else{
				$query .= "{$to})";
			}
		}elseif($integration === 'communitybuilder'){
			if(!empty($cbfield)){
				$cb 	= $db->getEscaped($cbfield);
			}else{
				JError::raiseWarning('', JText::_('SW_KBIRTHDAY_NOCBFIELD'));
				return NULL;
			}
			$query	= "SELECT b.username, b.name, b.id AS userid, YEAR(a.{$cb}) AS year, 
						MONTH(a.{$cb}) AS month,DAYOFMONTH(a.{$cb}) AS day
						FROM #__comprofiler AS a 
						INNER JOIN #__users AS b
						ON a.id = b.id
						WHERE (DAYOFYEAR(a.{$cb})>={$from} AND DAYOFYEAR(a.{$cb})<=";
			if($from>$to){
				$query .= "366) OR (DAYOFYEAR(a.{$cb})>=0 AND DAYOFYEAR(a.{$cb})<={$to})";
			}else{
				$query .= "{$to})";
			}
		}else{
			$query	= "SELECT b.username, b.name, b.id AS userid, YEAR(a.birthdate) AS year, 
						MONTH(a.birthdate) AS month,DAYOFMONTH(a.birthdate) AS day
						FROM #__kunena_users AS a 
						INNER JOIN #__users AS b
						ON a.userid = b.id
						WHERE (DAYOFYEAR(a.birthdate)>={$from} AND DAYOFYEAR(a.birthdate)<=";
			if($from>$to){
				$query .= "366) OR (DAYOFYEAR(a.birthdate)>=0 AND DAYOFYEAR(a.birthdate)<={$to})";
			}else{
				$query .= "{$to})";
			}
		}
		$db->setQuery($query,0,$limit);
		$res	= $db->loadAssocList();
		if($db->getErrorMsg()){ 
			KunenaError::checkDatabaseError();
			if($integration === 'communitybuilder')
				$app->enqueueMessage ( JText::_('SW_KBIRTHDAY_NOCBFIELD_IF') , 'error' );
		}
		
		if(!empty($res)){
			//setting up the right birthdate
			$todayyear	= (int) $today->toFormat('%Y');
			foreach ($res as $k=>$v){
				if($v['year'] == 1 || empty($v['year'])){
					unset($res[$k]);
				}else{
					$res[$k]['birthdate'] = new JDate( mktime(0,0,0,$v['month'],$v['day'],$v['year']) );
					$yday			= (int) $res[$k]['birthdate']->toFormat('%j');
					//we have NOT a leap year?
					if( ($todayyear % 400) != 0 || !( ( $todayyear % 4 ) == 0 && 
					( $todayyear % 100 ) != 0) ){
						//was the birthdate in a leap year?
						if( ($v['year'] % 400) == 0 || ( ( $v['year'] % 4 ) == 0 && ( $v['year'] % 100 ) != 0) ){
							//if we haven't leap year and birthdate was in leapyear we have to cut yday after february
							if($v['month'] >2){
								$res[$k]['birthdate'] 	= new JDate( ( $res[$k]['birthdate']->toUnix() - 86400) );
								$yday 					= $res[$k]['birthdate']->toFormat('%j');
								$ytoday					= (int)$today->toFormat('%j');
								$ytodaybt				= $ytoday+(int)$btimeline;
								if( ( $yday < $ytoday &&  $ytodaybt  <= 366) 
									|| ( $yday < $ytoday &&  $ytodaybt  > 366 && ( $ytodaybt-366 ) < $yday ) ){
										unset($res[$k]);
								}
							}
							//was birthday on 29 february? then show it on 1 march
							if($v['month'] == 2 && $v['day'] == 29){
								$res[$k]['birthdate'] = new JDate( ($res[$k]['birthdate']->toUnix() + 86400) );
							}
						}
					}
				}
			}
		}
		return $res;
	}
	
	
	/*
	 * @since 1.6.0
	 * @param $list Assoc list with user data
	 * @param $con which linktype to use
	 * @param $catid
	 * @param botname name which should be use to creat the threat
	 * @param botid userid which should be used to create the threat
	 * @return array of names/links
	 */
	private function getUserLinkList($list,$today,$params){
		$k_config = new CKunenaConfig();
		$res = '';
		foreach ($list as $k=>$user) {
			if($k_config->username === 0){ $username = $user['name']; }else{ $username = $user['username']; }
			$res[$k]['username']	= $username;
			$con	= $params->get('connection');
			switch ($con){
				case 'profil': 
					$res[$k]['link'] = CKunenaLink::GetProfileLink($user['userid']);
					break;
				case 'forum':
					require_once (JPATH_BASE . DS. 'components' . DS. 'com_kunena' . DS . 'lib' . DS . 'kunena.posting.class.php');
					if((int)$user['birthdate']->toFormat('%j') === (int)$today->toFormat('%j')){
						$subject = JText::sprintf('SW_KBIRTHDAY_SUBJECT', $username);
						$db		= JFactory::getDBO();
						$query	= "SELECT id,catid,subject,time as year FROM #__kunena_messages WHERE subject='{$subject}'";
						$db->setQuery($query,0,1);
						$post	= $db->loadAssoc();
						if($db->getErrorMsg()) KunenaError::checkDatabaseError();
						$catid		= $params->get('bcatid');
						if( empty($post) && !empty($catid) && $post['year']!= (int)$today->toFormat('%Y')){
							$botname	= $params->get('swkbbotname', JText::_('SW_KBIRTHDAY_FORUMPOST_BOTNAME_DEF'));
							$botid		= $params->get('swkbotid');
							$time		= CKunenaTimeformat::internalTime ();
							//Insert the birthday thread into DB
							$query	= "INSERT INTO #__kunena_messages (catid,name,userid,subject,time) 
								VALUES({$catid},'{$botname}',{$botid},'{$subject}', {$time})";
							$db->setQuery($query);
							$db->query();
							if($db->getErrorMsg()) KunenaError::checkDatabaseError();
							//What ID get our thread?
							$messid = (int) $db->insertID();
							//Insert the thread message into DB
							$message= JText::sprintf('SW_KBIRTHDAY_MESSAGE',$user['username']);
							$query	= "INSERT INTO #__kunena_messages_text (mesid,message) 
								VALUES({$messid},'{$message}')";
							$db->setQuery($query);
							$db->query();
							if($db->getErrorMsg()) KunenaError::checkDatabaseError();
							//We know the thread ID so we can update the parent thread id with it's own ID because we know it's
							//the first post
							$query = "UPDATE #__kunena_messages SET thread={$messid} WHERE id={$messid}";
							$db->setQuery($query);
							$db->query();
							if($db->getErrorMsg()) KunenaError::checkDatabaseError();
							$res[$k]['link'] = CKunenaLink::GetTopicPostReplyLink('reply', $catid, $messid, $username);
						}elseif (!empty($post)){
							$res[$k]['link'] = CKunenaLink::GetTopicPostReplyLink('reply', $post['catid'], $post['id'], $username);
						}
					}else{
						$res[$k]['link'] = CKunenaLink::GetProfileLink($user['userid']);
					}
					break;
				default:
					$res[$k]['link'] = $username;
					break;				
			}
		}
		return $res;
	}
	
	/*
	 * Add Age to the Asocc list
	 * @since 1.6.0
	 * @param $linklist
	 * @param $bd
	 * @param $year
	 * @return asocc list
	 */
	private function addUserAge($linklist, $bd, $year){
		$tyear	= (int)$year->toFormat('%Y');
		$tyday	= (int)$year->toFormat('%j');
		foreach ($bd as $key=>$value){
			$byday	= (int)$value['birthdate']->toFormat('%j');
			if( $tyday > $byday) $nexty = 1;
			else $nexty = 0;
			$linklist[$key-1]['age'] = $tyear + $nexty - (int)$value['birthdate']->toFormat('%Y');
		}
		return $linklist;
	}
	
	/*
	 * Sort the birthday list after daysin and username
	 * @since 1.7.0
	 * @param $list array
	 * @return array sorted
	 */
	static private function bsort($list){
		foreach ($list as $v) {
			$temp[$v['daytill']][$v['username']]	= $v;
		}
		//sort after days till
		ksort($temp);
		//second sort after name
		foreach ($temp as $k=>$v){
			ksort($v);
			$temp[$k]=$v;
		}
		unset($list);
		//bring back in old array form
		foreach ($temp as $value) {
			foreach ($value as $v) {
				$ttemp[]	= $v;
			};
		}
		return $ttemp;
	}
	
	/*
	 * Add number of days till birthdate and language string to the Asocc list
	 * @since 1.6.0
	 * @param $linklist
	 * @param $bd
	 * @param $year
	 * @return asocc list
	 */
	static private function addDaysTill($linklist, $bd, $today){
		$tyday		= $today->toFormat('%j');
		$tyear		= $today->toFormat('%Y');
		$bonusday	= 0;
		//We have leap year?
		if( ($tyear % 400) == 0 || ( ( $tyear % 4 ) == 0 && ( $tyear % 100 ) != 0) )
			$bonusday = 1;
		foreach ($bd as $key=>$value){
			$byday	= $value['birthdate']->toFormat('%j');
			if($byday < $tyday) $linklist[$key]['daytill']= (365 + $bonusday - $tyday) + $byday;
			elseif ($byday > $tyday) $linklist[$key]['daytill']= $byday- $tyday;
			else $linklist[$key]['daytill']= 0;
			
			if(empty($linklist[$key]['daytill']) || $linklist[$key]['daytill'] == 0) 
				$linklist[$key]['daystring']= JText::_('SW_KBIRTHDAY_TODAY');
			elseif($linklist[$key]['daytill'] == 1) 
				$linklist[$key]['daystring']= JText::sprintf('SW_KBIRTHDAY_DAY', $linklist[$key]['daytill']);
			else 
				$linklist[$key]['daystring']= JText::sprintf('SW_KBIRTHDAY_DAYS', $linklist[$key]['daytill']);
			
			
			
		}
		$linklist = self::bsort( $linklist);
fb($linklist);
		return $linklist;
	}
	
	/*
	 * Get the list of the user who have birthday in next days
	 * @since 1.6.0
	 * @param $btimeline show birthday from now to x days
	 * @param $limit maximum number of birthdays 
	 * @param $con link target
	 * @param $bcatid category ID where birthday thread is
	 * @param $disage display age?
	 * @return Array
	 */
	static function getUserBirthday($params){
		//get the time sequenz for display birthday
		$btimeline	= $params->get('nextxdays');
		//get the date today
		$timefrom	= $params->get('timefrom');
		$config	= JFactory::getConfig();
		$soffset = $config->getValue('config.offset'); 
		switch ($timefrom){
			case 'website':
				$timeo	= new JDate( '', $soffset);
				break;
			case 'user':
				$user	=& JFactory::getUser();
				if(!$user->guest){
					$offset	= $user->getParam('timezone');
					if(!empty($offset))
						$timeo	= new JDate( '', $offset);
					else
						$timeo	= new JDate( '', $soffset);
				}
				break;
			default:
				$timeo		= new JDate();
		}
		$datemaxo	= new JDate( ($timeo->toUnix() + ( $btimeline * 86400) ) );
		//get limit number for birthdays
		$limit		= $params->get('limit');
		
		//get the list of user birthdays
		$cbfield	= $params->get('swkbcbfield');
		$list		= self::getBirthdayUser($timeo->toFormat('%j'),$datemaxo->toFormat('%j'),$limit, $timeo, $cbfield , $btimeline);
		
		$list1 = '';
		if(!empty($list)){
			$list1		= self::getUserLinkList($list,$timeo, $params);
			$list1		= self::addDaysTill($list1,$list, $timeo);
			
			$disage		= $params->get('displayage');
			if (!empty($disage)) $list1 = self::addUserAge($list1,$list,$timeo); 

			If(!empty($list1)){
				foreach ($list1 as $k=>$v){
					if (!empty($v['age']) ) $age = JText::sprintf('SW_KBIRTHDAY_ADD_AGE', $v['age']);
					else $age='';
					$list1[$k]['link']		= JText::sprintf('SW_KBIRTHDAY_HAVEBIRTHDAYIN', $v['link'], $v['daystring'], $age );
				}
			}
		}
		
		return $list1;
	}
}