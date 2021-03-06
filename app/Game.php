<?php

namespace App;

use App\Team;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /**
     * Override the default primary key
     *
     * @var array
     */
    protected $primaryKey = 'game_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'team_a', 'team_b', 'score_a', 'score_b', 'master', 'winner', 'round_id', 'child_game_a', 'child_game_b',
    ];

    /**
     * Teams participating in the game
     *
     * @return void
     */
    public function teams()
    {
        $team_a = Team::where('team_id',$this->team_a)->first();
        $team_b = Team::where('team_id',$this->team_b)->first();
        return collect([$team_a, $team_b]);
    }

    public function victor()
    {
        return $this->belongsTo('App\Team','winner','team_id');
    }

    public function child_a()
    {
        return $this->belongsTo('App\Game','child_game_a','game_id');
    }

    public function child_b()
    {
        return $this->belongsTo('App\Game','child_game_b','game_id');
    }

}
