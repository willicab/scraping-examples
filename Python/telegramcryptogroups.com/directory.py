import json
import requests
import urllib
import re
import shutil
from openpyxl import load_workbook
try:
    from HTMLParser import HTMLParser
except ImportError:
    from html.parser import HTMLParser
h = HTMLParser()

wb = load_workbook(filename = '../base.xlsx')
ws1 = wb.active
ws1['A1'] = 'Group name'
ws1['B1'] = 'Description'
ws1['C1'] = 'Telegram link'
ws1['D1'] = 'Telegram username'
ws1['E1'] = 'No. of members'

index = 2
for i in range(1, 2000):
    url = 'https://telegramcryptogroups.com/telegram_groups/' + str(i)
    print (url)
    requests.packages.urllib3.disable_warnings(category=requests.packages.urllib3.exceptions.InsecureRequestWarning)
    page = requests.get(url, verify=False)
    page_response = page.text
    
    regex = r'(Couldn&#39;t find TelegramGroup with &#39;id&#39;=)'
    matches = re.findall(regex, page_response, re.MULTILINE)
    if len(matches) > 0:
        print 'Element ' + str(i) + ' not exist'
        continue
    
    regex = r'<h3 class="name font-weight-semibold">([^<]*)'
    matches = re.findall(regex, page_response, re.MULTILINE)
    name = h.unescape(matches[0].strip())
    
    regex = r'<blockquote>\n[ ]*<p>\n[ ]*([^\n]*)'
    matches = re.findall(regex, page_response, re.MULTILINE)
    description = re.sub("<.*?>", " ", h.unescape(matches[0].strip()))
    
    regex = r'class="account">([^<]*)'
    matches = re.findall(regex, page_response, re.MULTILINE)
    link = matches[0].strip()

    regex = r'class="account">http[s]*://t\.me/([^<]*)'
    matches = re.findall(regex, page_response, re.MULTILINE)
    username = matches[0].strip()
    if username[:9] == 'joinchat/':
        username = ''

    regex = r'<h4 class="count">([^<]*)'
    matches = re.findall(regex, page_response, re.MULTILINE)
    members = matches[0].strip()

    ws1['A'+str(index)] = name
    ws1['B'+str(index)] = description
    ws1['C'+str(index)] = link
    ws1['D'+str(index)] = username
    ws1['E'+str(index)] = members
    
    index = index + 1

wb.save(filename = 'groups.xlsx')
