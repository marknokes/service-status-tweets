## Donation
If you find this plugin useful, please consider making a donation. Thank you!

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=HQFGGDAGHHM22)

# service-status-tweets

Searches specified twitter account for specified hashtags and changes status of specified systems. For example, you could set up the following buttons: email, phones, network.
Then you could create a tweet with the hashtags #outage #email, and the widget would update itself dynamically.


![alt text](https://github.com/marknokes/service-status-tweets/blob/master/images/screenshot.PNG "Screenshot of service-status-tweets")

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
