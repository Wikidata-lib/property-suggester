<?php
/**
 * PropertySuggester extension.
 * License: GNU GPL v2+
 */

if ( defined( 'PropertySuggester_VERSION' ) ) {
	// Do not initialize more than once.
	return;
}

define( 'PropertySuggester_VERSION', '1.1.1 alpha' );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
}

global $wgExtensionCredits;
$wgExtensionCredits['wikibase'][] = array(
	'path' => __FILE__,
	'name' => 'PropertySuggester',
	'author' => array( 'Christian Dullweber', 'Moritz Finke', 'Felix Niemeyer', 'Virginia Weidhaas' ),
	'url' => 'https://github.com/Wikidata-lib/PropertySuggester',
	'descriptionmsg' => 'propertysuggester-desc'
);

global $wgExtensionMessagesFiles;
$wgExtensionMessagesFiles['PropertySuggester'] = __DIR__ . '/PropertySuggester.i18n.php';
$wgExtensionMessagesFiles['PropertySuggesterAlias'] = __DIR__ . '/PropertySuggester.alias.php';

global $wgMessagesDirs;
$wgMessagesDirs['PropertySuggester'] = __DIR__ . '/i18n';

global $wgSpecialPages;
$wgSpecialPages['PropertySuggester']			= 'PropertySuggester\Evaluation\SpecialEvaluator';

global $wgSpecialPagesGroups;
$wgSpecialPageGroups['PropertySuggester']		= 'wikibaserepo';

global $wgAPIModules;
$wgAPIModules['wbsgetsuggestions'] = 'PropertySuggester\GetSuggestions';

global $wgHooks;
$wgHooks['BeforePageDisplay'][] = 'PropertySuggesterHooks::onBeforePageDisplay';
$wgHooks['UnitTestsList'][] = 'PropertySuggesterHooks::onUnitTestsList';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'PropertySuggesterHooks::onCreateSchema';

$remoteExtPathParts = explode(
	DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR, __DIR__, 2
);

$wgResourceModules['ext.PropertySuggester.EntitySelector'] = array(
	'scripts'       => array( 'modules/ext.PropertySuggester.EntitySelector.js' ),
	'dependencies'  => array( 'jquery.wikibase.entityselector' ),
	'localBasePath' => __DIR__,
	'remoteExtPath' => $remoteExtPathParts[1],
);

$wgResourceModules['ext.PropertySuggester'] = array(
	'scripts'		=> array( 'modules/ext.PropertySuggester.js' ),
	'styles'		=> array( 'modules/ext.PropertySuggester.css' ),
	'dependencies'	=> array( 'ext.PropertySuggester.EntitySelector' ),
	'localBasePath'	=> __DIR__,
	'remoteExtPath'	=> 'PropertySuggester',
);

global $wgPropertySuggesterDeprecatedIds;
$wgPropertySuggesterDeprecatedIds = array(
	45, 70, 71, 74, 76, 77, 107, 168, 173, 295, 741
);
global $wgPropertySuggesterMinProbability;
$wgPropertySuggesterMinProbability = 0.05;
