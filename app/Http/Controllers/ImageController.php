<?php

namespace App\Http\Controllers;
use App\Images;
use DB;
use Response; 
use Illuminate\Http\Request;
use Validate;

class ImageController extends Controller
{
    public function index(){

        //$images = Images::all();

        $data = array();
        $image = DB::table('images')->get();
        
        foreach($image as $img){

            $page = (($img->id/9) + 1);

            $data[] = array(
                "id" => $img->id,
                "name" => $img->name,
                "url" => $img->url,
                "requestCount" => $img->requestCount,
                "page" => (int)$page,
            );
      
        }

        return response() -> json(["Data" => [$data]]);    
    }

    //Show using ID
    public function show(Images $image){

        $image->requestCount += 1;
        $image->update(); 

        $page = (($image->id/9) + 1);

        return response()-> json([ "Data" => [
                            'id' => $image->id,
                            'name' => $image->name,
                            'url' => $image->url,
                            'requestCount' => $image->requestCount,
                            'pages' => (int)$page,
                            ]
                        ]);
        
    }

    //Show using page
    public function byPage($pages){

        $firstID = 9 * ($pages - 1);
        $lastID  = 9 * $pages;

        $pageData = array();

        // $result = Images::query()->where ('id' , '>' , $firstID)->where('id' , '<=' , $lastID)->get();
        //  //$result = DB::select('select * from images where id > ? and id <= ?', [$firstID, $lastID]);
        
        $result = DB::table('images')->where ('id' , '>' , $firstID)->where('id' , '<=' , $lastID)->get();
        
        foreach($result as $res){
            
            $pageData[] = array(
                "id" => $res->id,
                "name" => $res->name,
                "url" => $res->url,
                "requestCount" => $res->requestCount,
                "page" => $pages,
            );
             
        }
        return response() -> json(["Data" => [$pageData]]);
    }

    public function byPopular(){

        $result = DB::select('select * from images where requestCount=(select max(requestCount) from images)');

        foreach($result as $res){

            $page = (($res->id/9) + 1);

            $pageData[] = array(
                "id" => $res->id,
                "name" => $res->name,
                "url" => $res->url,
                "requestCount" => $res->requestCount,
                "page" => (int)$page
            );

        }
        return response() -> json(["Data" => [$pageData]]);
    }

    public function create(Request $request){

        $this->validate($request, [
            'name' => 'required',
            'url'  => 'required',
        ]);

        $data = Images::create($request->all());

        return response()-> json([ "Data" => [
            'name' => $data->name,
            'url' => $data->url,
            ]  
        ]);
 
    }
}
