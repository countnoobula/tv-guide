<?php

class Channel extends Eloquent {

	public function shows() {
		return $this->hasMany("Show");
	}
}