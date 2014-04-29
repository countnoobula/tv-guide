<?php

class Show extends Eloquent {

	public function channel() {
		return $this->belongsTo("Channel");
	}
}