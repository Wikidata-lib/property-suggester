<?php

namespace PropertySuggester\Suggesters;

use DatabaseBase;
use MediaWikiTestCase;

use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;

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

	public function setUp() {
		parent::setUp();

		$this->tablesUsed[] = 'wbs_propertypairs';

		$this->suggester = new SimplePHPSuggester( $this->db );

	}

	public function testDbExists() {
		$res = $this->db->select( 'user', array( 'user_id' ) );
		$res = $this->db->select( 'wb_terms', array( 'term_row_id' ) );
		$res = $this->db->select( 'wbs_propertypairs', array( 'pid1' ) );
		$this->assertTrue(true);
	}

	public function tearDown() {
		parent::tearDown();
	}
}

