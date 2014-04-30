Small TV converter between XML and JSON
- Uses Laravel 4.
- Vagrant setup from https://github.com/bryannielsen/Laravel4-Vagrant

Routes setup as the following:
GET - http://localhost:8888/api/upload -> Form to upload XML files
POST - http://localhost:8888/api/upload -> Handles uploaded XML file and places it in the database
GET - http://localhost:8888/api/get -> Retrieves all shows for all channels
GET - http://localhost:8888/api/get/{id} -> Retrieves all shows for the specified channel (channel id)