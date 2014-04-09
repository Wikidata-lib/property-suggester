<?php

namespace PropertySuggester;

use Html;
use PropertySuggester\Suggesters\SimpleSuggester;
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

		$this->setHeaders();
		$out->addStyle( '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
		$out->addModules( 'ext.PropertySuggester' );

		$out->addWikiMsg( 'propertysuggester-intro' );
		$out->addHTML( '<p>Just enter some properties, the PropertySuggester will propose matching properties ranked by correlation.<br/>'
			. 'Try for example <i>place of birth</i> (person) and <i>singles record</i> (tennis player)'
			. ' and look how the results match to tennis player and persons.</p>'
		);

		$url = $out->getRequest()->getRequestURL();
		$out->addHTML( "<form action='$url' method='post' id ='form'>" );
		$out->addHTML( "<input placeholder='Item' id='entity-chooser' name='entity-chooser' autofocus>" );
		$out->addHTML( "<input value='Send' id='add-property-btn2' type='submit'  >" );
		$out->addElement("input", array("type"=> "hidden", "name" => "result", 'id'=>'result'));
		$out->addHTML( "<br/>" );
		$entity = $out->getRequest()->getText( "entity-chooser" );
		if ( $entity ) {
			$item = $this->get_the_item( $entity, $out );

			$snaks = $item->getAllSnaks();
			foreach ( $snaks as $snak ) {
				$this->add_properties( $snak, $out );
			}
			$out->addHTML( Html::closeElement( 'ul') );

			$suggestions = $this->suggester->suggestByItem( $item, 7 );

			$out->addElement( 'h2', null, 'Suggestions' );
			$out->addHTML( "<ul class='suggestion_evaluation'>" );

			foreach ( $suggestions as $suggestion ) {
				$this->add_suggestions( $suggestion, $out );
			}
			$out->addHTML( '</ul>' );
			$out->addHTML( "<input value='Submit' id='submit-button' name='submit-button' type='button'  >" );
			$out->addHTML( "<br/>" );

			// was war gut?

			// was fehlte?

		}
		$out->addHTML( "</form>" );

		$result = $out->getRequest()->getText( "result" );
		if ($result){
			$this->saveResult($result);
		}
	}

	public function saveResult( $result) {
		$identifier = $this->getUser()->getName();
		$dbw = wfGetDB( DB_MASTER );
		$dbw->insert( 'wbs_evaluations' , array( 'content' => $result, 'session_id' => $identifier) );

	}

	/**
	 * @param $suggestion
	 * @param $out
	 */
	public function add_suggestions( $suggestion, $out ) {
		$suggestion_prop = $suggestion->getPropertyId();
		$plabel = $this->loadEntity( $suggestion_prop )->getEntity()->getLabel( $this->language );
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
	 * @param $snak
	 * @param $out
	 */
	public function add_properties( $snak, $out ) {
		$pid = $snak->getPropertyId();
		$plabel = $this->loadEntity( $pid )->getEntity()->getLabel( $this->language );
		$out->addElement( 'li', array( 'data-property' => $pid, 'data-label' => $plabel ), "$pid $plabel" );
	}

	/**
	 * @param $entity
	 * @param $out
	 * @return \Wikibase\Entity
	 */
	public function get_the_item( $entity, $out ) {
		$itemId = $this->parseItemId( $entity );
		$item = $this->loadEntity( $itemId )->getEntity();
		$label = $item->getLabel( $this->language );
		$this->add_elements( $out, $label, $itemId );
		return $item;
	}


	/**
	 * @param $out
	 * @param $label
	 * @param $itemId
	 */
	public function add_elements( $out, $label, $itemId ) {
		$out->addElement( 'h2', null, "Chosen Item: $label" );
		$out->addElement( 'div', array( 'class' => 'entry', 'data-entry-id' => "$itemId" ) );
		$out->addHTML( Html::openElement( 'ul', array( 'class' => 'property-entries' ) ) );
	}
}

