
# Info
- Trevorbot uploads images liked by an instagram account on reddit/instagram and uploads them to that account
- See @trevorbot420 as an example of usage
- Please credit @trevorbot420 in your bio if you use this bot

# Installation
- PHP 5.5 (default php 7) or higher is needed - https://secure.php.net/downloads.php
- Unofficial instagram API is needed - https://github.com/mgp25/Instagram-API
- If using the reddit scraper, python 3.0 or higher is needed - https://www.python.org/downloads/
- If using the reddit scraper, the PRAW package must be added - https://github.com/praw-dev/praw

# Setup
- Change the 'username', 'password' and 'folderPath' variables near the top of 'upload.php', 'getPicsInstagram.php', and 'getPicsReddit.py'
- The 'folderPath' variable should be the full path to the project's directory
## Extra Setup
- "usr/bin/php full/path/to/upload.php" is the only command which needs to be run. This can be automated using a cronjob or launchd - https://superuser.com/questions/1144910/how-do-i-setup-a-cron-job-on-os-x-to-run-a-curl-command-at-a-specific-time-every
- When using cron (or launchd), the output of the scripts will be put in .txt files in a debug folder
- Change the '$debug' variables at the top of 'getPicsInstagram.php' and 'upload.php' to 'true' for a more in depth debug output
- Media queued for upload will be put in a 'media' folder
- Note that automatic captions are created crediting the source of the media uploaded

# Creds
- PRAW - python wrapper for the reddit API
- mgp25/Instagram-API - unofficial API

# Legal
- This bot uses an unofficial API not explicitly approved, endorsed, or affiliated by or to Instagram
- You must not use this bot for spam, advertising or marketing
