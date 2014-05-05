<?php
namespace PropertySuggester\Evaluation;

use WebRequest;

class EvaluationResult
{
	/**
	 * @var \LoadBalancer
	 */
	private $lb;

	public function __construct( \LoadBalancer $lb ) {
		$this->lb = $lb;
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
			$overall = $request->getText( 'overall' )[0];
			$this->saveResult( $user, $result, $resultQid, $overall, $opinionAnswer );
		}

	}

	/**
	 * @param string $user
	 * @param string $result
	 * @param string $qid
	 * @param string $overall
	 * @param string $opinionAnswer
	 * @internal param string $missing
	 */
	private function saveResult( $user, $result, $qid, $overall, $opinionAnswer ) {

		$dbw = $this->lb->getConnection( DB_MASTER );
		$result = json_decode( $result );
		$missing = json_encode( $result->questions->missing );
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
				'overall' => $overall
			)
		);
		$this->lb->reuseConnection( $dbw );
	}

} 