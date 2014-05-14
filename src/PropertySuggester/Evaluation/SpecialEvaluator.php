<?php

namespace PropertySuggester\Evaluation;

use LoadBalancer;
use Html;
use OutputPage;
use PropertySuggester\Suggesters\SimpleSuggester;
use PropertySuggester\Suggesters\SuggesterEngine;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\DataModel\Entity\Entity;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Snak\Snak;
use Wikibase\Repo\Specials\SpecialWikibaseRepoPage;


class SpecialEvaluator extends SpecialWikibaseRepoPage
{

	/**
	 * @var LoadBalancer
	 */
	protected $lb;

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
		$this->lb = wfGetLB( DB_SLAVE );
		$this->resultEvaluation = new EvaluationResult($this->lb);
		$this->suggester = new SimpleSuggester( $this->lb );
		global $wgPropertySuggesterDeprecatedIds;
		$this->suggester->setDeprecatedPropertyIds( $wgPropertySuggesterDeprecatedIds );
	}

	/**
	 * Main execution function
	 * @param $par string|null Parameters passed to the  page
	 * @return bool|void
	 */
	public function execute( $par ) {
		$out = $this->getContext()->getOutput();
		$out->addModules( 'ext.PropertySuggester' );

		// process response
		$old_request = $out->getRequest();
		$user = $this->getUser()->getName();
		$this->resultEvaluation->processResult( $old_request,  $user );


		// create new form
		$this->setHeaders();
		$out->addStyle( '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );

		// TODO wiki-msg
		$out->addHTML( "This is the Evaluation site for suggestions of the Property Suggester.<br/> You get a random item and are able to see all its properties.
		 In the next section, you get ranked suggestions of the entity suggester.  <br/> Please mark those as appropriate or good suggestions (green smiling emoticon) or  inapproriate
		 /bad suggestions (red frowning emoticon). <br/>If you don't know what a property is, choose the question mark. </br> If you cannot state if it is good or bad, use the orange emoticon in the middle.
		 At the end you can enter properties which would have been also good suggestions, but are not in  the list. In the overall rating, please rate the overall quality of the entity suggester (not e.g. the layout of this page)." );

		//$out->addWikiMsg( 'propertysuggester-intro' );
		$item = $this->getNewItemForUser( $user );
		$itemLabel = $item->getLabel( $this->language );
		$itemId = $item->getId()->getSerialization();
		$suggestions = $this->suggester->suggestByItem( $item, 7, 0.0 );
		$url = $out->getRequest()->getRequestURL();
		$description = $item->getDescription($this->language);
		$out->addHTML( Html::openElement( "form", array( "action" => $url, "method" => 'post', "id" => 'form' ) ) );

		$out->addHTML( HTML::hidden( 'qid', $itemId ) );
		$out->addHTML( HTML::hidden('result', '') );
		$out->addElement( "br" );
		$Itemurl = $this->getEntityTitle( $item->getId() )->getFullUrl();
		$Itemlink = Html::element( 'a', array( 'href' => $Itemurl ), "$itemLabel $itemId ");

		$out->addHTML(Html::openElement("h2"));
		$out->addHTML("Selected Random Item: " .$Itemlink ."($description)" );
		$out->addHTML(Html::closeElement("h2"));


		$out->addHTML( Html::openElement( 'ul', array( 'class' => 'property-entries' ) ) );
		$claims = $item->getClaims();
		foreach ( $claims as $claim ) {
			$this->addPropertyHtml( $claim->getMainSnak(), $out );
		}
		$out->addHTML( Html::closeElement( 'ul' ) );

		$out->addElement( 'h2', null, 'Suggestions' );

		$out->addHTML( Html::openElement( "ul", array( "class" => 'suggestion_evaluation' ) ) );
		foreach ( $suggestions as $suggestion ) {
			$this->addSuggestionHtml( $suggestion, $out );
		}
		$out->addHTML( Html::closeElement( "ul" ) );
		$out->addHTML( Html::openElement( "span", array( "class" => "description" ) ) );
		$out->addHTML( "Which properties were missing?" );

		$out->addHTML( Html::closeElement( "span" ) );
		$out->addElement( "input", array( "name" => "property-chooser", "class" => "question" ) );
		$out->addElement("i", array( 'class' => 'fa fa-plus', 'id' => 'addButton' ) );
		$out->addElement("ul", array("id"=>"missing-properties"));
		$out->addElement( "br" );

		$out->addHTML( Html::openElement( "span", array( "class" => "description" ) ) );
		$out->addHTML( "What did you like/ not like ?" );
		$out->addHTML( Html::closeElement( "span" ) );
		$out->addElement( "textarea", array( "name" => "opinion", "class" => "question textfield", "rows" => "2", "width" => "200px" ) );

		$out->addElement( "br" );

		$out->addHTML( Html::openElement( "span", array( "class" => "description" ) ) );
		$out->addHTML( "Overall experience" );
		$out->addHTML( Html::closeElement( "span" ) );
		$out->addHTML( Html::openElement( "select", array( "name" => "overall", "class" => "question" ) ) );
		$out->addElement( "option", null, "" );
		$out->addElement( "option", null, "1 (very good)" );
		$out->addElement( "option", null, "2" );
		$out->addElement( "option", null, "3" );
		$out->addElement( "option", null, "4" );
		$out->addElement( "option", null, "5" );
		$out->addElement( "option", null, "6 (very bad)" );
		$out->addHTML( Html::closeElement( "select" ) );

		$out->addElement( "br" );
		$out->addElement( "br" );
		$out->addElement( "input", array( "value" => "Submit", "id" => "submit-button", "type" => "button" ) );

		$out->addHTML( Html::closeElement( "form" ) );

	}


	/**
	 * @param Suggestion $suggestion
	 * @param OutputPage $out
	 */
	public function addSuggestionHtml( Suggestion $suggestion, OutputPage $out ) {
		$suggestionPropertyId = $suggestion->getPropertyId();
		$suggestionProbability = $suggestion->getProbability();
		try {
			$plabel = $this->loadEntity( $suggestionPropertyId )->getEntity()->getLabel( $this->language );
		} catch ( \Exception $e ) {
			$out->addHTML( "ERROR: $suggestionPropertyId" );
			return;
		}
		$pid = $suggestionPropertyId->getSerialization();
		$out->addHTML(Html::openElement("li", array('data-property'=> $pid, 'data-label' => $plabel, 'data-probability' => $suggestionProbability ) ));
		$out->addElement( "span", null, "$suggestionPropertyId $plabel" );
		$out->addHTML( "<span class='evaluation-box'>" );
		$out->addElement( 'i', array( 'class' => 'fa fa-smile-o evaluation-button smile_button', 'data-rating' => '1' ) );
		$out->addElement( 'i', array( 'class' => 'fa fa-meh-o evaluation-button meh_button ', 'data-rating' => '0' ) );
		$out->addElement( 'i', array( 'class' => 'fa fa-frown-o evaluation-button sad_button', 'data-rating' => '-1' ) );
		$out->addElement( 'i', array( 'class' => 'fa fa-question evaluation-button question_button selected', 'data-rating' => '-2' ) );
		$out->addHTML( Html::closeElement('span') );
		$out->addHTML( Html::closeElement('li') );
	}

	/**
	 * @param Snak $snak
	 * @param OutputPage $out
	 */
	public function addPropertyHtml( Snak $snak, OutputPage $out ) {
		$pid = $snak->getPropertyId();
		$plabel = $this->loadEntity( $pid )->getEntity()->getLabel( $this->language );
		$url = $this->getEntityTitle( $pid )->getFullUrl();
		$link = Html::element( 'a', array( 'href' => $url ), "$pid $plabel");
		$out->addHTML( Html::openElement('li', array( 'data-property' => $pid, 'data-label' => $plabel ) ) );
		$out->addHTML( $link );
		$out->addHTML( Html::closeElement( 'li' ) );
	}

	/**
	 * @param string $entity
	 * @return Entity
	 */
	public function getItem( $entity ) {
		$itemId = $this->parseItemId( $entity );
		$item = $this->loadEntity( $itemId )->getEntity();
		return $item;
	}

	/**
	 * @return string
	 */
	public function getRandomQid() {
		$dbr = $this->lb->getConnection( DB_SLAVE );
		$res = $dbr->select(
			'page',
			array( 'page_title' ), //vars
			array( 'page_content_model' => 'wikibase-item' ), //cond
			__METHOD__,
			array(
				'ORDER BY' => 'rand()',
				'LIMIT' => 1
			)
		);
		$this->lb->reuseConnection( $dbr );

		$qid = $res->fetchObject()->page_title;
		return $qid;
	}

	/**
	 * @param $identifier
	 * @param $qid
	 * @return bool
	 */
	public function wasAlreadyEvaluatedByUser( $identifier, $qid ) {
		$dbr = $this->lb->getConnection( DB_SLAVE );
		$entityResults = $dbr->select(
			'wbs_evaluations',
			array( 'entity' ),
			array( "session_id" => $identifier, "entity" => $qid )
		);
		$this->lb->reuseConnection( $dbr );
		$numberOfRows = $entityResults->numRows();
		return $numberOfRows > 0;
	}

	/**
	 * @param $identifier
	 * @return Item
	 */
	public function getNewItemForUser( $identifier ) {
		$qid = $this->getRequest()->getText( "next-id" );

		if ( $qid ) {
			$item = $this->getItem( $qid );
			return $item;
		}

		$item = null;
		for( $i=0; $i<100; $i++ ) {
			$qid = $this->getRandomQid();
			$wasEvaluated = $this->wasAlreadyEvaluatedByUser( $identifier, $qid );
			try {
			$item = $this->getItem( $qid );
			} catch (\Exception $e) {
				var_dump( $qid);
			}
			$claims = $item->getClaims();
			if ( !$wasEvaluated && count($claims) > 0 ) {
				break;
			}
		}
		return $item;
	}
}

