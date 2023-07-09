Step 1 php artisan migrate 
step 2 php artisan db:seed
Two Api 
Api end points 

GET /api/getAllSlots

POST /api/appointments_create
Send raw JSON {
  "slot_id": 196,
  "sappointment_details": [
    {
      "email": "john@example.com",
      "first_name": "John",
      "last_name": "Doe"
    },
    {
      "email": "jane@example.com",
      "first_name": "Jane",
      "last_name": "Smith"
    }
  ]
}
