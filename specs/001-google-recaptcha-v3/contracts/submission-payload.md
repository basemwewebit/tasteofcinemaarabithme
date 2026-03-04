# Form Submission Payload Contract

All forms protected by reCAPTCHA v3 will have their submission payload augmented via JavaScript before being sent to the server.

### Augmented Payload

The frontend script will inject the `g-recaptcha-response` hidden input field containing the generated token from Google's servers.

```text
POST /wp-login.php (or custom form endpoint)

Headers:
Content-Type: application/x-www-form-urlencoded

Body parameters:
- (original form fields)
- g-recaptcha-response: <base64 encoded token from google>
```

The backend hook intercepting these requests will extract the `g-recaptcha-response` and verify it before allowing the original submission logic to proceed.
