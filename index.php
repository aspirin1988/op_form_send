<?php
    require_once __DIR__.'/vendor/autoload.php';


    $ini_array = parse_ini_file( ".env" );
    foreach( $ini_array as $key => $value ) {
        define( $key, $value );
    }

    define( 'APPLICATION_NAME', 'Google Sheets API PHP Quickstart' );
    define( 'CREDENTIALS_PATH', '~/.credentials/sheets.googleapis.com-php-quickstart.json' );
    define( 'CLIENT_SECRET_PATH', __DIR__.'/client_secret.json' );
    define( 'SCOPES', implode( ' ', [
            Google_Service_Sheets::SPREADSHEETS_READONLY ]
    ) );

    if( php_sapi_name() != 'cli' ) {
        throw new Exception( 'This application must be run on the command line.' );
    }

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    function getClient()
    {
        $client = new Google_Client();
        $client->setApplicationName( APPLICATION_NAME );
        $client->setScopes( SCOPES );
        $client->setAuthConfig( CLIENT_SECRET_PATH );
        $client->setAccessType( 'offline' );

        // Load previously authorized credentials from a file.
        $credentialsPath = expandHomeDirectory( CREDENTIALS_PATH );
        if( file_exists( $credentialsPath ) ) {
            $accessToken = json_decode( file_get_contents( $credentialsPath ), true );
        }
        else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf( "Open the following link in your browser:\n%s\n", $authUrl );
            print 'Enter verification code: ';
            $authCode = trim( fgets( STDIN ) );

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode( $authCode );

            // Store the credentials to disk.
            if( !file_exists( dirname( $credentialsPath ) ) ) {
                mkdir( dirname( $credentialsPath ), 0700, true );
            }
            file_put_contents( $credentialsPath, json_encode( $accessToken ) );
            printf( "Credentials saved to %s\n", $credentialsPath );
        }
        $client->setAccessToken( $accessToken );

        // Refresh the token if it's expired.
        if( $client->isAccessTokenExpired() ) {
            $client->fetchAccessTokenWithRefreshToken( $client->getRefreshToken() );
            file_put_contents( $credentialsPath, json_encode( $client->getAccessToken() ) );
        }

        return $client;
    }

    /**
     * Expands the home directory alias '~' to the full path.
     *
     * @param string $path the path to expand.
     *
     * @return string the expanded path.
     */
    function expandHomeDirectory( $path )
    {
        $homeDirectory = getenv( 'HOME' );
        if( empty( $homeDirectory ) ) {
            $homeDirectory = getenv( 'HOMEDRIVE' ).getenv( 'HOMEPATH' );
        }

        return str_replace( '~', realpath( $homeDirectory ), $path );
    }

// Get the API client and construct the service object.
    $client  = getClient();
    $service = new Google_Service_Sheets( $client );

    $chatID        = CHAT_ID;
    $bot_key       = BOT_KEY;
    $spreadsheetId = PREADSHEET_ID;
    $listName      = LIST_NAME;
    $range         = RANGE;
    $response      = $service->spreadsheets_values->get( $spreadsheetId, $listName.'!'.$range );
    $values        = $response->getValues();
    $memcache_obj  = new Memcache;
    $memcache_obj->connect( '127.0.0.1', 11211 ) or die();

    if( count( $values ) > 0 ) {
        $send_messages          = @$memcache_obj->get( 'send_messages' );
        $send_reminder_messages = @$memcache_obj->get( 'send_reminder_messages' );
        $send_start_messages    = @$memcache_obj->get( 'send_start_messages' );
        if( !$send_messages ) {
            $send_messages = [];
        }
        if( !$send_reminder_messages ) {
            $send_reminder_messages = [];
        }
        if( !$send_start_messages ) {
            $send_start_messages = [];
        }
        $id_post                 = false;
        $date_event              = false;
        $registration_start_date = false;
        $now                     = date( 'Y-m-d' );
        $reminder_date           = date( 'Y-m-d', time() + ( 60 * 60 * 24 * REMINDER_DAYS ) );
        $bot                     = new Bot( $bot_key );
        foreach( $values as $row ) {
            $text = '';
            if( isset( $row[ 1 ] ) ) {
                $id_post                 = $row[ 0 ];
                $date_event              = $row[ 3 ];
                $registration_start_date = $row[ 4 ];
                for( $i = 1; $i < count( $row ); $i++ ) {
                    if( $row[ $i ] ) {
                        switch( $i ) {
                            case 1 :
                                $text .= "<strong>".$row[ $i ]."</strong>\n";
                                break;
                            case 2 :
                                $text .= '🔔  '.$row[ $i ]."\n";
                                break;
                            case 3 :
                                $text .= '📆 Дата проведения<pre>   '.$row[ $i ]."</pre>\n";
                                break;
                            case 4 :
                                $text .= '📆 Начало регистрации<pre>   '.$row[ $i ]."</pre>\n";
                                break;
                            case 5 :
                                $text .= '🏙 Город : <b>'.$row[ $i ]."</b>\n\n";
                                break;
                            case 6 :
                                $text .= '📍 Место : <b>'.$row[ $i ]."</b>\n\n";
                                break;
                            case 7 :
                                $text .= '🕐 Время<pre>   '.$row[ $i ]."</pre>\n";
                                break;
                            case 8 :
                                $text .= '🔗 Ссылка '.$row[ $i ]."\n";
                                break;
                            default:
                                $text .= $row[ $i ]."\n";
                                break;


                        }
                    }
                }
                if( $text && !in_array( $id_post, $send_messages ) ) {
                    $bot->sendMessage( $chatID, $text );
                    $send_messages[] = $id_post;
                    $memcache_obj->set( 'send_messages', $send_messages, false, 0 );
                }
                else {
                    if( $now == $registration_start_date || $now == $date_event ) {
                        if( !in_array( $id_post, $send_start_messages ) ) {
                            $bot->sendMessage( $chatID, $text );
                            $send_start_messages[] = $id_post;
                            $memcache_obj->set( 'send_start_messages', $send_start_messages, false, 0 );
                        }

                    }
                    elseif( $reminder_date == $registration_start_date || $reminder_date == $date_event ) {

                        if( !in_array( $id_post, $send_reminder_messages ) ) {
                            $bot->sendMessage( "$chatID", $text );
                            $send_reminder_messages[] = $id_post;
                            $memcache_obj->set( 'send_reminder_messages', $send_reminder_messages, false, 0 );
                        }
                    }

                }

            }

        }
    }