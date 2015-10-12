# service-status-tweets
Searches specified twitter account for specified hashtags and changes status of specified systems

You will need to edit some variables to get this up and running:

[script.js](../master/script.js)
```javascript
    /* Set up vars */
    var appUrl = "", // Ex: If on a different domain, https://www.domain.com/apps/service-status-tweets/'
        outageHashtag = "outage",
        issueHashtag = "issue",
        resolvedHashtag = "resolved",
        feed = "REPLACE_ME",
```
[TweetExplorer.php](../master/TweetExplorer.php)
```php
    /* EDIT THESE VARS */
  	private $allowed_domains = array(
  				'localhost', // Remove if in production
  				'domain.com',
  			);
  
  	private $twitter_creds = array(
  				'oauth_access_token' 		=> 'REPLACE_ME',
  				'oauth_access_token_secret' => 'REPLACE_ME',
  				'consumer_key' 				=> 'REPLACE_ME',
  				'consumer_secret' 			=> 'REPLACE_ME'
  			);
```

Take a look at index.php for example usage.
* The script src should be the URL to script.js
* The items inside div#service-status-container will dynamically generate the buttons and should be comma separated.

[index.php](../master/index.php)
```html
  <div id="service-status-container" style="visibility:hidden">network,email,phones,banner</div>
  <script type="text/javascript" src="script.js"></script>
```
