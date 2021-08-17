# Landmark SMS Service

The Landmark SMS service helps to send SMS to a number provided. The service has been configured for the providers such as Twilio and Plivo. 

# Instructions to Execute

Kindly follow the following steps.

  - Make a copy of .env.example and rename as .env
  - Configure credentials for both Twilio and Plivo
  - Execute `php composer update` to update the dependancies
  - Execute `php -S localhost:8000 -t public` to start the service

# Sample Request

```sh
curl --location --request POST 'http://localhost:8000/api/v1/sms/send' \
--data-raw '{
    "to": "917259791234",
    "provider": "twilio",
    "content": "Hello, Good morning"
}'
```