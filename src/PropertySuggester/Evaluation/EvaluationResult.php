<?php
/**
 * Created by PhpStorm.
 * User: virginia.weidhaas
 * Date: 4/23/14
 * Time: 3:25 PM
 */

namespace PropertySuggester;

use WebRequest;

class EvaluationResult
{
	public function __construct() {

	}

	/**
	 * @param WebRequest $request
	 * @param string $user
	 */
	public function processResult( WebRequest $request, $user ) {

		$result = $request->getText( 'result' );
		if ( $result ) {
			$resultQid = $request->getText( 'qid' );
			$opinionAnswer = $request->getText( 'opinion' );

			$overall = $request->getText( 'overall_exp' );

			$this->saveResult($user, $result, $resultQid , $overall,$opinionAnswer);
		}

	}

	/**
	 * @param string $user
	 * @param string $result
	 * @param string $qid
	 * @param string $overall
	 * @param string $missing
	 * @param string $opinionAnswer
	 */
	private function saveResult($user, $result, $qid ,$overall, $opinionAnswer) {

		$dbw = wfGetDB( DB_MASTER );
		$result = json_decode( $result );
		$missing = $result->questions->missing;
		$properties = json_encode( $result->properties );
		$suggestions_result = json_encode( $result->suggestions );

		$dbw->insert( 'wbs_evaluations',
			array(
				'session_id' => $user,
				'entity' => $qid,
				'properties' => $properties,
				'suggestions' => $suggestions_result,
				'missing' => $missing,
				'opinion' => $opinionAnswer,
				'overall'=> $overall
			)
		);
	}

} 