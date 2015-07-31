<html>
<head>
	<title>Twitter Test</title>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
	<div id="service-status-container">
		<ul id="service-status">
			<!-- id's need to match the outage areas in the file that process the request -->
			<li id="network" class="green"><span class="icon">&nbsp;</span><a href="https://twitter.com/search?q=%23outage%20AND%20%23network%20from%3A%40UCOGeeks%20since%3A">Network</a></li>
			<li id="email" class="green"><span class="icon">&nbsp;</span><a href="https://twitter.com/search?q=%23outage%20AND%20%23email%20from%3A%40UCOGeeks%20since%3A">Email</a></li>
			<li id="phones" class="green"><span class="icon">&nbsp;</span><a href="https://twitter.com/search?q=%23outage%20AND%20%23phones%20from%3A%40UCOGeeks%20since%3A">Phones</a></li>
			<li id="banner" class="green"><span class="icon">&nbsp;</span><a href="https://twitter.com/search?q=%23outage%20AND%20%23banner%20from%3A%40UCOGeeks%20since%3A">Banner</a></li>
		</ul>
	</div>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script type="text/javascript" src="script.js"></script>
</html>