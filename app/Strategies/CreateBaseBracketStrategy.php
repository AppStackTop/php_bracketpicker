<?php

namespace App\Strategies;

use App\Bracket;
use App\Strategies\AbstractCreateBracketStrategy;

use Log;
use DB;
use Illuminate\Http\Request;


/**
 * Concrete implementation of AbstractCreateBracketStrategy for
 * normal users and their brackets
 *
 */
class CreateBaseBracketStrategy extends AbstractCreateBracketStrategy
{
    /**
     * flag for master bracket
     */
    protected $master = 0;

    protected $teamRepo;

    /**
     * Create a new bracket from request
     *
     * @param Request  $req
     * @return Bracket|null
     */
    public function read($req)
    {
        DB::beginTransaction();
        Log::info('Creating base bracket using CreateBaseBracketStrategy');
        $bracket = $this->readHelper($req);
        if ($this->save($bracket,$req->get('name'),$req->get('user_id'))) {
            // alert user
            $alert = [
                'message' => 'Save successful',
                'level' => 'success'
            ];
        } else {
            Log::error('Something went wrong with user bracket creation');
            // alert user
            $alert = [
                'message' => 'Save unsuccessful',
                'level' => 'danger'
            ];
        }
        return $alert;
    }
}
