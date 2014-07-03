<?php

namespace PropertySuggester\Suggesters;

use LoadBalancerSingle;
use InvalidArgumentException;
use MediaWikiTestCase;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;

/**
 * @covers PropertySuggester\Suggesters\SimpleSuggester
 * @covers PropertySuggester\Suggesters\SuggesterEngine
 * @covers PropertySuggester\Suggesters\Suggestion
 * @group PropertySuggester
 * @group API
 * @group Database
 * @group medium
 */
class SimpleSuggesterTest extends MediaWikiTestCase {

	/**
	 * @var SimpleSuggester
	 */
	protected $suggester;

	private function row( $pid1, $qid1, $pid2, $count, $probability, $context ) {
		return array( 'pid1' => $pid1, 'qid1' => $qid1, 'pid2' => $pid2, 'count' => $count, 'probability' => $probability, 'context' => $context );
	}

	public function addDBData() {
		$rows = array();
		$rows[] = $this->row( 1, null, 2, 100, 0.1, 'item' );
		$rows[] = $this->row( 1, null, 3, 50, 0.05, 'item' );
		$rows[] = $this->row( 2, null, 3, 100, 0.3, 'item' );
		$rows[] = $this->row( 2, null, 4, 200, 0.2, 'item' );
		$rows[] = $this->row( 3, null, 1, 100, 0.5, 'item' );

		$this->db->insert( 'wbs_propertypairs', $rows );
	}

	public function setUp() {
		parent::setUp();

		$this->tablesUsed[] = 'wbs_propertypairs';
		$lb = new LoadBalancerSingle( array("connection" => $this->db ) );
		$this->suggester = new SimpleSuggester( $lb );
	}

	public function testDatabaseHasRows() {
		$res = $this->db->select( 'wbs_propertypairs', array( 'pid1', 'pid2' ) );
		$this->assertEquals( 5, $res->numRows() );
	}

	public function testSuggestByPropertyIds() {
		$ids = array( new PropertyId( 'p1' ) );

		$res = $this->suggester->suggestByPropertyIds( $ids, 100, 0.0 );

		$this->assertEquals( new PropertyId( 'p2' ), $res[0]->getPropertyId() );
		$this->assertEquals( 0.1, $res[0]->getProbability(), '', 0.0001 );
		$this->assertEquals( new PropertyId( 'p3' ), $res[1]->getPropertyId() );
		$this->assertEquals( 0.05, $res[1]->getProbability(), '', 0.0001 );
	}

	public function testSuggestByItem() {
		$item = Item::newFromArray( array( 'entity' => 'q42' ) );
		$statement = new Statement( new PropertySomeValueSnak( new PropertyId( 'P1' ) ) );
		$statement->setGuid( 'claim0' );
		$item->addClaim( $statement );

		$res = $this->suggester->suggestByItem( $item, 100, 0.0 );

		$this->assertEquals( new PropertyId( 'p2' ), $res[0]->getPropertyId() );
		$this->assertEquals( new PropertyId( 'p3' ), $res[1]->getPropertyId() );
	}

	public function testDeprecatedProperties() {
		$ids = array( new PropertyId( 'p1' ) );

		$this->suggester->setDeprecatedPropertyIds( array( 2 ) );

		$res = $this->suggester->suggestByPropertyIds( $ids, 100, 0.0 );

		$resultIds = array_map( function ( Suggestion $r ) { return $r->getPropertyId()->getNumericId(); }, $res );
		$this->assertNotContains( 2 , $resultIds );
		$this->assertContains( 3 , $resultIds );
	}

	public function testEmptyResult() {
		$this->assertEmpty( $this->suggester->suggestByPropertyIds( array(), 10, 0.01 ) );
	}

	public function testInitialSuggestionsResult() {
		$this->suggester->setInitialSuggestions( array( 42 ) );
		$this->assertEquals( array( new Suggestion( new PropertyId( "P42" ), 1.0) ),
							 $this->suggester->suggestByPropertyIds( array(), 10, 0.01, 'item' ) );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidLimit() {
		$this->suggester->suggestByPropertyIds( array(), '10', 0.01 );
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidMinProbability() {
		$this->suggester->suggestByPropertyIds( array(), 10, '0.01' );
	}

}
