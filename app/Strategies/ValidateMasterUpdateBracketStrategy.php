<?php

namespace App\Strategies;

use Log;
use App\Repositories\TeamRepository;
use Illuminate\Http\Request;

/**
 * Validator for creating new user brackets
 *
 */
class ValidateMasterUpdateBracketStrategy extends ValidateBaseBracketStrategy
{

    /**
     * bracket exception codes:
     *  BRACKET_EXISTS = 0;
     *  NOT_MASTER = 1;
     *  ROUND_EXISTS = 2;
     *  ROUND_GAME_COUNT = 3;
     *  TEAM_EXISTS = 4;
     *  TEAM_NOT_TBD = 5;
     *  TEAMS_NOT_SAME = 6
     *  LEAVES_MATCH_MASTER = 7;
     *  WINNER_IN_NEXT = 8;
     *  WINNER_FROM_TEAMS = 9;
     *  USER_EXISTS = 10;
     *  USER_MATCHES_OWNER = 11;
     *  NAME_SET = 12;
     *  SUBMISSION_CLOSED = 13;
     *  HALT = 14;
     */

    /**
     * Create a new validator instance
     *
     * @param  TeamRepository  $teams
     * @return void
     */
    public function __construct(TeamRepository $teams)
    {
        $this->teamRepo = $teams;
        $this->allowList = collect([
            $this::NOT_MASTER => 'bracket_id not yet created',
            $this::TEAM_NOT_TBD => 'bracket_id not yet created',
            $this::TEAMS_NOT_SAME => 'master bracket will have same teams',
            $this::WINNER_FROM_TEAMS => 'bracket_id not yet created',
            $this::USER_EXISTS => 'bracket_id not yet created',
            $this::USER_MATCHES_OWNER => 'bracket_id not yet created',
            $this::SUBMISSION_CLOSED => 'master can always be updated',
        ]);
    }


}
