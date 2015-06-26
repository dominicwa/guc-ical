<?php

	/*
	
		Australian Cinema Releases RSS -> iCal PHP Script
		by Dominic Manley (http://dominicmanley.com/)
		
		Additional Credits:
		
			The Australian Classification Board for providing the cinema releases RSS (http://www.classification.gov.au/Pages/Rss.aspx).
			Kig Konsult for the iCalCreator class (http://www.kigkonsult.se/iCalcreator/).
			
		Version History:
		
			17/12/14 Changed feed source to Australian Classification Board.
			05/01/10 Changed feed source to Village Cinemas (more reliable).
			29/03/09 Ampersand fix.
			24/05/08 The first version, tested with Google Calendar.
	
	 */

	$sFeedSource = 'http://www.classification.gov.au/RSS/Upcoming.aspx';

	$sFeedXML = file_get_contents($sFeedSource);
	$aFeedData = array();
	
	$oXML = new SimpleXMLElement($sFeedXML);
	foreach ($oXML->entry as $oItem) {
		$aItemData['title'] = $oItem->title->__toString();
		$aItemData['rating'] = $oItem->category[0]->attributes()[0]->__toString();
		$aItemData['link'] = $oItem->link->attributes()[1]->__toString();
		$aItemData['timestamp'] = strtotime($oItem->published);
		$aItemData['datetext'] = date('jS M Y', $aItemData['timestamp']);
		$aItemData['synopsis'] =  $oItem->summary->__toString();
		$aFeedData[sizeof($aFeedData)] = $aItemData;
	}
	unset ($oXML);

	//debug//echo '<pre>' . print_r($aFeedData, true) . '</pre>';

	require_once('includes/iCalcreator.class.php'); // @http://www.kigkonsult.se/iCalcreator/
	
	$oCal = new vcalendar();
	
	$oCal->setConfig('guc-ical',
		'dominicmanley.com');
	$oCal->setProperty('X-WR-CALNAME',
		'Australian Cinema Releases');
	$oCal->setProperty('X-WR-CALDESC',
		'Data provided by The Australian Classification Board (http://www.classification.gov.au).
		 Powered by iCalCreator (http://www.kigkonsult.se/iCalcreator/).
		 Written and hosted by Dominic Manley (http://dominicmanley.com/pb/guc-ical/).' );

	foreach ($aFeedData as $aItemData) {
		$oEvent = new vevent();
	
		$oEvent->setProperty('dtstart',
			intval(date('Ymd', $aItemData['timestamp'])),
			array('VALUE' => 'DATE'));
			
		$oEvent->setProperty('summary',
			$aItemData['title']);
		
		$oEvent->setProperty('description',
			'<p>' . str_replace(', ', '<br />', $aItemData['synopsis']) . '</p>' .
			'<p><a href="' . $aItemData['link'] . '">' . $aItemData['link'] . ' </a></p>');
			
		$oCal->addComponent($oEvent);
		
		unset($oEvent);
	}
	
	echo $oCal->createCalendar();
	unset($oCal);

?>