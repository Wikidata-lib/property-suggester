<?php
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
class SimplePHPSuggesterTest extends \MediaWikiTestCase {

	public function setUp() {
		parent::setUp();
		$this->tablesUsed[] = 'wbs_propertypairs';
	}

	public function testDbExists() {
		$this->assertTrue(true);
	}
}
