<?php

// Define the location of our class
// namespace Core;

class Twitch {

  /* Properties */

  private $api_url;
  private $token_url;
  private $client_id;
  private $client_secret;

  /**
   * __construct - Class constructor.  Initializes the object instance.
   * @param string $client_id     - The Twitch app registration id.
   * @param string $client_secret - The Twitch app registration secret.
   * @return void
   */
  public function __construct( $client_id, $client_secret ) {

    $this->api_url       = "https://api.twitch.tv/helix/";
    $this->token_url     = "https://id.twitch.tv/oauth2/token";
    $this->client_id     = $client_id;
    $this->client_secret = $client_secret;

  }

  /* Authorization Methods */

  /**
   * Get_AppToken - This function is used to retrieve an application token (does not require approval from user).
   * @return array $response - The response from Twitch, converted to an array.
   *                On Success:                           Example:
   *                  1. access_token                       1. dhgt9rlkrh3x0j6idiaapfzsm6eet9
   *                  2. expires_in                         2. 5233097 (seconds)
   *                  3. token_type                         3. bearer
   *                On Failure:                           Examples:
   *                  1. status                             1. 400, 403
   *                  2. message                            2. invalid client, invalid client secret
   */
  public function Get_AppToken() {
    
    // Specify the Data to pass to Twitch
    $data = array(
                  "client_id"     => $this->client_id,
                  "client_secret" => $this->client_secret,
                  "grant_type"    => "client_credentials"
                );

    // Set custom headers
    $headers = [
      'Content-Type: application/x-www-form-urlencoded'
    ];
    
    // Return the response
    return $this->Submit_POSTRequest( $this->token_url, $data, $headers );

  }

  /**
   * Get_UserToken - This function is used to retrieve a user token (requires approval from the user as it allows you to get the private information).
   * @param string $auth_code    - The authorization code received from Twitch after the user granted us permissions.
   * @param string $redirect_uri - The page that will handle the reponse and receive the authorization code.
   * @return array $response - The response from Twitch, converted to an array.
   *                On Success:                           Example:
   *                  1. access_token                       1. dhgt9rlkrh3x0j6idiaapfzsm6eet9
   *                  2. expires_in                         2. 5233090 (seconds)
   *                  3. refresh_token                      3. ab8si8ottgdp4lwf0lp3y0mrnb76f77th3rkmphirjzebtithj
   *                  4. scope                              4. array( [0] - user:read:email )
   *                  5. token_type                         5. bearer
   *                On Failure:                           Examples:
   *                  1. status                             1. 400, 403
   *                  2. message                            2. invalid client, invalid client secret
   */
  public function Get_UserToken( $auth_code, $redirect_uri ) {

    // Specify the Data to pass to Twitch
    $data = array(
      "client_id"     => $this->client_id,
      "client_secret" => $this->client_secret,
      "code"          => $auth_code,
      "grant_type"    => "authorization_code",
      "redirect_uri"  =>  $redirect_uri
    );

    // Set custom headers
    $headers = [
    'Content-Type: application/x-www-form-urlencoded'
    ];

    // Return the response
    return $this->Submit_POSTRequest( $this->token_url, $data, $headers );

  }

  /**
   * Refresh_UserToken - This function is used to retrieve a new user token once the current one expires.
   * @param string $refresh_token - The page that will handle the reponse and receive the authorization code.
   * @return array $response      - The response from Twitch, converted to an array.
   *                On Success:                           Example:
   *                  1. access_token                       1. dhgt9rlkrh3x0j6idiaapfzsm6eet9
   *                  2. expires_in                         2. 5233090 (seconds)
   *                  3. refresh_token                      3. ab8si8ottgdp4lwf0lp3y0mrnb76f77th3rkmphirjzebtithj
   *                  4. scope                              4. array( [0] - user:read:email )
   *                  5. token_type                         5. bearer
   *                On Failure:                           Examples:
   *                  1. status                             1. 400, 403
   *                  2. message                            2. invalid client, invalid client secret
   */
  public function Refresh_UserToken( $refresh_token ) {

    // Specify the Data to pass to Twitch
    $data = array(
      "client_id"     => $this->client_id,
      "client_secret" => $this->client_secret,
      "grant_type"    => "refresh_token",
      "refresh_token" => $refresh_token
    );

    // Set custom headers
    $headers = [
    'Content-Type: application/x-www-form-urlencoded'
    ];
    
    // Return the response
    return $this->Submit_POSTRequest( $this->token_url, $data, $headers );

  }

  /**
   * Request_UserAuthorization - This function redirects the user to Twitch to approve the acquisition of a user token to handle their restricted information.
   * @param string $redirect_uri - The page that will handle the reponse and receive the authorization code.
   * @param string $scope        - A space-delimited list of scopes. The APIs that you’re calling will identify the scopes you must list. You must URL encode the list.
   *                               https://dev.twitch.tv/docs/authentication/scopes/#twitch-api-scopes | https://dev.twitch.tv/docs/authentication/scopes/#twitch-access-token-scopes
   *                               Examples:
   *                                 1. channel%3Amanage%3Apolls+channel%3Aread%3Apolls   =>   channel:manage:poll+channel:read:polls
   * @param string $state        - [ string | null ] Optional, you are strongly encouraged to pass a state string to help prevent Cross-Site Request Forgery (CSRF) attacks.
   *                               The server returns this string to you in your redirect URI (see the state parameter in the fragment portion of the URI).
   *                               If this string doesn’t match the state string that you passed, ignore the response.
   *                               The state string should be randomly generated and unique for each OAuth request.
   * Responses
   * ---------
   *   1. If the user authorizes:
   *     a. code  - t60g810dwbu1kn49y9s1uvsroq9q8h
   *     b. scope - channel:manage:poll channel:read:polls
   *   2. If the user does not authorizes:
   *     a. error - access_denied
   *     b. error_description - The user denied you access
   */
  public function Request_UserAuthorization( $redirect_uri, $scope, $state ) {
    
    $URL = "https://id.twitch.tv/oauth2/authorize?response_type=code&client_id=" . $this->client_id . "&redirect_uri=" . $redirect_uri . "&scope=" . $scope;
    
    if( $state != null )
      $URL .= "state=$state";

    header( "Location: $URL" );
    exit();

  }

  /**
   * Validate_AccessToken - This function is used to check if the User Token is valid.
   * @param string $token  - The last saved token.
   * @return array $answer - The reply from the endpoint to the request.
   *                On Success:                           Example:
   *                  1. client_id                          1. <app registration client ID>
   *                  2. login                              2. Twitch User ID
   *                  3. scopes                             3. array( [0] - user:read:email )
   *                  4. user_id                            4. 22468572
   *                  5. expires_in                         5. 5000 (seconds)
   *                On Failure:                           Examples:
   *                  1. status                             1. 401
   *                  2. message                            2. invalid access token
   */
  public function Validate_AccessToken( $token ) {
    
    // Set custom headers
    $headers = [
      'Content-Type: application/x-www-form-urlencoded',
      "Authorization: OAuth $token"
      ];

    // Return the response
    return $this->Submit_GETRequest( "https://id.twitch.tv/oauth2/validate", $headers );
    
  }

  /* API Methods */

  /**
   * Submit_DELETERequest - This function is used to submit a DELETE request to an endpoint and retrieve its response.
   * @param string $url    - The URL of the endpoint to query.
   * @param array $headers - The headers of the request.
   * @return array $answer - The reply from the endpoint to the request.
   *                  1. HTTP Code                     1. 204 ... 500
   */
  private function Submit_DELETERequest( $url, $headers ) {

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE" );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

    // Execute cURL session
    $response = curl_exec( $ch );

    // Check for cURL errors
    if ( $response === false ) {
      die( 'Error occurred: ' . curl_error( $ch ) );
    }

    // Get the HTTP Code
    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

    // Close cURL session
    curl_close( $ch );

    // Return the response
    return $http_code;

  }
  
  /**
   * Submit_GETRequest - This function is used to submit a GET request to an endpoint and retrieve its response.
   * @param string $url    - The URL of the endpoint to query.
   * @param string $data   - The data that is appended to the request.
   * @param array $headers - The headers of the request.
   * @return array $answer - The reply from the endpoint to the request.
   */
  private function Submit_GETRequest( $url, $headers ) {

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

    // Execute cURL session
    $response = curl_exec( $ch );

    // Check for cURL errors
    if ( $response === false ) {
      die( 'Error occurred: ' . curl_error( $ch ) );
    }

    // Close cURL session
    curl_close( $ch );

    // Return the response
    return json_decode( $response, true );

  }

  /**
   * Submit_PATCHRequest - This function is used to submit a PATCH request to an endpoint and retrieve its response.
   * @param string $url    - The URL of the endpoint to query.
   * @param string $data   - The data that is appended to the request.
   * @param array $headers - The headers of the request.
   * @return array $answer - The reply from the endpoint to the request.
   */
  private function Submit_PATCHRequest( $url, $data, $headers ) {

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PATCH" );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

    // Execute cURL session
    $response = curl_exec( $ch );

    // Check for cURL errors
    if ( $response === false ) {
      die( 'Error occurred: ' . curl_error( $ch ) );
    }

    // Close cURL session
    curl_close( $ch );

    // Return the response
    return json_decode( $response, true );

  }

  /**
   * Submit_POSTRequest - This function is used to submit a POST request to an endpoint and retrieve its response.
   * @param string $url    - The URL of the endpoint to query.
   * @param string $data   - The data that is appended to the request.
   * @param array $headers - The headers of the request.
   * @return array $answer - The reply from the endpoint to the request.
   */
  private function Submit_POSTRequest( $url, $data, $headers ) {

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

    // Execute cURL session
    $response = curl_exec( $ch );

    // Check for cURL errors
    if ( $response === false ) {
      die( 'Error occurred: ' . curl_error( $ch ) );
    }

    // Close cURL session
    curl_close( $ch );

    // Return the response
    return json_decode( $response, true );

  }

  /**
   * Submit_PUTRequest - This function is used to submit a PUT request to an endpoint and retrieve its response.
   * @param string $url    - The URL of the endpoint to query.
   * @param string $data   - The data that is appended to the request.
   * @param array $headers - The headers of the request.
   * @return array $answer - The reply from the endpoint to the request.
   *                  1. HTTP Code                     1. 204 ... 401
   */
  private function Submit_PUTRequest( $url, $data, $headers ) {

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $data ) );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

    // Execute cURL session
    $response = curl_exec( $ch );

    // Check for cURL errors
    if ( $response === false ) {
      die( 'Error occurred: ' . curl_error( $ch ) );
    }

    // Get the HTTP Code
    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

    // Close cURL session
    curl_close( $ch );

    // Return the response
    return $http_code;

  }

  /* DELETE Methods */

  /**
   * Delete_TwitchData - This function is used to perform a DELETE request to the Twitch API.
   * @param string $token - The app or user token to request the data.
   * @param string $query - The endpoint query to execute.
   *                         Examples: channel_points/custom_rewards?broadcaster_id=12345678&id=b045196d-9ce7-4a27-a9b9-279ed341ab28
   * @return int $answer  - The reply from the endpoint to the request.
   *                  1. HTTP Code                     1. 204 ... 500
   */
  public function Delete_TwitchData( $token, $query ){
    
    // Set custom headers
    $headers = [
      'Content-Type: application/x-www-form-urlencoded',
      "Authorization: Bearer $token",
      "Client-Id: $this->client_id"
      ];

    // Return the response
    return $this->Submit_DELETERequest( $this->api_url . $query, $headers );

  }
  
  /* GET Methods */

  /**
   * Get_TwitchData - This function is used to perform a GET request to the Twitch API.
   * @param string $token  - The app or user token to request the data.
   * @param string $query  - The endpoint query to execute.
   *                         Examples: users?login=auronplay
   *                                   analytics/games?game_id=493057&started_at=2024-01-01T00:00:00Z&ended_at=2024-12-31T00:00:00Z
   * @return array $answer - The reply from the endpoint to the request.
   *                On Success:
   *                  array( The requested data )
   *                On Failure:                           Examples:
   *                  1. error                              1. Unauthorized
   *                  2. status                             2. 401
   *                  3. message                            3. invalid OAuth token
   */
  public function Get_TwitchData( $token, $query ){

    // Set custom headers
    $headers = [
      'Content-Type: application/x-www-form-urlencoded',
      "Authorization: Bearer $token",
      "Client-Id: $this->client_id"
      ];

    // Return the response
    return $this->Submit_GETRequest( $this->api_url . $query, $headers );

  }

  /* PATCH Methods */

  /**
   * Patch_TwitchData - This function is used to perform a PATCH request to the Twitch API.
   * @param string $token    - The app or user token to request the data.
   * @param string $endpoint - The API endpoint to interact with.
   *                           Example: channels/commercial
   * @param array $data      - The array of data that needs to be submitted in the request.
   *                           Example: array( broadcaster_id => 012345678, id => 05582bc1-0dar-3fgt-de47-6431c586a7bc, is_enabled => false )
   * @return array $answer - The reply from the endpoint to the request.
   *                On Success:
   *                  array( The requested data )
   *                On Failure:                           Examples:
   *                  1. error                              1. Not Found
   *                  2. status                             2. 404
   *                  3. message                            3. -
   */
  public function Patch_TwitchData( $token, $endpoint, $data ){
    
    // Set custom headers
    $headers = [
      'Content-Type: application/x-www-form-urlencoded',
      "Authorization: Bearer $token",
      "Client-Id: $this->client_id"
      ];

    // Return the response
    return $this->Submit_PatchRequest( $this->api_url . $endpoint, $data, $headers );

  }
  
  /* POST Methods */

  /**
   * Post_TwitchData - This function is used to perform a POST request to the Twitch API.
   * @param string $token    - The app or user token to request the data.
   * @param string $endpoint - The API endpoint to interact with.
   *                           Example: channels/commercial
   * @param array $data      - The array of data that needs to be submitted in the request.
   *                           Example: array( broadcaster_id => 012345678, length => 30 )
   * @return array $answer - The reply from the endpoint to the request.
   *                On Success:
   *                  array( The requested data )
   *                On Failure:                           Examples:
   *                  1. error                              1. Not Found
   *                  2. status                             2. 404
   *                  3. message                            3. -
   */
  public function Post_TwitchData( $token, $endpoint, $data ){
    
    // Set custom headers
    $headers = [
      'Content-Type: application/x-www-form-urlencoded',
      "Authorization: Bearer $token",
      "Client-Id: $this->client_id"
      ];

    // Return the response
    return $this->Submit_POSTRequest( $this->api_url . $endpoint, $data, $headers );

  }

  /* PUT Methods */

  /**
   * Put_TwitchData - This function is used to perform a POST request to the Twitch API.
   * @param string $token    - The app or user token to request the data.
   * @param string $endpoint - The API endpoint to interact with.
   *                           Example: channels/commercial
   * @param array $data      - The array of data that needs to be submitted in the request.
   *                           Example: array( broadcaster_id => 012345678, length => 30 )
   * @return int $answer     - The reply from the endpoint to the request.
   *                  1. HTTP Code                     1. 204 ... 500
   */
  public function Put_TwitchData( $token, $endpoint, $data ){
    
    // Set custom headers
    $headers = [
      'Content-Type: application/x-www-form-urlencoded',
      "Authorization: Bearer $token",
      "Client-Id: $this->client_id"
      ];

    // Return the response
    return $this->Submit_PUTRequest( $this->api_url . $endpoint, $data, $headers );

  }

  // /**
  //  *  - 
  //  */
  // function (){
    
  // }

}

?>