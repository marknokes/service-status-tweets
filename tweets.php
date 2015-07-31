<?php

/* Prevent Browser Access */
if ( !isset( $_POST['getem'] ) )
	die;

class TweetExplorer
{
	private $feed = "UCOGeeks";

	private $outage_hashtag = "outage";

	private $resolved_hashtag = "resolved"; 
	
	/* These need to match the css id's on the li's in the service status list */
	private $outage_areas = array(
		'network ',
		'email',
		'phones',
		'banner',
	);

	private $since;

	private $tw;

	private $tweets;

	public $return = false;
	
	public function __construct()
	{
		require_once("twitter/TwitterAPIExchange.php");

		$this->get_twitter();

		$this->set_since_date();

		$this->get_tweets();

		$this->parse_tweets();
	}

	private function get_twitter()
	{
		$this->tw = new TwitterAPIExchange( array(
			'oauth_access_token' 		=> '3301661635-tiXddkbsHDREZbPjVnhtn8rZZ8oah75MxhzGKWa',
			'oauth_access_token_secret' => 'WyfV56RZabcTPym9CyzgUa8UJuFwGqrckeFKIHuPxgBeS',
			'consumer_key' 				=> 'CVYUJRT73InObVGPvCLoBfQYT',
			'consumer_secret' 			=> 'Vzwh8BM5yEyQImwYL2kqml3Og1KqoIK0hk1elDaU9fSiZHu6Mz'		
		) );

		return;
	}

	private function set_since_date()
	{
		$this->since = date( "Y-m-d", strtotime( "-1 day" ) );

		return;
	}

	private function get_tweets()
	{
		$get = "?" . http_build_query( array(
			// Example: #outage AND (#network OR #phones OR #banner) from:@UCOGeeks since: 2015-07-30
			"q" => "#" . $this->outage_hashtag . " AND (#" . implode( " OR #", $this->outage_areas ) . ")" . "from:@" . $this->feed . " since:" . $this->since,
			// I think we should account for at least one outage tweet and one resolved tweet for each area above...just in case, right?
			"count" => sizeof( $this->outage_areas ) * 2,
		) );

		$tweets = $this->tw->setGetfield( $get )->buildOauth( "https://api.twitter.com/1.1/search/tweets.json", "GET")->performRequest();

		$this->tweets = json_decode( $tweets );

		return;
	}

	private function parse_tweets()
	{
		if ( $this->tweets && isset( $this->tweets->statuses ) )
		{
			$return = array();

			$issues = array();

			$merged = array();

			foreach( $this->tweets->statuses as $tweet )
			{
				$hashtags = array();

				foreach ( $tweet->entities->hashtags as $obj )
					// we may be able to separate errors from slowness here to change the icon...actually we could.
					$hashtags[] = $obj->text;


				$outage_areas = array_intersect( $this->outage_areas, $hashtags );
			
				// If the resolved status is found among the hashtags of a tweet, assume it has been resolved and skip it
				if ( in_array( $this->resolved_hashtag, $hashtags ) )
					break;

				// We only want to return hashtags listed in the outage areas array above..in case the tweet has some unrelated ones.
				$issues = array_merge( $issues, $outage_areas );
			}

			if ( $issues )
				// make a unique array of the issues and set $this->return to the json_encoded value
				$this->return = json_encode( array_unique( $issues ) );
		}

		return;
	}
}

$TweetExplorer = new TweetExplorer();

if ( false !== $TweetExplorer->return )
	echo $TweetExplorer->return;

die;