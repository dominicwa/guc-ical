<?php

	/*
	
		Cinema Releases -> iCal PHP Script
		by Dominic Manley (http://dominicmanley.com/)
		
		Additional Credits:
		
			- IMDb for publishing the release info (https://www.imdb.com/calendar).
			- Muhammad Imangazaliev for DiDOM HTML parser (https://github.com/Imangazaliev/DiDOM).
			- Kjell-Inge Gustafsson for iCalCreator class (https://github.com/iCalcreator/iCalcreator).
			
		Version History:
		
			11/09/21 Changed feed source to IMDb.
			17/12/14 Changed feed source to Australian Classification Board.
			05/01/10 Changed feed source to Village Cinemas (more reliable).
			29/03/09 Ampersand fix.
			24/05/08 The first version, tested with Google Calendar.
	
	 */

	error_reporting(E_ALL ^ E_NOTICE);

	// Configure the script.

	$aConfig = array(
		'sFeedRegion'			=> 'AU',					// https://www.imdb.com/calendar
		'sTimeZone'				=> 'Australia/Perth',		// https://www.php.net/manual/en/timezones.php
		'bAllowQSOverrides'		=> TRUE
	);

	// Override configs with environment vars (if set).

	if (isset($_ENV['FEED_REGION'])) $aConfig['sFeedRegion'] = $_ENV['FEED_REGION'];
	if (isset($_ENV['TIMEZONE'])) $aConfig['sTimeZone'] = $_ENV['TIMEZONE'];

	// Override configs with querystring vars (if allowed).

	if ($aConfig['bAllowQSOverrides']) {
		if (isset($_GET['r'])) $aConfig['sFeedRegion'] = $_GET['r'];
		if (isset($_GET['tz'])) $aConfig['sTimeZone'] = $_GET['tz'];
	}

	require_once('vendor/autoload.php');

	// Parse the feed source and build a data array.

	use DiDom\Document;
	use DiDom\Query;

	$sFeedSource = 'https://www.imdb.com/calendar?region=' . $aConfig['sFeedRegion'];

	$oDiDoc = new Document($sFeedSource, true);
	$aChildNodes = $oDiDoc->find('#main > *');

	$aDatedReleases = array();
	$sCurrentDate = '';

	foreach ($aChildNodes as $oChild) {
		if ($oChild->getNode()->nodeName == 'h4') {
			$sCurrentDate = $oChild->getNode()->textContent;
			if (!array_key_exists($sCurrentDate, $aDatedReleases))
				$aDatedReleases[$sCurrentDate] = array();
		}
		if ($oChild->getNode()->nodeName == 'ul') {
			$aListNodes = $oChild->find('li > a');
			foreach ($aListNodes as $oList) {
				$sCleanLink = $oList->getNode()->getAttribute('href');
				$sCleanLink = 'https://www.imdb.com' . $sCleanLink;
				$sCleanLink = str_replace('?ref_=rlm', '', $sCleanLink);
				array_push(
					$aDatedReleases[$sCurrentDate], 
					array(
						'title' => $oList->getNode()->textContent,
						'link' => $sCleanLink
					)
				);
			}
		}
	}

	// Construct the calendar feed from the data array.

	use Kigkonsult\Icalcreator\Vcalendar;

	$oCal = new vcalendar();

	$oCal->setXprop(
		Vcalendar::X_WR_CALNAME,
		'Cinema Releases (' . $aConfig['sFeedRegion'] . ')'
	);

	$oCal->setXprop(
		Vcalendar::X_WR_CALDESC,
		"Cinema Releases -> iCal PHP Script by Dominic Manley (http://dominicmanley.com/)"
	);

	foreach ($aDatedReleases as $sDate => $aReleases) {

		foreach ($aReleases as $aReleaseInfo) {

			$oEvent = $oCal->newVevent();

			$oEventSDate = new DateTime(
				$sDate,
				new DateTimezone( $aConfig['sTimeZone'] )
			);

			$oEventEDate = (clone $oEventSDate)->modify('+1 day');

			$oEvent->setDtstart($oEventSDate, array('VALUE' => 'DATE'));
			$oEvent->setDtend($oEventEDate, array('VALUE' => 'DATE'));
			$oEvent->setSummary($aReleaseInfo['title']);
			$oEvent->setDescription( $aReleaseInfo['link']);

			unset($oEvent);

		}

	}
	
	echo $oCal->vtimezonePopulate()->createCalendar();
	unset($oCal);

?>