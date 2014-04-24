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

	public function processResult( WebRequest $request, $out, $user ) {

		$result = $request->getText( 'result' ); // TODO alles was speichert in eigene klasse!
		if ( $result ) {
			$resultQid = $request->getText( 'qid' );
			$opinionAnswer = $request->getText( 'opinion' );
			// TODO request an saveResult übergeben, da auslesen
			$missing = $request->getText( 'missing' );
			$overall = $request->getText( 'overall_exp' );

			$this->saveResult($user, $result, $resultQid , $overall, $missing,$opinionAnswer);
		}

	}

	public function saveResult($user, $result, $qid ,$overall, $missing,$opinionAnswer) {

		$dbw = wfGetDB( DB_MASTER );
		$result = json_decode( $result );
		$missing = $result->questions->missing;
		$properties = json_encode( $result->properties );
		$suggestions_result = json_encode( $result->suggestions );


		$dbw->insert( 'wbs_evaluations',
			array(
				'properties' => $properties, 'suggestions' => $suggestions_result, 'entity' => $qid, 'session_id' => $user,
				'missing' => $missing, 'opinion' => $opinionAnswer  ,'overall'=> $overall ));
	}

} 