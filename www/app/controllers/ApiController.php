<?php

class ApiController extends BaseController {

	public function uploadXML() {
		$xmlFile = Input::file('xmlFile');

		$this->removeOldEvents();

		$xml = simplexml_load_file($xmlFile);

		$channels = array();

		foreach ($xml->children() as $node) {
			$channelName = strip_tags($node->channel->asXML());
			if(!isset($channels[$channelName])) {
				$channels[$channelName] = $this->getChannelID($channelName);
			}

			$channelID = $channels[$channelName];
			$success = $this->addEvent($channelID, $node->starting_time, $node->title, $node->episode_title, $node->country, $node->genre, $node->parental_rating, $node->performer, $node->regie, $node->story_middle, $node->year);
		}

		return var_dump($channels);
	}

	private function removeOldEvents() {
		// delete events from previous day and earlier
	}

	private function getChannelID($channelName) {
		$channel = Channel::where("name", "=", $channelName)->first();

		if($channel == null) {
			$channel = new Channel();
			$channel->name = $channelName;
			$channel->save();
		}

		return $channel->id;
	}

	private function addEvent($channelID, $startingTime, $title, $episodeTitle = "", $country = "", $genre = "", $parentalRating = "", $performer = "", $regie = "", $storyMiddle = "", $year = "") {
		// if show exists with startingTime and channelID
		//   update show
		//   return true
		// else 
		//   insert show

		/*
			$queries = DB::getQueryLog();
			$last_query = end($queries);
			die(var_dump($last_query));
		*/

		return true;
	}
}