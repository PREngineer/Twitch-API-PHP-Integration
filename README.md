## 💰 Please Support This Project!

[![](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=53CD2WNX3698E&lc=US&item_name=PREngineer&item_number=Twitch-API-PHP-Integration&currency_code=USD&bn=PP%2dTwitch-API-PHP-Integration%3abtn_donateCC_LG%2egif%3aNonHosted)

![Bitcoin Donation](https://raw.githubusercontent.com/PREngineer/AppImgs/main/btc.png)
19JXFGfRUV4NedS5tBGfJhkfRrN2EQtxVo

# Twitch API - PHP Integration
 A PHP implementation to interact with the Twitch API.

# Register your application with Twitch

1. Login [here](https://dev.twitch.tv/console/apps/create) to create the application registration:

    a. Give it a name
    b. Specify the URL to return to
    c. Select the category: Website Integration
    d. Click the Create button.

2. Click the Manage button to view the registration's details.

3. Copy the Client ID.  (You’ll use to get your access token and to set the Client-Id header in all API requests. Client IDs are considered public and can be embedded in a web page’s source.)

4. Click the New Secret button then click OK on the pop up window.  Copy the Client Secret.  (This is passed to the token exchange endpoints to obtain a token. You must keep this confidential.)

    NOTE: Do not share client IDs among applications; each application must have its own client ID. Sharing client IDs among applications may result in the suspension of your application’s access to the Twitch API.

# Basic usage

The methods return data.  To identify what it returned, you can print it to the screen with print_r().

1. Require the class and create an instance of the Twitch API integration.

    ```
    require_once 'Twitch.class.php' ;

    $twitch = new Twitch( <Client ID>, <Client Secret> );
    ```

2. If your application needs an application token, this is how you request it:

    ```
    $twitch->Get_AppToken();
    ```

    An application token gives you access to their public (non-sensitive) information and doesn't require the user's approval.

3. If your application needs a user approved token, this is how you request it:

    First, get the user to approve your request for permissions.  It will return an authorization code if they do.

    ```
    // Get the User Authorization Code (without providing the optional state variable)
    $twitch->Request_UserAuthorization( "https://site.com/Twitch.php", <List Of Permissions>, null );

    // Get the User Authorization Code (with the optional state variable)
    $twitch->Request_UserAuthorization( "https://site.com/Twitch.php", <List Of Permissions>, <Some State Value> );
    ```

    Get the User Approved Token by submitting the Authorization Code you just received in the GET response.

    ```
    // Get the User Authorized Token using that code.
    $twitch->Get_UserToken( <Authorization Code>, "https://site.com/Twitch.php" );
    ```

4. If you need to validate the existing user approved token, this is how you do it:

    ```
    $twitch->Validate_AccessToken( <User Access Token> );
    ```

5. Submit API Requests

    To identify the required parameters to pass in the requests, please take a look at the [Twitch API Reference](https://dev.twitch.tv/docs/api/reference/).

    ### GET requests - To retrieve data from the API.

    ```
    // Get the analytics of a game played by a user
    $twitch->Get_TwitchData( <User/App Access Token>, "analytics/games?game_id=493057&started_at=2024-01-01T00:00:00Z&ended_at=2024-10-01T00:00:00Z" );
    
    // Get a user's details
    $twitch->Get_TwitchData( <User/App Access Token>, "users?login=auronplay&login=zackrawrr" )
    ```

    ### DELETE requests - To delete a Twitch resource.

    ```
    $twitch->Delete_TwitchData( $access_token, "channel_points/custom_rewards?broadcaster_id=12345678-0dar-3fgt-de47-6431c586a7bc" )
    ```
    
    ### PATCH requests - To apply partial updates to a resource.

    ```
    // Disable a custom reward
    $data = array(
      "broadcaster_id" => "12345678",
      "title"          => "test",
      "cost"           => "500000",
      "id"             => "05582bc1-0dar-3fgt-de47-6431c586a7bc",
      "is_enabled"     => true
    );
    $twitch->Patch_TwitchData( $access_token, "channel_points/custom_rewards", $data );
    ```

    ### POST requests - To create a new resource.

    ```
    // Start a 30s commercial
    $data = array(
              "broadcaster_id" => "12345678",
              "length"         => "30"
            );
    $twitch->Post_TwitchData( <User/App Access Token>, "channels/comercial", $data );

    // Create a custom reward
    $data = array(
      "broadcaster_id" => "12345678",
      "title" => "Awesome Reward",
      "cost"  => "500000"
    );
    $twitch->Post_TwitchData( <User/App Access Token>, "channel_points/custom_rewards", $data );
    ```

    ### PUT requests - To update an existing resource.

    ```
    // Change the user's color in chat
    $data = array();
    $twitch->Put_TwitchData( <User/App Access Token>, "chat/color?user_id=12345678&color=blue", $data );
    ```