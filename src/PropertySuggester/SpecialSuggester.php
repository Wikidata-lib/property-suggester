<?php

namespace PropertySuggester;
use PropertySuggester\Suggesters\SimplePHPSuggester;
use PropertySuggester\Suggesters\SuggesterEngine;
use Wikibase\Repo\Specials\SpecialWikibaseRepoPage;
use Wikibase\Repo\WikibaseRepo;

class SpecialSuggester extends SpecialWikibaseRepoPage {


    /**
     * @var GetSuggestionsHelper;
     */
    protected $suggestionHelper;

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

        $suggester = new SimplePHPSuggester(wfGetDB(DB_SLAVE));
        $lookup = WikibaseRepo::getDefaultInstance()->getEntityRevisionLookup( 'uncached' );
        $this->suggestionHelper = new GetSuggestionsHelper($lookup, $suggester);
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

        $entity = $out->getRequest()->getText("entity-choser");
        $itemId = $this->parseItemId($entity);
        $item = $this->loadEntity($itemId)->getEntity();
        $label = $item->getLabel($this->language);
        $out->addHTML("entity: $label");

        $out->addWikiMsg( 'propertysuggester-intro' );
        $out->addHTML( '<p>Just enter some properties, the PropertySuggester will propose matching properties ranked by correlation.<br/>'
            . 'Try for example <i>place of birth</i> (person) and <i>singles record</i> (tennis player)'
            . ' and look how the results match to tennis player and persons.</p>'
        );

        $url = $out->getRequest()->getRequestURL();
        $out->addHTML( "<form action='$url' method='get'>");
        $out->addHTML("<input placeholder='Item' id='entity-choser' name='entity-choser' autofocus>" );
        $out->addHTML( "<input value='Dummy' id='add-property-btn2' type='submit'>" );
        $out->addHTML("</form>");
        $out->addHTML("<br/>");
        $out->addHTML("I just came to say hello");

        //$out->addHTML( "<input placeholder='Property' id='property-chooser' autofocus>" );
        //$out->addHTML( "<input type='button' value='Add' id='add-property-btn'> </input>" );

        $out->addHTML( "<p/>" );
        $out->addHtml( "<ul id='selected-properties-list'></ul>" );
        $out->addHtml( "<p/>" );
        $out->addHTML( "<div id='result-item'></div>" );
        $out->addHTML( "<div id='result'></div>" );
    }
}

