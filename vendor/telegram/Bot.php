<?php

    class Bot
    {
//        const TOKEN = '225366019:AAGDMEeCVLARyx8A5g5BAUHqjMAPjR7AFOQ';

        /*Тестовый Bot*/
        const TOKEN = '359525019:AAFRhz39XVLOGRz9UDNRoR7JDs3TwcAZSvE';
        public $token;

        function __construct( $token )
        {
            $this->token = $token;
        }

        public static function send( $chat_id, $message, $keyboard = [] )
        {
            $url     = "https://api.telegram.org/bot".self::TOKEN."/sendMessage";
            $content = [
                'chat_id'    => $chat_id,
                'text'       => $message,
                'parse_mode' => 'html',
            ];
            if( $keyboard ) {
                $replyMarkup               = [
                    'keyboard'        => $keyboard,
                    'resize_keyboard' => true,
                    'selective'       => true,
                ];
                $encodedMarkup             = json_encode( $replyMarkup );
                $content[ 'reply_markup' ] = $encodedMarkup;
            }
            $ch = curl_init();

            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $content ) );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/x-www-form-urlencoded' ] );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            $result = curl_exec( $ch );
            curl_close( $ch );

            return $result;
        }

        public function sendMessage( $chat_id, $message, $keyboard = [] )
        {
            $url     = "https://api.telegram.org/bot".$this->token."/sendMessage";
            $content = [
                'chat_id'    => $chat_id,
                'text'       => $message,
                'parse_mode' => 'html',
            ];
            if( $keyboard ) {
                $replyMarkup               = [
                    'keyboard'        => $keyboard,
                    'resize_keyboard' => true,
                    'selective'       => true,
                ];
                $encodedMarkup             = json_encode( $replyMarkup );
                $content[ 'reply_markup' ] = $encodedMarkup;
            }
            $ch = curl_init();

            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $content ) );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/x-www-form-urlencoded' ] );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            $result = curl_exec( $ch );
            curl_close( $ch );

            return $result;
        }

        public static function sendInlineKeyBoard( $chat_id, $message, $inlineKeyBoard = [] )
        {
            $url     = "https://api.telegram.org/bot".self::TOKEN."/sendMessage";
            $content = [
                'chat_id'    => $chat_id,
                'text'       => $message,
                'parse_mode' => 'html',
            ];
            if( $inlineKeyBoard ) {
                $replyMarkup               = [
                    'inline_keyboard' => $inlineKeyBoard,
                ];
                $encodedMarkup             = json_encode( $replyMarkup );
                $content[ 'reply_markup' ] = $encodedMarkup;
            }
            $ch = curl_init();

            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $content ) );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/x-www-form-urlencoded' ] );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_exec( $ch );
            curl_close( $ch );

        }

        public static function SendImage( $chat_id, $ImageUlr, $caption = false, $keyboard = [ [ 'Ⓜ️Меню' ] ], $test = false )
        {
            $bot_url = "https://api.telegram.org/bot".self::TOKEN."/";
            $url     = $bot_url."sendPhoto?chat_id=".$chat_id;

            $path = ( isset( $_SERVER[ 'CONTEXT_DOCUMENT_ROOT' ] ) ? $_SERVER[ 'CONTEXT_DOCUMENT_ROOT' ] : dirname( dirname( Yii::app()->request->scriptFile ) ) );

            if( $test ) {
                try {
                    $file = file_get_contents( 'http://tengrinews.kz'.$ImageUlr );


                } catch( Exception $e ) {
                    $file = file_get_contents( 'https://tengrinews.kz/static/stub.jpg' );

                }
                file_put_contents( $path.'/userdata/temp.jpg', $file );
                $file = $path.'/userdata/temp.jpg';
            }
            else {
                $file = $_SERVER[ 'CONTEXT_DOCUMENT_ROOT' ].$ImageUlr;

            }

            $post_fields = [ 'chat_id' => $chat_id,
                             'photo'   => new \CURLFile( realpath( $file ) )
            ];
            if( $caption ) {
                $post_fields[ 'caption' ] = $caption;
            }

            if( $keyboard ) {
                $replyMarkup                   = [ 'keyboard'        => $keyboard,
                                                   'resize_keyboard' => true,
                                                   'selective'       => true,
                ];
                $encodedMarkup                 = json_encode( $replyMarkup );
                $post_fields[ 'reply_markup' ] = $encodedMarkup;
            }

            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_HTTPHEADER, [
                "Content-Type:multipart/form-data"
            ] );
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $post_fields );
            curl_exec( $ch );
            curl_close( $ch );

        }

        public function getToken()
        {
            return $this->token;
        }

        public static function EditMessage( $chat_id, $message_id, $message, $add_text = '', $keyboard = false )
        {
            $bot_url = "https://api.telegram.org/bot".self::TOKEN."/";

            $content = [
                'chat_id'    => $chat_id,
                'message_id' => $message_id,
                'text'       => $message.'
            '.$add_text,
                'parse_mode' => 'html',
            ];
            if( $keyboard ) {
                $replyMarkup               = [
                    'inline_keyboard' => $keyboard,
                    'resize_keyboard' => true,
                    'selective'       => true,
                ];
                $encodedMarkup             = json_encode( $replyMarkup );
                $content[ 'reply_markup' ] = $encodedMarkup;
            }
            $ch = curl_init();

            curl_setopt( $ch, CURLOPT_URL, $bot_url );
            curl_setopt( $ch, CURLOPT_POST, 1 );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $content ) );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/x-www-form-urlencoded' ] );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_exec( $ch );
            curl_close( $ch );
        }

    }