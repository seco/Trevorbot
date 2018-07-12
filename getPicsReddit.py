import sys
import os
import praw
import requests
import json
from unidecode import unidecode
#from subprocess import call

def downloadfile(name,url):
    r=requests.get(url)
    print("****Connected****")
    f=open(name,'wb');
    print("Donloading.....")
    for chunk in r.iter_content(chunk_size=255):
        if chunk: # filter out keep-alive new chunks
            f.write(chunk)
    print("Done")
    f.close()

username = 'xxxxxxxx'
password = 'xxxxxxxx'

folderPath = ''

reddit = praw.Reddit(client_id='7SSTPtjQngFGyA',
                     client_secret='O-qmkSkaLLdug8agGgtUE0Tc7WY',
                     user_agent='script:7SSTPtjQngFGyA:v0.0.2 (meme scraper created by /u/memescraper420)',
                     username=username,
                     password=password)
redditor = reddit.redditor(username)


count = 0
for media in reddit.redditor(username).upvoted(limit=16):
    count = count + 1

    mediaId = (media.id[0:50])
    mediaUrl = (media.url[0:50])
    print(mediaUrl)
    mediaData = requests.get(mediaUrl)
    print(mediaData)

    submission = reddit.submission(id=mediaId)
    title = submission.title
    op = submission.author
    submission.clear_vote()

    if mediaData.status_code == 200:
        urlArray = mediaUrl.split(".")
        if urlArray[-1] == "jpg":
            #normal image
            try:
                with open(folderPath + "/media/reddit" + str(count) + ".jpg", 'wb') as media:
                    media.write(mediaData.content)
            except IOError as e:
                print("Couldn't open or write to file - image error. (%s)." % e)

        if urlArray[-1] == "gifv":
            #gif hosted on imgur
            mediaUrl = mediaUrl.replace("gifv", "mp4")
            downloadfile(folderPath + "/media/reddit" + str(count) + ".mp4",mediaUrl)
        # create custom caption file
        try:
            with open(folderPath + "/media/captionreddit" + str(count) + ".txt", 'w') as caption:
                print(f"Meme from Reddit. " + str(title) +  " - from u/" + str(op) + ".", file=caption)
        except IOError as e:
            print("Couldn't open or write to file - caption write error. (%s)." % e)

            #kinda broken also i cba to do it cus its cpu/time expensive and few memes are gonna be reddit videos
        # if "v" in urlArray[0]:
            #reddit video
            #output = os.system("youtube-dl " + mediaUrl)
            #print("shell youtube-dl output: " + str(output))
            #now move to media folder
            #splitArray = mediaUrl.split("_")
            #videoName = splitArray[-1] + "-" + splitArray[-1] + ".mp4"
            #os.system("mv /Users/luca/Desktop/bots/trevorbot/" + videoName + "/Users/luca/Desktop/bots/trevorbot/media")
