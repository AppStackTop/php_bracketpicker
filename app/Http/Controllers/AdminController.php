<?php

namespace App\Http\Controllers;

use Log;
use App\Team;
use App\Bracket;
use App\Region;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\TeamRepository;

class AdminController extends Controller
{
    /**
     * The team repository instance
     *
     * @var TeamRepository
     */
    protected $teamrepo;

    /**
     * Create a new controller instance.
     *
     * @param  TaskRepository  $tasks
     * @return void
     */
    public function __construct(TeamRepository $teams)
    {
        $this->teamrepo = $teams;
    }
    /**
     * Display admin home page
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        return view('admin.index');
    }

    /**
     * Show master bracket unless needs to be created
     * in which case, redirect to showCreateMaster
     *
     * @param  Request  $request
     * @return Response
     */
    public function showMaster(Request $request)
    {
        $bracket = Bracket::where('master',true)->first();
        if(empty($bracket)) {
            //need to set up master bracket
            $teams = Team::all();
            $regions = Region::where('region','<>','')->get();
            return view('admin.create_master',[
                'teamrepo' => $this->teamrepo,
                'teams' => $teams,
                'regions' => $regions,
                'region_size' => 16,
            ]);
        }
        return view('admin.bracket',[
            'master' => $bracket
        ]);
    }

    /**
     * Validate all constraints are met and then save
     * master bracket. Check for start tournament flag
     *
     * @param  Request  $request
     * @return Response
     */
    public function createMaster(Request $request)
    {
        if ($request->start_madness==='true') {
            Log::debug('Master initiation request received');
            $this->validate($request, [
                'team.South.*' => 'required|exists:teams,name',
                'team.East.*' => 'required|exists:teams,name',
                'team.West.*' => 'required|exists:teams,name',
                'team.Midwest.*' => 'required|exists:teams,name',
            ]);
            // update teams
        } else {
            Log::debug('Master creation request received');
            $this->validate($request, [
                'team.South.*' => 'exists:teams,name',
                'team.East.*' => 'exists:teams,name',
                'team.West.*' => 'exists:teams,name',
                'team.Midwest.*' => 'exists:teams,name',
            ]);
        }

        foreach ($request->team as $region => $teams) {
            foreach ($teams as $rank => $team) {
                if(!empty($team)) {
                    Log::debug('Team: '.$team.' found in form with region: '.$region.' and rank: '.$rank);
                    $team_actual = Team::where('name',$team)->first();
                    $team_actual->setRegionRank($region,$rank);
                }
            }
        }
        $alert = [
            'message' => 'Save successful',
            'level' => 'success'
        ];
        $request->session()->put('alert', $alert);

        return redirect()->action('AdminController@showMaster');
    }

    public function setMaster(BracketSetRequest $request)
    {
        $master = Bracket::where('master',true)->first();

        return redirect()->action('AdminController@showMaster');
    }

    public function listTeams(Request $request)
    {
        $teams = Team::all();
        $regions = Region::all();
        return view('admin.teams',[
            'teams' => $teams,
            'regions' => $regions
        ]);
    }

    public function createTeam(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'mascot' => 'max:255',
            'primary_color' => array('regex:/^([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?$/'),
            'accent_color' => array('regex:/^([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?$/'),
            'region' => 'exists:regions,region',
            'rank' => 'between:0,17'
        ]);
        // save team
        Team::create([
            'name' => $request->name,
            'mascot' => $request->mascot,
            'icon_path' => '/path/to/icon',
            'primary_color' => $request->primary_color,
            'accent_color' => $request->accent_color,
            'region_id' => Region::where('region',$request->region)->first()->region_id,
            'rank' => $request->rank,
        ]);
        $alert = [
            'message' => 'Save successful',
            'level' => 'success'
        ];

        return redirect()->action('AdminController@listTeams');
    }

    public function viewTeam(Request $request, Team $team)
    {
        $regions = Region::all();
        return view('admin.team_details',[
            'old' => [
                'name'=>$team->name,
                'mascot'=>$team->mascot,
                'primary_color'=>$team->primary_color,
                'accent_color'=>$team->accent_color,
                'rank'=>$team->rank,
                'region'=>$team->region
            ],
            'regions' => $regions,
            'team' => $team
        ]);
    }

    public function updateTeam(Request $request, Team $team)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'mascot' => 'max:255',
            'primary_color' => array('regex:/^([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?$/'),
            'accent_color' => array('regex:/^([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?$/'),
            'region' => 'exists:regions,region',
            'rank' => 'between:0,17'
        ]);

        $team->name = $request->name;
        $team->mascot = $request->mascot;
        $team->primary_color = $request->primary_color;
        $team->accent_color = $request->accent_color;
        $team->setRegionRank($request->region, $request->rank);

        $team->save();

        $alert = [
            'message' => 'Save successful',
            'level' => 'success'
        ];
        $request->session()->put('alert', $alert);

        return redirect()->action('AdminController@listTeams');
    }

    public function destroyTeam(Request $request, Team $team)
    {
        $name = $team->name;
        $team->delete();

        $alert = [
            'message' => 'Team ('.$name.') deleted',
            'level' => 'warning'
        ];
        $request->session()->put('alert', $alert);

        return redirect()->action('AdminController@listTeams');
    }

}
