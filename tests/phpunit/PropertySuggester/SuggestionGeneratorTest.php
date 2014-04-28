<?php

namespace PropertySuggester;

use MediaWikiTestCase;
use PropertySuggester\Suggesters\SuggesterEngine;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\EntityLookup;
use Wikibase\TermIndex;

/**
 * @covers PropertySuggester\GetSuggestionHelper
 * @group PropertySuggester
 * @group API
 * @group medium
 */
class SuggestionGeneratorTest extends MediaWikiTestCase {

	/**
	 * @var SuggestionGenerator
	 */
	protected $suggestionGenerator;

	/**
	 * @var SuggesterEngine
	 */
	protected $suggester;

	/**
	 * @var EntityLookup
	 */
	protected $lookup;

	/**
	 * @var TermIndex
	 */
	protected $termIndex;

	public function setUp() {
		parent::setUp();

		$this->lookup = $this->getMock( 'Wikibase\EntityLookup' );
		$this->termIndex = $this->getMock( 'Wikibase\TermIndex' );
		$this->suggester = $this->getMock( 'PropertySuggester\Suggesters\SuggesterEngine' );

		$this->suggestionGenerator = new SuggestionGenerator( $this->lookup, $this->termIndex, $this->suggester );
	}

	public function testFilterSuggestions() {
		$p7 = PropertyId::newFromNumber( 7 );
		$p10 = PropertyId::newFromNumber( 10 );
		$p12 = PropertyId::newFromNumber( 12 );
		$p15 = PropertyId::newFromNumber( 15 );
		$p23 = PropertyId::newFromNumber( 23 );

		$suggestions = array(
			new Suggestion( $p12, 0.9 ), // this will stay at pos 0
			new Suggestion( $p23, 0.8 ), // this doesn't match
			new Suggestion( $p7, 0.7 ), // this will go to pos 1
			new Suggestion( $p15, 0.6 ) // this is outside of resultSize
		);

		$resultSize = 2;

		$this->termIndex->expects( $this->any() )
			->method( 'getMatchingIDs' )
			->will( $this->returnValue( array( $p7, $p10, $p15, $p12 ) ) );

		$result = $this->suggestionGenerator->filterSuggestions( $suggestions, 'foo', 'en', $resultSize );

		$this->assertEquals( array( $suggestions[0], $suggestions[2] ), $result );

	}

	public function testGenerateSuggestionsWithPropertyList() {
		$properties = array();
		$properties[] = new PropertyId( 'P12' );
		$properties[] = new PropertyId( 'P13' );
		$properties[] = new PropertyId( 'P14' );

		$this->suggester->expects( $this->any() )
			->method( 'suggestByPropertyIds' )
			->with( $this->equalTo( $properties ) )
			->will( $this->returnValue( array( 'foo' ) ) );

		$result1 = $this->suggestionGenerator->generateSuggestionsByPropertyList( array( 'P12', 'p13', '14' ) , 100, 0.0 );
		$this->assertEquals( $result1, array( 'foo' ) );

	}

	public function testGenerateSuggestionsWithItem() {
		$item = Item::newFromArray( array( 'entity' => 'Q42' ) );
		$statement = new Statement( new PropertySomeValueSnak( new PropertyId( 'P12' ) ) );
		$statement->setGuid( 'claim0' ); // otherwise "InvalidArgumentException: Can't add a Claim without a GUID."
		$item->addClaim( $statement );

		$itemId = new ItemId( 'Q42' );

		$this->lookup->expects( $this->once() )
			->method( 'getEntity' )
			->with( $this->equalTo( $itemId ) )
			->will( $this->returnValue( $item ) );

		$this->suggester->expects( $this->any() )
			->method( 'suggestByItem' )
			->with( $this->equalTo( $item ) )
			->will( $this->returnValue( array( 'foo' ) ) );

		$result3 = $this->suggestionGenerator->generateSuggestionsByItem( 'Q42', 100, 0.0 );
		$this->assertEquals( $result3, array( 'foo' ) );
	}

}
