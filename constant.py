import urllib
import urllib2
from xml.dom import minidom
 
cc_url = 'https://api.constantcontact.com/ws/customers/{alexcastro}/contacts' cc_api_key = '{82e71781-b465-4723-ae11-59d5952ba41a}'
cc_username = '{alexcastro}'
cc_password = '{9774564}'
 
TROPO_URL = 'http://api.tropo.com/1.0/sessions?action=create&token={The Outbound Messaging Token goes here}'
SMS_MSG = 'Wishing you a happy holidays! - From the Tropo Team'
 
#CC uses Basic Authentication for their API. This code sets up a password manager to take care of that for us
passman = urllib2.HTTPPasswordMgrWithDefaultRealm()
passman.add_password(None, cc_url, cc_api_key + '%' + cc_username, cc_password)
authhandler = urllib2.HTTPBasicAuthHandler(passman)
opener = urllib2.build_opener(authhandler) urllib2.install_opener(opener)
 
pagehandle = urllib2.urlopen(cc_url)
data = pagehandle.read()
dom = minidom.parseString(data)
entry_node = dom.getElementsByTagName("entry")
for entry in entry_node:
    id = entry.getElementsByTagName("id")
    id = id[0].firstChild.nodeValue
    pagehandle = urllib2.urlopen(cc_url + '/' + id.rsplit('/', 1)[1])
    data = pagehandle.read()
    dom = minidom.parseString(data)
    contact_node = dom.getElementsByTagName("Contact")
    for contact in contact_node:
        phone_num = contact.getElementsByTagName("HomePhone")
        phone_num = phone_num[0].firstChild.nodeValue
        url = TROPO_URL + '&numberToDial=' + phone_num + '&msg=' + urllib.quote(SMS_MSG)
        page = urllib2.urlopen(url)
        data = page.read()