#!/usr/bin/env python
# -*- coding: utf-8 -*-
# Written as part of https://www.scrapehero.com/how-to-scrape-amazon-product-reviews-using-python/		
import json
import requests
import urllib
import re
import shutil

def getInfo(url):
    file = open("list.txt", "w") 
    while True:
        print(url)
        file.write(url+'\n')
        requests.packages.urllib3.disable_warnings(category=requests.packages.urllib3.exceptions.InsecureRequestWarning)
        page = requests.get(url, verify=False)
        page_response = page.text
        
        regex = r"\(scan: ([^\)]*)\)\n[^\n]*\n  <a href=\"([^\"]*)\">Download</a>"
        matches = re.findall(regex, page_response, re.MULTILINE)
        #print(matches)
        #download_file(matches[0][1], matches[0][0])
        
        regex = r"<a class=\"float-right\" href=\"([^\"]*)\"> Next"
        matches = re.findall(regex, page_response, re.MULTILINE)
        #print(matches)
        if len(matches) == 0:
            break
        else:
            url = 'https://szukajwarchiwach.pl' + matches[0]
    file.close() 
    
def download_file(url, local_filename):
    url = 'https://szukajwarchiwach.pl' + url
    #local_filename = url.split('/')[-1]
    requests.packages.urllib3.disable_warnings(category=requests.packages.urllib3.exceptions.InsecureRequestWarning)
    r = requests.get(url, stream=True, verify=False)
    with open(local_filename, 'wb') as f:
        shutil.copyfileobj(r.raw, f)
    return local_filename

getInfo('https://szukajwarchiwach.pl/53/1318/0/-/24/skan/full/4tU7herNt3Ia-i1Xoweoug')
