<?php
session_start();

// Include the Google API client library
require_once __DIR__ . '/vendor/autoload.php';

// Configuration
$clientID = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');
$redirectUri = 'http://localhost/callback.php'; // Update with your redirect URI

// Create a new Google Client
$client = new Google\Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUri);
$client->addScope("email");
$client->addScope("profile");

// Handle the OAuth callback
if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    // Store the token in session for future use
    $_SESSION['access_token'] = $token;

    // Get user profile information
    $oauthService = new Google\Service\Oauth2($client);
    $userData = $oauthService->userinfo->get();

    // Store user data in session
    $_SESSION['user'] = [
        'email' => $userData->getEmail(),
        'name' => $userData->getName(),
        'picture' => $userData->getPicture(),
    ];

    // Redirect to a dashboard or home page
    header('Location: dashboard.php');
    exit;
} else {
    echo "Error: No code provided.";
}
