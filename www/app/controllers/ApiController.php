<?php

class ApiController extends BaseController {

	public function generateJSON($channelID = -1) {
		$generateJSON = array();

		if($channelID == -1) {
			$shows = Show::orderBy('starting_time', 'ASC')->join('channels', 'shows.channel_id', '=', 'channels.id');
		} else {
			$channel = Channel::find($channelID);
			$shows = ($channel != null ? Show::where('channel_id', '=', $channelID)->orderBy('starting_time', 'ASC') : null);
		}

		$generateJSON["hasTVGuideChannel"] = ($shows != null && $shows->count() > 0);

		if($generateJSON["hasTVGuideChannel"]) {
			$channels = array();

			$showsJSON = array();
			$shows = $shows->get();
			$currentTime = time("Y-m-d H:i:s");

			$showID = 0;
			$needsEmpty = false;
			$previousShow = null;
			$currentShow = null;

			foreach ($shows as $s) {
				if(!$needsEmpty) {
					$previousShow = $currentShow;
					$currentShow = $s;
					
					if($previousShow != null) {
						$channelName = $previousShow->channel->name;
						$url = "../shows/show.html";
						$startingTime = $previousShow->starting_time;
						$start = date('H:i', strtotime($previousShow->starting_time));
						$end = date('H:i', strtotime($currentShow->starting_time));
						$duration = (strtotime($end) - strtotime($start)) / 60;
						$width = $duration * 6;
						$title = $previousShow->title;
						$episodeTitle = $previousShow->episode_title;
						$country = $previousShow->country;
						$genre = $previousShow->genre;
						$parentalRating = $previousShow->parental_rating;
						$performer = $previousShow->performer;
						$regie = $previousShow->regie;
						$storyMiddle = $previousShow->story_middle;
						$year = $previousShow->year;

						$show = array();
						$show['id'] = $showID;
						$show['channel'] = $channelName;
						$show['url'] = $url;
						$show['starting_time'] = $startingTime;
						$show['start'] = $start;
						$show['end'] = $end;
						$show['duration'] = $duration;
						$show['width'] = $width;
						$show['title'] = $title;
						$show['episode_title'] = $episodeTitle;
						$show['country'] = $country;
						$show['genre'] = $genre;
						$show['parental_rating'] = $parentalRating;
						$show['performer'] = $performer;
						$show['regie'] = $regie;
						$show['story_middle'] = $storyMiddle;
						$show['year'] = $year;

						if($show['duration'] <= 15) {
							$show['isNarrow'] = true;
						}
						if((strtotime($show['starting_time']) + $duration) < $currentTime) {
							$show['isDisabled'] = true;	
						}
						if((strtotime($show['starting_time']) + $duration) > $currentTime && (strtotime($show['starting_time'])) < $currentTime) {
							$show['isCurrent'] = true;
						}

						$showsJSON[] = $show;
					}
				} else {
					$show = array();

					$startingTime = 0;
					$start = 0;
					$end = 0;
					$duration = 0;
					$width = 0;

					$show['id'] = $showID;
					$show['starting_time'] = $startingTime;
					$show['start'] = $start;
					$show['end'] = $end;
					$show['duration'] = $duration;
					$show['width'] = $width;
					$show['isEmpty'] = true;

					$showsJSON[] = $show;
				}
				$showID++;
			}

			// Do last show

			$channels["shows"] = $showsJSON;
			$generateJSON["tv_guide_channel"] = $channels;
		}

		return json_encode($generateJSON, JSON_UNESCAPED_SLASHES);
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
		Show::where("starting_time", "<", date('d.m.Y'))->delete();
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