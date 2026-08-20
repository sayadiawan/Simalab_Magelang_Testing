<?php

namespace Smt\Masterweb\Http\Controllers;

use Smt\Masterweb\Models\Layout;
use Smt\Masterweb\Models\Module;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Smt\Masterweb\Models\Type;

class AdmLayoutController extends Controller
{
    public function __construct()
	{
		$this->middleware('auth');
	}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        //action
        if($request->get('action') != NULL)
        {
            $action = $request->get('action');
        }else{
            $action = 'index';
        }
        $id_type = $request->segment(3);
        //get all module
         $module = Module::all();
         $LayoutModule = Layout::where(['id_type'=>$id_type,'action'=>$action])->first();
         $type = Type::where(['id'=>$id_type])->first();
         //get auth user
         return view('masterweb::module.admin.layoutmodule.form',compact('module','LayoutModule','type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $data = Layout::updateOrCreate(
            ['id_type' => $request->post('type'),'action' => $request->post('action')],
            ['id_type' => $request->post('type'), 'action' => $request->post('action'), 'layout'=>serialize($request->post('layoutData'))]
        );
        return 'true';
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getOption(Request $request)
    {
        ?>
        <hr>
        <form action="" id="">
            <div class="row">
                <div class="col-md-12">
                    <label for="">Title</label>
                    <input type="text" class="form-control" name="title" value="">
                </div>
            </div>
        </form>
        <?php
    }

    public function columnData(Request $request)
    {
        $column = $request->columnData;
        if($column == "1")
        {
            ?>
            <div class="row layoutRow" data-id="1">
                <div class="col-md-12 text-right">
                    <span><i class="fa fa-bars handle"></i></span>
                </div>
                <div class="col-md-12 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>
            </div>
            <?php
        }else if($column == "2")
        {
            ?>
            <div class="row layoutRow" data-id="2">
                <div class="col-md-12 text-right"><span><i class="fa fa-bars handle"></i></span></div>
                <div class="col-md-8 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>

                <div class="col-md-4 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>
            </div>
            <?php
        }else if($column == "2b")
        {
            ?>
            <div class="row layoutRow" data-id="2b">
                <div class="col-md-12 text-right"><span><i class="fa fa-bars handle"></i></span></div>

                <div class="col-md-4 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>

                <div class="col-md-8 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>
            </div>
            <?php
        }else if($column == "3")
        {
            ?>
            <div class="row layoutRow" data-id="3">
                <div class="col-md-12 text-right"><span><i class="fa fa-bars handle"></i></span></div>

                <div class="col-md-4 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>

                <div class="col-md-4 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>

                <div class="col-md-4 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>
            </div>
            <?php
        }else if($column == "4")
        {
            ?>
            <div class="row layoutRow" data-id="4">
                <div class="col-md-12 text-right">
                    <span><i class="fa fa-bars handle"></i></span>
                    <button class="btn-remove-container"> <i class="fa fa-trash"></i> </button>
                </div>

                <div class="col-md-3 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>

                <div class="col-md-3 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>

                <div class="col-md-3 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>
                
                <div class="col-md-3 layoutColumn" data-id="0">
                    <div class="main card">
                    </div>
                </div>
            </div>
            <?php
        }
        die();
        ?>
        <?php
    }
}
