<?php

return [
  'api_url' => env('SMS_API_URL', 'https://messaging-service.co.tz/link/sms/v1/text/single'),

  'username' => env('SMS_USERNAME', ''),

  'password' => env('SMS_PASSWORD', ''),

  'sender_id' => env('SMS_SENDER_ID', 'WauminiLnk'),

  'timeout' => (int) env('SMS_TIMEOUT', 15),
];
