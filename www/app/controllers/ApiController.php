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
						$showsJSON[] = $this->getShowArray($s, $currentShow->starting_time, $showID, $currentTime);
					}
				} else {
					$show = array();

					$startingTime = date("Y-m-d H:i:s", mktime(0, 0, 0, date('n', strtotime($previousShow->starting_time)), date('j', strtotime($previousShow->starting_time)) + 1));
					$start = date("H:i", strtotime($startingTime));
					$end = date("H:i", strtotime($currentShow->starting_time));
					$duration = (strtotime($currentShow->starting_time) - strtotime($startingTime)) / 60;
					$width = $duration * 6;

					$show['id'] = $showID;
					$show['starting_time'] = $startingTime;
					$show['start'] = $start;
					$show['end'] = $end;
					$show['duration'] = $duration;
					$show['width'] = $width;
					$show['isEmpty'] = true;

					if($show['duration'] <= 15) {
						$show['isNarrow'] = true;
					}
					if((strtotime($show['starting_time']) + $duration) < $currentTime) {
						$show['isDisabled'] = true;	
					}

					$showsJSON[] = $show;

					$needsEmpty = false;
				}
				$showID++;
			}

			$showsJSON[] = $this->getShowArray($currentShow, date("Y-m-d H:i:s", mktime(0, 0, 0, date('n', strtotime($previousShow->starting_time)), date('j', strtotime($previousShow->starting_time)) + 1)), $showID, $currentTime);

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

	private function getShowArray($show, $endTime, $showID, $currentTime) {
		$channelName = $show->channel->name;
		$url = "../shows/show.html";
		$startingTime = $show->starting_time;
		$start = date('H:i', strtotime($show->starting_time));
		$end = date('H:i', strtotime($endTime));
		$duration = (strtotime($end) - strtotime($start)) / 60;
		$width = $duration * 6;
		$title = $show->title;
		$episodeTitle = $show->episode_title;
		$country = $show->country;
		$genre = $show->genre;
		$parentalRating = $show->parental_rating;
		$performer = $show->performer;
		$regie = $show->regie;
		$storyMiddle = $show->story_middle;
		$year = $show->year;

		$nextMidnight = mktime(0, 0, 0, date('n', strtotime($show->starting_time)), date('j', strtotime($show->starting_time)) + 1);
		if(strtotime($endTime) > $nextMidnight && strtotime($show->starting_time) < $nextMidnight) {
			$end = date("H:i", $nextMidnight);
			$duration = ($nextMidnight - strtotime($startingTime)) / 60;
			$width = $duration * 6;
			$needsEmpty = true;
		}

		$newShow = array();
		$newShow['id'] = $showID;
		$newShow['channel'] = $channelName;
		$newShow['url'] = $url;
		$newShow['starting_time'] = $startingTime;
		$newShow['start'] = $start;
		$newShow['end'] = $end;
		$newShow['duration'] = $duration;
		$newShow['width'] = $width;
		$newShow['title'] = $title;
		$newShow['episode_title'] = $episodeTitle;
		$newShow['country'] = $country;
		$newShow['genre'] = $genre;
		$newShow['parental_rating'] = $parentalRating;
		$newShow['performer'] = $performer;
		$newShow['regie'] = $regie;
		$newShow['story_middle'] = $storyMiddle;
		$newShow['year'] = $year;

		if($newShow['duration'] <= 15) {
			$newShow['isNarrow'] = true;
		}
		if((strtotime($newShow['starting_time']) + ($duration * 60)) < $currentTime) {
			$newShow['isDisabled'] = true;	
		}
		if((strtotime($startingTime) + ($duration * 60)) > $currentTime && (strtotime($startingTime)) < $currentTime) {
			$newShow['isCurrent'] = true;
		}

		return $newShow;
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