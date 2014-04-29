<?php

class ApiController extends BaseController {

	public function generateJSON() {

	}

	public function uploadXML() {
		$xmlFile = Input::file('xmlFile');

		// $this->removeOldEvents();

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

		return "Shows uploaded successfully.";
	}

	private function removeOldEvents() {
		Show::where("starting_time", "<", date('d.m.Y',strtotime("-1 days")))->delete();
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

	private function addEvent($channelID, $startingTime, $title, $episodeTitle, $country, $genre, $parentalRating, $performer, $regie, $storyMiddle, $year) {
		$show = Show::whereRaw("starting_time = ? AND channel_id = ?", array($startingTime, $channelID));
		if($show->count() == 0) {
			$show = new Show();
		}

		$show->channel_id = $channelID;
		$show->starting_time = date_create_from_format("d.m.Y H:i:s:u", $startingTime);
		$show->title = $title;
		$show->episode_title = $episodeTitle;
		$show->country = $country;
		$show->genre = $genre;
		$show->parental_rating = $parentalRating;
		$show->performer = $performer;
		$show->regie = $regie;
		$show->story_middle = $storyMiddle;
		$show->year = $year;

		$show->save();

		// error handling
		return true;
	}
}