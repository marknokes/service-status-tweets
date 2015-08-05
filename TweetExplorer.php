<?php

class TweetExplorer
{
	private $feed = "";

	private $outage_hashtag = "";

	private $issue_hashtag = "";

	private $resolved_hashtag = ""; 
	
	private $outage_areas = array();

	private $since = "";

	private $tw;

	private $tweets;

	public $return = false;
	
	public function __construct()
	{
		$this->set_access_control_header();

		require_once("twitter/TwitterAPIExchange.php");

		if ( false === $this->set_local_vars() )
			return false;

		$this->get_twitter();

		$this->get_tweets();

		$this->parse_tweets();
	}

	private function set_access_control_header()
	{
		$allowed = array(
			'localhost', // Remove if in production
			'uco.edu',
			'preview.uco.local',
			'wcms.uco.edu',
			'wcmstest.uco.edu',
			'lonotest.uco.local'
		);

		$origin_parts = isset( $_SERVER['HTTP_ORIGIN'] ) ? parse_url( $_SERVER['HTTP_ORIGIN'] ) : array('host' => '');

		$origin_host = str_replace("www.", "", $origin_parts['host'] );

		if ( in_array( $origin_host, $allowed ) )
			header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'] );
		else
			die;
	}

	private function set_local_vars()
	{
		if ( !isset(
				$_POST['outage_areas'],
				$_POST['outage_hashtag'],
				$_POST['issue_hashtag'],
				$_POST['resolved_hashtag'],
				$_POST['feed']
			) )
			return false;

		$this->outage_areas = $_POST['outage_areas'];

		$this->feed = $_POST['feed'];

		$this->outage_hashtag = $_POST['outage_hashtag'];

		$this->issue_hashtag = $_POST['issue_hashtag'];

		$this->resolved_hashtag = $_POST['resolved_hashtag'];
		
		$this->since = date( "Y-m-d", strtotime( "-1 day" ) );
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

	private function get_tweets()
	{
		$get = "?" . http_build_query( array(
			// Example: (#outage OR #issue) AND (#network OR #phones OR #banner) from:@UCOGeeks since: 2015-07-30
			"q" => "(#" . $this->outage_hashtag . " OR " . "#". $this->issue_hashtag .") AND (#" . implode( " OR #", $this->outage_areas ) . ") " . "from:@" . $this->feed . " since:" . $this->since,
			// I think we should account for at least one outage tweet and one resolved tweet for each area above...just in case, right?
			"count" => sizeof( $this->outage_areas ) * 2,
		) );

		//$tweets = $this->tw->setGetfield( $get )->buildOauth( "https://api.twitter.com/1.1/search/tweets.json", "GET")->performRequest();
		include"demo-data.php";
		$this->tweets = json_decode( $tweets );

		return;
	}

	private function parse_tweets()
	{
		if ( $this->tweets && isset( $this->tweets->statuses ) )
		{
			$problems = array(
				'issues' => array(),
				'outages' => array()
			);

			$issue_areas = array();

			$outage_areas = array();

			$resolved_areas = array();

			foreach( $this->tweets->statuses as $tweet )
			{
				$hashtags = array();

				foreach ( $tweet->entities->hashtags as $obj )
					$hashtags[] = $obj->text;

				// Only return hashtags available in $this->outage_areas..in case the tweet has some unrelated ones.
				$areas = array_intersect( $this->outage_areas, $hashtags );

				if ( in_array( $this->resolved_hashtag, $hashtags ) )
				{
					// save an array of resolved areas so we can remove them from $problems later
					$resolved_areas = array_merge( $resolved_areas, $areas );
					// skip this tweet as it contains the resolved hashtag
					continue;
				}
				// Tweets without the resolved hashtag are saved in $problems.
				if ( in_array( $this->issue_hashtag, $hashtags ) )
					$problems['issues'] = array_merge( $problems['issues'], $areas );
				if( in_array( $this->outage_hashtag, $hashtags ) )
					$problems['outages'] = array_merge( $problems['outages'], $areas );

			}

			foreach( $problems as $key => &$value )
			{
				// remove duplicates
				$value = array_unique( $value );
				// Remove from $problems those areas whch are saved in $resolved_areas.
				// Since we don't want to have to go back and add #resolved to each tweet about the issue/outage
				// we should globally mark it resolved for the last calendar day...I think.
				foreach ( $value as $subkey => $area )
					if ( in_array( $area, $resolved_areas ) )
						unset( $problems[ $key ][ $subkey ] );
			}

			if ( sizeof( $problems['issues'] ) > 0 || sizeof( $problems['outages'] ) > 0 )
				// make a unique array of the issues and set $this->return to the json_encoded value
				$this->return = json_encode( $problems );
		}

		return;
	}
}

$TweetExplorer = new TweetExplorer();

if ( false !== $TweetExplorer->return )
	echo $TweetExplorer->return;

die;