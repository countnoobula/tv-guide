Small TV guide converter between XML and JSON
- Uses Laravel 4.
- Vagrant setup from https://github.com/bryannielsen/Laravel4-Vagrant

Refer to notes folder for improvements and todo.

Routes setup as the following:
- GET - http://localhost:8888/api/upload -> Form to upload XML files
- POST - http://localhost:8888/api/upload -> Handles uploaded XML file and places it in the database
- GET - http://localhost:8888/api/get -> Retrieves all shows for all channels
- GET - http://localhost:8888/api/get/{id} -> Retrieves all shows for the specified channel (channel id)

Benchmark info:
- Box runs single core + 400mb RAM.
- Served 2000 requests at 5 concurrency in 1400 seconds without any failure.
- Average request time: 3.5 seconds.
