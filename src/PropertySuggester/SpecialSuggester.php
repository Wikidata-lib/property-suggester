<?php

namespace PropertySuggester;

use Html;
use OutputPage;
use PropertySuggester\Suggesters\SimpleSuggester;
use PropertySuggester\Suggesters\SuggesterEngine;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\DataModel\Entity\Entity;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\Repo\Specials\SpecialWikibaseRepoPage;

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

		$lb = wfGetLB( DB_SLAVE );
		$this->suggester = new SimpleSuggester( $lb );
		global $wgPropertySuggesterDeprecatedIds;
		$this->suggester->setDeprecatedPropertyIds($wgPropertySuggesterDeprecatedIds);
	}

	/**
	 * Main execution function
	 * @param $par string|null Parameters passed to the  page
	 * @return bool|void
	 */
	public function execute( $par ) {
		$out = $this->getContext()->getOutput();

        // process response
        $result = $out->getRequest()->getText( 'result' );
        if ($result){
            $oldRequest = $out->getRequest()->getText( 'qid' );
            $this->saveResult($result, $oldRequest);
        }

        // create new form
        $this->setHeaders();
		$out->addStyle( '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
		$out->addModules( 'ext.PropertySuggester' );

		$out->addWikiMsg( 'propertysuggester-intro' );
		$out->addHTML( '<p>Just enter some properties, the PropertySuggester will propose matching properties ranked by correlation.<br/>'
			. 'Try for example <i>place of birth</i> (person) and <i>singles record</i> (tennis player)'
			. ' and look how the results match to tennis player and persons.</p>'
		);

        $qid = $this->getRequest()->getText("next-id");
        if (!$qid) {
            $qid = $this->getRandomQid();
        }

        $url = $out->getRequest()->getRequestURL();
		$out->addHTML( "<form action='$url' method='post' id ='form'>" ); // add Element
        $out->addElement("input", array("type"=> "hidden", "name" => "qid", 'value' => $qid ));
        $out->addElement("input", array("type"=> "hidden", "name" => "result"));
		$out->addHTML( "<br/>" ); // add element

        $item = $this->get_the_item( $qid, $out );
        $label = $item->getLabel( $this->language );
        $itemId = $item->getId()->getSerialization();
        $out->addElement( 'h2', null, "Selected Random Item: $label $itemId" );


        $out->addHTML( Html::openElement( 'ul', array( 'class' => 'property-entries' ) ) );
        $snaks = $item->getAllSnaks();
        foreach ( $snaks as $snak ) {
            $this->addPropertyHtml( $snak, $out );
        }
        $out->addHTML( Html::closeElement( 'ul') );

        $out->addElement( 'h2', null, 'Suggestions' );
        $suggestions = $this->suggester->suggestByItem( $item, 7 );

        $out->addHTML( "<ul class='suggestion_evaluation'>" ); // use addElement
        foreach ( $suggestions as $suggestion ) {
            $this->addSuggestionHtml( $suggestion, $out );
        }
        $out->addHTML( '</ul>' ); // use addElement

        $out->addHTML( "<input value='Submit' id='submit-button' name='submit-button' type='button'  >" ); // add element
        $out->addHTML( "<br/>" );

        // was war gut?

        // was fehlte?

		$out->addHTML( "</form>" );

	}

	public function saveResult( $result, $qid) {
		$identifier = $this->getUser()->getName();
        $dbw = wfGetDB( DB_MASTER );
		$dbw->insert( 'wbs_evaluations' , array( 'content' => $result,'entity' => $qid,  'session_id' => $identifier) );
	}

	/**
	 * @param Suggestion $suggestion
	 * @param OutputPage $out
	 */
	public function addSuggestionHtml( Suggestion $suggestion, OutputPage $out ) {
		$suggestion_prop = $suggestion->getPropertyId();
        try {
		    $plabel = $this->loadEntity( $suggestion_prop )->getEntity()->getLabel( $this->language );
        } catch (\Exception $e) {
            $out->addHTML("ERROR: $suggestion_prop");
            return;
        }
		$pid = $suggestion_prop->getSerialization();
		$out->addHTML( "<li data-property='$pid' data-label ='$plabel'>" );
		$out->addElement( "span", null, $suggestion_prop . " " . $plabel );
		$out->addHTML( "<span class='buttons'>" );
		$out->addElement( 'i', array( 'class' => 'fa fa-smile-o button smile_button', 'data-rating' => '1' ) );
		$out->addElement( 'i', array( 'class' => 'fa fa-meh-o button question_button selected', 'data-rating' => '0' ) );
		$out->addElement( 'i', array( 'class' => 'fa fa-frown-o button sad_button', 'data-rating' => '-1' ) );
		$out->addHTML( "</span>" );
		$out->addHTML( "</li>" );
	}

	/**
	 * @param Snak $snak
	 * @param OutputPage $out
	 */
	public function addPropertyHtml( Snak $snak, OutputPage $out ) {
		$pid = $snak->getPropertyId();
		$plabel = $this->loadEntity( $pid )->getEntity()->getLabel( $this->language );
		$out->addElement( 'li', array( 'data-property' => $pid, 'data-label' => $plabel ), "$pid $plabel" );
	}

	/**
	 * @param string $entity
	 * @return Entity
	 */
	public function get_the_item( $entity ) {
		$itemId = $this->parseItemId( $entity );
		$item = $this->loadEntity( $itemId )->getEntity();
		return $item;
	}

    /**
     * @return string
     */
    public function getRandomQid()
    {
        $dbr = wfGetDB(DB_SLAVE);
        $res = $dbr->select(
            'page',
            array('page_title'), //vars
            array("page_title LIKE 'Q%'"), //cond
            __METHOD__,
            array(
                'ORDER BY' => 'rand()',
                'LIMIT' => 1
            )
        );
        $qid = $res->fetchObject()->page_title;
        return $qid;
    }
}

