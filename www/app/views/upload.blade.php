@extends('layout')

@section('content')
	<h1>Upload XML File</h1>

	<form action="/api/upload" method="POST" enctype="multipart/form-data">
		<input type="file" name="xmlFile" />
		<input type="submit" />
	</form>
@stop