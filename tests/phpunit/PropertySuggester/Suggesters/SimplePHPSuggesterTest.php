<?php

namespace PropertySuggester\Suggesters;

use DatabaseBase;
use MediaWikiTestCase;

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;

/**
 *
 * @covers PropertySuggester\Suggesters\SimplePHPSuggester
 *
 * @group PropertySuggester
 *
 * @group API
 * @group Database
 *
 * @group medium
 *
 */
class SimplePHPSuggesterTest extends MediaWikiTestCase {

	/**
	 * @var SimplePHPSuggester
	 */
	protected $suggester;


	private function row( $pid1, $pid2, $count, $probability ) {
		return array( 'pid1' => $pid1, 'pid2' => $pid2, 'count' => $count, 'probability' => $probability );
	}

/*	public function addDBData() {
		$rows = array();
		$rows[] = $this->row( 1, 2, 100, 0.1 );
		$rows[] = $this->row( 1, 3, 50, 0.05 );
		$rows[] = $this->row( 2, 3, 100, 0.1 );
		$rows[] = $this->row( 2, 4, 200, 0.2 );
		$rows[] = $this->row( 3, 1, 100, 0.5 );

		$this->db->delete( 'wbs_propertypairs', "*" );
		$this->db->insert( 'wbs_propertypairs', $rows );
	}*/

	public function setUp() {
		parent::setUp();

		$this->tablesUsed[] = 'wbs_propertypairs';

		$this->suggester = new SimplePHPSuggester( $this->db );

	}

/*	public function testDatabaseHasRows() {
		$res = $this->db->select( 'wbs_propertypairs', array( 'pid1', 'pid2') );
		$this->assertEquals( 5, $res->numRows() );
	}*/

	public function testDbExists() {
		$res = $this->db->select( 'user', array( 'user_id' ) );
		$res = $this->db->select( 'wb_terms', array( 'term_row_id' ) );
		$res = $this->db->select( 'wbs_propertypairs', array( 'pid1' ) );
		$res = $this->db->delete( 'user', "*" );
		$res = $this->db->delete( 'wb_terms', "*" );
		#$res = $this->db->delete( 'wbs_propertypairs', "*" );
		$this->assertTrue(true);
	}

	/*
	public function testDbWriteExists() {
		$res = $this->db->delete( 'user', "*" );
		$res = $this->db->delete( 'wb_terms', "*" );
		$res = $this->db->delete( 'wbs_propertypairs', "*" );
		$this->assertTrue(true);
	}
	*/

/*	public function testSuggestByPropertyIds() {
		$ids = array( PropertyId::newFromNumber( 1 ) );

		$res = $this->suggester->suggestByPropertyIds($ids);

		$this->assertEquals( PropertyId::newFromNumber( 2 ), $res[0]->getPropertyId() );
		$this->assertEquals( PropertyId::newFromNumber( 3 ), $res[1]->getPropertyId() );
	}

	public function testSuggestByItem() {
		$item = Item::newFromArray( array( 'entity' => 'q42' ) );
		$statement = new Statement( new PropertySomeValueSnak( new PropertyId( 'P1' ) ) );
		$statement->setGuid( 'claim0' );
		$item->addClaim( $statement );

		$res = $this->suggester->suggestByItem($item);

		$this->assertEquals( PropertyId::newFromNumber( 2 ), $res[0]->getPropertyId() );
		$this->assertEquals( PropertyId::newFromNumber( 3 ), $res[1]->getPropertyId() );
	}

	public function testDeprecatedProperties() {
		$ids = array( PropertyId::newFromNumber( 1 ) );

		$this->suggester->setDeprecatedPropertyIds( array( 2 ) );

		$res = $this->suggester->suggestByPropertyIds( $ids );

		$resIds = array_map( function( $r ) { return $r->getPropertyId()->getSerialization(); }, $res );
		$this->assertNotContains( "P2", $resIds );
		$this->assertContains( "P3", $resIds );

	}*/

	public function tearDown() {
		parent::tearDown();
	}
}

