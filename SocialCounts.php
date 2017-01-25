<?php
namespace cmsgears\widgets\social\counts;

// Yii Imports
use \Yii;
use yii\base\Widget;
use yii\helpers\Html;

class SocialCounts extends \cmsgears\core\common\base\Widget {

	// Variables ---------------------------------------------------

	// Public Variables --------------------

	public $links	          	= null;
    public $template            = 'simple';

	private $linkFacebook		= null;
	private $linkTwitter		= null;
	private $linkPinterest		= null;

	private $facebookLikes		= 0;
	private $twitterFollowers	= 0;
	private $pinterestPins		= 0;

	// Constructor and Initialisation ------------------------------

	// yii\base\Object

    public function init() {

        parent::init();

		// Do init tasks
    }

	// Instance Methods --------------------------------------------

	// yii\base\Widget

    public function run() {

        if( $this->links != null ) {

            $this->initLinks( $this->links );
        }

		if( $this->linkFacebook != null ) {

			$this->facebookLikes	= $this->getFacebookLikes( $this->linkFacebook );
		}

		if( $this->linkTwitter != null ) {

			$this->twitterFollowers	= $this->getTwitterFollowers( $this->linkTwitter );
		}

		if( $this->linkPinterest != null ) {

			$this->pinterestPins = $this->getPinterestPins( $this->linkPinterest );
		}

		$widgetHtml				= $this->renderWidget();

		return Html::tag( 'div', $widgetHtml, $this->options );
    }

	public function renderWidget( $config = [] ) {

		$widgetHtml = $this->render( $this->template, [
			'facebookLikes' => $this->facebookLikes,
			'twitterFollowers' => $this->twitterFollowers,
			'pinterestPins' => $this->pinterestPins
		] );

		return $widgetHtml;
	}

    protected function initLinks( $links ) {

        foreach( $links as $link ) {

	        if( strcmp( $link->sns, 'Facebook' ) ) {

	            $this->linkFacebook   = $link->address;
	        }
			else if( strcmp( $link->sns, 'Twitter' ) ) {

	            $this->linkTwitter    = $link->address;
	        }
			else if( strcmp( $link->sns, 'Pintrest' ) ) {

	           $this->linkPinterest  = $link->address;
	        }
        }
    }

	protected function getFacebookLikes( $link ) {

		// Query in FQL
	    $fql		= "SELECT share_count, like_count, comment_count ";
	    $fql		.= " FROM link_stat WHERE url = '$link'";
	    $fqlURL		= "https://api.facebook.com/method/fql.query?format=json&query=" . urlencode($fql);
	    $response 	= json_decode( file_get_contents( $fqlURL ) );
		$likes		= 0;

		if( isset( $response ) && !empty( $response ) && is_array( $response ) ) {

			$likes	= $response[ 0 ]->like_count;
		}

		return $likes;
	}

	protected function getTwitterFollowers( $link ) {

		$username		= substr( $link, strrpos( $link, '/' ) + 1 );
		$data 			= file_get_contents( 'https://cdn.syndication.twimg.com/widgets/followbutton/info.json?screen_names='.$username );
		$parsed 		= json_decode( $data,true );
		$tw_followers	= 0;

		if( isset( $parsed ) ) {

			foreach( $parsed as $pageData ) {

				if( !isset( $pageData['status'] ) ) {

					$tw_followers 	= $parsed[ 0 ][ 'followers_count' ];
				}
			}
		}

		return $tw_followers;
	}

	protected function getPinterestPins( $link ) {

		$response	= file_get_contents( "http://api.pinterest.com/v1/urls/count.json?callback=receiveCount&url=$link" );
		$count		= 0;
		$trimUrl	= 'receiveCount({"url":"'.$link.'","count":';
		$count		= str_replace( $trimUrl,"",$response);
		$count		= substr( $count, 0, -2 );

		return $count;
	}
}

?>