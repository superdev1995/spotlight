<?php

class Autocompleter {
    private $curl;
    private $urlbase;

    public function __construct($urlbase)
    {
        $this->curl = new Curl\Curl();
        $this->urlbase = $urlbase;
    }

    public function publish($user_id, $story_id){
        $this->curl->setHeader("Content-Type","application/json");
        $this->curl->post($this->urlbase . "/train_story",json_encode(array(
           'user_id' => $user_id,
           'story_id' => $story_id,
        )));
    }

    public function wrap_texts($data){
        $str = implode(". ", [@$data['story_action_1'],
                @$data['story_action_2'],
                @$data['story_action_3'],
                @$data['story_action_4'],
                @$data['story_action_5'],
                @$data['story_action_6'],
                @$data['story_reflection_1'],
                @$data['story_reflection_2'],
                @$data['story_reflection_3'],
                @$data['story_reflection_4'],
                @$data['story_reflection_5']]);
        return $str;
    }

    public function trainRecommendation($user_id, $story_id, $text, $frameworks_goals){
        $this->curl->setHeader("Content-Type","application/json");
        $payload = json_encode(array(
            'user_id' => $user_id,
            'story_id' => $story_id,
            'choices' => $frameworks_goals,
            'text' => $text
        ));
        $this->curl->post($this->urlbase . "/train_recommendations", $payload);
//        var_dump($payload);
//        die();
    }
}