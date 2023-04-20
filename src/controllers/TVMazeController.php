<?php
namespace Vanier\Api\Controllers;

use Vanier\Api\Helpers\WebServiceInvoker;

class TVMazeController extends WebServiceInvoker
{
    //Consume shows resource
    public function handleGetAllShows() : array
    {
        $show_uri = 'https://api.tvmaze.com/shows';
        $data = $this->invokeUri($show_uri);
        $shows = json_decode($data); 
        
        $retrieved_shows = [];

        foreach($shows as $key => $show)
        {
            $retrieved_shows[$key]['name'] = $show->name;
            $retrieved_shows[$key]['language'] = $show->language;
            $retrieved_shows[$key]['status'] = $show->status;
            $retrieved_shows[$key]['premiered'] = $show->premiered;
            $retrieved_shows[$key]['ended'] = $show->ended;
            $retrieved_shows[$key]['genres'] = implode(',', $show->genres);
            $retrieved_shows[$key]['uri'] = $show_uri.'/'.$show->id;
        }

        return $retrieved_shows;
    }
}