#!/usr/bin/env python
# -*- coding: utf-8 -*-
# Written as part of https://www.scrapehero.com/how-to-scrape-amazon-product-reviews-using-python/		
#from lxml import html  
import json
import requests
import json,re
from dateutil import parser as dateparser
from time import sleep
from html import unescape
from openpyxl import load_workbook

def getInfo(url):
    info = [];
    regex = r"\/([^_]*)_rect\/([^_]*)_zm"
    matches = re.findall(regex, url, re.MULTILINE)
    r = matches[0][0].replace('.','').split(',')
    rect = r[3]+','+r[2]+','+r[1]+','+r[0]
    zoom = (matches[0][1])
    url= 'https://www.zillow.com/search/GetResults.htm?spt=homes&status=000010&lt=000000&ht=111111&pr=,&mp=,&bd=0%2C&ba=0%2C&sf=,&lot=0%2C&yr=,&singlestory=0&hoa=0%2C&pho=0&pets=0&parking=0&laundry=0&income-restricted=0&fr-bldg=0&condo-bldg=0&furnished-apartments=0&cheap-apartments=0&studio-apartments=0&pnd=0&red=0&zso=0&days=any&ds=all&pmf=0&pf=0&sch=100111&zoom='+zoom+'&rect='+rect+'&sort=days&search=maplist&rid=40326&rt=6&listright=true&isMapSearch=true&zoom='+zoom+'&p='
    i = 1
    
    headers = {
        'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Host': 'www.zillow.com',
        'DNT': '1'
    }
    while True:
        page = requests.get(url+str(i), headers = headers)
        #url = 'http://localhost/zillow.json'
        #page = requests.get(url, headers = headers)
        page_response = page.text
        
        j = json.loads(page_response)
        data = unescape(j['list']['listHTML'].replace('\\', ''))
        
        regex = r"<span itemprop=\"streetAddress\">([^<]*)[^\"]*\"addressLocality\">([^<]*)[^\"]*\"addressRegion\">([^<]*)[^\"]*\"postalCode\" class=\"hide\">([^<]*).+?(?=zsg-photo-card-price)zsg-photo-card-price\">([^<]*)<\/span><span class=\"zsg-photo-card-info\">([^<]*)<span class=\'interpunct\'>&middot;<\/span>([^<]*)<span class=\'interpunct\'>&middot;<\/span>([^<]*)<\/span><\/p>"
        matches = re.findall(regex, data, re.MULTILINE)
        info = info + matches
        print('page ' + str(i) + ': ' + str(len(matches)))
        i = i + 1        
        if len(matches) == 0:
            break
            
    print(str(len(info)) + ' pages')
    
    wb = load_workbook(filename = '../base.xlsx')
    ws1 = wb.active
    ws1['A1'] = 'Price'
    ws1['B1'] = 'Type of apartment'
    ws1['C1'] = 'Bathrooms'
    ws1['D1'] = 'Square footage'
    ws1['E1'] = 'Address'
    for row in range(2, (len(info) + 1)):
        ws1['A' + str(row)] = info[row - 1][4].strip()
        ws1['B' + str(row)] = info[row - 1][5].strip()
        ws1['C' + str(row)] = info[row - 1][6].strip()
        ws1['D' + str(row)] = info[row - 1][7].strip()
        ws1['E' + str(row)] = info[row - 1][0] +', '+ info[row - 1][1] +', '+ info[row - 1][2] +' ('+ info[row - 1][3] +')'
    wb.save(filename = 'zillow.xlsx')
    
    
if __name__ == '__main__':
    getInfo('https://www.zillow.com/homes/for_rent/Phoenix-AZ/40326_rid/33.480243,-112.040248,33.438711,-112.122903_rect/12_zm/')
