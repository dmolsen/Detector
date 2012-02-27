<?php

// an example of getting a copy of the original ua-parser file. maybe you'd want to run this with a cron job or something...
// NOTE: it will currently overwrite some custom changes i made to the user_agents_regex.yaml file!
require("UAParser.php");
UA::get();

?>