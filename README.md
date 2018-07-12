
# Info
- Trevorbot automatically uploads media from reddit and instagram to a specified instagram account
## Usage
- Please credit @trevorbot420 in your bio if you use this bot

# Installation
- PHP 5.5 < is needed - https://secure.php.net/downloads.php
- Unofficial instagram API is needed - https://github.com/mgp25/Instagram-API
- If using the reddit scraper, python three is needed - https://www.python.org/downloads/
- If using the reddit scraper, PRAW package must be added - https://github.com/praw-dev/praw

# Setup
- For a quick setup -
- Change the 'username', 'password' and 'folderPath' variables near the top of - upload.php, getPicsInstagram.php, and getPicsReddit.py
- The 'folderPath' variable should be the projects directory
## Extra Setup
- "usr/bin/php full/path/to/upload.php" is the only command which needs to be run. This can be automated using a cronjob - https://superuser.com/questions/1144910/how-do-i-setup-a-cron-job-on-os-x-to-run-a-curl-command-at-a-specific-time-every
- When using cron, the output of the scripts will be put in .txt files a debug folder
- Media queued for upload will be put in a 'media' folder

# Creds
- PRAW - python wrapper for the reddit API
- mgp25/Instagram-API - unofficial API

# Legal
- This bot uses an unofficial API not explicitly approved, endorsed, or affiliated by or to Instagram
- You must not use this bot for spam, advertising or marketing
