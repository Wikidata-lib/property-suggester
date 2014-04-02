<?php

namespace PropertySuggester;

use SpecialPage;

class SpecialSuggester extends SpecialPage {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct( 'PropertySuggester', '', true );
	}

	/**
	 * Main execution function
	 * @param $par string|null Parameters passed to the page
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
                $out->addHTML( "<input placeholder='Item' id='entity-chooser' autofocus>" );
                $out->addHTML("<br/>");

                
                
		//$out->addHTML( "<input placeholder='Property' id='property-chooser' autofocus>" );
                

		//$out->addHTML( "<input type='button' value='Add' id='add-property-btn'> </input>" );

        $out->addHTML( "<input type='button' value='Dummy' id='add-property-btn2'> </input>" );

		$out->addHTML( "<p/>" );

		$out->addHtml( "<ul id='selected-properties-list'></ul>" );

		$out->addHtml( "<p/>" );
        $out->addHTML( "<div id='result-item'></div>" );

		$out->addHTML( "<div id='result'></div>" );
	}
}

