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
		
		$out->addHTML("This is the evaluation site for suggetsions of the Property Suggester.<br/> You get a random item and are able to see all its properties.
		 In the next section, you get ranked suggestions of the entity suggester.  <br/> Please mark those as appropriate or good suggestions (green smiling emoticon) or  inapproriate
		 /bad suggestions (red frowning emoticon). <br/>If you don't know what a property is or you cannot state if it is good or bad, use the orange emoticon in the middle.
		 At the end you can enter properties which would have been also good suggestions, but are not in  the list. In the overall rating, please rate the overall quality of the entity suggester (not e.g. the layout of this page).");

		$out->addWikiMsg( 'propertysuggester-intro' );
		$qid = $this->getRequest()->getText("next-id");

        if (!$qid) {
            $qid = $this->getRandomQid();
		}
		$item = $this->get_the_item( $qid );
		$snaks = $item->getAllSnaks();
		if (!$snaks){
			$i=0;
			while ( $i<100 or !$snaks){
				$qid = $this->getRandomQid();
				$item = $this->get_the_item( $qid );
				$snaks = $item->getAllSnaks();
				$i = $i+1;
			}
		}
		$url = $out->getRequest()->getRequestURL();
		$out->addHTML(Html::openElement("form", array("action"=> $url, "method"=>'post', "id"=>'form')));

        $out->addElement("input", array("type"=> "hidden", "name" => "qid", 'value' => $qid ));
        $out->addElement("input", array("type"=> "hidden", "name" => "result"));
		$out->addElement("br");

        $label = $item->getLabel( $this->language );
        $itemId = $item->getId()->getSerialization();
        $out->addElement( 'h2', null, "Selected Random Item: $label $itemId" );


        $out->addHTML( Html::openElement( 'ul', array( 'class' => 'property-entries' ) ) );
        foreach ( $snaks as $snak ) {
            $this->addPropertyHtml( $snak, $out );
        }
        $out->addHTML( Html::closeElement( 'ul') );

        $out->addElement( 'h2', null, 'Suggestions' );
        $suggestions = $this->suggester->suggestByItem( $item, 7 );

        $out->addHTML( Html::openElement("ul",array("class"=>'suggestion_evaluation')));
        foreach ( $suggestions as $suggestion ) {
            $this->addSuggestionHtml( $suggestion, $out );
        }
        $out->addHTML( Html::closeElement("ul") );
		$out->addHTML(Html::openElement("span", array("class" =>"description")));
		$out->addHTML("Which properties were missing?");
		$out->addHTML(Html::closeElement("span"));
		$out->addElement("input", array("name" => "missing", "class" => "question"));

		$out->addElement("br");
		$out->addHTML(Html::openElement("span",array("class" =>"description")));
		$out->addHTML("What did you like/ not like ?");
		$out->addHTML(Html::closeElement("span"));
		$out->addElement("textarea", array("name" => "like", "class"=>"question textfield", "rows"=>"2", "width"=>"200px"));

		$out->addElement("br");
		$out->addHTML(Html::openElement("span",array("class" =>"description")));
		$out->addHTML("Overall experience");
		$out->addHTML(Html::closeElement("span"));
		$out->addHTML(Html::openElement("select",array("name" => "overall_exp", "class"=>"question")));
		$out->addElement("br");
		$out->addElement("option",null,"");
		$out->addElement("option",null,"1 (very good)");
		$out->addElement("option",null,"2");
		$out->addElement("option",null,"3");
		$out->addElement("option",null,"4");
		$out->addElement("option",null,"5");
		$out->addElement("option",null,"6 (very bad)");

		$out->addHTML(Html::closeElement("select"));
		$out->addElement("br");
		$out->addElement("br");
		$out->addElement("input",array("value"=>"Submit", "id"=> "submit-button", "type"=>"button"));

		$out->addHTML( Html::closeElement("form"));

	}

	public function saveResult( $result, $qid) {
		$identifier = $this->getUser()->getName();
        $dbw = wfGetDB( DB_MASTER );
		$result = json_decode($result);
		$missing = $result->questions->missing;
		$properties = json_encode($result->properties);
		$suggestions_result = json_encode($result->suggestions);
		$positive = $result->questions->positive;
		$overall = $result->questions->overall;

		$dbw->insert( 'wbs_evaluations' ,
			array(
				'properties' => $properties, 'suggestions'=> $suggestions_result,'entity' => $qid,  'session_id' => $identifier,
			'missing'=>$missing, 'positive'=> $positive, 'overall'=> $overall ));
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

