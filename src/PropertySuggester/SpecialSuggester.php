<?php

namespace PropertySuggester;

use PropertySuggester\Suggesters\SimplePHPSuggester;
use PropertySuggester\Suggesters\SuggesterEngine;
use Wikibase\Repo\Specials\SpecialWikibaseRepoPage;
use Wikibase\Repo\WikibaseRepo;

class SpecialSuggester extends SpecialWikibaseRepoPage
{


	/**
	 * @var SuggesterEngine
	 */
	protected $suggester;

	/**
	 * @var string
	 */
	protected $language;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'PropertySuggester', '' );
		$this->language = $this->getContext()->getLanguage()->getCode();

		$dbr = wfGetDB( DB_SLAVE );
		$this->suggester = new SimplePHPSuggester( $dbr );
	}

	/**
	 * Main execution function
	 * @param $par string|null Parameters passed to the  page
	 * @return bool|void
	 */
	public function execute( $par ) {
		$out = $this->getContext()->getOutput();
		$this->setHeaders();
		$out->addModules( 'ext.PropertySuggester' );

		$out->addWikiMsg( 'propertysuggester-intro' );
		$out->addHTML( '<p>Just enter some properties, the PropertySuggester will propose matching properties ranked by correlation.<br/>'
			. 'Try for example <i>place of birth</i> (person) and <i>singles record</i> (tennis player)'
			. ' and look how the results match to tennis player and persons.</p>'
		);

		$url = $out->getRequest()->getRequestURL();
		$out->addHTML( "<form action='$url' method='get'>" );
		$out->addHTML( "<input placeholder='Item' id='entity-chooser' name='entity-chooser' autofocus>" );
		$out->addHTML( "<input value='Dummy' id='add-property-btn2' type='submit'  >" );
		$out->addHTML( "</form>" );
		$out->addHTML( "<br/>" );

		$entity = $out->getRequest()->getText( "entity-chooser" );
		if ( $entity ) {
			$itemId = $this->parseItemId( $entity );
			$item = $this->loadEntity( $itemId )->getEntity();
			$label = $item->getLabel( $this->language );

			$out->addElement( "h2", null, "Choosen Item: ".$label );

			$snaks = $item->getAllSnaks();
				foreach ( $snaks as $snak) {
					$pid = $snak->getPropertyId();
					$plabel = $this->loadEntity($pid)->getEntity()->getLabel( $this->language );
					$out->addElement( "p", null, $pid." ".$plabel );
				}

			$suggestions = $this->suggester->suggestByItem( $item );

			$out->addElement( "h2", null, "Suggestions" );
			for ($i=0; $i<7; $i++) {
				$suggestion_prop = $suggestions[$i]->getPropertyId();
				$plabel = $this->loadEntity($suggestion_prop)->getEntity()->getLabel( $this->language );
				$pid = $suggestion_prop->getSerialization();
				$out->addHTML("<div class='suggestions_entry' data-property='$pid'>");

				$out->addElement( "span", null, $suggestion_prop ." ".$plabel );
				$out->addElement( "span", array( 'class'=> 'button smile_button', 'href' => '#') );
				$out->addElement( "span", array( 'class'=> 'button sad_button', 'href' => '#' ) );
				$out->addHTML("</div>");
			}
			//$out->addHTML( "<form action='$url' method='get'>" );
			$out->addHTML( "<input value='Submit' id='submit-button' name='submit-button' type='button'  >" );
			//$out->addHTML( "</form>" );
			$out->addHTML( "<br/>" );

		}
	}
}

