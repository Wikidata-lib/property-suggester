<?php

namespace PropertySuggester;

use MediaWikiTestCase;
use PropertySuggester\Suggesters\SuggesterEngine;
use PropertySuggester\Suggesters\Suggestion;
use Wikibase\DataModel\Entity\EntityId;
use Wikibase\DataModel\Entity\Property;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Entity\Item;
use Wikibase\DataModel\Claim\Statement;
use Wikibase\DataModel\Snak\PropertySomeValueSnak;
use Wikibase\DataModel\Entity\ItemId;
use Wikibase\EntityLookup;
use Wikibase\Repo\WikibaseRepo;
use Wikibase\TermIndex;
use InvalidArgumentException;
use Wikibase\Test\Api\EntityTestHelper;
use Wikibase\Test\Api\WikibaseApiTestCase;

/**
 * @covers PropertySuggester\GetSuggestions
 * @covers PropertySuggester\ResultBuilder
 *
 * @group PropertySuggester
 * @group API
 * @group Wikibase
 * @group WikibaseAPI
 * @group WikibaseRepo
 * @group BreakingTheSlownessBarrier
 * @group Database
 * @group medium
 */
class GetSuggestionTest extends WikibaseApiTestCase {

	/** @var EntityId[] */
	private static $idMap;

	/** @var bool */
	private static $hasSetup;

	/** @var GetSuggestions */
	public $getSuggestions;

	public function setup() {
		parent::setup();

		$this->tablesUsed[] = 'wbs_propertypairs';

		$apiMain = $this->getMockBuilder( 'ApiMain' )->disableOriginalConstructor()->getMockForAbstractClass();
		$this->getSuggestions = new GetSuggestions( $apiMain, 'wbgetsuggestion' );
	}

	public function addDBData() {
		if (!self::$hasSetup) {
			$store = WikibaseRepo::getDefaultInstance()->getEntityStore();

			$prop = Property::newFromType( 'string' );
			$store->saveEntity( $prop, 'EditEntityTestP56', $GLOBALS['wgUser'], EDIT_NEW );
			self::$idMap['%P56%'] = $prop->getId()->getSerialization();

			$prop = Property::newFromType( 'string' );
			$store->saveEntity( $prop, 'EditEntityTestP72', $GLOBALS['wgUser'], EDIT_NEW );
			self::$idMap['%P72%'] = $prop->getId()->getSerialization();

			self::$hasSetup = true;
		}

		$p56 = self::$idMap['%P56%'];
		$p72 = self::$idMap['%P72%'];
		$ip56 = (int)substr( $p56, 1 );
		$ip72 = (int)substr( $p72, 1 );

		$row = array( 'pid1' => $ip56, 'qid1' => null, 'pid2' => $ip72, 'count' => 1,
			'probability' => 0.3, 'context' => 'item' );

		$this->db->insert( 'wbs_propertypairs', array( $row ) );
	}

	public function testDatabaseHasRows() {
		$p56 = self::$idMap['%P56%'];
		$p72 = self::$idMap['%P72%'];
		$ip56 = (int)substr( $p56, 1 );
		$ip72 = (int)substr( $p72, 1 );

		$res = $this->db->select( 'wbs_propertypairs', array( 'pid1', 'pid2' ),  array( 'pid1' => $ip56, 'pid2' => $ip72) );
		$this->assertEquals( 1, $res->numRows() );
	}

	public function testExecution() {
		$p56 = self::$idMap['%P56%'];
		$p72 = self::$idMap['%P72%'];

		$params = array( 'action' => 'wbsgetsuggestions', 'properties' => $p56, 'search' => '*' );
		$res = $this->doApiRequest( $params );
		$result = $res[0];

		$this->assertEquals( 1, $result['success'] );
		$this->assertEquals( '', $result['searchinfo']['search'] );
		$this->assertCount( 1, $result['search'] );
		$suggestions = $result['search'][0];
		$this->assertEquals( $p72, $suggestions['id'] );
	}

	public function testExecutionWithSearch() {
		$p56 = self::$idMap['%P56%'];

		// TODO add terms to do a useful search!
		$params = array( 'action' => 'wbsgetsuggestions', 'properties' => $p56, 'search' => 'IdontExist', 'continue' => 0);
		$res = $this->doApiRequest( $params );
		$result = $res[0];

		$this->assertEquals( 1, $result['success'] );
		$this->assertEquals( 'IdontExist', $result['searchinfo']['search'] );
		$this->assertCount( 0, $result['search'] );
	}

	public function testGetAllowedParams() {
		$this->assertNotEmpty( $this->getSuggestions->getAllowedParams() );
	}

	public function testGetParamDescription() {
		$this->assertNotEmpty( $this->getSuggestions->getParamDescription() );
	}

	public function testGetDescription() {
		$this->assertNotEmpty( $this->getSuggestions->getDescription() );
	}

	public function testGetExamples() {
		$this->assertNotEmpty( $this->getSuggestions->getExamples() );
	}

}
