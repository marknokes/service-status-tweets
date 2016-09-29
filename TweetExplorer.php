<?php

class TweetExplorer
{
	/* EDIT THESE VARS */

	/*
	* Interval of time between api calls
	* @var int
	*/
	private $run_interval = 1800; // 30 min

	/*
	* List of allowed domains. Domains in this list will be allowed via $this->set_access_control_header()
	* @var array
	*/
	private $allowed_domains = array(
            'localhost', // Remove if in production
            'domain.com',
        );

	/*
	* Twitter oauth credentials
	* @var array
	*/
	private $twitter_creds = array(
            'oauth_access_token'        => 'REPLACE_ME',
            'oauth_access_token_secret' => 'REPLACE_ME',
            'consumer_key'              => 'REPLACE_ME',
            'consumer_secret'           => 'REPLACE_ME'
        );

	/*
	* Full computer path to run log file. Default is current directory.
	* @var str
	*/
	private $log = "log.txt";

	/*
	* Full computer path to cache file. Default is current directory.
	* @var str
	*/
	private $json = "tweets.json";

	/* STOP EDITING */

	private $feed = "",
			$outage_hashtag = "",
			$issue_hashtag = "",
			$resolved_hashtag = "",
			$since = "";
	
	private $outage_areas = array();

	private $tw,
			$tweets;

	public $return = false;

	private $last_run = 0;
	
	public function __construct()
	{
		$this->set_access_control_header();

		$this->do_run = $this->do_run();

        if( file_exists( $this->json ) && false === $this->do_run ) {

        	$this->return = file_get_contents( $this->json );

        	return;
        }

		require_once("twitter/TwitterAPIExchange.php");

		if ( false === $this->set_local_vars() )
			return false;

		$this->get_twitter();

		$this->get_tweets();

		$this->log_run_time();

		$this->parse_tweets();
	}

	/*
	* Sets the Access-Control-Allow-Origin header for the current HTTP_ORIGIN
	* if it's in the list of $this->allowed_domains
	* 
	* @return null
	*/
	private function set_access_control_header()
	{

		$origin_parts = isset( $_SERVER['HTTP_ORIGIN'] ) ? parse_url( $_SERVER['HTTP_ORIGIN'] ) : array('host' => '');

		$origin_host = str_replace("www.", "", $origin_parts['host'] );

		if ( in_array( $origin_host, $this->allowed_domains ) )
			header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'] );
		else
			die;
	}

	/*
	* Logs a current unix timestamp to $this->log
	* 
	* @return null
	*/
	private function log_run_time()
	{
		if ( !file_put_contents( $this->log , time() ) )

		return;
	}

	/*
	* Determine if we should make an api call, or wait the specified $this->run_interval
	* 
	* @return bool True if it's time or if log doesn't exist (first run), false otherwise
	*/
	private function do_run()
	{
		if ( !file_exists( $this->log ) )
		{
			$this->log_run_time();

			return true;
		}

		$timestamp = @file_get_contents( $this->log );

		$this->last_run = $timestamp ? intval( $timestamp ) : 0;

		if( 0 !== $this->last_run && time() >= ( $this->last_run + $this->run_interval ) )

			return true;

		return false;
	}

	/*
	* Set class vars from $_POST'ed data
	* 
	* @return null
	*/
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

	/*
	* Create twitter object
	* 
	* @return null
	*/
	private function get_twitter()
	{
		$this->tw = new TwitterAPIExchange( $this->twitter_creds );

		return;
	}

	/*
	* Build and format a query for the twitter api. Make the api call. 
	* Set $this->tweets to an array of tweets that match query.
	* 
	* @return null
	*/
	private function get_tweets()
	{
		$get = "?" . http_build_query( array(
			// Example: (#outage OR #issue) AND (#network OR #phones OR #banner) from:@UCOGeeks since: 2015-07-30
			"q" => "(#" . $this->outage_hashtag . " OR " . "#". $this->issue_hashtag .") AND (#" . implode( " OR #", $this->outage_areas ) . ") " . "from:@" . $this->feed . " since:" . $this->since,
			// I think we should account for at least one outage tweet and one resolved tweet for each area above...just in case, right?
			"count" => sizeof( $this->outage_areas ) * 2,
		) );

		$tweets = $this->tw->setGetfield( $get )->buildOauth( "https://api.twitter.com/1.1/search/tweets.json", "GET")->performRequest();

		$this->tweets = json_decode( $tweets );

		return;
	}

	/*
	* Sort $this->tweets into array of issues and outages. If a tweet contains the $this->resolved_hashtag, remove from the array.
	* 
	* @return null
	*/
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

			$problems['run_data'] = array(
				'last_run' => $this->last_run
			);

			// make a unique array of the issues and set $this->return to the json_encoded value
			$encoded = json_encode( $problems );
			// the return value
			$this->return = $encoded;
			// the return value saved on the server to prevent hitting the api on every page load
			file_put_contents( $this->json, $encoded );
		}

		return;
	}
}

$TweetExplorer = new TweetExplorer();

if ( false !== $TweetExplorer->return )

	echo $TweetExplorer->return;

die;